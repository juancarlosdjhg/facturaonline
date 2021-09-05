<?php

namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Core\Model\PresupuestoCliente;

class PresupuestoProducto extends JoinModel
{
    public function __construct($data = [])
    {
       parent::__construct($data);
       $this->setMasterModel(new PresupuestoCliente());
    }

    protected function getFields(): array
    {
        return [
            'idproducto' => 'productos.idproducto',
            'idlinea' => 'lineaspresupuestoscli.idlinea',
            'descripcion' => 'productos.descripcion',
            'description' => 'productos.description',
            'cifnif' => 'presupuestoscli.cifnif',
            'fecha' => 'presupuestoscli.fecha',
            'idpresupuesto' => 'presupuestoscli.idpresupuesto',
            'idempresa' => 'presupuestoscli.idempresa',
            'codigo' => 'presupuestoscli.codigo',
            'numero2' => 'presupuestoscli.numero2',
            'codcliente' => 'presupuestoscli.codcliente',
            'nombrecliente' => 'presupuestoscli.nombrecliente',
            'cifnif' => 'presupuestoscli.cifnif',
            'direccion' => 'presupuestoscli.direccion',
            'apartado' => 'presupuestoscli.apartado',
            'codpostal' => 'presupuestoscli.codpostal',
            'ciudad' => 'presupuestoscli.ciudad',
            'provincia' => 'presupuestoscli.provincia',
            'codpais' => 'presupuestoscli.codpais',
            'observaciones' => 'presupuestoscli.observaciones',
            'idestado' => 'presupuestoscli.idestado',
            'femail' => 'presupuestoscli.femail',
            'netosindto' => 'presupuestoscli.netosindto',
            'dtopor1' => 'presupuestoscli.dtopor1',
            'dtopor2' => 'presupuestoscli.dtopor2',
            'neto' => 'presupuestoscli.neto',
            'pedir' => 'lineaspresupuestoscli.pedir',
            'totaliva' => 'presupuestoscli.totaliva',
            'totalrecargo' => 'presupuestoscli.totalrecargo',
            'irpf' => 'presupuestoscli.irpf',
            'totalirpf' => 'presupuestoscli.totalirpf',
            'totalsuplidos' => 'presupuestoscli.totalsuplidos',
            'total' => 'presupuestoscli.total',
            'codagente' => 'presupuestoscli.codagente',
            'totalcomision' => 'presupuestoscli.totalcomision',
            'fecha' => 'presupuestoscli.fecha'
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'lineaspresupuestoscli inner join productos on productos.idproducto=lineaspresupuestoscli.idproducto inner join presupuestoscli on presupuestoscli.idpresupuesto=lineaspresupuestoscli.idpresupuesto';
    }

    protected function getTables(): array
    {
        return ['lineaspresupuestoscli','productos','presupuestoscli'];
    }

    public static function primaryColumn(): string {
        return 'presupuestoscli.idpresupuesto';
    }

    public static function primaryDescription(): string {
        return 'presupuestoscli.codigo';
    }

    //public function save(): string{
    //    $trazabilidad = new Trazabilidad();
    //    $trazabilidad->partida = $this->partida;
    //    $trazabilidad->lote = $this->lote;
    //    $trazabilidad->procedencia = $this->procedencia;
    //    $trazabilidad->fechaproduccion = $this->fechaproduccion;
    //    $trazabilidad->fechacaducidad = $this->fechacaducidad;
    //    $trazabilidad->descripcion = $this->descripcion;
//
//
    //    if ($trazabilidad->save($trazabilidad)) {
    //        return true;
    //    }
//
    //    else {
    //        return false;
    //    }
    //}
//
    //protected function saveInsert(array $values = [])
    //{
    //    if (parent::saveInsert($values)) {
    //        $trazabilidadprod = new TrazabilidadProd();
    //        $trazabilidadprod->codtrazabilidadprod = $this->codtrazabilidadprod;
    //        $trazabilidadprod->codtrazabilidad = $this->codtrazabilidad;
    //        $trazabilidadprod->idproducto = $this->idproducto;
    //        if ($trazabilidadprod->save($trazabilidadprod)) {
    //            return true;
    //        }
//
    //        $this->delete($values);
    //        return false;
    //    }
//
    //    return false;
    //}
}

