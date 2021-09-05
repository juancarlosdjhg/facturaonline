<?php
namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base;

class TrazabilidadProd extends Base\ModelClass {

    use Base\ModelTrait;

    public $codtrazabilidadprod;
    public $codtrazabilidad;
    public $idproducto;

    public function clear() {
        parent::clear();
    }
    
    public static function primaryColumn(): string {
        return 'codtrazabilidadprod';
    }
    
    public static function tableName(): string{
        return 'trazabilidadesprod';
    }
    
    public function save()
    {
        $this->codtrazabilidadprod = (string) $this->newCode('codtrazabilidadprod');
        return parent::saveInsert();
    }
    //public function guardarProductos(array $values = [])
    //{
    //    $num = 0;
    //    foreach ($values as $valores){
    //        if (true === $dataBase->exec("insert into trazabilidadesprod values (". $valores->codtrazabilidadprod[$num] .", ". $valores->codtrazabilidad[$num] .", ". $valores->code[$num] . ");")){
    //            $num++;
    //        }
//
    //        return true 
    //    }
    //}

    //public function save(): string{
    //    if ($trazabilidadprod->save($trazabilidadprod)) {
    //        return true;
    //    }
//
    //    $this->delete($values);
    //    return false;
    //}

/*     use Base\ModelTrait;

    public $codtrazabilidadprod;
    public $codtrazabilidad;
    public $idproducto;

    public function clear() {
        parent::clear();
    }

    public static function primaryColumn(): string {
        return 'codtrazabilidadprod';
    }

    public static function tableName(): string{
        return 'trazabilidadesprod';
    }

    protected function saveInsert(array $values = [])
    {
        if (parent::saveInsert($values)) {
            $trazabilidadprod = new TrazabilidadProd();
            $trazabilidadprod->codtrazabilidadprod = $this->codtrazabilidadprod;
            $trazabilidadprod->codtrazabilidad = $this->codtrazabilidad;
            $trazabilidadprod->idproducto = $this->idproducto;
            if ($trazabilidadprod->save($trazabilidadprod)) {
                return true;
            }

            $this->delete($values);
            return false;
        }

        return false;
    } */

}
