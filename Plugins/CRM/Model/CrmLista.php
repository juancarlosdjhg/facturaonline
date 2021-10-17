<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;

/**
 * Description of CrmLista
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmLista extends Base\ModelClass
{

    use Base\ModelTrait;

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
     * @var name
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
     * @return CrmListaContacto[]
     */
    public function getMembers()
    {
        $member = new CrmListaContacto();
        $where = [new DataBaseWhere('idlista', $this->primaryColumnValue())];
        return $member->all($where, [], 0, 0);
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
    public static function tableName(): string
    {
        return 'crm_listas';
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
        /// get the number of contacts in this list
        $member = new CrmListaContacto();
        $where = [new DataBaseWhere('idlista', $this->primaryColumnValue())];
        $this->numcontactos = $member->count($where);

        return parent::saveUpdate($values);
    }
}
