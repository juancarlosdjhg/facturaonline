<?php
namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Controller\EditProducto as ParentController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

class EditProducto extends ParentController {

    protected function createViews() {
        parent::createViews();

        $this->addListView('ListTrazabilidadProducto', 'TrazabilidadProducto', 'Trazabilidad');
        $this->addListView('ListFacturaProducto', 'FacturaProducto', 'Facturas', 'fas fa-copy');
        $this->addListView('ListPresupuestoProducto', 'PresupuestoProducto', 'Presupuestos', 'fas fa-copy');

    }

    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListTrazabilidadProducto':
                $idproducto = $this->getViewModelValue('EditProducto', 'idproducto');
                $where = [new DataBaseWhere('productos.idproducto', $idproducto)];
                $view->loadData('', $where);
                break;

            case 'ListFacturaProducto':
                $idproducto = $this->getViewModelValue('EditProducto', 'idproducto');
                $where = [new DataBaseWhere('productos.idproducto', $idproducto)];
                $view->loadData('', $where);
                break;

            case 'ListPresupuestoProducto':
                $idproducto = $this->getViewModelValue('EditProducto', 'idproducto');
                $where = [new DataBaseWhere('productos.idproducto', $idproducto)];
                $view->loadData('', $where);
                break;

            //case 'ListPresupuestoProducto':
            //    $idproducto = $this->getViewModelValue('EditProducto', 'idproducto');
            //    $where = [new DataBaseWhere('productos.idproducto', $idproducto)];
            //    $view->loadData('', $where);
            //    break;
 
            default:
                parent::loadData($viewName, $view);
                break;
        }
     }
}