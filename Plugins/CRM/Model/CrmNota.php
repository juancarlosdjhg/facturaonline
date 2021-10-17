<?php
/**
 * Copyright (C) 2019-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Dinamic\Model\Contacto as DinContacto;

/**
 * Description of CrmNota
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmNota extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     *
     * @var bool
     */
    public $automatica;

    /**
     *
     * @var bool
     */
    public $avisar;

    /**
     *
     * @var string
     */
    public $documento;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var string
     */
    public $fechaaviso;

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
    public $iddocumento;

    /**
     *
     * @var int
     */
    public $idinteres;

    /**
     *
     * @var int
     */
    public $idoportunidad;

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
     * @var string
     */
    public $tipodocumento;

    public function clear()
    {
        parent::clear();
        $this->automatica = false;
        $this->avisar = false;
        $this->fecha = \date(self::DATE_STYLE);
    }

    /**
     * 
     * @return DinContacto
     */
    public function getContact()
    {
        $contact = new DinContacto();
        $contact->loadFromCode($this->idcontacto);
        return $contact;
    }

    /**
     * 
     * @return string
     */
    public function install()
    {
        /// needed dependencies
        new CrmInteres();
        new CrmOportunidad();

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
     * @return bool
     */
    public function save()
    {
        if (false === parent::save()) {
            return false;
        }

        /// save contact interest
        if ($this->idinteres) {
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
        return 'crm_notas';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
        $this->observaciones = $this->toolBox()->utils()->noHtml($this->observaciones);
        return parent::test();
    }
}
