<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\PlantillasPDF\Lib\PlantillasPDF;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Model\Base\BusinessDocumentLine;
use FacturaScripts\Dinamic\Model\AttachedFile;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\CuentaBanco;
use FacturaScripts\Dinamic\Model\CuentaBancoCliente;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Dinamic\Model\FormaPago;
use FacturaScripts\Dinamic\Model\FormatoDocumento;
use FacturaScripts\Dinamic\Model\Impuesto;
use FacturaScripts\Dinamic\Model\Pais;
use FacturaScripts\Dinamic\Model\ReciboCliente;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Description of BaseTemplate
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
abstract class BaseTemplate
{

    const DEFAULT_LOGO = 'Core/Assets/Images/horizontal-logo.png';
    const MAX_IMAGE_FILE_SIZE = 2048000;
    const MEGACITY20_LOGO = 'Plugins/MC20Instance/Assets/Images/horizontal-logo.png';

    /**
     *
     * @var string
     */
    protected $body = '';

    /**
     *
     * @var string
     */
    protected $config = [];

    /**
     *
     * @var Empresa
     */
    protected $empresa;

    /**
     *
     * @var array
     */
    protected $fixedBlocks = [];

    /**
     *
     * @var FormatoDocumento
     */
    public $format;

    /**
     * 
     * @var BusinessDocument
     */
    public $headerModel;

    /**
     * 
     * @var bool
     */
    public $isBusinessDoc = false;

    /**
     *
     * @var string
     */
    protected $logoPath;

    /**
     *
     * @var bool
     */
    protected $showHeaderTitle = true;

    abstract public function addInvoiceFooter($model);

    abstract public function addInvoiceHeader($model);

    abstract public function addInvoiceLines($model);

    public function __construct()
    {
        /// logo
        $this->logoPath = \file_exists(self::MEGACITY20_LOGO) ? self::MEGACITY20_LOGO : self::DEFAULT_LOGO;
        $this->setLogo($this->get('idlogo'));

        $this->empresa = new Empresa();
        $this->empresa->loadFromCode($this->toolBox()->appSettings()->get('default', 'idempresa'));
        $this->setLogo($this->empresa->idlogo);
    }

    /**
     * 
     * @param array $data
     */
    public function addDualColumnTable($data)
    {
        $html = '';
        $num = 0;
        foreach ($data as $row) {
            if ($num === 0) {
                $html .= '<tr>';
            } elseif ($num % 2 == 0) {
                $html .= '</tr><tr>';
            }

            $html .= '<td width="50%"><b>' . $row['title'] . '</b>: ' . $row['value'] . '</td>';

            $num++;
        }

        $html .= '</tr>';
        $this->writeHTML('<table class="table-big table-dual">' . $html . '</table><br/>');
    }

    /**
     * 
     * @param array $rows
     * @param array $titles
     * @param array $alignments
     */
    public function addTable($rows, $titles, $alignments)
    {
        $html = '<thead><tr>';
        foreach ($titles as $key => $title) {
            $html .= '<th align="' . $alignments[$key] . '">' . $title . '</th>';
        }
        $html .= '</tr></thead>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {
                $html .= '<td align="' . $alignments[$key] . '">' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $this->writeHTML('<table class="table-big table-list">' . $html . '</table><br/>');
    }

    /**
     * 
     * @param string $fileName
     *
     * @return string
     */
    public function output(string $fileName = '')
    {
        $orientation = strtolower(substr($this->get('orientation'), 0, 1)) === 'l' ? 'L' : 'P';

        $config = [
            'format' => $this->get('size') . '-' . $orientation,
            'margin_top' => $this->get('topmargin'),
            'margin_bottom' => $this->get('bottommargin'),
            'tempDir' => \FS_FOLDER . '/MyFiles/Cache'
        ];

        $mpdf = new Mpdf($config);
        $mpdf->SetCreator('FacturaScripts');
        $mpdf->SetHTMLHeader($this->header());
        $mpdf->SetHTMLFooter($this->footer());
        $mpdf->WriteHTML($this->html());
        foreach ($this->fixedBlocks as $block) {
            $mpdf->WriteFixedPosHTML($block['html'], $block['x'], $block['y'], $block['w'], $block['h']);
        }
        return $mpdf->Output($fileName, Destination::STRING_RETURN);
    }

