<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Lib\Import;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\BusinessDocumentTools;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\FacturaCliente;

/**
 * Description of CustomerInvoiceImport
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CustomerInvoiceImport extends CsvImporClass
{

    const MAX_INVOICES_IMPORT = 1000;

    /**
     * 
     * @param array $line
     *
     * @return Cliente
     */
    protected static function getFactusolCustomer($line)
    {
        /// get code
        $parts = \explode('-', $line['Cliente']);
        $code = (int) $parts[0];
        if (empty($code)) {
            $code = 99999;
        }

        $customer = new Cliente();
        if (false === $customer->loadFromCode($code)) {
            /// save new customer
            $customer->cifnif = '';
            $customer->codcliente = $code;
            $customer->nombre = empty($parts[1]) ? '-' : $parts[1];
            $customer->save();
        }

        return $customer;
    }

    /**
     * 
     * @param string $filePath
     *
     * @return int
     */
    protected static function getFileType(string $filePath): int
    {
        $csv = static::getCsv($filePath);

        if (\count($csv->titles) < 2) {
            return static::TYPE_NONE;
        } elseif ($csv->titles[0] === 'S.' && $csv->titles[3] === 'Cliente') {
            return static::TYPE_FACTUSOL;
        }

        return static::TYPE_NONE;
    }

    /**
     * 
     * @return string
     */
    protected static function getProfile()
    {
        return 'customer-invoices';
    }

    /**
     * 
     * @param string $filePath
     * @param string $mode
     *
     * @return int
     */
    protected static function importCSVfactusol(string $filePath, string $mode): int
    {
        $csv = static::getCsv($filePath);

        $num = 0;
        foreach ($csv->data as $row) {
            if (empty($row['S.'])) {
                continue;
            } elseif ($num >= static::MAX_INVOICES_IMPORT) {
                static::toolBox()->i18nLog()->notice('max-invoices-import', ['%max%' => static::MAX_INVOICES_IMPORT]);
                break;
            }

            /// find invoice
            $invoice = new FacturaCliente();
            $where = [
                new DataBaseWhere('codserie', $row['S.']),
                new DataBaseWhere('numero', (int) $row['Num.']),
            ];
            if ($invoice->loadFromCode('', $where) && $mode === static::INSERT_MODE) {
                /// force date checking
                static::checkInvoiceDate($invoice);
                continue;
            }

            /// save new invoice
            $invoice->setSubject(static::getFactusolCustomer($row));
            $invoice->codserie = static::getSerie($row['S.'])->codserie;
            $invoice->setDate(static::getFixedDate($row['Fecha']), \date('H:i:s'));
            $invoice->numero = (int) $row['Num.'];
            static::checkInvoiceDate($invoice);
            if (false === $invoice->save()) {
                break;
            }

            $num++;
            $newLine = $invoice->getNewLine();
            $newLine->descripcion = $row['Cliente'];
            static::setFactusolIVA($newLine, $row['Base'], $row['IVA'], $row['Rec.']);
            $newLine->save();

            $docTools = new BusinessDocumentTools();
            $docTools->recalculate($invoice);
            if (\abs($invoice->total - static::getFloat($row['Total'])) > 0.01) {
                static::toolBox()->i18nLog()->warning('total-value-error', [
                    '%docType%' => $invoice->modelClassName(),
                    '%docCode%' => $invoice->codigo,
                    '%docTotal%' => $invoice->total,
                    '%calcTotal%' => static::getFloat($row['Total'])
                ]);
            }

            $invoice->save();

            /// paid invoice?
            if ($row['Est.'] === 'Cobra') {
                foreach ($invoice->getReceipts() as $receipt) {
                    $receipt->fechapago = $invoice->fecha;
                    $receipt->pagado = true;
                    $receipt->save();
                }
            }
        }

        return $num;
    }

    /**
     * 
     * @param int    $type
     * @param string $filePath
     * @param string $mode
     *
     * @return int
     */
    protected static function importType($type, $filePath, $mode): int
    {
        switch ($type) {
            case static::TYPE_FACTUSOL:
                return static::importCSVfactusol($filePath, $mode);

            default:
                static::toolBox()->i18nLog()->warning('file-not-supported-advanced');
                return 0;
        }
    }
}
