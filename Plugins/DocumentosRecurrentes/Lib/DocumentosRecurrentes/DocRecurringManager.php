<?php
/**
 * This file is part of DocumentosRecurrentes plugin for FacturaScripts.
 * FacturaScripts         Copyright (C) 2015-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 * DocumentosRecurrentes  Copyright (C) 2020-2021 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\DocumentosRecurrentes\Lib\DocumentosRecurrentes;

use Exception;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Dinamic\Lib\BusinessDocumentTools;
use FacturaScripts\Dinamic\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Base\DocRecurring;
use FacturaScripts\Dinamic\Model\DocRecurringPurchase;
use FacturaScripts\Dinamic\Model\DocRecurringSale;
use FacturaScripts\Dinamic\Model\DocTransformation;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Core\Lib\CodePatterns;

/**
 * Description of DocRecurringGenerator
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class DocRecurringManager
{

    /**
     * Link to the document being created.
     *
     * @var BusinessDocument
     */
    protected $document;

    /**
     * Recurring document that acts as a template.
     *
     * @var DocRecurring
     */
    protected $template;

    /**
     *
     * @var string
     */
    private $date;

    /**
     *
     * @var bool
     */
    private $purchase = false;

    /**
     * Forces a new customer/supplier for the document to be generated.
     *
     * @var string
     */
    private $target = '';

    /**
     * Generate a purchase document based on the indicated template identifier.
     *
     * @param int   $iddoc
     * @param array $options
     */
    public function generatePurchaseDoc(int $iddoc, array $options): bool
    {
        $this->template = new DocRecurringPurchase();
        if (false == $this->template->loadFromCode($iddoc)) {
            return false;
        }

        $this->purchase = true;
        $this->date = $options['date'];
        $this->target = $options['target'] ?? '';
        return $this->generate();
    }

    /**
     * Generate a sale document based on the indicated template identifier.
     *
     * @param int   $iddoc
     * @param array $options
     */
    public function generateSaleDoc(int $iddoc, array $options): bool
    {
        $this->template = new DocRecurringSale();
        if (false == $this->template->loadFromCode($iddoc)) {
            return false;
        }

        $this->date = $options['date'];
        $this->target = $options['target'] ?? '';
        return $this->generate();
    }

    /**
     * Create a Business Document from Document Recurring template.
     *
     * @return bool
     */
    protected function createDocument(): bool
    {
        $newDocClass = '\\FacturaScripts\\Dinamic\\Model\\' . $this->template->generatedoc;
        $this->document = new $newDocClass();
        $subjectColumn = $this->document->subjectColumn();
        $this->document->{$subjectColumn} = empty($this->target)
            ? $this->template->{$subjectColumn}
            : $this->target;

        if (false == $this->document->updateSubject()) {
            return false;
        }

        $this->document->codserie = $this->template->codserie;
        $this->document->codpago = $this->template->codpago;
        $this->document->codalmacen = $this->template->codalmacen;
        $this->document->setDate($this->getDateForDocument(), '00:00:00');
        $this->document->setCurrency($this->template->coddivisa, $this->purchase);

        if (!empty($this->template->codagente)) {
            $this->document->codagente = $this->template->codagente;
        }

        if ($this->document->save()) {
            return true;
        }

        $this->toolBox()->i18nLog()->warning('doc-recurring-document-error');
        return false;
    }

    /**
     * Create a Business Document Lines from Document Recurring template.
     *
     * @return bool
     */
    protected function createDocumentLines(): bool
    {
        $docTrans = new DocTransformation();
        foreach ($this->template->getLines() as $templateLine) {
            $newLine = empty($templateLine->reference)
                ? $this->document->getNewLine()
                : $this->document->getNewProductLine($templateLine->reference);

            $newLine->cantidad = $templateLine->quantity;
            $this->setValueIfNotEmpty($newLine->pvpunitario, $templateLine->price);
            $this->setValueIfNotEmpty($newLine->dtopor, $templateLine->discount);
            $this->setDescFromTemplate($newLine->descripcion, $templateLine->name);

            if (false == $newLine->save()) {
                $this->toolBox()->i18nLog()->warning('doc-recurring-document-line-error');
                return false;
            }

            /// save documents relation
            $docTrans->clear();
            $docTrans->cantidad = 0;

            $docTrans->model1 = $this->template->modelClassName();
            $docTrans->iddoc1 = $this->template->primaryColumnValue();
            $docTrans->idlinea1 = $templateLine->primaryColumnValue();

            $docTrans->model2 = $this->document->modelClassName();
            $docTrans->iddoc2 = $this->document->primaryColumnValue();
            $docTrans->idlinea2 = $newLine->primaryColumnValue();

            if (false == $docTrans->save()) {
                $this->toolBox()->i18nLog()->warning('doc-recurring-document-relation-error');
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @return bool
     */
    protected function recalculateDocument(): bool
    {
        $tool = new BusinessDocumentTools();
        $tool->recalculate($this->document);
        return $this->document->save();
    }

    /**
     *
     * @return ToolBox
     */
    protected function toolBox()
    {
        return new ToolBox();
    }

    /**
     * Update the template date data.
     *
     * @return bool
     */
    protected function updateTemplate(): bool
    {
        $this->template->lastdate = $this->date;
        return $this->template->save();
    }

    /**
     * Main process. Create new document and document lines.
     *
     * @return bool
     */
    private function generate(): bool
    {
        $dataBase = new DataBase();
        $dataBase->beginTransaction();
        try {
            if ($this->createDocument() &&
                $this->createDocumentLines() &&
                $this->recalculateDocument() &&
                $this->updateTemplate()) {
                $dataBase->commit();
                $this->toolBox()->i18nLog()->notice('doc-recurring-generate-ok');
                return true;
            }
        } catch (Exception $exc) {
            $this->toolBox()->log()->error($exc->getMessage());
        } finally {
            if ($dataBase->inTransaction()) {
                $dataBase->rollback();
            }
        }

        return false;
    }

    /**
     * Avoid using a date before the date of the last sales invoice issued.
     *
     * @return string
     */
    private function getDateForDocument()
    {
        if ($this->template->generatedoc == 'FacturaCliente') {
            $where = [
                new DataBaseWhere('idempresa', $this->document->idempresa),
                new DataBaseWhere('codserie', $this->document->codserie)
            ];
            $doc = new FacturaCliente();
            foreach ($doc->all($where, ['fecha' => 'DESC'], 0, 1) as $lastDoc) {
                if (\strtotime($lastDoc->fecha) > \strtotime($this->date)) {
                    return $lastDoc->fecha;
                }
            }
        }
        return $this->date;
    }

    /**
     *
     * @param string $description
     * @param string $pattern
     */
    private function setDescFromTemplate(&$description, $pattern)
    {
        $this->setValueIfNotEmpty($description, $pattern);
        if (!empty($description)) {
            $description = CodePatterns::trans($description, $this->document);
        }
    }

    /**
     *
     * @param string $fieldValue
     * @param string $value
     */
    private function setValueIfNotEmpty(&$fieldValue, $value)
    {
        if (!empty($value)) {
            $fieldValue = $value;
        }
    }
}
