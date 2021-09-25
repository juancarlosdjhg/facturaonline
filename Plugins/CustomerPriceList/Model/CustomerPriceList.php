<?php
namespace FacturaScripts\Plugins\CustomerPriceList\Model;

use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Cliente as DinCliente;

class CustomerPriceList extends Base\ModelClass {

    use Base\ModelTrait;

    public $codcustomerpricelist;
    public $codcliente;
    public $fechacaducidad;
    public $idproducto;
    public $pvp;
    public $codigoexterno;

    public function clear() {
        parent::clear();
        $this->codcustomerpricelist = (string) $this->newCode('codcustomerpricelist');

    }

    public static function primaryColumn(): string {
        return 'codcustomerpricelist';
    }

    public static function tableName(): string{
        return 'customerpricelists';
    }

    public function save()
    {


        //$dataBase = new DataBase();
        //$data = $dataBase->select('SELECT * from customerpricelists where codcliente='.$this->codcliente.' and idproducto='.$this->idproducto.' and ('.$this->fechainicio.' between fechainicio and fechafin or '.$this->fechafin.' between fechainicio and fechafin);');
        //
        //$string = $data[0];
        //$total= (integer) $string["total"];
        //if ($total === 0) {

            if (parent::save()) {
                return true;
            }
            
            $this->toolBox()->log()->warning('Ha ocurrido un error al guardar los datos, por favor contacte con Soporte.');
        //}
        
        //else {
        //    
        //    $this->toolBox()->log()->warning('El rango de fecha indicado coincide con otro rango ya existente para el producto en la lista de precios.');
        //}

        return false;
    }


    public function install()
    {
        /// needed dependencies
        new DinCliente();

        return parent::install();
    }

     /**
     * 
     * @return DinCliente
     */
    public function getSubject()
    {
        $customer = new DinCliente();
        $customer->loadFromCode($this->codcliente);
        return $customer;
    }

    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return empty($this->codcliente) || $type == 'list' ? parent::url($type, $list) : $this->getSubject()->url();
    }
}
