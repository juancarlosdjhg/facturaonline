<?php

namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Plugins\Trazabilidad\Model\Trazabilidad;

class TrazabilidadProducto extends JoinModel
{

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
        return 'trazabilidades inner join trazabilidadesprod on trazabilidades.codtrazabilidad = trazabilidadesprod.codtrazabilidad inner join productos on productos.idproducto = trazabilidadesprod.idproducto';
    }

    protected function getTables(): array
    {
        return ['trazabilidades','trazabilidadesprod','productos'];
    }

    public function primaryColumnValue()
    {
        return $this->codtrazabilidadesprod;
    }

}
