<?php
/**
 * This file is part of ProductoPack plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ProductoPack    Copyright (C) 2019 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ProductoPack\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Variante;

/**
 * Controller to list the items in the Product Pack model
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class EditProductPack extends EditController
{

    /**
     * Returns the model name
     */
    public function getModelClassName()
    {
        return 'ProductPack';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'pack';
        $pagedata['icon'] = 'fas fa-box-open';
        $pagedata['menu'] = 'warehouse';
        $pagedata['showonmenu'] = false;

        return $pagedata;
    }

    /**
     * Create the view to display.
     */
    protected function createViews()
    {
        parent::createViews();
        $this->addProductPackLineView();
    }

    /**
     * Loads the data to display.
     *
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'EditProductPackLine':
                $this->loadDataProductPackLine($view);
                break;

            default:
                parent::loadData($viewName, $view);
                $view->disableColumn('code', true);  // Force disable PK
                $view->disableColumn('product-code', true);  // Force disable Link column with product

                // Load product and variant data
                $this->loadProductData($viewName);
                $this->loadVariantData($viewName);
                break;
        }
    }

    /**
     * Add product pack detaill view
     *
     * @param string $viewName
     */
    private function addProductPackLineView($viewName = 'EditProductPackLine')
    {
        $this->addEditListView($viewName, 'ProductPackLine', 'items', 'fas fa-boxes');
        $this->setTabsPosition('bottom');
    }

    /**
     * Get a array list for Widget Select of all References of one product
     *
     * @param int $idproduct
     * @return array
     */
    private function getReferencesForProduct($idproduct)
    {
        $where = [ new DataBaseWhere('idproducto', $idproduct) ];
        $order = [ 'referencia' => 'ASC' ];
        $result = [];

        $variant = new Variante();
        foreach ($variant->all($where, $order, 0, 0) as $row) {
            $description = $row->description(true);
            $title = empty($description)
                ? $row->referencia
                : $row->referencia . ' : ' . $description;

            $result[] = ['value' => $row->referencia, 'title' => $title];
        }
        return $result;
    }

    /**
     * Load data to view with product pack detaill
     *
     * @param BaseView $view
     */
    private function loadDataProductPackLine($view)
    {
        /// Get master data
        $mainViewName = $this->getMainViewName();
        $idpack = $this->getViewModelValue($mainViewName, 'id');

        /// Load view data
        $where = [ new DataBaseWhere('idpack', $idpack) ];
        $view->loadData(false, $where, ['sortnum' => 'DESC', 'reference' => 'ASC']);
    }

    /**
     * Create product model and load data
     *
     * @param string $viewName
     */
    private function loadProductData($viewName)
    {
        $idproduct = $this->getViewModelValue($viewName, 'idproduct');
        if (empty($idproduct)) {
            return;
        }

        $product = new Producto();
        if ($product->loadFromCode($idproduct)) {
            // Inject the product values into the main model. Is necessary for the xml view.
            $mainModel = $this->getModel();
            $mainModel->productdescription = $product->descripcion;
        }
    }

    /**
     * Create variant product model and load data
     *
     * @param string $viewName
     */
    private function loadVariantData($viewName)
    {
        $idproduct = $this->getViewModelValue($viewName, 'idproduct');
        if (empty($idproduct)) {
            return;
        }

        // Add variant data to widget select array
        $columnReference = $this->views[$viewName]->columnForName('reference');
        if ($columnReference) {
            $values = $this->getReferencesForProduct($idproduct);
            $columnReference->widget->setValuesFromArray($values, false);
        }
    }
}
