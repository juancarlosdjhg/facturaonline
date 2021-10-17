<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\CRM\Model\CrmNota;

/**
 * Description of EditPresupuestoCliente
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class EditPresupuestoCliente
{

    protected function createViews()
    {
        return function() {
            $viewName = 'CrmOportunidad';
            $this->addHTMLView($viewName, 'Tab/CrmOportunidad', 'CrmOportunidad', 'oportunities', 'fas fa-trophy');
        };
    }

    protected function editCrmNoteAction()
    {
        return function() {
            $nota = new CrmNota();
            $id = $this->request->request->get('id');
            if (false === $nota->loadFromCode($id)) {
                return true;
            }

            $nota->observaciones = $this->request->request->get('observaciones');
            $nota->fechaaviso = $this->request->request->get('fechaaviso');
            if (empty($nota->fechaaviso)) {
                $nota->fechaaviso = null;
            }

            if ($nota->save()) {
                $this->toolBox()->i18nLog()->notice('record-updated-correctly');
                return true;
            }

            $this->toolBox()->i18nLog()->warning('record-updated-error');
            return true;
        };
    }

    protected function execPreviousAction()
    {
        return function($action) {
            if ($action === 'edit-crm-note') {
                return $this->editCrmNoteAction();
            } elseif ($action === 'new-crm-note') {
                return $this->newCrmNoteAction();
            }

            return true;
        };
    }

    protected function loadData()
    {
        return function($viewName, $view) {
            if ($viewName === 'CrmOportunidad') {
                $idpresupuesto = $this->getViewModelValue($this->getMainViewName(), 'idpresupuesto');
                $where = [new DataBaseWhere('idpresupuesto', $idpresupuesto)];
                $view->loadData('', $where);
            }
        };
    }

    protected function newCrmNoteAction()
    {
        return function() {
            $nota = new CrmNota();
            $nota->idoportunidad = $this->request->request->get('idoportunidad');
            $nota->nick = $this->user->nick;
            $nota->observaciones = $this->request->request->get('observaciones');
            $nota->fechaaviso = $this->request->request->get('fechaaviso');
            if (empty($nota->fechaaviso)) {
                $nota->fechaaviso = null;
            }

            if ($nota->save()) {
                $this->toolBox()->i18nLog()->notice('record-updated-correctly');
                return true;
            }

            $this->toolBox()->i18nLog()->warning('record-updated-error');
            return true;
        };
    }
}
