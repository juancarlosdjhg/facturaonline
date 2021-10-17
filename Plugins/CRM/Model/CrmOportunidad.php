<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 * Description of CrmOportunidad
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmOportunidad extends Base\ModelOnChangeClass
{

    use Base\ModelTrait;

    /**
     *
     * @var string
     */
    public $codagente;

    /**
     *
     * @var string
     */
    public $coddivisa;

    /**
     *
     * @var string
     */
    public $descripcion;

    /**
     *
     * @var bool
     */
    public $editable;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var string
     */
    public $fechamod;

    /**
     *
     * @var string
     */
    public $fecha_cierre;

    /**
     *
     * @var string
     */
    public $hora;

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $idcontacto;

    /**
     *
     * @var int
     */
    public $idestado;

    /**
     *
     * @var int
     */
    public $idfuente;

    /**
     *
     * @var int
     */
    public $idinteres;

    /**
     *
     * @var int
     */
    public $idpresupuesto;

    /**
     *
     * @var float
     */
    public $neto;

    /**
     *
     * @var float
     */
    public $netoeuros;

    /**
     *
     * @var string
     */
    public $nick;

    /**
     *
     * @var string
     */
    public $observaciones;

    /**
     *
     * @var bool
     */
    public $rechazado;

    /**
     *
     * @var float
     */
    public $tasaconv;

    public function clear()
    {
        parent::clear();
        $this->fecha = \date(self::DATE_STYLE);
        $this->fechamod = \date(self::DATETIME_STYLE);
        $this->hora = \date(self::HOUR_STYLE);
        $this->neto = 0.0;
        $this->netoeuros = 0.0;
        $this->tasaconv = 1.0;

        /// set estado
        $estadoModel = new CrmOportunidadEstado();
        foreach ($estadoModel->all([], [], 0, 0) as $estado) {
            if ($estado->predeterminado) {
                $this->editable = $estado->editable;
                $this->idestado = $estado->id;
                $this->rechazado = $estado->rechazado;
            }
        }
    }

    /**
     * 
     * @return Contacto
     */
    public function getContacto()
    {
        $contact = new Contacto();
        $contact->loadFromCode($this->idcontacto);
        return $contact;
    }

    /**
     * 
     * @return CrmOportunidadEstado
     */
    public function getEstado()
    {
        $estado = new CrmOportunidadEstado();
        $estado->loadFromCode($this->idestado);
        return $estado;
    }

    /**
     * 
     * @return CrmNota[]
     */
    public function getNotas()
    {
        $noteModel = new CrmNota();
        $where = [new DataBaseWhere('idoportunidad', $this->id)];
        $order = ['fecha' => 'DESC', 'hora' => 'DESC'];
        $notes = $noteModel->all($where, $order, 0, 0);

        $estimation = $this->getPresupuesto();
        if ($estimation->exists()) {
            $where2 = [
                new DataBaseWhere('idoportunidad', null, 'IS'),
                new DataBaseWhere('documento', $estimation->codigo),
                new DataBaseWhere('tipodocumento', 'presupuesto de cliente')
            ];
            foreach ($noteModel->all($where2, $order, 0, 0) as $note) {
                $notes[] = $note;
            }
        }

        return $notes;
    }

    /**
     * 
     * @return PresupuestoCliente
     */
    public function getPresupuesto()
    {
        $presupuesto = new PresupuestoCliente();
        $presupuesto->loadFromCode($this->idpresupuesto);
        return $presupuesto;
    }

    /**
     * 
     * @return string
     */
    public function install()
    {
        /// needed dependency
        new CrmOportunidadEstado();
        new PresupuestoCliente();

        return parent::install();
    }

    /**
     * 
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     * 
     * @return string
     */
    public function primaryDescriptionColumn(): string
    {
        return 'id';
    }

    /**
     * 
     * @return bool
     */
    public function save()
    {
        if (false === parent::save()) {
            return false;
        }

        /// save contact interest
        if ($this->idinteres && $this->idcontacto) {
            $interesContacto = new CrmInteresContacto();
            $where = [
                new DataBaseWhere('idcontacto', $this->idcontacto),
                new DataBaseWhere('idinteres', $this->idinteres)
            ];
            if (false === $interesContacto->loadFromCode('', $where)) {
                $interesContacto->idcontacto = $this->idcontacto;
                $interesContacto->idinteres = $this->idinteres;
                $interesContacto->save();
            }
        }

        return true;
    }

    /**
     * 
     * @return string
     */
    public static function tableName(): string
    {
        return 'crm_oportunidades';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
        $utils = $this->toolBox()->utils();
        $this->descripcion = $utils->noHtml($this->descripcion);
        $this->observaciones = $utils->noHtml($this->observaciones);

        return parent::test();
    }

    /**
     * 
     * @param string $field
     *
     * @return bool
     */
    protected function onChange($field)
    {
        if ($field === 'idestado') {
            $estado = $this->getEstado();
            $this->editable = $estado->editable;
            $this->idestado = $estado->id;
            $this->rechazado = $estado->rechazado;
            $this->fecha_cierre = $estado->editable ? null : \date(self::DATE_STYLE);
        }

        return parent::onChange($field);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveUpdate(array $values = [])
    {
        $this->fechamod = \date(self::DATETIME_STYLE);
        return parent::saveUpdate($values);
    }

    /**
     * 
     * @param array $fields
     */
    protected function setPreviousData(array $fields = [])
    {
        $more = ['idestado'];
        parent::setPreviousData(array_merge($more, $fields));
    }
}
