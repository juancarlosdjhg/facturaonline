<?php
namespace FacturaScripts\Plugins\CustomerPriceList\Model;

use FacturaScripts\Core\Model\Base;

use FacturaScripts\Dinamic\Model\Cliente as DinCliente;

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
