<?php
/**
 * This file is part of DocumentosRecurrentes plugin for FacturaScripts.
 * FacturaScripts         Copyright (C) 2015-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 * DocumentosRecurrentes  Copyright (C) 2020-2021 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\DocumentosRecurrentes\Model\Base;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\CompanyRelationTrait;
use FacturaScripts\Core\Model\Base\CurrencyRelationTrait;
use FacturaScripts\Core\Model\Base\PaymentRelationTrait;
use FacturaScripts\Core\Model\Base\SerieRelationTrait;
use FacturaScripts\Dinamic\Model\Almacen;
use FacturaScripts\Dinamic\Model\DocTransformation;

/**
 * Model template for DocRecurring Purchase and Sale
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
abstract class DocRecurring extends ModelClass
{

    use CompanyRelationTrait,
        CurrencyRelationTrait,
        PaymentRelationTrait,
        SerieRelationTrait;

    const MODEL_NAMESPACE = '\\FacturaScripts\\Dinamic\\Model\\';

    public const TERM_TYPE_DAYS = 1;
    public const TERM_TYPE_WEEKS = 2;
    public const TERM_TYPE_MONTHS = 3;
    public const TERM_TYPE_MANUAL = 99;

    /**
     * Link to Almacen model
     *
     * @var string
     */
    public $codalmacen;

    /**
     * Days left to generate the next document.
     *
     * @var int|null
     */
    public $days;

    /**
     * End date of the automatic generation period.
     *
     * @var string
     */
    public $enddate;

    /**
     * Identifier of the type of document to be generated.
     *
     * @var string
     */
    public $generatedoc;

    /**
     * Primary Key.
     *
     * @var int
     */
    public $id;

    /**
     * Date indicating the last time the document was generated.
     *
     * @var string
     */
    public $lastdate;

    /**
     * Human description that identifies the template.
     *
     * @var string
     */
    public $name;

    /**
     * Date that indicates when the next automatic generation
     * of the document will be.
     *
     * @var string
     */
    public $nextdate;

    /**
     * User who created this document. User model.
     *
     * @var string
     */
    public $nick;

    /**
     * Any kind of note, clarification or reminder.
     *
     * @var text
     */
    public $notes;

    /**
     * Type of term for the automatic generation of the document.
     * See TERM_TYPE_* conts for more info.
     *
     * @var int
     */
    public $termtype;

    /**
     * Amount that is applied to the term type.
     *
     * @var int
     */
    public $termunits;

    /**
     * Start date of the automatic generation period.
     *
     * @var string
     */
    public $startdate;

    /**
     * Returns the lines associated with the document.
     */
    abstract public function getLines();

    /**
     * Returns a new line for the document.
     */
    abstract public function getNewLine(array $data = [], array $exclude = ['id', 'iddoc']);


    /**
     * Returns all children documents of this one.
     *
     * @return TransformerDocument[]
     */
    public function childrenDocuments()
    {
        $children = [];
        $docTransformation = new DocTransformation();
        $newModelClass = self::MODEL_NAMESPACE . $this->generatedoc;
        $order = ['iddoc2' => 'ASC'];
        $where = [
            new DataBaseWhere('model1', $this->modelClassName()),
            new DataBaseWhere('iddoc1', $this->id),
            new DataBaseWhere('model2', $this->generatedoc)
        ];

        foreach ($docTransformation->all($where, $order, 0, 0) as $docTrans) {
            $newModel = new $newModelClass();
            if ($newModel->loadFromCode($docTrans->iddoc2)) {
                $children[] = $newModel;
            }
        }

        return $children;
    }

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->days = null;
        $this->termtype = self::TERM_TYPE_MONTHS;
        $this->termunits = 1;
        $this->startdate = \date(self::DATE_STYLE);
    }

    /**
     * Returns all avaliable status for this type of document.
     * Return empty result becouse Document Recurring don't have status.
     *
     * @return []
     */
    public function getAvaliableStatus()
    {
        return [];
    }

    /**
     * Assign the values of the $data array to the model properties.
     *
     * @param array $data
     * @param array $exclude
     */
    public function loadFromData(array $data = array(), array $exclude = array())
    {
        parent::loadFromData($data, $exclude);
        if (($this->termtype !== self::TERM_TYPE_MANUAL) && (!empty($this->nextdate))) {
            $currentDay = date(self::DATE_STYLE);
            $this->days = $this->daysBetween($currentDay, $this->nextdate);
        }
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     * Returns the name of the column that describes the model, such as name, description...
     *
     * @return string
     */
    public function primaryDescriptionColumn(): string
    {
        return 'name';
    }

    /**
     * Returns true if there are no errors in the values of the model properties.
     * It runs inside the save method.
     *
     * @return bool
     */
    public function test()
    {
        $this->name = $this->toolBox()->utils()->noHtml($this->name);
        $this->notes = $this->toolBox()->utils()->noHtml($this->notes);
        $this->idempresa = $this->calculateCompany();
        $this->lastdate = $this->calculateLastDate();
        $this->nextdate = $this->calculateNextDate();
        return parent::test();
    }

    /**
     * Calculate id company from selected warehouse
     *
     * @return int
     */
    private function calculateCompany()
    {
        $warehouse = new Almacen();
        $warehouse->loadFromCode($this->codalmacen);
        return $warehouse->idempresa;
    }

    /**
     * Calculate the last date from generated recurring documents.
     *
     * @return string|null
     */
    private function calculateLastDate()
    {
        $children = $this->childrenDocuments();
        $lastDoc = end($children);
        if ($lastDoc === false) {
            return null;
        }

        $docDate = \strtotime($lastDoc->fecha);
        $startDate = \strtotime($this->startdate);
        if ($docDate < $startDate) {
            return null;
        }

        return $lastDoc->fecha;
    }

    /**
     * Calculate the next date to generate the recurring document.
     *
     * @return string|null
     */
    private function calculateNextDate()
    {
        if ($this->termtype == self::TERM_TYPE_MANUAL) {
            return null;
        }

        $prevDate = empty($this->lastdate) ? \strtotime($this->startdate) : \strtotime($this->lastdate);
        $nextDate = \strtotime($this->getTermTypeDateFormat(), $prevDate);
        if (empty($this->enddate) || \strtotime($this->enddate) >= $nextDate) {
            return \date(self::DATE_STYLE, $nextDate);
        }

        return null;
    }

    /**
     * Calculate number days between two dates
     *
     * @param string $start
     * @param string $end
     * @param boolean $increment
     * @return integer
     */
    private function daysBetween($start, $end, $increment = false): int
    {
        if (empty($start) || empty($end)) {
            return 0;
        }

        $diff = strtotime($end) - strtotime($start);
        $result = ceil($diff / 86400);
        if ($increment) {
            ++$result;
        }
        return $result;
    }

    /**
     *
     * @return string
     */
    private function getTermTypeDateFormat(): string
    {
        $format = '+' . $this->termunits . ' ';
        switch ($this->termtype) {
            case self::TERM_TYPE_DAYS:
                return $format . ' days';

            case self::TERM_TYPE_WEEKS:
                return $format . ' weeks';

            case self::TERM_TYPE_MONTHS:
                return $format . ' months';

            default:
                return '';
        }
    }
}
