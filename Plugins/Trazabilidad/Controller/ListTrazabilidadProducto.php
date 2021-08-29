<?php

namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Plugins\Trazabilidad\Controller\ListTrazabilidadesProd;

class ListTrazabilidadProducto extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Trazabilidad';
        $pageData['icon'] = 'fas fa-tasks';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    protected function createViews() {
        $this->addView('ListTrazabilidadProducto', 'TrazabilidadProducto');
        $this->addSearchFields('ListTrazabilidadProducto', ['productos.referencia','productos.descripcion', 'productos.description','trazabilidades.codtrazabilidad', 'lote', 'partida', 'trazabilidades.descripcion', 'procedencia', 'fechaproduccion', 'fechacaducidad']);
        $this->addOrderBy('ListTrazabilidadProducto', ['productos.referencia'], 'product');
        $this->addOrderBy('ListTrazabilidadProducto', ['productos.descripcion'], 'description');
        $this->addOrderBy('ListTrazabilidadProducto', ['productos.description'], 'description_eng');
        $this->addOrderBy('ListTrazabilidadProducto', ['trazabilidades.codtrazabilidad'], 'code');
        $this->addOrderBy('ListTrazabilidadProducto', ['partida'], 'partity');
        $this->addOrderBy('ListTrazabilidadProducto', ['lote'], 'lot');
        $this->addOrderBy('ListTrazabilidadProducto', ['trazabilidades.descripcion'], 'description');
        $this->addOrderBy('ListTrazabilidadProducto', ['procedencia'], 'origin');
        $this->addOrderBy('ListTrazabilidadProducto', ['fechaproduccion'], 'production-date');
        $this->addOrderBy('ListTrazabilidadProducto', ['fechacaducidad'], 'expiration-date');
    }

}