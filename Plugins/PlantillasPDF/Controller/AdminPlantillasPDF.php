<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\PlantillasPDF\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Core\Lib\ExtendedController\PanelController;
use FacturaScripts\Dinamic\Model\FacturaCliente;

/**
 * Description of AdminPlantillasPDF
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class AdminPlantillasPDF extends PanelController
{

    /**
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'pdf-templates';
        $data['icon'] = 'fas fa-print';
        return $data;
    }

    protected function createViews()
    {
        $this->setTemplate('EditSettings');
        $this->createViewsEditConfig();
        $this->createViewsFormats();
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsEditConfig(string $viewName = 'ConfigPlantillasPDF')
    {
        $this->addEditView($viewName, 'Settings', 'general');

        /// disable buttons
        $this->setSettings($viewName, 'btnDelete', false);
        $this->setSettings($viewName, 'btnNew', false);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewsFormats(string $viewName = 'ListFormatoDocumento')
    {
        $this->addListView($viewName, 'FormatoDocumento', 'printing-formats', 'fas fa-print');
        $this->views[$viewName]->searchFields = ['nombre', 'titulo', 'texto'];
        $this->views[$viewName]->addOrderBy(['nombre'], 'name');
        $this->views[$viewName]->addOrderBy(['titulo'], 'title');
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
            case 'preview':
                return $this->previewAction();

            default:
                return parent::execPreviousAction($action);
        }
    }

    /**
     * 
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'ConfigPlantillasPDF':
                $view->loadData('plantillaspdf');
                $view->model->name = 'plantillaspdf';
                $this->loadPDFtemplates($view);
                $this->loadLogoFiles($view);
                break;

            case 'ListFormatoDocumento':
                $view->loadData();
                break;
        }
    }

    /**
     * 
     * @param BaseView $view
     */
    protected function loadLogoFiles(&$view)
    {
        $columnLogo = $view->columnForName('logo');
        if ($columnLogo) {
            $images = $this->codeModel->all('attached_files', 'idfile', 'filename', true, [
                new DataBaseWhere('mimetype', 'image/gif,image/jpeg,image/png', 'IN')
            ]);
            $columnLogo->widget->setValuesFromCodeModel($images);
        }
    }

    /**
     * 
     * @param BaseView $view
     */
    protected function loadPDFtemplates(&$view)
    {
        $list = [];
        foreach ($this->toolBox()->files()->scanFolder(FS_FOLDER . '/Dinamic/Lib/PlantillasPDF') as $fileName) {
            if (\substr($fileName, 0, 4) != 'Base') {
                $list[] = \substr($fileName, 0, -4);
            }
        }

        $templateColumn = $view->columnForName('template');
        if ($templateColumn) {
            $templateColumn->widget->setValuesFromArray($list);
        }
    }

    /**
     * 
     * @return bool
     */
    protected function previewAction()
    {
        $FacturaCliente = new FacturaCliente();
        $facturas = $FacturaCliente->all([], ['fecha' => 'DESC']);
        if (empty($facturas)) {
            $this->toolBox()->i18nLog()->warning('no-invoices-to-preview');
            return true;
        }

        \shuffle($facturas);
        foreach ($facturas as $factura) {
            $this->setTemplate(false);
            $this->exportManager->newDoc('PDF');
            $this->exportManager->addBusinessDocPage($factura);
            $this->exportManager->show($this->response);
            break;
        }

        return true;
    }
}
