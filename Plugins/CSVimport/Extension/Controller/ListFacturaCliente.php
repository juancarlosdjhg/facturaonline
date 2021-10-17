<?php
/**
 * Copyright (C) 2019-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Extension\Controller;

use FacturaScripts\Dinamic\Lib\Import\CustomerInvoiceImport;

/**
 * Description of ListFacturaCliente
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class ListFacturaCliente
{

    public function createViews()
    {
        return function() {
            if ($this->user->admin) {
                /// import button
                $this->addButton('ListFacturaCliente', [
                    'action' => 'import-invoices',
                    'color' => 'warning',
                    'icon' => 'fas fa-file-import',
                    'label' => 'import-invoices',
                    'type' => 'modal'
                ]);
            }
        };
    }

    public function execPreviousAction()
    {
        return function($action) {
            if ($action === 'import-invoices') {
                $this->importInvoicesAction();
            }
        };
    }

    public function importInvoicesAction()
    {
        return function() {
            $uploadFile = $this->request->files->get('invoicesfile');
            if (false === CustomerInvoiceImport::isValidFile($uploadFile)) {
                $this->toolBox()->i18nLog()->warning('file-not-supported');
                $this->toolBox()->i18nLog()->warning($uploadFile->getMimeType());
                return true;
            }

            $mode = $this->request->request->get('mode', CustomerInvoiceImport::INSERT_MODE);
            if ($mode === CustomerInvoiceImport::ADVANCED_MODE) {
                $newCsvFile = CustomerInvoiceImport::advancedImport($uploadFile);
                $this->redirect($newCsvFile->url());
                return true;
            }

            $num = CustomerInvoiceImport::importCSV($uploadFile->getPathname(), $mode);
            $this->toolBox()->i18nLog()->notice('items-added-correctly', ['%num%' => $num]);
            return true;
        };
    }
}
