<?php
/**
 * This file is part of ProductoPack plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ProductoPack    Copyright (C) 2019 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ProductoPack\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\Variante;

/**
 * Product pack for a variant product
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ProductPack extends ModelClass
{

    use ModelTrait;

    /**
     * Primary key.
     *
     * @var int
     */
    public $id;

    /**
     * Link to the product model.
     *
     * @var int
     */
    public $idproduct;

    /**
     * Human description for pack
     *
     * @var string
     */
    public $name;

    /**
     * Link to the variant model.
     *
     * @var string
     */
    public $reference;

    /**
     * Get the products from a pack.
     *
     * @return ProductPackLine[]
     */
    public function getLines()
    {
        $packLine = new ProductPackLine();
        $where = [new DataBaseWhere('idpack', $this->id)];
        return $packLine->all($where, ['sortnum' => 'ASC'], 0, 0);
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
        new Variante();
        return parent::install();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'id';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'productopack_pack';
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
    {
        $list = 'EditProducto?code=' . $this->idproduct . '&active=List';
        return parent::url($type, $list);
    }
}