    /**
     * 
     * @param int $idempresa
     */
    public function setEmpresa($idempresa)
    {
        if ($idempresa != $this->empresa->idempresa) {
            $this->empresa->loadFromCode($idempresa);
            $this->setLogo($this->empresa->idlogo);
        }
    }

    /**
     * 
     * @param FormatoDocumento $format
     */
    public function setFormat($format)
    {
        $this->format = $format;

        $optionalFields = [
            'color1', 'linecolalignments', 'linecols', 'linecoltypes', 'orientation', 'size'
        ];
        foreach ($optionalFields as $field) {
            if ($format->{$field}) {
                $this->config[$field] = $format->{$field};
            }
        }

        $fields = ['footertext', 'linesheight', 'thankstext', 'thankstitle'];
        foreach ($fields as $field) {
            $this->config[$field] = $format->{$field};
        }

        if ($format->texto) {
            $this->config['endtext'] = $format->texto;
        }

        if ($format->idlogo) {
            $this->setLogo($format->idlogo);
        }
    }

    /**
     * 
     * @param string $title
     * @param bool   $force
     */
    public function setHeaderTitle($title, bool $force = false)
    {
        if (empty($this->config['headertitle']) || $force) {
            $this->config['headertitle'] = $title;
        }
    }

    /**
     * 
     * @param int $idfile
     */
    public function setLogo($idfile)
    {
        $atFile = new AttachedFile();
        if ($idfile && $atFile->loadFromCode($idfile) && $atFile->size <= static::MAX_IMAGE_FILE_SIZE) {
            $this->logoPath = $atFile->path;
        }
    }

    /**
     * 
     * @param string $value
     */
    public function setOrientation($value)
    {
        $this->config['orientation'] = $value;
    }

    /**
     * 
     * @param string $title
     * @param bool   $force
     */
    public function setTitle($title, bool $force = false)
    {
        if (empty($this->config['title']) || $force) {
            $this->config['title'] = $title;
        }
    }

    /**
     * 
     * @param string $html
     * @param float  $x
     * @param float  $y
     * @param float  $w
     * @param float  $h
     */
    public function writeFixedPosHTML(string $html, $x, $y, $w, $h)
    {
        $this->fixedBlocks[] = [
            'html' => $html,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h
        ];
    }

    /**
     * 
     * @param string $html
     */
    public function writeHTML(string $html)
    {
        $this->body .= $html;
    }

    /**
     * 
     * @param array $lines
     */
    protected function autoHideLineColumns($lines)
    {
        $alignments = [];
        $cols = [];
        $types = [];
        foreach ($this->getInvoiceLineFields() as $key => $field) {
            $show = false;
            foreach ($lines as $line) {
                if (isset($line->{$key}) && $line->{$key}) {
                    $show = true;
                    break;
                }
            }

            if ($show) {
                $cols[] = $key;
                $alignments[] = $field['align'];
                $types[] = $field['type'];
            }
        }

        $this->config['linecols'] = \implode(',', $cols);
        $this->config['linecolalignments'] = \implode(',', $alignments);
        $this->config['linecoltypes'] = \implode(',', $types);
    }

    /**
     * 
     * @param BusinessDocument|Contacto $model
     *
     * @return string
     */
    protected function combineAddress($model): string
    {
        if (!isset($model->direccion)) {
            return '';
        }

        $utils = $this->toolBox()->utils();
        $completeAddress = $utils->fixHtml($model->direccion);
        $completeAddress .= empty($model->apartado) ? '' : ', ' . $this->toolBox()->i18n()->trans('box') . ' ' . $model->apartado;
        $completeAddress .= empty($model->codpostal) ? '' : '<br/>' . $model->codpostal;
        $completeAddress .= empty($model->ciudad) ? '' : ', ' . $utils->fixHtml($model->ciudad);
        $completeAddress .= empty($model->provincia) ? '' : ' (' . $utils->fixHtml($model->provincia) . ')';
        $completeAddress .= empty($model->codpais) ? '' : ', ' . $this->getCountryName($model->codpais);
        return $completeAddress;
    }

