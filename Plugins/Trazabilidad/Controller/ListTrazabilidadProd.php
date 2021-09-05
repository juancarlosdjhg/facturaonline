<?php

namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListTrazabilidadProd extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Trazabilidad';
        $pageData['icon'] = 'fas fa-tasks';

        return $pageData;
    }

    protected function createViews() {
        $url = 'ListTrazabilidadProducto';
        $this->addView('ListTrazabilidad', 'Trazabilidad');
        $this->addView('ListTrazabilidadProducto', 'TrazabilidadProd');
        $this->redirect($url, 0);
    }
/* 
    public function redirect() {
        $url='ListTrazabilidadProducto';
        $this->redirect($url, 0);
    }
 */
}