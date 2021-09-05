<?php

namespace FacturaScripts\Plugins\Trazabilidad\Model;

use FacturaScripts\Core\Model\Base\JoinModel;
use FacturaScripts\Core\Model\FacturaCliente;

class FacturaProducto extends JoinModel
{
    public function __construct($data = [])
    {
       parent::__construct($data);
       $this->setMasterModel(new FacturaCliente());
    }

    protected function getFields(): array
    {
        return [
            'idproducto' => 'productos.idproducto',
            'idlinea' => 'lineasfacturascli.idlinea',
            'descripcion' => 'productos.descripcion',
            'description' => 'productos.description',
            'cifnif' => 'facturascli.cifnif',
            'fecha' => 'facturascli.fecha',
            'idfactura' => 'facturascli.idfactura',
            'idempresa' => 'facturascli.idempresa',
            'codigo' => 'facturascli.codigo',
            'numero2' => 'facturascli.numero2',
            'codcliente' => 'facturascli.codcliente',
            'nombrecliente' => 'facturascli.nombrecliente',
            'cifnif' => 'facturascli.cifnif',
            'direccion' => 'facturascli.direccion',
            'apartado' => 'facturascli.apartado',
            'codpostal' => 'facturascli.codpostal',
            'ciudad' => 'facturascli.ciudad',
            'provincia' => 'facturascli.provincia',
            'codpais' => 'facturascli.codpais',
            'observaciones' => 'facturascli.observaciones',
            'idestado' => 'facturascli.idestado',
            'femail' => 'facturascli.femail',
            'pagada' => 'facturascli.pagada',
            'netosindto' => 'facturascli.netosindto',
            'dtopor1' => 'facturascli.dtopor1',
            'dtopor2' => 'facturascli.dtopor2',
            'neto' => 'facturascli.neto',
            'totaliva' => 'facturascli.totaliva',
            'totalrecargo' => 'facturascli.totalrecargo',
            'irpf' => 'facturascli.irpf',
            'totalirpf' => 'facturascli.totalirpf',
            'totalsuplidos' => 'facturascli.totalsuplidos',
            'total' => 'facturascli.total',
            'codagente' => 'facturascli.codagente',
            'totalcomision' => 'facturascli.totalcomision',
            'fecha' => 'facturascli.fecha'
        ];
    }

    protected function getSQLFrom(): string
    {
        return 'lineasfacturascli inner join productos on productos.idproducto=lineasfacturascli.idproducto inner join facturascli on facturascli.idfactura=lineasfacturascli.idfactura';
    }

    protected function getTables(): array
    {
        return ['lineasfacturascli','productos','facturascli'];
    }

    public static function primaryColumn(): string {
        return 'facturascli.idfactura';
    }

    public static function primaryDescription(): string {
        return 'facturascli.codigo';
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