    /**
     * 
     * @return string
     */
    protected function css(): string
    {
        return 'body {font-color: ' . $this->get('fontcolor') . '; font-family: ' . $this->get('font') . '; font-size: ' . $this->get('fontsize') . 'px;}'
            . '.font-big {font-size: ' . (2 + $this->get('fontsize')) . 'px;}'
            . '.m2 {margin: 2px;}'
            . '.m3 {margin: 3px;}'
            . '.m4 {margin: 4px;}'
            . '.m5 {margin: 5px;}'
            . '.m10 {margin: 10px;}'
            . '.p2 {padding: 2px;}'
            . '.p3 {padding: 3px;}'
            . '.p4 {padding: 4px;}'
            . '.p5 {padding: 5px;}'
            . '.p10 {padding: 10px;}'
            . '.spacer {font-size: 8px;}'
            . '.text-center {text-align: center;}'
            . '.text-left {text-align: left;}'
            . '.text-right {text-align: right;}'
            . '.border1 {border: 1px solid ' . $this->get('color1') . ';}'
            . '.no-border {border: 0px;}'
            . '.primary-box {background-color: ' . $this->get('color1') . '; color: ' . $this->get('color2') . '; padding: 10px; '
            . 'text-transform: uppercase; font-size: ' . $this->get('titlefontsize') . 'px; font-weight: bold;}'
            . '.seccondary-box {background-color: ' . $this->get('color3') . '; padding: 10px; '
            . 'text-transform: uppercase; font-size: ' . $this->get('titlefontsize') . 'px; font-weight: bold;}'
            . '.title {color: ' . $this->get('color1') . '; font-size: ' . $this->get('titlefontsize') . 'px;}'
            . '.table-big {width: 100%;}'
            . '.table-lines {height: ' . $this->get('linesheight') . 'px;}'
            . '.end-text {font-size: ' . $this->get('endfontsize') . 'px; text-align: ' . $this->get('endalign') . ';}'
            . '.footer-text {font-size: ' . $this->get('footerfontsize') . 'px; text-align: ' . $this->get('footeralign') . ';}';
    }

    /**
     * 
     * @return string
     */
    protected function footer(): string
    {
        return empty($this->get('footertext')) ? '' : '<p class="footer-text">' . \nl2br($this->get('footertext')) . '</p>';
    }

    /**
     * 
     * @param string $key
     *
     * @return mixed
     */
    protected function get($key)
    {
        if (!isset($this->config[$key])) {
            $this->config[$key] = $this->toolBox()->appSettings()->get('plantillaspdf', $key);
        }

        return $this->config[$key];
    }

    /**
     * 
     * @param ReciboCliente $receipt
     *
     * @return string
     */
    protected function getBankData($receipt): string
    {
        $paymentMethod = new FormaPago();
        if (false === $paymentMethod->loadFromCode($receipt->codpago)) {
            return '-';
        }

        $cuentaBancoCli = new CuentaBancoCliente();
        $where = [new DataBaseWhere('codcliente', $receipt->codcliente)];
        if ($paymentMethod->domiciliado && $cuentaBancoCli->loadFromCode('', $where, ['principal' => 'DESC'])) {
            return $paymentMethod->descripcion . ' : ' . $cuentaBancoCli->getIban(true, true);
        }

        $cuentaBanco = new CuentaBanco();
        if ($paymentMethod->codcuentabanco && $cuentaBanco->loadFromCode($paymentMethod->codcuentabanco)) {
            return $paymentMethod->descripcion . ' : ' . $cuentaBanco->getIban(true);
        }

        return $paymentMethod->descripcion;
    }

    /**
     * Gets the name of the country with that code.
     *
     * @param string $code
     *
     * @return string
     */
    protected function getCountryName($code): string
    {
        if (empty($code)) {
            return '';
        }

        $country = new Pais();
        return $country->loadFromCode($code) ? $this->toolBox()->utils()->fixHtml($country->nombre) : '';
    }

