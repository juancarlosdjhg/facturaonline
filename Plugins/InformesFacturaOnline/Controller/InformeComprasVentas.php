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
        $this->pagos = $this->request->request->get('pagos');


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
        if ($this->fechadesde === null) {
            $sql = "select * from facturascli where false ";
        }
        
        else {
            $sql = 
                "select 
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
                from 
                    facturascli f
                inner join
                    estados_documentos ed on f.idestado = ed.idestado
                inner join
                    clientes c on f.codcliente = c.codcliente
                where
                    true " ;

            if (!empty($this->fechadesde)) {
                $sql .= " and ( fecha between '" . $this->fechadesde . "' and '" . $this->fechahasta . "') ";
            }

            if (!empty($this->serie) ) {
                $sql .= " and upper(codserie) like upper('" . $this->serie . "') ";
            }

            if (!empty($this->divisa) ) {
                $sql .= " and upper(coddivisa) like upper('" . $this->divisa . "') ";            
            }

            if (!empty($this->cliente) ) {
                $sql .= " and ( upper(c.cifnif) like upper('%" . $this->cliente . "%') or upper(nombrecliente) like upper('%" . $this->cliente . "%') or upper(direccion) like upper('%" . $this->cliente . "%') or upper(c.nombre) like upper('%" . $this->cliente . "%') or upper(c.razonsocial) like upper('%" . $this->cliente . "%') ) ";
            }
            
            if (!empty($this->estado or $this->estado != NULL) ) {
                $sql .= " and ( upper(ed.nombre) like upper('%" . $this->estado . "%') ) ";
            }

            if ( !empty($this->pagos) or $this->pagos != NULL ) {
                if ( $this->pagos === 'Pagadas' ) {
                    $sql .= " and ( f.pagada = 1) ";
                }

                elseif ( $this->pagos === 'Pendientes' ) {
                    $sql .= " and ( f.pagada = 0 ) ";
                }
            }

            $sql .= " order by codigo;";
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
        if ($this->fechadesde === null) {
            $sql = "select * from facturasprov where false ";
        }
        
        else {
            $sql = 
                "select 
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
                from 
                    facturasprov f
                inner join
                    estados_documentos ed on f.idestado = ed.idestado
                inner join
                    proveedores p on p.codproveedor = f.codproveedor
                where
                    true " ;

            if (!empty($this->fechadesde) ) {
                $sql .= " and ( fecha between '" . $this->fechadesde . "' and '" . $this->fechahasta . "') ";
            }

            if (!empty($this->serie) ) {
                $sql .= " and upper(codserie) like upper('" . $this->serie . "') ";
            }

            if (!empty($this->divisa) ) {
                $sql .= " and upper(coddivisa) like upper('" . $this->divisa . "') ";            
            }

            if (!empty($this->proveedor) ) {
                $sql .= " and ( upper(p.cifnif) like upper('%" . $this->proveedor . "%') or upper(p.nombre) like upper('%" . $this->proveedor . "%') or upper(f.observaciones) like upper('%" . $this->proveedor . "%') or upper(p.nombre) like upper('%" . $this->proveedor . "%') or upper(p.razonsocial) like upper('%" . $this->proveedor . "%') ) ";
            }

            if (!empty($this->estado or $this->estado != NULL) ) {
                $sql .= " and ( upper(ed.nombre) like upper('%" . $this->estado . "%') ) ";
            }

            if ( !empty($this->pagos) or $this->pagos != NULL ) {
                if ( $this->pagos === 'Pagadas' ) {
                    $sql .= " and ( f.pagada = 1) ";
                }

                elseif ( $this->pagos === 'Pendientes' ) {
                    $sql .= " and ( f.pagada = 0 ) ";
                }
            }

            $sql .= " order by codigo;";
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
