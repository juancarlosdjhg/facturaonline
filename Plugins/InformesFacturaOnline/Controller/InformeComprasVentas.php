<?php
/**
 *  This plugin is property of DimaraCanarias www.dimaracanarias.es
 */

namespace FacturaScripts\Plugins\InformesFacturaOnline\Controller;

use FacturaScripts\Dinamic\Model\Serie;
use FacturaScripts\Dinamic\Model\Divisa;
use FacturaScripts\Dinamic\Model\EstadoDocumento;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Dinamic\Lib\Export\XLSExport;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of Modelo115
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class InformeComprasVentas extends Controller
{
    /**
     * 
     * @return array
     */
    public function allSeries()
    {
        $serie = new Serie();
        return $serie->all([], ['codserie' => 'ASC'], 0, 0);
    }

    /**
     * 
     * @return array
     */
    public function allDivisas()
    {
        $divisa = new Divisa();
        return $divisa->all([], ['coddivisa' => 'ASC'], 0, 0);
    }

    /**
     * 
     * @return array
     */
    public function allEstados()
    {
        $estado = new EstadoDocumento();
        return $estado->all([], ['idestado' => 'ASC'], 0, 0);
    }

    /**
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'reports';
        $data['title'] = 'buys-sales-report';
        $data['icon'] = 'fas fa-file-invoice-dollar';
        return $data;
    }
    
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action', '');
        switch ($action) {
            case 'download':
                $this->defaultAction();
                $this->downloadAction();
                break;

            default:
                $this->defaultAction();
        }
    }

    protected function defaultAction()
    {
        
        /// get last exercise code
        /*$codejercicio = null;
        $exerciseModel = new Ejercicio();
        foreach ($exerciseModel->all([], ['fechainicio' => 'DESC'], 0, 0) as $exe) {
            if ($exe->isOpened()) {
                $codejercicio = $exe->codejercicio;
                break;
            }
        }

        $this->amount = (float)$this->request->request->get('amount', $this->amount);
        $this->codejercicio = $this->request->request->get('codejercicio', $codejercicio);
        $this->examine = $this->request->request->get('examine', $this->examine);
        $this->excludeIrpf = (bool)$this->request->request->get('excludeirpf', $this->excludeIrpf);
        */

        $this->fechadesde = $this->request->request->get('date-from');
        $this->fechahasta = $this->request->request->get('date-to');
        $this->serie = $this->request->request->get('serie');
        $this->divisa = $this->request->request->get('divisa');
        $this->estado = $this->request->request->get('estado');
        $this->proveedor = $this->request->request->get('proveedor');
        $this->cliente = $this->request->request->get('cliente');


        /*print("Fecha desde:" . $this->fechadesde . ", Fecha hasta: ". $this->fechahasta . $this->serie . $this->divisa . $this->estado . $this->proveedor . $this->cliente);*/
        $this->loadCustomersData();
        $this->loadSuppliersData();
    }

    protected function downloadAction()
    {
        $this->setTemplate(false);
        $xlsExport = new XLSExport();
        $xlsExport->newDoc($this->toolBox()->i18n()->trans('buys-sales-report'), 0, '');

        $i18n = $this->toolBox()->i18n();

        /// customers data
        $customersHeaders = [
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'num2' => $i18n->trans('externalordernumber'), 
            'fecha' => $i18n->trans('date'), 
            'cliente' => $i18n->trans('customer'),
            'cifnif' => $i18n->trans('cifnif'),
            'neto' => $i18n->trans('net'), 
            'iva' => $i18n->trans('vat'), 
            'irpf' => $i18n->trans('irpf'), 
            'total' => $i18n->trans('total')
        ];
        $name = 'Doc. Ventas';
        $rows1 = $this->customersData;
        $xlsExport->addTablePageName($customersHeaders, $rows1, $name);

        /// suppliers data
        $suppliersHeaders = [
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'numproveedor' => 'Número proveedor',
            'fecha' => $i18n->trans('date'), 
            'cliente' => $i18n->trans('customer'),
            'cifnif' => $i18n->trans('cifnif'),
            'neto' => $i18n->trans('net'), 
            'iva' => $i18n->trans('vat'), 
            'irpf' => $i18n->trans('irpf'), 
            'total' => $i18n->trans('total')
        ];
        $name = 'Doc. Compras';
        $rows2 = $this->suppliersData;
        $xlsExport->addTablePageName($suppliersHeaders, $rows2, $name);

        $xlsExport->show($this->response);
    }

    protected function groupTotals(&$item, $row)
    {
        $item['total'] = (float)$row['total'];
    }

    /**
     *
     * @return array
     */
    protected function getCustomersDataInvoices(): array
    {
        if ($this->fechadesde === NULL) {
            $sql = "Select * From Facturascli Where False ";
        }
        
        else {
            $sql = 
                "Select 
                    f.codigo,
                    f.codcliente,
                    f.codserie,
                    f.numero2,
                    f.fecha,
                    concat(c.nombre, ' - ', c.razonsocial) as nombrecliente,
                    f.cifnif,
                    f.neto,
                    f.totaliva,
                    f.totalirpf,
                    f.total
                From 
                    Facturascli F
                Inner Join
                    Estados_Documentos ED On F.IdEstado = ED.IdEstado
                Inner Join
                    Clientes C On F.CodCliente = C.CodCliente
                Where
                    Lower(ED.Nombre) like '%completad%' " ;

            if (!empty($this->fechadesde)) {
                $sql .= " And ( fecha between '" . $this->fechadesde . "' And '" . $this->fechahasta . "') ";
            }

            if (!empty($this->serie) ) {
                $sql .= " And Upper(codserie) like Upper('" . $this->serie . "') ";
            }

            if (!empty($this->divisa) ) {
                $sql .= " And Upper(coddivisa) like Upper('" . $this->divisa . "') ";            
            }

            if (!empty($this->estado) ) {
                $sql .= " And Upper(estado) like Upper('" . $this->estado . "') ";            
            }

            if (!empty($this->cliente) ) {
                $sql .= " And ( Upper(c.cifnif) like Upper('%" . $this->cliente . "%') OR Upper(nombrecliente) like Upper('%" . $this->cliente . "%') OR UPPER(direccion) like UPPER('%" . $this->cliente . "%') OR UPPER(c.nombre) like UPPER('%" . $this->cliente . "%') OR UPPER(c.razonsocial) like UPPER('%" . $this->cliente . "%') ) ";
            }

            $sql .= " ORDER BY codigo;";
        }

        //print($sql);
        $items = [];
        $invoices = 0;

        foreach ($this->dataBase->select($sql) as $row) {

            $items[$invoices] = [
                'codserie' => $row['codserie'],
                'codigo' => $row['codigo'],
                'numero2' => $row['numero2'],
                'fecha' => $row['fecha'],
                'nombrecliente' => $row['nombrecliente'],
                'cifnif' => $row['cifnif'],
                'neto' => $row['neto'],
                'totaliva' => $row['totaliva'],
                'totalirpf' => $row['totalirpf'],
                'total' => $row['total']
            ];

            $invoices++;

            /*
            $this->groupTotals($items[$codcliente], $row);
            */
        }

        return $items;
    }

    /**
     *
     * @return array
     */
    protected function getSuppliersDataInvoices(): array
    {
        if ($this->fechadesde === NULL) {
            $sql = "Select * From Facturasprov Where False ";
        }
        
        else {
            $sql = 
                "Select 
                    f.codproveedor,
                    f.codserie,
                    f.codigo,
                    f.numproveedor,
                    f.fecha,
                    concat(p.nombre, ' - ', p.razonsocial) as nombre,
                    f.cifnif,
                    f.neto,
                    f.totaliva,
                    f.totalirpf,
                    f.total
                From 
                    FacturasProv F
                Inner Join
                    Estados_Documentos ED On F.IdEstado = ED.IdEstado
                Inner Join
                    Proveedores P On P.CodProveedor = F.CodProveedor
                Where
                    Lower(ED.Nombre) like '%completad%' " ;

            if (!empty($this->fechadesde) ) {
                $sql .= " And ( fecha between '" . $this->fechadesde . "' And '" . $this->fechahasta . "') ";
            }

            if (!empty($this->serie) ) {
                $sql .= " And Upper(codserie) like Upper('" . $this->serie . "') ";
            }

            if (!empty($this->divisa) ) {
                $sql .= " And Upper(coddivisa) like Upper('" . $this->divisa . "') ";            
            }

            if (!empty($this->estado) ) {
                $sql .= " And Upper(estado) like Upper('" . $this->estado . "') ";            
            }

            if (!empty($this->proveedor) ) {
                $sql .= " And ( Upper(p.cifnif) like Upper('%" . $this->proveedor . "%') OR Upper(p.nombre) like Upper('%" . $this->proveedor . "%') OR UPPER(f.observaciones) like UPPER('%" . $this->proveedor . "%') OR UPPER(p.nombre) like UPPER('%" . $this->proveedor . "%') OR UPPER(p.razonsocial) like UPPER('%" . $this->proveedor . "%') ) ";
            }

            $sql .= " ORDER BY codigo;";
        }

        $items = [];
        $invoices = 0;

        foreach ($this->dataBase->select($sql) as $row) {           

            $items[$invoices] = [
                'codserie' => $row['codserie'],
                'codigo' => $row['codigo'],
                'numproveedor' => $row['numproveedor'],
                'fecha' => $row['fecha'],
                'nombre' => $row['nombre'],
                'cifnif' => $row['cifnif'],
                'neto' => $row['neto'],
                'totaliva' => $row['totaliva'],
                'totalirpf' => $row['totalirpf'],
                'total' => $row['total']
            ];

            $invoices++;

            /* 
            $this->groupTotals($items[$codproveedor], $row);
            */
        }

        return $items;
    }

    
    protected function loadCustomersData()
    {
        $this->customersData = $this->getCustomersDataInvoices();
    }

    protected function loadSuppliersData()
    {
        $this->suppliersData = $this->getSuppliersDataInvoices();
    }    
}