    /**
     * 
     * @param int $num
     *
     * @return string
     */
    protected function getInvoiceLineFieldAlignment($num): string
    {
        $valid = ['left', 'right', 'center', 'justify'];
        foreach (\explode(',', $this->get('linecolalignments')) as $num2 => $value) {
            if ($num == $num2 && in_array($value, $valid)) {
                return $value;
            }
        }

        return 'left';
    }

    /**
     * 
     * @param string $txt
     *
     * @return string
     */
    protected function getInvoiceLineFieldTitle(string $txt): string
    {
        $codes = [
            'cantidad' => 'quantity-abb',
            'descripcion' => 'description',
            'dtopor' => 'dto',
            'dtopor2' => 'dto-2',
            'iva' => 'tax-abb',
            'pvpunitario' => 'price',
            'pvptotal' => 'net',
            'recargo' => 're',
            'referencia' => 'reference',
            'codigoexterno' => 'external-code'
        ];

        return isset($codes[$txt]) ? $this->toolBox()->i18n()->trans($codes[$txt]) : $this->toolBox()->i18n()->trans($txt);
    }

    /**
     * 
     * @param int $num
     *
     * @return string
     */
    protected function getInvoiceLineFieldType($num): string
    {
        $valid = [
            'money', 'money0', 'money1', 'money2', 'money3', 'money4', 'money5',
            'number', 'number0', 'number1', 'number2', 'number3', 'number4', 'number5',
            'percentage', 'percentage0', 'percentage1', 'percentage2', 'percentage3', 'percentage4', 'percentage5',
            'text'
        ];
        foreach (\explode(',', $this->get('linecoltypes')) as $num2 => $value) {
            if ($num == $num2 && in_array($value, $valid)) {
                return $value;
            }
        }

        return 'text';
    }

    /**
     * 
     * @return array
     */
    protected function getInvoiceLineFields(): array
    {
        $fields = [];
        foreach (\explode(',', $this->get('linecols')) as $num => $key) {
            $fields[$key] = [
                'align' => $this->getInvoiceLineFieldAlignment($num),
                'key' => $key,
                'title' => $this->getInvoiceLineFieldTitle($key),
                'type' => $this->getInvoiceLineFieldType($num)
            ];
        }

        return $fields;
    }

    /**
     * 
     * @param BusinessDocumentLine $line
     * @param array                $field
     *
     * @return string
     */
    protected function getInvoiceLineValue($line, $field): string
    {
        if (!isset($line->{$field['key']})) {
            return '';
        }

        $value = $line->{$field['key']};
        if (empty($value) && (!isset($line->cantidad) || empty($line->cantidad))) {
            return '&nbsp;';
        }

        switch ($field['type']) {
            case 'money':
                $txt = $this->toolBox()->coins()->format($value);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money0':
                $txt = $this->toolBox()->coins()->format($value, 0);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money1':
                $txt = $this->toolBox()->coins()->format($value, 1);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money2':
                $txt = $this->toolBox()->coins()->format($value, 2);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money3':
                $txt = $this->toolBox()->coins()->format($value, 3);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money4':
                $txt = $this->toolBox()->coins()->format($value, 4);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'money5':
                $txt = $this->toolBox()->coins()->format($value, 5);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number':
                $txt = $this->toolBox()->numbers()->format($value);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number0':
                $txt = $this->toolBox()->numbers()->format($value, 0);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number1':
                $txt = $this->toolBox()->numbers()->format($value, 1);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number2':
                $txt = $this->toolBox()->numbers()->format($value, 2);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number3':
                $txt = $this->toolBox()->numbers()->format($value, 3);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number4':
                $txt = $this->toolBox()->numbers()->format($value, 4);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'number5':
                $txt = $this->toolBox()->numbers()->format($value, 5);
                return \str_replace(' ', '&nbsp;', $txt);

            case 'percentage':
                return $this->toolBox()->numbers()->format($value) . '%';

            case 'percentage0':
                return $this->toolBox()->numbers()->format($value, 0) . '%';

            case 'percentage1':
                return $this->toolBox()->numbers()->format($value, 1) . '%';

            case 'percentage2':
                return $this->toolBox()->numbers()->format($value, 2) . '%';

            case 'percentage3':
                return $this->toolBox()->numbers()->format($value, 3) . '%';

            case 'percentage4':
                return $this->toolBox()->numbers()->format($value, 4) . '%';

            case 'percentage5':
                return $this->toolBox()->numbers()->format($value, 5) . '%';

            case 'text':
                return \nl2br($value);

            default:
                return $value;
        }
    }

