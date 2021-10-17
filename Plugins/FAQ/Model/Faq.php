<?php
namespace Facturascripts\Plugins\Faq\Model; //Nombre de la carpeta
use FacturaScripts\Core\Model\Base;

class Faq extends Base\ModelClass{
    use Base\ModelTrait;

    public $idfaq;
    public $creationdate;
    public $idcategory;
    public $symptom;
    public $cause;
    public $solution;
    public $comments;

    public function clear() { //reseteamos los valores por defecto
        parent::clear();
        $this->creationdate = date ('d-m-Y'); //asigna el día hoy al valor por defecto
    }

    public static function primaryColumn(): string { //imprescindible
        return 'idfaq';
    }

    public static function tableName(): string { //imprescindible
        return 'faqs';
    }
}
