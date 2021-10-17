<?php
namespace Facturascripts\Plugins\Faq\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditFaqcategory extends EditController {
    public function getModelClassName(){ //Sobre qué modelo trabajamos
        return 'Faqcategory';
    }

    public function getPageData(){
      $data = parent::getPageData();
      $data['title'] = 'Categoría';
      $data['icon'] = 'fas fa-wrench';
      return $data;
    }

    protected function createViews(){
        parent::createViews();
        $this->addListView('ListFaq', 'Faq', 'Entradas');
    }

    protected function loadData($viewName, $view){
        switch ($viewName) {
            case 'ListFaq':
                $idcategory = $this->getViewModelValue('EditFaqcategory', 'idcategory');
                $where = [new DataBaseWhere('idcategory', $idcategory)];
                $view->loadData('', $where);
                break;

            case 'EditFaqcategory':
                parent::loadData($viewName, $view);
                //Si los datos son nuevos, el usuario es el que está logueado
                if(!$this->views[$viewName]->model->exists()){
                    $this->views[$viewName]->model->user = $this->user->nick;
                }
                break;
        }
    }

}
