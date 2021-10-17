<?php
/**
 * This file is part of DocumentosRecurrentes plugin for FacturaScripts.
 * FacturaScripts         Copyright (C) 2015-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 * DocumentosRecurrentes  Copyright (C) 2020-2021 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\DocumentosRecurrentes\Controller;

use FacturaScripts\Dinamic\Lib\DocumentosRecurrentes\DocRecurringManager;
use FacturaScripts\Dinamic\Lib\DocumentosRecurrentes\ListDocRecurring;
use FacturaScripts\Plugins\DocumentosRecurrentes\Model\DocRecurringSale;

/**
 * Description of ListDocRecurringSale
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class ListDocRecurringSale extends ListDocRecurring
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'sales';
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
    protected function createViewsRecurring(string $viewName = 'ListDocRecurringSale')
    {
        $this->addView($viewName, 'DocRecurringSale', 'recurring', 'fas fa-calendar-plus');
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
        $model = new DocRecurringSale();
        foreach ($model->all($where, [], 0, 0) as $template) {
            if ($docRecurring->generateSaleDoc($template->id, ['date' => $template->nextdate])) {
                $num++;
            }
        }

        $this->toolBox()->i18nLog()->notice('generated-documents-quantity', ['%quantity%' => $num]);
        return true;
    }
}
