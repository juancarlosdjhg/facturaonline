<?php
/**
 *  This plugin is property of DimaraCanarias www.dimaracanarias.es
 */

namespace FacturaScripts\Plugins\InformesFacturaOnline\Controller;

use FacturaScripts\Dinamic\Model\Serie;
use FacturaScripts\Dinamic\Model\Divisa;
use FacturaScripts\Dinamic\Model\EstadoDocumento;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Dinamic\Lib\Export\XLSExport;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\App\AppSettings;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;


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
            
            case 'pdfdownload':
                $this->defaultAction();
                $this->downloadPDFAction();
                break;
            
            case 'pdfcustomersdownload':
                $this->defaultAction();
                $this->downloadPDFActionCustomers();
                break;
            
            case 'pdfsuppliersdownload':
                $this->defaultAction();
                $this->downloadPDFActionSuppliers();
                break;

            default:
                $this->defaultAction();
        }
    }

    protected function defaultAction()
    {
        $this->fechadesde = $this->request->request->get('date-from');
        $this->fechahasta = $this->request->request->get('date-to');
        $this->serie = $this->request->request->get('serie');
        $this->divisa = $this->request->request->get('divisa');
        $this->estado = $this->request->request->get('estado');
        $this->proveedor = $this->request->request->get('proveedor');
        $this->cliente = $this->request->request->get('cliente');
        $this->pagos = $this->request->request->get('pagos'); 
        $this->observaciones = $this->request->request->get('observaciones');

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
            'fecha' => $i18n->trans('date'), 
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'num2' => $i18n->trans('externalordernumber'), 
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
            'fecha' => $i18n->trans('date'), 
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'numproveedor' => 'Número proveedor',
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
                $sql .= " and upper(f.codserie) like upper('" . $this->serie . "') ";
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
                'fecha' => $row['fecha'],
                'codserie' => $row['codserie'],
                'codigo' => $row['codigo'],
                'numero2' => $row['numero2'],
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
                $sql .= " and upper(f.codserie) like upper('" . $this->serie . "') ";
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
                'fecha' => $row['fecha'],
                'codserie' => $row['codserie'],
                'codigo' => $row['codigo'],
                'numproveedor' => $row['numproveedor'],
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

    protected function downloadPDFActionCustomers()
    {
        $logoPath='Core\Assets\Images\logo.png';
        $i18n = $this->toolBox()->i18n();
        $fileName = "InformeComprasVentas_Ventas.pdf";
        $appSettings = new AppSettings();

        $empresa = new Empresa();
        
        if ($empresa->loadFromCode($appSettings->get('default', 'idempresa'))){
            $detallesEmpresa = $empresa->nombre . ' - ' . $empresa->tipoidfiscal . ': ' . $empresa->cifnif . '<br>' . $empresa->direccion . ' - ' . $empresa->ciudad . ' - ' . $empresa->provincia . '<br>' . $empresa->telefono1 . ' - ' . $empresa->email;
        }
        
        $headers = [
            'fecha' => $i18n->trans('date'), 
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'cliente' => $i18n->trans('customer'),
            'cifnif' => $i18n->trans('cifnif'),
            'neto' => $i18n->trans('net'), 
            'iva' => $i18n->trans('vat'), 
            'irpf' => $i18n->trans('irpf'), 
            'total' => $i18n->trans('total')
        ];

        $this->loadCustomersData();
        $rows = $this->customersData;
        
        $orientation = 'P';

        $config = [
            'format' => 'A4-' . $orientation,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'tempDir' => \FS_FOLDER . '/MyFiles/Cache'
        ];

        $mpdf = new Mpdf($config);
        $mpdf->SetCreator('FacturaOnline');

        $title = '<table class="table-big">'
                . '<tr>'
                . '<td align="left"><img src="' . $logoPath . '" height="150px"/></td>'
                . '<td align="center" class="header-title">'
                . '<h2> Informe de Compras - Ventas </h2>'
                . '<p>'. $detallesEmpresa .'</p>'
                . '</td>'
                . '</tr>'
                . '</table>';
        
        $this->title = $title;

        $mpdf->WriteHTML($this->html($headers, $rows));

        $mpdf->Output($fileName, Destination::DOWNLOAD);
    }

    protected function downloadPDFActionSuppliers()
    {
        $logoPath='Core\Assets\Images\logo.png';
        $i18n = $this->toolBox()->i18n();
        $fileName = "InformeComprasVentas_Compras.pdf";
        $appSettings = new AppSettings();
        $empresa = new Empresa();
        
        if ($empresa->loadFromCode($appSettings->get('default', 'idempresa'))){
            $detallesEmpresa = $empresa->nombre . ' - ' . $empresa->tipoidfiscal . ': ' . $empresa->cifnif . '<br>' . $empresa->direccion . ' - ' . $empresa->ciudad . ' - ' . $empresa->provincia . '<br>' . $empresa->telefono1 . ' - ' . $empresa->email;
        }
        
        
        $headers = [
            'fecha' => $i18n->trans('date'), 
            'serie' => $i18n->trans('serie'), 
            'codigo' => $i18n->trans('code'), 
            'cliente' => $i18n->trans('customer'),
            'cifnif' => $i18n->trans('cifnif'),
            'neto' => $i18n->trans('net'), 
            'iva' => $i18n->trans('vat'), 
            'irpf' => $i18n->trans('irpf'), 
            'total' => $i18n->trans('total')
        ];

        $this->loadSuppliersData();
        $rows = $this->suppliersData;
        
        $orientation = 'P';

        $config = [
            'format' => 'A4-' . $orientation,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'tempDir' => \FS_FOLDER . '/MyFiles/Cache'
        ];

        $mpdf = new Mpdf($config);
        $mpdf->SetCreator('FacturaOnline');

        $title = '<table class="table-big">'
                . '<tr>'
                . '<td align="left"><img src="' . $logoPath . '" height="150px"/></td>'
                . '<td align="center" class="header-title">'
                . '<h2> Informe de Compras - Ventas </h2>'
                . '<p>'. $detallesEmpresa .'</p>'
                . '</td>'
                . '</tr>'
                . '</table>';
        
        $this->title = $title;

        $mpdf->WriteHTML($this->html($headers, $rows));

        $mpdf->Output($fileName, Destination::DOWNLOAD);
    }

    protected function html($headers, $rows): string
    {
        $css = 'body {font-color: black; font-family: arial; font-size: 12px;}'
        . '.font-big {font-size: 14px;}'
        . '.m2 {margin: 2px;}'
        . '.m3 {margin: 3px;}'
        . '.m4 {margin: 4px;}'
        . '.m5 {margin: 5px;}'
        . '.m10 {margin: 10px;}'
        . '.p2 {padding: 2px;}'
        . '.p3 {padding: 3px;}'
        . '.p4 {padding: 4px;}'
        . '.p5 {padding: 5px;}'
        . '.p10 {padding: 30px;}'
        . '.spacer {font-size: 8px;}'
        . '.text-center {text-align: center;}'
        . '.text-left {text-align: left;}'
        . '.text-right {text-align: right;}'
        . '.border1 {border: 1px solid black;}'
        . '.no-border {border: 0px;}'
        . '.primary-box {background-color: white; color: black; padding: 12px; '
        . 'text-transform: uppercase; font-size: 14px; font-weight: bold;}'
        . '.seccondary-box {background-color: white; padding: 12px; '
        . 'text-transform: uppercase; font-size: 14px; font-weight: bold;}'
        . '.title {color: black; font-size: 14px;}'
        . '.table-big {width: 100%;}'
        . '.table-lines {height: 14px;}'
        . '.end-text {font-size: 12px; text-align: left;}'
        . '.footer-text {font-size: 12px; text-align: left;}'
        . '.header-title {border: 1px solid black; border-radius: 6px;}'
        . ' tr.bordernegro {border: 1px solid black;}'
        ;
        
        $this->addTable($headers, $rows);

        return '<html>'
        . '<head>'
        . '<title>Informe de Compras - Ventas </title>'
        . '<style>' . $css . '</style>'
        . '</head>'
        . '<body>' . $this->title . '<br>' . $this->body . '</body>'
        . '</html>';
    }
    
    /**
     *
     * @return array
     */
    
    
    /**
     * 
     * @param array $headers
     * @param array $rows
     * @param array $alignments
     */
    public function addTable($headers, $rows)
    {
        $countColor = 0;
        $fechaDesde = date("d-m-Y", strtotime($this->fechadesde));
        $fechaHasta = date("d-m-Y", strtotime($this->fechahasta));

        $html = '<caption align="top">Desde: '. $fechaDesde . ' - Hasta: ' . $fechaHasta . ' ';
        
        if (!empty($this->serie) ) {
            $html .= '<br>Serie: ' . $this->serie . ' ';
        }
        
        if (!empty($this->divisa) ) {
            $html .= '<br>Divisa: ' . $this->divisa . ' ';
        }
        
        if (!empty($this->estado) ) {
            if($this->estado === 'nueva'){
                $html .= '<br>Estado: Nuevas';
            }
            elseif($this->estado === 'completada'){
                $html .= '<br>Estado: Completadas';
            }
            else{
                $html .= '<br>Estado: ' . $this->estado . ' ';
            }
        }
            
        if (!empty($this->pagos) ) {
            if($this->pagos === 'Pagadas'){
                $html .= '<br>Pago: Pagadas';
            }
            elseif($this->pagos === 'Pendientes'){
                $html .= '<br>Pago: Pendientes de pago';
            }
            else{
                $html .= '<br>Pagos: ' . $this->pagos . ' ';
            }
        }
        
        if (!empty($this->observaciones) ) {
            $html .= '<br><br><b>Observaciones: </b>' . $this->observaciones . ' ';
        }

        $html .= '</caption> '
                .'<thead>    '
                .'<tr>';

        foreach ($headers as $key => $title) {
            if ($title === 'Cliente'){
                $html .= '<th nowrap align="center" style="border: 1px solid black; width: 60%;">' . $title . '</th>';
            }
            elseif ($title === 'Código'){
                $html .= '<th nowrap align="center" style="border: 1px solid black; width: 25%;">' . $title . '</th>';
            }
            elseif ($title === 'Serie'){
                $html .= '<th nowrap align="center" style="border: 1px solid black; width: 10%;">' . $title . '</th>';
            }
            else {
                $html .= '<th align="center" style="border: 1px solid black; width: 22%;">' . $title . '</th>';
            }
        }

        $html .= '</tr>    '
                .'</thead> ';
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {

                if ($key === 'numproveedor' || $key === 'numero2'){
                }

                elseif ($key === 'nombrecliente' || $key === 'nombre'){
                        if ($countColor %2==0){
                            $html .= '<td align="left" >' . $cell . '</td>';
                        }
                        else {
                            $html .= '<td align="left" style="background-color: #EEE;">' . $cell . '</td>';
                        }
                }

                elseif($key === 'neto' || $key === 'totaliva' || $key === 'totalirpf' || $key === 'total'){
                    if (strtoupper($this->divisa) === 'EUR'){
                        if ($countColor %2==0){
                            $html .= '<td align="right">' . $this->toolBox()->coins()->format($cell, 2) . '</td>';
                        }
                        else {
                            $html .= '<td align="right" style="background-color: #EEE; width: 20%;">' . $this->toolBox()->coins()->format($cell, 2) . '</td>';
                        }
                    }
                    else {
                        if ($countColor %2==0){
                            $html .= '<td align="right">' . $this->toolBox()->coins()->format($cell, 2) . '</td>';
                        }
                        else {
                            $html .= '<td align="right" style="background-color: #EEE; width: 20%;">' . $this->toolBox()->coins()->format($cell, 2) . '</td>';
                        }
                    }
                }

                else {
                    if ($countColor %2==0){
                        $html .= '<td align="right">' . $cell . '</td>';
                    }
                    else {
                        $html .= '<td align="right" style="background-color: #EEE; width: 20%;">' . $cell . '</td>';
                    }
                }
                $countColor++;

            }
            $html .= '</tr>';
        }

        $this->writeHTML('<table class="table-big table-list border1" style="width: 50%;">' . $html . '</table><br/>');
    }

    public function writeHTML(string $html)
    {
        $this->body .= $html;
    }

}
