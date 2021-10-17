<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\ListView;
use FacturaScripts\Core\Lib\ExtendedController\ListController;

/**
 * Description of ListCrmNota
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class ListCrmNota extends ListController
{

    /**
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'crm';
        $data['title'] = 'notes';
        $data['icon'] = 'far fa-sticky-note';
        return $data;
    }

    protected function createViews()
    {
        $this->createViewNotes();
        $this->createViewNotices();
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewNotes(string $viewName = 'ListCrmNota')
    {
        $this->addView($viewName, 'CrmNota', 'notes', 'far fa-sticky-note');
        $this->addOrderBy($viewName, ['fecha'], 'date', 2);
        $this->addOrderBy($viewName, ['fechaaviso'], 'notice-date');
        $this->addSearchFields($viewName, ['observaciones']);

        /// filters
        $this->addFilterPeriod($viewName, 'fecha', 'date', 'fecha');

        $users = $this->codeModel->all('users', 'nick', 'nick');
        $this->addFilterSelect($viewName, 'nick', 'user', 'nick', $users);

        $interests = $this->codeModel->all('crm_intereses', 'id', 'nombre');
        $this->addFilterSelect($viewName, 'idinteres', 'interest', 'idinteres', $interests);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewNotices(string $viewName = 'ListCrmNota-notices')
    {
        $this->addView($viewName, 'CrmNota', 'notices', 'far fa-calendar-check');
        $this->addOrderBy($viewName, ['fecha'], 'date');
        $this->addOrderBy($viewName, ['fechaaviso'], 'notice-date', 2);
        $this->addSearchFields($viewName, ['observaciones']);

        /// filters
        $this->addFilterPeriod($viewName, 'fecha', 'date', 'fecha');

        $users = $this->codeModel->all('users', 'nick', 'nick');
        $this->addFilterSelect($viewName, 'nick', 'user', 'nick', $users);

        $interests = $this->codeModel->all('crm_intereses', 'id', 'nombre');
        $this->addFilterSelect($viewName, 'idinteres', 'interest', 'idinteres', $interests);
    }

    /**
     * 
     * @param string   $viewName
     * @param ListView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'ListCrmNota-notices':
                $where = [new DataBaseWhere('fechaaviso', null, 'IS NOT')];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
        }
    }
}
