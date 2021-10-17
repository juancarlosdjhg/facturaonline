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
use FacturaScripts\Dinamic\Model\ProductPack;
use FacturaScripts\Dinamic\Model\CodeModel;

/**
 * List of product variants includes into a pack
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ProductPackLine extends ModelClass
{
    use ModelTrait;

    /**
     * Primary key.
     *
     * @var int
     */
    public $id;

    /**
     * Link to the product pack model.
     *
     * @var int
     */
    public $idpack;

    /**
     * Quantity of variant child
     *
     * @var double
     */
    public $quantity;

    /**
     * Link to the variant product model.
     *
     * @var int
     */
    public $reference;

    /**
     * Indicates if the product is mandatory
     *
     * @var boolean
     */
    public $required;

    /**
     * Display or print order
     *
     * @var integer
     */
    public $sortnum;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->quantity = 1;
        $this->required = false;
        $this->sortnum = 0;
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
        new ProductPack();
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
        return 'productopack_packlines';
    }

    /**
     * Returns true if there are no errors in the values of the model properties.
     * It runs inside the save method.
     *
     * @return bool
     */
    public function test()
    {
        if ($this->reference == $this->parentReference()) {
            self::toolBox()->i18nLog()->warning('error-same-reference-parent');
            return false;
        }

        if (empty($this->id) && $this->countReference() > 0) {
            self::toolBox()->i18nLog()->warning('error-reference-exists');
            return false;
        }

        if ($this->quantity == 0) {
            self::toolBox()->i18nLog()->warning('error-quantity-non-zero');
            return false;
        }

        return parent::test();
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
        $list = 'EditProductPack?code=' . $this->idpack . '&active=List';
        return parent::url($type, $list);
    }

    /**
     * Return count number for reference into pack
     *
     * @return int
     */
    private function countReference()
    {
        $where = [
            new DataBaseWhere('idpack', $this->idpack),
            new DataBaseWhere('reference', $this->reference)
        ];
        $data = CodeModel::all(self::tableName(), 'reference', 'reference', false, $where);
        return count($data);
    }

    /**
     * Return product parent reference
     *
     * @return string
     */
    private function parentReference()
    {
        $model = new CodeModel();
        $result = $model->getDescription('productopack_pack', 'id', $this->idpack, 'reference');
        return $result;
    }
}
