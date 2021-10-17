<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Lib\Import;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\BusinessDocumentTools;
use FacturaScripts\Dinamic\Model\FacturaProveedor;
use FacturaScripts\Dinamic\Model\Proveedor;

/**
 * Description of SupplierInvoiceImport
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class SupplierInvoiceImport extends CsvImporClass
{

    const MAX_INVOICES_IMPORT = 1000;

    /**
     * 
     * @param array $line
     *
     * @return Proveedor
     */
    protected static function getFactusolSupplier($line)
    {
        /// get code
        $parts = \explode('-', $line['Proveedor']);
        $code = (int) $parts[0];
        if (empty($code)) {
            $code = 99999;
        }

        $supplier = new Proveedor();
        if (false === $supplier->loadFromCode($code)) {
            /// save new supplier
            $supplier->cifnif = '';
            $supplier->codproveedor = $code;
            $supplier->nombre = empty($parts[1]) ? '-' : $parts[1];
            $supplier->save();
        }

        return $supplier;
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
        } elseif ($csv->titles[0] === 'S.' && $csv->titles[2] === 'Factura recibida') {
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
        return 'supplier-invoices';
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
            $invoice = new FacturaProveedor();
            $where = [
                new DataBaseWhere('codserie', $row['S.']),
                new DataBaseWhere('numero', (int) $row['Núm.']),
            ];
            if ($invoice->loadFromCode('', $where) && $mode === static::INSERT_MODE) {
                /// force date checking
                static::checkInvoiceDate($invoice);
                continue;
            }

            /// save new invoice
            $invoice->setSubject(static::getFactusolSupplier($row));
            $invoice->codserie = static::getSerie($row['S.'])->codserie;
            $invoice->setDate(static::getFixedDate($row['Fecha']), \date('H:i:s'));
            $invoice->numero = (int) $row['Núm.'];
            $invoice->numproveedor = $row['Factura recibida'];
            static::checkInvoiceDate($invoice);
            if (false === $invoice->save()) {
                break;
            }

            $num++;
            $newLine = $invoice->getNewLine();
            $newLine->descripcion = $row['Proveedor'];
            static::setFactusolIVA($newLine, $row['Base'], $row['IVA'], $row['Rec']);
            $newLine->save();

            $docTools = new BusinessDocumentTools();
            $docTools->recalculate($invoice);
            if (\abs($invoice->total - static::getFloat($row['Total'])) >= 0.01) {
                static::toolBox()->i18nLog()->warning('total-value-error', [
                    '%docType%' => $invoice->modelClassName(),
                    '%docCode%' => $invoice->codigo,
                    '%docTotal%' => $invoice->total,
                    '%calcTotal%' => static::getFloat($row['Total'])
                ]);
            }

            $invoice->save();

            /// paid invoice?
            if ($row['Estado'] === 'Pagado') {
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
