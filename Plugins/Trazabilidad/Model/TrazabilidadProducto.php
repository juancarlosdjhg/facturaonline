<?php

namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Plugins\Trazabilidad\Model\TrazabilidadProd;
use FacturaScripts\Plugins\Trazabilidad\Model\Trazabilidad;

class TrazabilidadProducto extends JoinModel
{
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
            'codtrazabilidad' => 'trazabilidades.codtrazabilidad',
            'partida' => 'trazabilidades.partida',
            'lote' => 'trazabilidades.lote',
            'procedencia' => 'trazabilidades.procedencia',
            'fechaproduccion' => 'trazabilidades.fechaproduccion',
            'fechacaducidad' => 'trazabilidades.fechacaducidad',
            'descripciontrazabilidad' => 'trazabilidades.descripcion',
            'codtrazabilidadprod' => 'trazabilidadesprod.codtrazabilidadprod'
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'trazabilidades left join trazabilidadesprod on trazabilidades.codtrazabilidad = trazabilidadesprod.codtrazabilidad left join productos on productos.idproducto = trazabilidadesprod.idproducto';
    }

    protected function getTables(): array
    {
        return ['trazabilidades','trazabilidadesprod','productos'];
    }

    public static function primaryColumn(): string {
        return 'trazabilidadesprod.codtrazabilidadprod';
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

