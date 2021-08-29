<?php
namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base;

class TrazabilidadProd extends Base\ModelClass {

    use Base\ModelTrait;

    public $codtrazabilidad;
    public $partida;
    public $lote;
    public $procedencia;
    public $fechaproduccion;
    public $fechacaducidad;
    public $descripcion;

    public function clear() {
        parent::clear();
    }

    public static function primaryColumn(): string {
        return 'codtrazabilidad';
    }

    public static function tableName(): string{
        return 'trazabilidades';
    }
/* 
    public function save(): string{
        if ($trazabilidadprod->save($trazabilidadprod)) {
            return true;
        }

        $this->delete($values);
        return false;
    }
 */

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
