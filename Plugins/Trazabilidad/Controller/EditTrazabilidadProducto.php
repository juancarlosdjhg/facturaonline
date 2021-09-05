<?php
namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\TrazabilidadProd;
use FacturaScripts\Dinamic\Model\Producto;

class EditTrazabilidadProducto extends EditController {
    public function getModelClassName() {
        return 'TrazabilidadProducto';
    }

    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Trazabilidad';
        $pageData['icon'] = 'fas fa-tasks';

        return $pageData;
    }

    protected function addProductAction()
    {
        $codes = $this->request->request->get('code', []);
        $trazabilidad = $this->request->request->get('codtrazabilidad', []);
        var_dump($codes);
        var_dump($trazabilidad);
        if (false === \is_array($codes)) {
            return;
        }

        $num = 0;
        $producto = new TrazabilidadProd();
        foreach ($codes as $code) {
            if (false === $producto->loadFromCode($code)) {
                return;
            }
            
            $producto->idproducto = $this->request->query->get('code');
            if ($producto->save()) {
                $num++;
            }
        }

        $this->toolBox()->i18nLog()->notice('items-added-correctly', ['%num%' => $num]);
    }

    protected function createViews()
    {
        parent::createViews();
        $this->setTabsPosition('bottom');

        $this->createViewProducts();
        $this->createViewNewProducts();
    }
    
    protected function createViewCommon(string $viewName)
    {
        $this->views[$viewName]->addOrderBy(['referencia'], 'reference');
        $this->views[$viewName]->addOrderBy(['descripcion'], 'description');
        $this->views[$viewName]->addOrderBy(['description'], 'english-description');
        $this->views[$viewName]->searchFields = ['referencia', 'descripcion', 'description'];

        /// settings
        $this->views[$viewName]->settings['btnNew'] = false;
        $this->views[$viewName]->settings['btnDelete'] = false;

    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewProducts(string $viewName = 'ListTrazabilidadProducto')
    {
        $this->addListView($viewName, 'Producto', 'products', 'fas fa-list');
        $this->createViewCommon($viewName);

        /// add action button
        $this->addButton($viewName, [
            'action' => 'remove-product',
            'color' => 'danger',
            'confirm' => true,
            'icon' => 'fas fa-minus-square',
            'label' => 'remove-from-list'
        ]);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewNewProducts(string $viewName = 'ListTrazabilidadProducto-new')
    {
        $this->addListView($viewName, 'Producto', 'add-products', 'fas fa-plus-square');
        $this->createViewCommon($viewName);

        /// add action button
        $this->addButton($viewName, [
            'action' => 'add-product',
            'color' => 'success',
            'icon' => 'fas fa-plus-square',
            'label' => 'add'
        ]);
    }

        /**
     * 
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'add-product':
                $this->addProductAction();
                return true;

            case 'remove-product':
                $this->removeProductAction();
                return true;

            default:
                return parent::execPreviousAction($action);
        }
    }

     /**
     *
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        $codtrazabilidad = $this->getViewModelValue('EditTrazabilidadProducto', 'codtrazabilidad');
        $where = [new DataBaseWhere('codtrazabilidad', $codtrazabilidad)];
        
        switch ($viewName) {
            case 'ListTrazabilidadProducto':
                $inSQL = 'SELECT idproducto FROM trazabilidadesprod WHERE codtrazabilidad = ' . $this->dataBase->var2str($codtrazabilidad);
                $where = [new DataBaseWhere('idproducto', $inSQL, 'IN')];
                $view->loadData('', $where);
                break;
                
            case 'ListTrazabilidadProducto-new':
                $dataBase = new DataBase();
                $data = $dataBase->select('SELECT DISTINCT referencia, descripcion FROM productos WHERE trazabilidad=1;');
                $view->loadData($data);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    protected function removeProductAction()
    {
        $codes = $this->request->request->get('idproducto', []);
        if (false === \is_array($codes)) {
            return;
        }

        $num = 0;
        $producto = new Producto();
        foreach ($codes as $code) {
            if (false === $producto->loadFromCode($code)) {
                return;
            }

            $producto->codgrupo = null;
            if ($producto->save()) {
                $num++;
            }
        }

        $this->toolBox()->i18nLog()->notice('items-removed-correctly', ['%num%' => $num]);
    }

}



