<?php

namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListPresupuestoProducto extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Presupuestos de venta';
        $pageData['icon'] = 'fas fa-copy';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    protected function createViews() {
        $this->addView('ListPresupuestoProducto', 'PresupuestoProducto');
        $this->addSearchFields('ListPresupuestoProducto', ['presupuestoscli.codigo','presupuestoscli.cifnif', 'presupuestoscli.fecha','presupuestoscli.numero2','presupuestoscli.nombrecliente','presupuestoscli.cifnif']);
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.codigo'], 'code');
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.descripcion'], 'description');
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.numero2'], 'externalordernumber');
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.nombrecliente'], 'customer-name');
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.cifnif'], 'fiscal-number');
        $this->addOrderBy('ListPresupuestoProducto', ['presupuestoscli.fecha'], 'date');

    }

}