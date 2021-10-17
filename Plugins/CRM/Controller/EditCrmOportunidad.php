<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 * Description of EditCrmOportunidad
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class EditCrmOportunidad extends EditController
{

    /**
     * 
     * @return string
     */
    public function getModelClassName()
    {
        return 'CrmOportunidad';
    }

    /**
     * 
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'crm';
        $data['title'] = 'oportunity';
        $data['icon'] = 'fas fa-trophy';
        return $data;
    }

    protected function createEstimationAction()
    {
        $mainView = $this->getMainViewName();
        $contact = $this->views[$mainView]->model->getContacto();
        if (!$contact->exists()) {
            $this->toolBox()->i18nLog()->error('contact-not-found');
            return;
        }

        $customer = $contact->getCustomer();
        if (!$customer->exists()) {
            $this->toolBox()->i18nLog()->error('customer-not-found');
            return;
        }

        $presupuesto = new PresupuestoCliente();
        $presupuesto->setSubject($customer);
        $presupuesto->codagente = $this->views[$mainView]->model->codagente;
        if ($presupuesto->save()) {
            $this->views[$mainView]->model->coddivisa = $presupuesto->coddivisa;
            $this->views[$mainView]->model->idpresupuesto = $presupuesto->primaryColumnValue();
            $this->views[$mainView]->model->neto = $presupuesto->neto;
            $this->views[$mainView]->model->netoeuros = empty($presupuesto->tasaconv) ? 0 : round($presupuesto->neto / $presupuesto->tasaconv, 5);
            $this->views[$mainView]->model->tasaconv = $presupuesto->tasaconv;
            $this->views[$mainView]->model->save();

            $this->redirect($presupuesto->url());
            return;
        }

        $this->toolBox()->i18nLog()->error('record-save-error');
    }

    protected function createViews()
    {
        parent::createViews();
        $this->setTabsPosition('bottom');

        $this->createViewNotes();
        $this->createViewEstimations();
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewEstimations(string $viewName = 'ListPresupuestoCliente')
    {
        $this->addListView($viewName, 'PresupuestoCliente', 'estimations', 'fas fa-copy');

        /// disable buttons
        $this->setSettings($viewName, 'btnNew', false);
        $this->setSettings($viewName, 'btnDelete', false);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createViewNotes(string $viewName = 'EditCrmNota')
    {
        $this->addEditListView($viewName, 'CrmNota', 'notes', 'fas fa-sticky-note');

        /// disable columns
        $this->views[$viewName]->disableColumn('contact');
        $this->views[$viewName]->disableColumn('oportunity');
    }

    /**
     * 
     * @param string $action
     */
    protected function execAfterAction($action)
    {
        /// buttons
        $mainView = $this->getMainViewName();
        if (empty($this->views[$mainView]->model->idpresupuesto)) {
            $newButton = [
                'action' => 'create-estimation',
                'color' => 'success',
                'icon' => 'fas fa-plus',
                'label' => 'create-estimation'
            ];
            $this->addButton('ListPresupuestoCliente', $newButton);
        }

        if ($action === 'create-estimation') {
            $this->createEstimationAction();
        }

        parent::execAfterAction($action);
    }

    /**
     * 
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case $this->getMainViewName():
                parent::loadData($viewName, $view);
                /// set user nick
                if (!$view->model->exists()) {
                    $view->model->nick = $this->user->nick;
                }

                /// disable columns if not editable
                if (!$view->model->editable) {
                    $view->disableColumn('agent', false, 'true');
                    $view->disableColumn('contact', false, 'true');
                    $view->disableColumn('description', false, 'true');
                    $view->disableColumn('interest', false, 'true');
                    $view->disableColumn('observations', false, 'true');
                }
                break;

            case 'EditCrmNota':
                $idoportunidad = $this->getViewModelValue($this->getMainViewName(), 'id');
                $where = [new DataBaseWhere('idoportunidad', $idoportunidad)];
                $view->loadData('', $where, ['fecha' => 'DESC', 'id' => 'DESC']);
                /// set user nick
                if (!$view->model->exists()) {
                    $view->model->idcontacto = $this->getViewModelValue($this->getMainViewName(), 'idcontacto');
                    $view->model->nick = $this->user->nick;
                }
                break;

            case 'ListPresupuestoCliente':
                $idpresupuesto = $this->getViewModelValue($this->getMainViewName(), 'idpresupuesto');
                if (!empty($idpresupuesto)) {
                    $where = [new DataBaseWhere('idpresupuesto', $idpresupuesto)];
                    $view->loadData('', $where);
                }
                break;
        }
    }
}
