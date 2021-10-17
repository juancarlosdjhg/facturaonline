<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditRole
 *
 * @author Athos Online <info@athosonline.com>
 */
class EditRole {
    public function createViews()
    {
        return function() {
            $this->addListView('ListEmail', 'EmailGrupo', 'emails', 'fas fa-envelope');
            $this->views['ListEmail']->disableColumn('emaildefault', true);
            $this->setSettings('ListEmail', 'btnNew', false);
            $this->setSettings('ListEmail', 'btnDelete', false);
            $this->setSettings('ListEmail', 'checkBoxes', false);
        };
    }
    
    public function loadData()
    {
        return function($viewName, $view) {
            if ($viewName === 'ListEmail') {
                $where = [new DataBaseWhere('codrole', $this->request->query->get('code'))];
                $view->loadData('', $where);
            }
        };
    }
}