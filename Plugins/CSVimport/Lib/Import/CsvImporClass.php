<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Lib\Import;

use Exception;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base\BusinessDocumentLine;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\Impuesto;
use FacturaScripts\Dinamic\Model\Pais;
use FacturaScripts\Dinamic\Model\Serie;
use FacturaScripts\Plugins\CSVimport\Model\CSVfile;
use ParseCsv\Csv;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of CsvImporClass
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
abstract class CsvImporClass
{

    const ADVANCED_MODE = 'advanced';
    const INSERT_MODE = 'insert';
    const UPDATE_MODE = 'update';
    const TYPE_FACTURASCRIPTS = 1;
    const TYPE_FACTURASCRIPTS_2017 = 2;
    const TYPE_FACTUSOL = 3;
    const TYPE_NONE = 0;

    /**
     * 
     * @var Impuesto[]
     */
    protected static $impuestos;

    /**
     * 
     * @var array
     */
    protected static $invoiceDates = [];

    /**
     * 
     * @var Serie[]
     */
    protected static $series = [];

    abstract protected static function getFileType(string $filePath);

    abstract protected static function getProfile();

    abstract protected static function importType($type, $filePath, $mode);

    /**
     * 
     * @param UploadedFile $uploadFile
     *
     * @return CSVfile
     */
    public static function advancedImport($uploadFile)
    {
        $newCsvFile = new CSVfile();
        if ($uploadFile->move(\FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles', $uploadFile->getClientOriginalName())) {
            $newCsvFile->path = $uploadFile->getClientOriginalName();
            $newCsvFile->profile = static::getProfile();
            $newCsvFile->save();
        }

        return $newCsvFile;
    }

    /**
     * 
     * @param string $filePath
     * @param string $mode
     *
     * @return int
     */
    public static function importCSV(string $filePath, string $mode): int
    {
        if (false === \file_exists($filePath)) {
            static::toolBox()->i18nLog()->warning('file-not-found', ['%fileName%' => $filePath]);
            return 0;
        }

        /// start transaction
        $dataBase = new DataBase();
        $dataBase->beginTransaction();

        $return = 0;
        try {
            $type = static::getFileType($filePath);
            $return += static::importType($type, $filePath, $mode);

            /// confirm data
            $dataBase->commit();
        } catch (Exception $exp) {
            static::toolBox()->log()->error($exp->getLine() . ': ' . $exp->getMessage());
        } finally {
            if ($dataBase->inTransaction()) {
                $dataBase->rollback();
            }
        }

        return $return;
    }

    /**
     * 
     * @param UploadedFile $uploadFile
     *
     * @return bool
     */
    public static function isValidFile($uploadFile): bool
    {
        if ('csv' === $uploadFile->getClientOriginalExtension()) {
            return true;
        }

        switch ($uploadFile->getMimeType()) {
            case 'application/vnd.ms-excel':
            case 'application/octet-stream':
            case 'application/vnd.oasis.opendocument.spreadsheet':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                static::toolBox()->i18nLog()->warning('save-as-csv');
                return false;

            case 'text/csv':
            case 'text/plain':
            case 'text/x-Algol68':
                return true;

            default:
                return false;
        }
    }

    /**
     * 
     * @param FacturaCliente $invoice
     */
    protected static function checkInvoiceDate(&$invoice)
    {
        /// undefined?
        if (false === isset(self::$invoiceDates[$invoice->codejercicio][$invoice->codserie])) {
            self::$invoiceDates[$invoice->codejercicio][$invoice->codserie] = \strtotime($invoice->fecha);
            return;
        }

        /// lower date?
        if (\strtotime($invoice->fecha) < self::$invoiceDates[$invoice->codejercicio][$invoice->codserie]) {
            $newDate = \date(FacturaCliente::DATE_STYLE, self::$invoiceDates[$invoice->codejercicio][$invoice->codserie]);
            static::toolBox()->i18nLog()->warning('invoice-date-changed', ['%old%' => $invoice->fecha, '%new%' => $newDate]);
            $invoice->fecha = $newDate;
            return;
        }

        /// upper date
        self::$invoiceDates[$invoice->codejercicio][$invoice->codserie] = \strtotime($invoice->fecha);
    }

    /**
     * 
     * @param string $code
     *
     * @return Pais
     */
    protected static function getCountry($code)
    {
        $country = new Pais();
        if ($country->loadFromCode($code)) {
            return $country;
        }

        $where = [new DataBaseWhere('codiso', $code)];
        $country->loadFromCode('', $where);
        return $country;
    }

    /**
     * 
     * @param string $filePath
     *
     * @return Csv
     */
    protected static function getCsv(string $filePath)
    {
        $csv = new Csv();
        $csv->auto($filePath);
        return $csv;
    }

    /**
     * 
     * @param string $text
     *
     * @return string
     */
    protected static function getFixedDate($text)
    {
        if (empty($text)) {
            return \date(FacturaCliente::DATE_STYLE);
        }

        $parts = \explode('/', $text);
        if (\count($parts) !== 3) {
            return \date(FacturaCliente::DATE_STYLE, \strtotime($text));
        }

        $newText = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
        if (\strlen($parts[2]) == 2) {
            $newText = (int) $parts[2] > 70 ? $parts[0] . '-' . $parts[1] . '-19' . $parts[2] : $parts[0] . '-' . $parts[1] . '-20' . $parts[2];
        }

        return \date(FacturaCliente::DATE_STYLE, \strtotime($newText));
    }

    /**
     * 
     * @param float $totalIva
     * @param float $net
     *
     * @return float
     */
    protected static function getFactusolIVA($totalIva, $net)
    {
        if (empty(static::getFloat($net)) || empty(static::getFloat($net))) {
            return 0.0;
        }

        return static::getFloat($totalIva) * 100 / static::getFloat($net);
    }

    /**
     * 
     * @param string $value
     *
     * @return float
     */
    protected static function getFloat($value): float
    {
        return (float) \str_replace(',', '.', $value);
    }

    /**
     * 
     * @param string $value
     *
     * @return Serie
     */
    protected static function getSerie($value): Serie
    {
        /// find serie on the list
        if (isset(self::$series[$value])) {
            return self::$series[$value];
        }

        /// find serie on database
        $serie = new Serie();
        if (false === $serie->loadFromCode($value)) {
            /// create new serie
            $serie->codserie = $value;
            $serie->descripcion = 'FactuSol #' . $value;
            if ($serie->save()) {
                self::$series[$value] = $serie;
            }
        }

        return $serie;
    }

    /**
     * 
     * @param BusinessDocumentLine $line
     * @param float                $net
     * @param float                $totalIva
     * @param float                $re
     */
    protected static function setFactusolIVA(&$line, $net, $totalIva, $re)
    {
        $line->codimpuesto = null;
        $line->iva = static::getFactusolIVA($totalIva, $net);
        $line->pvpunitario = static::getFloat($net);
        $line->recargo = static::getFactusolIVA($re, $net);

        if (null === self::$impuestos) {
            $impuesto = new Impuesto();
            self::$impuestos = $impuesto->all();
        }

        foreach (self::$impuestos as $imp) {
            $subtotal = $line->pvpunitario * $imp->iva / 100;
            if (\abs($subtotal - static::getFloat($totalIva)) < 0.01) {
                $line->codimpuesto = $imp->codimpuesto;
                $line->iva = $imp->iva;
                $line->recargo = static::getFactusolIVA($re, $net);
                break;
            }
        }
    }

    /**
     * 
     * @return ToolBox
     */
    protected static function toolBox()
    {
        return new ToolBox();
    }
}
