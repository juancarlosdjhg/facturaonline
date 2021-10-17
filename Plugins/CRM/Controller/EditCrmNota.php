<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Controller;

use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * Description of EditCrmNota
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class EditCrmNota extends EditController
{

    /**
     * 
     * @return string
     */
    public function getModelClassName()
    {
        return 'CrmNota';
    }

    /**
     * 
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'crm';
        $data['title'] = 'note';
        $data['icon'] = 'far fa-sticky-note';
        return $data;
    }

    /**
     * 
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            default:
                parent::loadData($viewName, $view);
                if (!$this->views[$this->active]->model->exists()) {
                    $this->views[$this->active]->model->nick = $this->user->nick;
                }
                break;
        }
    }
}
