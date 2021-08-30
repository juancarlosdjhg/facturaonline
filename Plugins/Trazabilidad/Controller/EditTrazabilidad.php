<?php
namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Dinamic\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;
use FacturaScripts\Plugins\Trazabilidad\Model\TrazabilidadProd;
use FacturaScripts\Dinamic\Model\Producto;

class EditTrazabilidad extends EditController {
    public function getModelClassName() {
        return 'Trazabilidad';
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
        $codtrazabilidad = $this->request->query->get('code');
        if (false === \is_array($codes)) {
            return;
        }

        $num = 0;
        foreach ($codes as $code) {
            $trazabilidadprod = new TrazabilidadProd();
            $trazabilidadprod->codtrazabilidadprod = $trazabilidadprod->newCode('codtrazabilidadprod');
            $trazabilidadprod->codtrazabilidad = $codtrazabilidad;
            $trazabilidadprod->idproducto = $code;

            if ($trazabilidadprod->save($trazabilidadprod)) {
                $num++;
            }
        }

        $this->toolBox()->i18nLog()->notice('items-added-correctly', ['%num%' => $num]);
    }

    protected function removeProductAction()
    {
        $codes = $this->request->request->get('code', []);
        $codtrazabilidad = $this->request->query->get('code');
        if (false === \is_array($codes)) {
            return;
        }

        $num = 0;
        foreach ($codes as $code) {
            $trazabilidadprod = new TrazabilidadProd();
            $trazabilidadprod->codtrazabilidad = $codtrazabilidad;
            $trazabilidadprod->idproducto = $code;
          
            $dataBase = new DataBase();
            $array = $dataBase->select('SELECT codtrazabilidadprod FROM trazabilidadesprod where codtrazabilidad='.$codtrazabilidad.' and idproducto='.$code.';');
            $codtrazabilidadprod = $array[0];

            if ($trazabilidadprod->loadFromCode($codtrazabilidadprod['codtrazabilidadprod'])) {
                $trazabilidadprod->delete($trazabilidadprod->codtrazabilidadprod);
                $num++;
            }

        }

        $this->toolBox()->i18nLog()->notice('items-removed-correctly', ['%num%' => $num]);
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
    protected function createViewProducts(string $viewName = 'ListProducto')
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
    protected function createViewNewProducts(string $viewName = 'ListProducto-new')
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
        $codtrazabilidad = $this->getViewModelValue('EditTrazabilidad', 'codtrazabilidad');
        $where = [new DataBaseWhere('codtrazabilidad', $codtrazabilidad)];
        
        switch ($viewName) {
            case 'ListProducto':
                $inSQL = 'SELECT idproducto FROM trazabilidadesprod WHERE codtrazabilidad = ' . $this->dataBase->var2str($codtrazabilidad);
                $where = [new DataBaseWhere('idproducto', $inSQL, 'IN')];
                $view->loadData('', $where);
                break;
                
            case 'ListProducto-new':
                 $inSQL = 'SELECT idproducto FROM trazabilidadesprod WHERE codtrazabilidad = ' . $this->dataBase->var2str($codtrazabilidad);
                 $where = [
                    new DataBaseWhere('trazabilidad', '1'),
                    new DataBaseWhere('idproducto', $inSQL, 'NOT IN')
                ];
                $view->loadData('', $where);                
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

}



