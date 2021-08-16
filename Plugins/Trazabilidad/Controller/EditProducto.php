<?php
namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Controller\EditProducto as ParentController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

class EditProducto extends ParentController {

    protected function createViews() {
        parent::createViews();

        $this->addListView('ListTrazabilidadProducto', 'TrazabilidadProducto', 'Trazabilidad');

    }

    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'ListTrazabilidadProducto':
                $idproducto = $this->getViewModelValue('EditProducto', 'idproducto');
                $where = [new DataBaseWhere('productos.idproducto', $idproducto)];
                $view->loadData('', $where);
                break;
 
            default:
                parent::loadData($viewName, $view);
                break;
        }
     }
}