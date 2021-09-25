<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\PlantillasPDF\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Dinamic\Model\Serie;

/**
 * Model to personalize the impresion of sales and buy documents.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class FormatoDocumento extends Base\ModelClass
{

    use Base\ModelTrait;

    const SETTINGS_NAME = 'plantillaspdf';

    /**
     * 
     * @var bool
     */
    public $autoaplicar;

    /**
     * Foreign key with series table
     *
     * @var string
     */
    public $codserie;

    /**
     * Color 1
     *
     * @var string
     */
    public $color1;

    /**
     *
     * @var string
     */
    public $footertext;

    /**
     *
     * @var bool
     */
    public $hidetotals;

    /**
     * Primary key
     *
     * @var int
     */
    public $id;

    /**
     * Foreign key with table business
     *
     * @var int
     */
    public $idempresa;

    /**
     *
     * @var int
     */
    public $idlogo;

    /**
     *
     * @var string
     */
    public $linecolalignments;

    /**
     *
     * @var string
     */
    public $linecols;

    /**
     *
     * @var string
     */
    public $linecoltypes;

    /**
     *
     * @var float
     */
    public $linesheight;

    /**
     *
     * @var string
     */
    public $nombre;

    /**
     *
     * @var string
     */
    public $orientation;

    /**
     *
     * @var string
     */
    public $size;

    /**
     *
     * @var string
     */
    public $texto;

    /**
     *
     * @var string
     */
    public $thankstext;

    /**
     *
     * @var string
     */
    public $thankstitle;

    /**
     *
     * @var string
     */
    public $tipodoc;

    /**
     *
     * @var string
     */
    public $titulo;

    public function clear()
    {
        parent::clear();
        $this->autoaplicar = false;
        $this->hidetotals = false;

        $appSettings = $this->toolBox()->appSettings();
        $this->texto = $appSettings->get(self::SETTINGS_NAME, 'endtext');

        $fields = [
            'color1', 'linecolalignments', 'linecols', 'linecoltypes', 'linesheight'
        ];
        foreach ($fields as $field) {
            $this->{$field} = $appSettings->get(self::SETTINGS_NAME, $field);
        }
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        /// needed dependencies
        new Serie();
        new Empresa();

        return parent::install();
    }

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn()
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
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'formatos_documentos';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
        $utils = $this->toolBox()->utils();
        $this->nombre = empty($this->nombre) ? $utils->noHtml($this->titulo) : $utils->noHtml($this->nombre);

        $fields = [
            'color1', 'footertext', 'linecolalignments', 'linecols',
            'linecoltypes', 'orientation', 'size', 'texto', 'thankstext',
            'thankstitle', 'titulo'
        ];
        foreach ($fields as $field) {
            $this->{$field} = $utils->noHtml($this->{$field});
        }

        if (empty($this->idempresa)) {
            $this->idempresa = $this->toolBox()->appSettings()->get('default', 'idempresa');
        }

        return parent::test();
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'AdminPlantillasPDF?activetab=List'): string
    {
        return parent::url($type, $list);
    }
}
