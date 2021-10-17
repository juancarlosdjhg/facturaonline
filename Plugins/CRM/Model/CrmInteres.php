<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;

/**
 * Description of CrmInteres
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmInteres extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     *
     * @var string
     */
    public $descripcion;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var string
     */
    public $nombre;

    /**
     *
     * @var int
     */
    public $numcontactos;

    public function clear()
    {
        parent::clear();
        $this->fecha = \date(self::DATE_STYLE);
        $this->numcontactos = 0;
    }

    /**
     * 
     * @return CrmInteresContacto[]
     */
    public function getInteresteds()
    {
        $interested = new CrmInteresContacto();
        $where = [new DataBaseWhere('idinteres', $this->primaryColumnValue())];
        return $interested->all($where, [], 0, 0);
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
        return 'nombre';
    }

    /**
     * 
     * @return string
     */
    public static function tableName(): string
    {
        return 'crm_intereses';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
        $this->descripcion = $this->toolBox()->utils()->noHtml($this->descripcion);
        $this->nombre = $this->toolBox()->utils()->noHtml($this->nombre);
        return parent::test();
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'ListContacto?activetab=List'): string
    {
        return parent::url($type, $list);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveUpdate(array $values = [])
    {
        /// get the number of contacts with this interest
        $interested = new CrmInteresContacto();
        $where = [new DataBaseWhere('idinteres', $this->primaryColumnValue())];
        $this->numcontactos = $interested->count($where);

        return parent::saveUpdate($values);
    }
}
