<?php
/**
 * Copyright (C) 2019-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Extension\Controller;

use FacturaScripts\Dinamic\Lib\Import\CustomerImport;

/**
 * Description of ListCliente
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class ListCliente
{

    public function createViews()
    {
        return function() {
            if ($this->user->admin) {
                /// import button
                $this->addButton('ListCliente', [
                    'action' => 'import-customers',
                    'color' => 'warning',
                    'icon' => 'fas fa-file-import',
                    'label' => 'import-customers',
                    'type' => 'modal'
                ]);
            }
        };
    }

    public function execPreviousAction()
    {
        return function($action) {
            if ($action === 'import-customers') {
                $this->importCustomersAction();
            }
        };
    }

    public function importCustomersAction()
    {
        return function() {
            $uploadFile = $this->request->files->get('customersfile');
            if (false === CustomerImport::isValidFile($uploadFile)) {
                $this->toolBox()->i18nLog()->warning('file-not-supported');
                $this->toolBox()->i18nLog()->warning($uploadFile->getMimeType());
                return true;
            }

            $mode = $this->request->request->get('mode', CustomerImport::INSERT_MODE);
            if ($mode === CustomerImport::ADVANCED_MODE) {
                $newCsvFile = CustomerImport::advancedImport($uploadFile);
                $this->redirect($newCsvFile->url());
                return true;
            }

            $num = CustomerImport::importCSV($uploadFile->getPathname(), $mode);
            $this->toolBox()->i18nLog()->notice('items-added-correctly', ['%num%' => $num]);
            return true;
        };
    }
}