    /**
     * 
     * @param BusinessDocument $model
     * @param string           $class
     *
     * @return string
     */
    protected function getInvoiceTaxes($model, $class = 'table-big'): string
    {
        $rows = $this->getTaxesRows($model);
        if (empty($model->totaliva)) {
            return '';
        }

        $coins = $this->toolBox()->coins();
        $i18n = $this->toolBox()->i18n();
        $numbers = $this->toolBox()->numbers();

        $trs = '';
        foreach ($rows as $row) {
            $trs .= '<tr>'
                . '<td align="left">' . $row['tax'] . '</td>'
                . '<td align="center">' . $coins->format($row['taxbase']) . '</td>'
                . '<td align="center">' . $numbers->format($row['taxp']) . '%</td>'
                . '<td align="center">' . $coins->format($row['taxamount']) . '</td>';

            if (empty($model->totalrecargo)) {
                $trs .= '</tr>';
                continue;
            }

            $trs .= '<td align="center">' . (empty($row['taxsurchargep']) ? '-' : $numbers->format($row['taxsurchargep']) . '%') . '</td>'
                . '<td align="right">' . (empty($row['taxsurcharge']) ? '-' : $coins->format($row['taxsurcharge'])) . '</td>'
                . '</tr>';
        }

        if (empty($model->totalrecargo)) {
            return '<table class="' . $class . '">'
                . '<thead>'
                . '<tr>'
                . '<th align="left">' . $i18n->trans('tax') . '</th>'
                . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
                . '<th align="center">' . $i18n->trans('percentage') . '</th>'
                . '<th align="center">' . $i18n->trans('amount') . '</th>'
                . '</tr>'
                . '</thead>'
                . $trs
                . '</table>';
        }

        return '<table class="' . $class . '">'
            . '<tr>'
            . '<th align="left">' . $i18n->trans('tax') . '</th>'
            . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
            . '<th align="center">' . $i18n->trans('tax') . '</th>'
            . '<th align="center">' . $i18n->trans('amount') . '</th>'
            . '<th align="center">' . $i18n->trans('re') . '</th>'
            . '<th align="right">' . $i18n->trans('amount') . '</th>'
            . '</tr>'
            . $trs
            . '</table>';
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getObservations($model)
    {
        return (bool) $this->get('hideobservations') ? '' : \nl2br($model->observaciones);
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getSubjectIdFiscalStr($model)
    {
        if ( $model->getSubject()->tipoidfiscal === 'CIF' || $model->getSubject()->tipoidfiscal === 'NIF' ) {
            return 'VAT Nº: ' . $model->cifnif;
        }
        
        else {
            return empty($model->cifnif) ? '' : $model->getSubject()->tipoidfiscal . ': ' . $model->cifnif;
        }
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getSubjectName($model)
    {
        return isset($model->nombrecliente) ? $model->nombrecliente : $model->nombre;
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getSubjectTitle($model)
    {
        return isset($model->nombrecliente) ? $this->toolBox()->i18n()->trans('customer') : $this->toolBox()->i18n()->trans('supplier');
    }

    /**
     * 
     * @param BusinessDocument $model
     */
    protected function getTaxesRows($model)
    {
        /// calculate total discount
        $totalDto = 1.0;
        foreach ([$model->dtopor1, $model->dtopor2] as $dto) {
            $totalDto *= 1 - $dto / 100;
        }

        $subtotals = [];
        foreach ($model->getLines() as $line) {
            $pvptotal = $line->pvptotal * $totalDto;
            if (empty($pvptotal) || $line->suplido) {
                continue;
            }

            $key = $line->codimpuesto . '_' . $line->iva . '_' . $line->recargo;
            if (!isset($subtotals[$key])) {
                $subtotals[$key] = [
                    'tax' => $key,
                    'taxbase' => 0,
                    'taxp' => $line->iva,
                    'taxamount' => 0,
                    'taxsurchargep' => $line->recargo,
                    'taxsurcharge' => 0
                ];

                $impuesto = new Impuesto();
                if ($line->codimpuesto && $impuesto->loadFromCode($line->codimpuesto)) {
                    $subtotals[$key]['tax'] = $impuesto->descripcion;
                }
            }

            $subtotals[$key]['taxbase'] += $pvptotal;
            $subtotals[$key]['taxamount'] += $pvptotal * $line->iva / 100;
            $subtotals[$key]['taxsurcharge'] += $pvptotal * $line->recargo / 100;
        }

        /// irpf
        foreach ($model->getLines() as $line) {
            if (empty($line->irpf)) {
                continue;
            }

            $key = 'irpf_' . $line->irpf;
            if (!isset($subtotals[$key])) {
                $subtotals[$key] = [
                    'tax' => $this->toolBox()->i18n()->trans('irpf') . ' ' . $line->irpf . '%',
                    'taxbase' => 0,
                    'taxp' => $line->irpf,
                    'taxamount' => 0,
                    'taxsurchargep' => 0,
                    'taxsurcharge' => 0
                ];
            }

            $subtotals[$key]['taxbase'] += $line->pvptotal * $totalDto;
            $subtotals[$key]['taxamount'] -= $line->pvptotal * $totalDto * $line->irpf / 100;
        }

        /// round
        foreach ($subtotals as $key => $value) {
            $subtotals[$key]['taxbase'] = \round($value['taxbase'], FS_NF0);
            $subtotals[$key]['taxamount'] = \round($value['taxamount'], FS_NF0);
            $subtotals[$key]['taxsurcharge'] = \round($value['taxsurcharge'], FS_NF0);
        }

        return $subtotals;
    }

    /**
     * 
     * @return string
     */
    protected function header(): string
    {
        switch ($this->get('logoalign')) {
            case 'center':
                return $this->headerCenter();

            case 'full-size':
                return $this->headerFull();

            case 'right':
                return $this->headerRight();

            /// logo align left
            default:
                return $this->headerLeft();
        }
    }

    /**
     * 
     * @return string
     */
    protected function headerCenter(): string
    {
        $contactData = [];
        /*
        foreach (['web', 'email', 'telefono1', 'telefono2'] as $field) {
        */
        foreach (['web', 'email'] as $field) {
            if ($this->empresa->{$field}) {
                $contactData[] = $this->empresa->{$field};
            }
        }

        $title = $this->showHeaderTitle ? '<h1 class="title text-center no-border">' . $this->get('headertitle') . '</h1>' : '';
        if ($this->empresa->tipoidfiscal === 'CIF') {
            return '<table class="table-big">'
            . '<tr>'
            . '<td valign="top" width="35%">'
            . '<p><b>' . $this->empresa->nombre . '</b>'
            . '<br/>' . $this->toolBox()->i18n()->trans($this->empresa->tipoidfiscal) . ': ' . $this->empresa->cifnif
            . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
            . '</td>'
            . '<td align="center" valign="top">'
            . '<img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/>'
            . '</td>'
            . '<td align="right" valign="top" width="35%">'
            . '<p>' . \implode('<br/>', $contactData) . '</p>'
            . '</td>'
            . '</tr>'
            . '</table>' . $title;
        }
        else {
            return '<table class="table-big">'
                . '<tr>'
                . '<td valign="top" width="35%">'
                . '<p><b>' . $this->empresa->nombre . '</b>'
                . '<br/>' . $this->empresa->tipoidfiscal . ': ' . $this->empresa->cifnif
                . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
                . '</td>'
                . '<td align="center" valign="top">'
                . '<img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/>'
                . '</td>'
                . '<td align="right" valign="top" width="35%">'
                . '<p>' . \implode('<br/>', $contactData) . '</p>'
                . '</td>'
                . '</tr>'
                . '</table>' . $title;

        }
    }

    /**
     * 
     * @return string
     */
    protected function headerFull(): string
    {
        return '<div class="text-center">'
            . '<img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/>'
            . '</div>';
    }

    /**
     * 
     * @return string
     */
    protected function headerLeft(): string
    {
        $contactData = [];
        /*
        foreach (['telefono1', 'telefono2', 'email', 'web'] as $field) {
        */
        foreach (['email', 'web'] as $field) {
            if ($this->empresa->{$field}) {
                $contactData[] = $this->empresa->{$field};
            }
        }

        $title = $this->showHeaderTitle ? '<h1 class="title">' . $this->get('headertitle') . '</h1>' . $this->spacer() : '';
        if ($this->empresa->tipoidfiscal === 'CIF') {
            return '<table class="table-big">'
            . '<tr>'
            . '<td valign="top"><img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/></td>'
            . '<td align="right" valign="top">'
            . $title
            . '<p><b>' . $this->empresa->nombre . '</b>'
            . '<br/>' . $this->toolBox()->i18n()->trans($this->empresa->tipoidfiscal) . ': ' . $this->empresa->cifnif
            . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
            . $this->spacer()
            . '<p>' . \implode(' · ', $contactData) . '</p>'
            . '</td>'
            . '</tr>'
            . '</table>';
        }
        else {
            return '<table class="table-big">'
                . '<tr>'
                . '<td valign="top"><img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/></td>'
                . '<td align="right" valign="top">'
                . $title
                . '<p><b>' . $this->empresa->nombre . '</b>'
                . '<br/>' . $this->empresa->tipoidfiscal . ': ' . $this->empresa->cifnif
                . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
                . $this->spacer()
                . '<p>' . \implode(' · ', $contactData) . '</p>'
                . '</td>'
                . '</tr>'
                . '</table>';

        }
    }

    /**
     * 
     * @return string
     */
    protected function headerRight(): string
    {
        $contactData = [];
        /*
        foreach (['telefono1', 'telefono2', 'email', 'web'] as $field) {
        */
        foreach (['email', 'web'] as $field) {
            if ($this->empresa->{$field}) {
                $contactData[] = $this->empresa->{$field};
            }
        }

        $title = $this->showHeaderTitle ? '<h1 class="title">' . $this->get('headertitle') . '</h1>' . $this->spacer() : '';
        if ($this->empresa->tipoidfiscal === 'CIF') {
            return '<table class="table-big">'
            . '<tr>'
            . '<td>'
            . $title
            . '<p><b>' . $this->empresa->nombre . '</b>'
            . '<br/>' . $this->toolBox()->i18n()->trans($this->empresa->tipoidfiscal) . ': ' . $this->empresa->cifnif
            . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
            . $this->spacer()
            . '<p>' . \implode(' · ', $contactData) . '</p>'
            . '</td>'
            . '<td align="right"><img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/></td>'
            . '</tr>'
            . '</table>';
        }
        else {
            return '<table class="table-big">'
                . '<tr>'
                . '<td>'
                . $title
                . '<p><b>' . $this->empresa->nombre . '</b>'
                . '<br/>' . $this->empresa->tipoidfiscal . ': ' . $this->empresa->cifnif
                . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
                . $this->spacer()
                . '<p>' . \implode(' · ', $contactData) . '</p>'
                . '</td>'
                . '<td align="right"><img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/></td>'
                . '</tr>'
                . '</table>';

        }
    }

    /**
     * 
     * @return string
     */
    protected function html(): string
    {
        return '<html>'
            . '<head>'
            . '<title>' . $this->get('title') . '</title>'
            . '<style>' . $this->css() . '</style>'
            . '</head>'
            . '<body>' . $this->body . '</body>'
            . '</html>';
    }

    /**
     * 
     * @param int $num
     *
     * @return string
     */
    protected function spacer($num = 1): string
    {
        $html = '';
        while ($num > 0) {
            $html .= '<div class="spacer">&nbsp;</div>';
            $num--;
        }

        return $html;
    }

    /**
     * 
     * @return ToolBox
     */
    protected function toolBox()
    {
        return new ToolBox();
    }
}
