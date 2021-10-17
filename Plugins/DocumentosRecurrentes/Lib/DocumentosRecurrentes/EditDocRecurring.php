<?php
/**
 * This file is part of DocumentosRecurrentes plugin for FacturaScripts.
 * FacturaScripts         Copyright (C) 2015-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 * DocumentosRecurrentes  Copyright (C) 2020-2021 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\DocumentosRecurrentes\Lib\DocumentosRecurrentes;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\Base\DocRecurring;

/**
 * Description of EditDocRecurring
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
abstract class EditDocRecurring extends EditController
{

    abstract protected function duplicateDocAction();

    abstract protected function generateDocsAction();

    abstract protected function generateManuallyAction();

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'doc-recurring';
        $pagedata['icon'] = 'fas fa-calendar-plus';
        $pagedata['showonmenu'] = false;
        return $pagedata;
    }

    /**
     * Create the view to display.
     */
    protected function createViews()
    {
        parent::createViews();
        $this->createLinesView();
        $this->setTabsPosition('bottom');
    }

    /**
     * Run the actions that alter data before reading it.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'generate-docs':
                $this->generateDocsAction();
                return true;

            case 'generate-manually':
                $this->generateManuallyAction();
                return true;

            case 'duplicate-doc':
                $this->duplicateDocAction();
                return true;

            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     * Loads the data to display.
     *
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        $mvn = $this->getMainViewName();

        switch ($viewName) {
            case $this->getLinesViewName():
                $view->loadData('', $this->getMainWhere($mvn), ['id' => 'DESC']);
                break;

            case $mvn:
                parent::loadData($viewName, $view);
                if (empty($view->model->nick)) {
                    $view->model->nick = $this->user->nick;
                }
                $this->addGenerateDoc($viewName);
                $this->setGenerateModal($viewName);
                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }

    /**
     * Add button for manual generate document from template.
     *
     * @param string $viewName
     */
    private function addGenerateDoc(string $viewName)
    {
        $termType = $this->getViewModelValue($viewName, 'termtype');
        if ($termType === DocRecurring::TERM_TYPE_MANUAL) {
            $this->addButton($viewName, [
                'action' => 'generate-manually',
                'color' => 'warning',
                'icon' => 'fas fa-magic',
                'label' => 'generate',
                'type' => 'modal'
            ]);
            return;
        }

        $nextDate = $this->getViewModelValue($viewName, 'nextdate');
        if (!empty($nextDate)) {
            $this->addButton($viewName, [
                'action' => 'generate-docs',
                'color' => 'warning',
                'confirm' => true,
                'icon' => 'fas fa-magic',
                'label' => 'generate'
            ]);
        }
    }

    /**
     * Add document lines view.
     */
    private function createLinesView()
    {
        $this->addEditListView(
            $this->getLinesViewName(),
            $this->getModelClassName() . 'Line',
            'lines',
            'fas fa-tasks'
        );
    }

    /**
     *
     * @return string
     */
    private function getLinesViewName()
    {
        return 'Edit' . $this->getModelClassName() . 'Line';
    }

    /**
     *
     * @param string $mainViewName
     *
     * @return DatabaseWhere[]
     */
    private function getMainWhere(string $mainViewName)
    {
        $iddoc = $this->getViewModelValue($mainViewName, 'id');
        return [new DataBaseWhere('iddoc', $iddoc)];
    }

    /**
     *
     * @param string $viewName
     */
    private function setGenerateModal(string $viewName)
    {
        $this->views[$viewName]->model->generatedate = \date(DocRecurring::DATE_STYLE);
        if ($this->views[$viewName]->model->generatedoc == 'FacturaCliente') {
            $this->views[$viewName]->disableColumn('generatedate', false, 'true');
        }
    }
}
