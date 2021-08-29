<?php

namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Base\Controller;

class ListTrazabilidadesProd extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Trazabilidad';
        $pageData['icon'] = 'fas fa-tasks';

        return $pageData;
    }

    protected function createViews() {
        $url='ListTrazabilidadProducto';
        $this->redirect($url, 0);
        $this->addView('ListTrazabilidadesProd', 'TrazabilidadProd');
        $this->addSearchFields('ListTrazabilidadesProd', ['productos.referencia','productos.descripcion', 'productos.description','trazabilidades.codtrazabilidad', 'lote', 'partida', 'trazabilidades.descripcion', 'procedencia', 'fechaproduccion', 'fechacaducidad']);
        $this->addOrderBy('ListTrazabilidadesProd', ['productos.referencia'], 'product');
        $this->addOrderBy('ListTrazabilidadesProd', ['productos.descripcion'], 'description');
        $this->addOrderBy('ListTrazabilidadesProd', ['productos.description'], 'description_eng');
        $this->addOrderBy('ListTrazabilidadesProd', ['trazabilidades.codtrazabilidad'], 'code');
        $this->addOrderBy('ListTrazabilidadesProd', ['partida'], 'partity');
        $this->addOrderBy('ListTrazabilidadesProd', ['lote'], 'lot');
        $this->addOrderBy('ListTrazabilidadesProd', ['trazabilidades.descripcion'], 'description');
        $this->addOrderBy('ListTrazabilidadesProd', ['procedencia'], 'origin');
        $this->addOrderBy('ListTrazabilidadesProd', ['fechaproduccion'], 'production-date');
        $this->addOrderBy('ListTrazabilidadesProd', ['fechacaducidad'], 'expiration-date');
    }
 
    public function redirigir() {
        $url='ListTrazabilidadProducto';
        $this->redirect($url, 0);
    }

}