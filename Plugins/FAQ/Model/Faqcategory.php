<?php
namespace Facturascripts\Plugins\Faq\Model; //Nombre de la carpeta
use FacturaScripts\Core\Model\Base;

class Faqcategory extends Base\ModelClass{
    use Base\ModelTrait;

    public $idcategory;
    public $namecategory;

    public function clear() { //reseteamos los valores por defecto
        parent::clear();
    }

    public static function primaryColumn(): string { //imprescindible
        return 'idcategory';
    }

    public static function tableName(): string { //imprescindible
        return 'faqcategories';
    }
}
