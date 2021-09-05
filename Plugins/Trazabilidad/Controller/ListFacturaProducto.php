<?php

namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFacturaProducto extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Facturas de venta';
        $pageData['icon'] = 'fas fa-copy';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    protected function createViews() {
        $this->addView('ListFacturaProducto', 'FacturaProducto');
        $this->addSearchFields('ListFacturaProducto', ['facturascli.codigo','facturascli.cifnif', 'facturascli.fecha','facturascli.numero2','facturascli.nombrecliente','facturascli.cifnif']);
        $this->addOrderBy('ListFacturaProducto', ['facturascli.codigo'], 'code');
        $this->addOrderBy('ListFacturaProducto', ['facturascli.descripcion'], 'description');
        $this->addOrderBy('ListFacturaProducto', ['facturascli.numero2'], 'externalordernumber');
        $this->addOrderBy('ListFacturaProducto', ['facturascli.nombrecliente'], 'customer-name');
        $this->addOrderBy('ListFacturaProducto', ['facturascli.cifnif'], 'fiscal-number');
        $this->addOrderBy('ListFacturaProducto', ['facturascli.fecha'], 'date');

    }

}