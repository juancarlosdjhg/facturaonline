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
        $timeInicio = strtotime($this->fechainicio);
        $timeFin = strtotime($this->fechafin);
        $newFormatInicio = date('Y-m-d',$timeInicio);
        $newFormatFin = date('Y-m-d',$timeFin);
        if ($newFormatFin < $newFormatInicio){
            $this->toolBox()->log()->warning('Rango de fechas no válido.');
        }
        else {
            $dataBase = new DataBase();
            $sql = '
            SELECT 
                count(*) as total 
            from 
                customerpricelists 
            where 
                codcliente='.$this->codcliente.' 
            and 
                idproducto='.$this->idproducto.' 
            and 
                (
                    (
                        (
                            CAST(fechainicio AS DATE) <= CAST("'.$newFormatInicio.'" AS DATE) and CAST(fechafin AS DATE) >= CAST("'.$newFormatInicio.'" AS DATE)
                        )
                    or 
                        (
                            CAST(fechainicio AS DATE) <= CAST("'.$newFormatFin.'" AS DATE) and CAST(fechafin AS DATE) >= CAST("'.$newFormatFin.'" AS DATE)
                        )
                    )
                or
                    (
                        (
                            CAST("'.$newFormatInicio.'" AS DATE) <= CAST(fechainicio AS DATE) and CAST("'.$newFormatFin.'" AS DATE) >= CAST(fechainicio AS DATE)
                        )
                    or 
                        (
                            CAST("'.$newFormatInicio.'" AS DATE) <= CAST(fechafin AS DATE) and CAST("'.$newFormatFin.'" AS DATE) >= CAST(fechafin AS DATE)
                        )
                    )
                )
            and 
                codcustomerpricelist <> '.$this->codcustomerpricelist.'
            and 
                estado = "Activo"
            ;'; 

            $data = $dataBase->select($sql);
            $total= (integer) $data[0]['total'];

            if ($total === 0) {
                if (parent::save()) {
                    return true;
                }            
                $this->toolBox()->log()->warning('Ha ocurrido un error al guardar los datos, por favor contacte con Soporte.');
            }

            else {            
                $this->toolBox()->log()->warning('El rango de fecha indicado coincide con otro rango ya existente para el producto en la lista de precios.');
            }

            return false;
        }
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
