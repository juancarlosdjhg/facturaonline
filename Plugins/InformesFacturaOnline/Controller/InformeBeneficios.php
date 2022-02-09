<?php
/**
 *  This plugin is property of DimaraCanarias www.dimaracanarias.es
 */

namespace FacturaScripts\Plugins\InformesFacturaOnline\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Model\Empresa;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use FacturaScripts\Core\App\AppSettings;


/**
 * Description of Modelo115
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class InformeBeneficios extends Controller
{
    protected $body = '';

    /**
     * 
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'reports';
        $data['title'] = 'benefits-report';
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

        $this->fechadesde = $this->request->request->get('date-from');
        $this->fechahasta = $this->request->request->get('date-to');

        $this->loadData();
    }

    protected function downloadAction()
    {
        $logoPath='Core\Assets\Images\logo.png';
        $i18n = $this->toolBox()->i18n();
        $fileName = "InformeDeBeneficios.pdf";
        $appSettings = new AppSettings();

        $empresa = new Empresa();
        
        if ($empresa->loadFromCode($appSettings->get('default', 'idempresa'))){
            $detallesEmpresa = $empresa->nombre . ' - ' . $empresa->tipoidfiscal . ': ' . $empresa->cifnif . '<br>' . $empresa->direccion . ' - ' . $empresa->ciudad . ' - ' . $empresa->provincia . '<br>' . $empresa->telefono1 . ' - ' . $empresa->email;
        }
        
        
        $headers = [
            'referencia' => $i18n->trans('code'), 
            'descripcion' => $i18n->trans('product'), 
            'cantidadcompras' => $i18n->trans('bought-units'), 
            'importecompras' => $i18n->trans('bought-amount'), 
            'cantidadventas' => $i18n->trans('sales-units'),
            'importeventas' => $i18n->trans('sales-amount'),
            'beneficio' => $i18n->trans('benefit'), 
            'porcentaje' => '%'
        ];

        $this->loadData();
        $rows = $this->data;
        
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
                . '<h2> Informe de Compras - Ventas - Beneficios </h2>'
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
            . '<title>Informe de Beneficios</title>'
            . '<style>' . $css . '</style>'
            . '</head>'
            . '<body>' . $this->title . '<br><br><br>' . $this->body . '</body>'
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
        $fechaDesde = date("d-m-Y", strtotime($this->fechadesde));
        $fechaHasta = date("d-m-Y", strtotime($this->fechahasta));

        $html = '<caption align="top">Desde: '. $fechaDesde . ' - Hasta: ' . $fechaHasta . '</caption><thead><tr>';
        foreach ($headers as $key => $title) {
            if ($title === 'Producto'){
                $html .= '<th nowrap align="center" style="border: 1px solid black; width: 70%;">' . $title . '</th>';
            }
            else {
                $html .= '<th align="center" style="border: 1px solid black; width: 20%;">' . $title . '</th>';
            }
        }
        $html .= '</tr></thead>';
        
        $countColor = 0;
        $totalCC = 0;
        $totalIC = 0;
        $totalCV = 0;
        $totalIV = 0;
        $totalBeneficio = 0;
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {
                if ($key === 'descripcion'){
                    if ($countColor %2==0){
                        $html .= '<td align="left">' . $cell . '</td>';
                    }
                    else {
                        $html .= '<td align="left" style="background-color: #EEE; width: 20%;">' . $cell . '</td>';
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
            
                if ($key === 'cantidadcompras'){
                    $totalCC += $cell;
                }
                if ($key === 'importecompras'){
                    $totalIC += $cell;
                }
                if ($key === 'cantidadventas'){
                    $totalCV += $cell;
                }
                if ($key === 'importeventas'){
                    $totalIV += $cell;
                }
                if ($key === 'beneficio'){
                    $totalBeneficio += $cell;
                }
            }
            $html .= '</tr>';
        }

        $totalCC = round((double)$totalCC, 2);
        $totalIC = round((double)$totalIC, 2);
        $totalCV = round((double)$totalCV, 2);
        $totalIV = round((double)$totalIV, 2);
        $totalBeneficio = round((double)$totalBeneficio, 2);

        $totales = '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>'
        . '<tr class="bordernegro">'
        . '<td> </td>'
        . '<td align="right">Totales: </td>'
        . '<td align="right">' . $totalCC . '</td>'
        . '<td align="right">' . $totalIC . '€</td>'
        . '<td align="right">' . $totalCV . '</td>'
        . '<td align="right">' . $totalIV . '€</td>'
        . '<td align="right">' . $totalBeneficio . '€</td>'
        . '<td> </td>'
        . '</tr>';

        $html .= $totales;
        $this->writeHTML('<table class="table-big table-list border1" style="width: 50%;">' . $html . '</table><br/>');
    }

    public function writeHTML(string $html)
    {
        $this->body .= $html;
    }

    protected function getData(): array
    {
        if ($this->fechadesde === null) {
            $sql = "select * from productos where false ";
        }
        
        else {
            $sql = 
            "select
            p.referencia,
            p.descripcion,
            coalesce(sum(compras.cantidadcompras), 0) as cantidadcompras,
            round(coalesce(sum(compras.importecompras), 0), 2) as importecompras,
            coalesce(sum(ventas.cantidadventas), 0) as cantidadventas,
            round(coalesce(sum(ventas.importeventas), 0), 2) as importeventas,
            round(coalesce(sum(ventas.cantidadventas), 0) - coalesce(sum(compras.cantidadcompras), 0)) as beneficio,
            round(coalesce(round( ( ( coalesce(sum(ventas.importeventas), 0) - coalesce(sum(compras.importecompras), 0) ) / coalesce(sum(compras.importecompras), 0) ) * 100 ), 100), 2) as porcentaje
        from
            productos as p
        left join
            (
                select
                    sum(cantidadcompras) as cantidadcompras,
                    (sum(importecompras) / count(importecompras)) as importecompras,
                    pvpunitario,
                    idproducto
                from 
                (
                    select
                        sum(lfp.cantidad) as cantidadcompras,
                        sum(lfp.pvptotal) as importecompras,
                        pvpunitario,
                        idproducto
                    from
                        lineasfacturasprov as lfp
                    inner join
                        facturasprov as fp on fp.idfactura=lfp.idfactura
                    where
                        fp.fecha between '" . $this->fechadesde . "' and '" . $this->fechahasta . "'
                    group by 
                        idproducto, pvpunitario
                ) as query_compras
                group by idproducto
            )
                as compras on compras.idproducto = p.idproducto
        
        left join
            (
                select
                    sum(cantidadventas) as cantidadventas,
                    (sum(importeventas) / count(importeventas)) as importeventas,
                    pvpunitario,
                    margen,
                    cantidad,
                    idproducto
                from 
                (
                    select
                        sum(lfc.cantidad) as cantidadventas,
                        sum(lfc.pvptotal) as importeventas,
                        pvpunitario,
                        sum(lfc.margen) as margen,
                        count(lfc.margen) as cantidad,
                        idproducto
                    from
                        lineasfacturascli as lfc
                    inner join
                        facturascli as fc on fc.idfactura=lfc.idfactura
                    where
                        fc.fecha between '" . $this->fechadesde . "' and '" . $this->fechahasta . "'
                    group by 
                        idproducto, pvpunitario
                ) as query_ventas
                group by
                    idproducto
            ) as ventas on ventas.idproducto = p.idproducto        
        
        group by 
            p.referencia
        order by
            p.referencia
        ;";
        
        }

        $items = [];
        $invoices = 0;

        foreach ($this->dataBase->select($sql) as $row) {

            $items[$invoices] = [
                'referencia' => $row['referencia'], 
                'descripcion' => $row['descripcion'], 
                'cantidadcompras' => $row['cantidadcompras'], 
                'importecompras' => $row['importecompras'], 
                'cantidadventas' => $row['cantidadventas'],
                'importeventas' => $row['importeventas'],
                'beneficio' => $row['beneficio'], 
                'porcentaje' => $row['porcentaje']
            ];

            $invoices++;

        }

        return $items;
    }
   
    public function loadData()
    {
        $this->data = $this->getData();
    }
}
