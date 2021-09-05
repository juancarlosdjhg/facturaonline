<?php
namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base;

class Trazabilidad extends Base\ModelClass {

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
}
