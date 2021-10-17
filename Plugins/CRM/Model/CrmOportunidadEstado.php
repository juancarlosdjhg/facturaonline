<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Dinamic\Model\CodeModel;

/**
 * Description of CrmOportunidadEstado
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmOportunidadEstado extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     *
     * @var bool
     */
    public $editable;

    /**
     *
     * @var string
     */
    public $icon;

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
    public $orden;

    /**
     *
     * @var bool
     */
    public $predeterminado;

    /**
     *
     * @var bool
     */
    public $rechazado;

    public function clear()
    {
        parent::clear();
        $this->editable = true;
        $this->icon = 'fas fa-tag';
        $this->orden = 100;
        $this->predeterminado = false;
        $this->rechazado = false;
    }

    /**
     * Allows to use this model as source in CodeModel special model.
     * 
     * @param string $fieldCode
     * 
     * @return CodeModel[]
     */
    public function codeModelAll(string $fieldCode = '')
    {
        $results = [];
        $field = empty($fieldCode) ? static::primaryColumn() : $fieldCode;

        $sql = 'SELECT DISTINCT ' . $field . ' AS code, ' . $this->primaryDescriptionColumn() . ' AS description, orden '
            . 'FROM ' . static::tableName() . ' ORDER BY orden ASC';
        foreach (self::$dataBase->selectLimit($sql, CodeModel::ALL_LIMIT) as $d) {
            $results[] = new CodeModel($d);
        }

        return $results;
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
        return 'crm_oportunidades_estados';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
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
    public function url(string $type = 'auto', string $list = 'ListCrmOportunidad?activetab=List'): string
    {
        return parent::url($type, $list);
    }
}
