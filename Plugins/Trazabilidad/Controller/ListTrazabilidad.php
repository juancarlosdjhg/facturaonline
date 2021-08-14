<?php
namespace FacturaScripts\Plugins\Trazabilidad\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListTrazabilidad extends ListController {
    
    public function getPageData() {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'warehouse';
        $pageData['title'] = 'Trazabilidad';
        $pageData['icon'] = 'fas fa-tasks';

        return $pageData;
    }

    protected function createViews() {
        $this->addView('ListTrazabilidad', 'Trazabilidad');
        $this->addSearchFields('ListTrazabilidad', ['codtrazabilidad', 'lote', 'partida', 'descripcion', 'procedencia', 'fechaproduccion', 'fechacaducidad']);
        $this->addOrderBy('ListTrazabilidad', ['codtrazabilidad'], 'code');
        $this->addOrderBy('ListTrazabilidad', ['partida'], 'partity');
        $this->addOrderBy('ListTrazabilidad', ['lote'], 'lot');
        $this->addOrderBy('ListTrazabilidad', ['descripcion'], 'description');
        $this->addOrderBy('ListTrazabilidad', ['procedencia'], 'origin');
        $this->addOrderBy('ListTrazabilidad', ['fechaproduccion'], 'production-date');
        $this->addOrderBy('ListTrazabilidad', ['fechacaducidad'], 'expiration-date');
    }
}