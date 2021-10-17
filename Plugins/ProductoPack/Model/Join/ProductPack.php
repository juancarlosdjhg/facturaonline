<?php
/**
 * This file is part of ProductoPack plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ProductoPack    Copyright (C) 2019 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ProductoPack\Model\Join;

use FacturaScripts\Dinamic\Model\Base\JoinModel;
use FacturaScripts\Dinamic\Model\ProductPack as ProductPackModel;

/**
 * List of product pack. Model View.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ProductPack extends JoinModel
{

    /**
     * Constructor and class initializer.
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->setMasterModel(new ProductPackModel());
    }

    /**
     * List of tables required for the execution of the view.
     */
    protected function getTables(): array
    {
        return [
            'productopack_pack',
            'variantes'
        ];
    }

    /**
     * List of fields or columns to select clausule
     */
    protected function getFields(): array
    {
        return [
            'id' => 'productopack_pack.id',
            'idproduct' => 'productopack_pack.idproduct',
            'name' => 'productopack_pack.name',
            'reference' => 'productopack_pack.reference',
            'price' => 'variantes.precio',
            'idattribute1' => 'variantes.idatributovalor1',
            'idattribute2' => 'variantes.idatributovalor2',
            'nameattribute1' => 'attribute1.descripcion',
            'nameattribute2' => 'attribute2.descripcion',
            'items' => 'COALESCE((' . $this->getItemsSelect() . '), 0)',
            'priceitems' => 'COALESCE((' . $this->getPriceItems() . '), 0.00)'
        ];
    }

    /**
     * List of tables related to from clausule
     */
    protected function getSQLFrom(): string {
        return 'productopack_pack'
            . ' INNER JOIN variantes ON variantes.referencia = productopack_pack.reference'
            . ' LEFT JOIN atributos_valores attribute1 ON attribute1.id = variantes.idatributovalor1'
            . ' LEFT JOIN atributos_valores attribute2 ON attribute2.id = variantes.idatributovalor2';
    }

    /**
     * Return SQL for get number of variants into a pack
     *
     * @return string
     */
    private function getItemsSelect()
    {
        return 'SELECT COUNT(1)'
             .  ' FROM productopack_packlines t1'
             . ' WHERE t1.idpack = productopack_pack.id';
    }

    /**
     * Return SQL for get total price of variants into a pack
     *
     * @return string
     */
    private function getPriceItems()
    {
        return 'SELECT SUM(t2.precio * t1.quantity)'
             .  ' FROM productopack_packlines t1'
             . ' INNER JOIN variantes t2 ON t2.referencia = t1.reference'
             . ' WHERE t1.idpack = productopack_pack.id';
    }
}
