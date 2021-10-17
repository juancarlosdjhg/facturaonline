<?php
namespace FacturaScripts\Plugins\Faq\Controller;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFaq extends ListController{
    public function getPageData(){
        $pageData = parent::getPageData();
        $pageData['menu'] = 'FAQs';
        $pageData['title'] = 'Entradas';
        $pageData['icon'] = 'fas fa-question';

        return $pageData;
    }

    protected function createViews(){
        $this->addView('ListFaq', 'Faq');
        $this->addSearchFields('ListFaq', ['symptom', 'cause', 'solution', 'comments']);
        $this->addOrderBy('ListFaq', ['creationdate'], 'date', 2);
        $this->addOrderBy('ListFaq', ['idcategory']);
        $this->addFilterPeriod('ListFaq', 'creationdate', 'date', 'creationdate');

        ///filtros
        $users=$this->codeModel->all('users', 'nick', 'nick'); //modelo especial en todos los controladores función all
        $this->addFilterAutocomplete('ListFaq', 'idcategory', 'category', 'idcategory', 'faqcategories', 'idcategory', 'namecategory');
    }
}
