<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Controller;

use FacturaScripts\Core\Lib\ExtendedController;
use FacturaScripts\Plugins\MultiEmail\Lib\Email\NewMail;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditEmail
 *
 * @author Athos Online <info@athosonline.com>
 */
class EditEmail extends ExtendedController\EditController
{
    public function getPageData() {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'email';
        $data['icon'] = 'fas fa-envelope';
        return $data;
    }

    public function getModelClassName() {
        return 'Email';
    }
    
    protected function createViews() {
        parent::createViews();
        $this->setTabsPosition('bottom');
        $this->addEditListView('EditEmailEmpresa', 'EmailEmpresa', 'company', 'fa fa-building');
        $this->addEditListView('EditEmailGrupo', 'EmailGrupo', 'roles', 'fa fa-id-card');
        $this->addEditListView('EditEmailUsuario', 'EmailUsuario', 'users', 'fa fa-users');
    }
    
    protected function loadData($viewName, $view) {
        switch ($viewName) {
            case 'EditEmailEmpresa':
                $where = [new DataBaseWhere('idemail', $this->request->query->get('code'))];
                $view->loadData('', $where);
                break;
            
            case 'EditEmailGrupo':
                $where = [new DataBaseWhere('idemail', $this->request->query->get('code'))];
                $view->loadData('', $where);
                break;
            
            case 'EditEmailUsuario':
                $where = [new DataBaseWhere('idemail', $this->request->query->get('code'))];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
    
    protected function execAfterAction($action)
    {
        switch ($action) {
            case 'testmail':
                $email = new NewMail();
                $email->configEmail($this->request->query->get('code'));
                if ($email->test()) {
                    $this->toolBox()->i18nLog()->notice('mail-test-ok');
                } else {
                    $this->toolBox()->i18nLog()->error('mail-test-error');
                }
                break;
            
            default:
                parent::execAfterAction($action);
        }
    }
}