<?php

namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Plugins\Trazabilidad\Model\TrazabilidadProd;
use FacturaScripts\Plugins\Trazabilidad\Model\Trazabilidad;

class TrazabilidadProducto extends JoinModel
{
    const DOC_TABLE = 'trazabilidades';
    const MAIN_TABLE = 'trazabilidadesprod';

    public function __construct($data = [])
    {
       parent::__construct($data);
       $this->setMasterModel(new Trazabilidad());
    }
    
    protected function getFields(): array
    {
        return [
            'idproducto' => 'productos.idproducto',
            'referencia' => 'productos.referencia',
            'descripcionproducto' => 'productos.descripcion',
            'description' => 'productos.description',
            'codtrazabilidad' => static::MAIN_TABLE .'codtrazabilidad',
            'partida' => 'trazabilidades.partida',
            'lote' => 'trazabilidades.lote',
            'procedencia' => 'trazabilidades.procedencia',
            'fechaproduccion' => 'trazabilidades.fechaproduccion',
            'fechacaducidad' => 'trazabilidades.fechacaducidad',
            'descripciontrazabilidad' => 'trazabilidades.descripcion',
            'codtrazabilidadesprod' => static::MAIN_TABLE .'codtrazabilidadesprod'
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'trazabilidades left join '. static::MAIN_TABLE .' on trazabilidades.codtrazabilidad = '. static::MAIN_TABLE .'.codtrazabilidad left join productos on productos.idproducto = '. static::MAIN_TABLE .'.idproducto';
    }

    protected function getTables(): array
    {
        return ['trazabilidades', static::MAIN_TABLE ,'productos'];
    }

    public static function primaryColumn(): string {
        return static::MAIN_TABLE .'codtrazabilidadesprod';
    }

    public static function primaryDescription(): string {
        return 'trazabilidades.descripcion';
    }

    public function save(): string{
        $trazabilidad = new Trazabilidad();
        $trazabilidad->partida = $this->partida;
        $trazabilidad->lote = $this->lote;
        $trazabilidad->procedencia = $this->procedencia;
        $trazabilidad->fechaproduccion = $this->fechaproduccion;
        $trazabilidad->fechacaducidad = $this->fechacaducidad;
        $trazabilidad->descripcion = $this->descripcion;

        if ($trazabilidad->save($trazabilidad)) {
            return true;
        }

        else {
            return false;
        }
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
    }
}

