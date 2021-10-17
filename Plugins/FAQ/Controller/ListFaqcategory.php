<?php
namespace FacturaScripts\Plugins\Faq\Controller;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListFaqcategory extends ListController{
    public function getPageData(){
        $pageData = parent::getPageData();
        $pageData['menu'] = 'FAQs';
        $pageData['title'] = 'Categorías';
        $pageData['icon'] = 'fas fa-wrench';

        return $pageData;
    }

    protected function createViews(){
        $this->addView('ListFaqcategory', 'Faqcategory');
        $this->addSearchFields('ListFaqcategory', ['namecategory']);
        $this->addOrderBy('ListFaqcategory', ['namecategory']);
    }
}
