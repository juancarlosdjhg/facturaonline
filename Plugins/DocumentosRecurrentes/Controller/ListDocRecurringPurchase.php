<?php
/**
 * This file is part of DocumentosRecurrentes plugin for FacturaScripts.
 * FacturaScripts         Copyright (C) 2015-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 * DocumentosRecurrentes  Copyright (C) 2020-2021 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\DocumentosRecurrentes\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\DocumentosRecurrentes\DocRecurringManager;
use FacturaScripts\Dinamic\Lib\DocumentosRecurrentes\ListDocRecurring;
use FacturaScripts\Plugins\DocumentosRecurrentes\Model\DocRecurringPurchase;

/**
 * Description of ListDocRecurringPurchase
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class ListDocRecurringPurchase extends ListDocRecurring
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'purchases';
        $pagedata['title'] = 'recurring';
        $pagedata['icon'] = 'fas fa-calendar-plus';
        return $pagedata;
    }

    protected function createViews()
    {
        $this->createViewsRecurring();
    }

    /**
     *
     * @param string $viewName
     */
    protected function createViewsRecurring(string $viewName = 'ListDocRecurringPurchase')
    {
        $this->addView($viewName, 'DocRecurringPurchase', 'recurring', 'fas fa-calendar-plus');
        $this->addCommonSearchFields($viewName);
        $this->addCommonOrderBy($viewName);
        $this->addCommonFilters($viewName);

        /// buttons
        $this->addButton($viewName, [
            'action' => 'generate-docs',
            'color' => 'warning',
            'icon' => 'fas fa-magic',
            'label' => 'generate',
            'type' => 'modal'
        ]);
    }

    /**
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'generate-docs':
                return $this->generateDocsAction();

            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     *
     * @return bool
     */
    protected function generateDocsAction(): bool
    {
        $where = $this->generateDocsWhere();
        if (empty($where)) {
            $this->toolBox()->i18nLog()->warning('no-selected-item');
            return true;
        }

        $num = 0;
        $docRecurring = new DocRecurringManager();
        $model = new DocRecurringPurchase();
        foreach ($model->all($where, [], 0, 0) as $template) {
            if ($docRecurring->generatePurchaseDoc($template->id, $template->nextdate)) {
                $num++;
            }
        }

        $this->toolBox()->i18nLog()->notice('generated-documents-quantity', ['%quantity%' => $num]);
        return true;
    }
}
