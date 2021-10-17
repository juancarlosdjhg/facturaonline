<?php
/**
 * This file is part of ProductoPack plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ProductoPack    Copyright (C) 2019 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ProductoPack\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Class to list the items in the Producto edit view
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class EditProducto
{
    /**
     * Load views
     */
    public function createViews()
    {
        return function() {
            $this->addListView('ListProductPack', 'Join\ProductPack', 'packs', 'fas fa-box-open');
        };
    }

    /**
     * Load view data procedure
     *
     * @param string                      $viewName
     * @param ExtendedController\BaseView $view
     * @return function
     */
    public function loadData()
    {
        return function($viewName, $view) {
            if ($viewName == 'ListProductPack') {
                $mainViewName = $this->getMainViewName();
                $idproduct = $this->getViewModelValue($mainViewName, 'idproducto');
                $this->loadDataProductPack($view, $idproduct);
            }
        };
    }

    /**
     * Load Product List of Variants Pack
     *
     * @return function
     */
    public function loadDataProductPack()
    {
        return function($view, $idproduct) {
            $where = [new DataBaseWhere('idproduct', $idproduct)];
            $order = ['productopack_pack.reference' => 'ASC'];
            $view->loadData('', $where, $order);
        };
    }
}