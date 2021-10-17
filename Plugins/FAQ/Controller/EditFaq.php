<?php
namespace Facturascripts\Plugins\Faq\Controller;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditFaq extends EditController {
    public function getModelClassName(){ //Sobre qué modelo trabajamos
        return 'Faq';
    }

    public function getPageData(){
      $data = parent::getPageData();
      $data['title'] = 'Entrada';
      $data['icon'] = 'fas fa-question';
      return $data;
    }

}
