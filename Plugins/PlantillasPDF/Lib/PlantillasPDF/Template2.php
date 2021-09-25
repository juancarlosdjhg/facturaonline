<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\PlantillasPDF\Lib\PlantillasPDF;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\Proveedor;

/**
 * Description of Template2
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class Template2 extends BaseTemplate
{

    /**
     * 
     * @param BusinessDocument|FacturaCliente $model
     */
    public function addInvoiceFooter($model)
    {
        $coins = $this->toolBox()->coins();
        $i18n = $this->toolBox()->i18n();
        $receipts = $model->modelClassName() === 'FacturaCliente' ? $model->getReceipts() : [];
        if ($receipts) {
            $trs = '<thead>'
                . '<tr>'
                . '<th>' . $i18n->trans('receipt') . '</th>'
                . '<th>' . $i18n->trans('payment-method') . '</th>'
                . '<th align="right">' . $i18n->trans('amount') . '</th>'
                . '<th align="right">' . $i18n->trans('expiration') . '</th>'
                . '</tr>'
                . '</thead>';
            foreach ($receipts as $receipt) {
                $expiration = $receipt->pagado ? $i18n->trans('paid') : $receipt->vencimiento;
                $trs .= '<tr>'
                    . '<td align="center">' . $receipt->numero . '</td>'
                    . '<td align="center">' . $this->getBankData($receipt) . '</td>'
                    . '<td align="right">' . $coins->format($receipt->importe) . '</td>'
                    . '<td align="right">' . $expiration . '</td>'
                    . '</tr>';
            }

            $this->writeHTML('<br/><table class="table-big table-list">' . $trs . '</table>');
        } elseif (isset($model->codcliente) && false === $this->format->hidetotals) {
            $expiration = isset($model->finoferta) ? $model->finoferta : '';
            $trs = '<thead>'
                . '<tr>'
                . '<th align="left">' . $i18n->trans('payment-method') . '</th>'
                . '<th align="right">' . $i18n->trans('expiration') . '</th>'
                . '</tr>'
                . '</thead>'
                . '<tr>'
                . '<td align="left">' . $this->getBankData($model) . '</td>'
                . '<td align="right">' . $expiration . '</td>'
                . '</tr>';

            $this->writeHTML('<br/><table class="table-big table-list">' . $trs . '</table>');
        }

        if (!empty($this->get('endtext')) && !empty($this->getObservations($model))) {
            $html = '<p class="end-text">' . \nl2br($this->get('endtext')) . '</p>';
            $this->writeHTML($html);
        }
    }

    /**
     * 
     * @param BusinessDocument $model
     */
    public function addInvoiceHeader($model)
    {
        $this->showHeaderTitle = false;

        $html = '<td class="primary-box">' . $this->get('headertitle') . '</td>'
            . '<td align="center" class="seccondary-box">' . $model->fecha . '</td>';

        $html .= $this->format->hidetotals ? '' :
            '<td align="right" class="seccondary-box">' . $this->toolBox()->coins()->format($model->total) . '</td>';

        $this->writeHTML('<br/><table class="table-big table-boxes"><tr>' . $html . '</tr></table>');

        $html2 = $this->getInvoiceHeaderBilling($model)
            . $this->getInvoiceHeaderShipping($model)
            . $this->getInvoiceHeaderResume($model);
        $this->writeHTML('<table class="table-big"><tr>' . $html2 . '</tr></table><br/>');
    }

    /**
     * 
     * @param BusinessDocument $model
     */
    public function addInvoiceLines($model)
    {
        $lines = $model->getLines();
        $this->autoHideLineColumns($lines);

        $html2 = '<thead><tr>';
        foreach ($this->getInvoiceLineFields() as $field) {
            $html2 .= '<th align="' . $field['align'] . '">' . $field['title'] . '</th>';
        }
        $html2 .= '</tr></thead>';

        foreach ($lines as $line) {
            $html2 .= '<tr>';
            foreach ($this->getInvoiceLineFields() as $field) {
                $html2 .= '<td align="' . $field['align'] . '" valign="top">' . $this->getInvoiceLineValue($line, $field) . '</td>';
            }
            $html2 .= '</tr>';
        }

        $this->writeHTML('<div class="table-lines"><table class="table-big table-list">'
            . $html2 . '</table></div>' . $this->getInvoiceTotals($model));
    }

    /**
     * 
     * @return string
     */
    protected function css(): string
    {
        return parent::css()
            . '.footer-text {background-color: ' . $this->get('color3') . '; padding: 10px;}'
            . '.thanks-title {font-size: ' . $this->get('titlefontsize') . 'px; font-weight: bold; color: ' . $this->get('color1') . '; '
            . 'text-align: center;}'
            . '.thanks-text {text-align: center;}'
            . '.table-border {border-top: 1px solid ' . $this->get('color1') . '; border-bottom: 1px solid ' . $this->get('color1') . ';}'
            . '.invoice-total {font-size: ' . $this->get('titlefontsize') . 'px; font-weight: bold; color: ' . $this->get('color1') . ';}'
            . '.table-boxes {border-spacing: 3px;}'
            . '.table-dual {border-top: 1px solid ' . $this->get('color1') . '; border-bottom: 1px solid ' . $this->get('color1') . ';}'
            . '.table-list {border-spacing: 2px; border-bottom: 1px solid ' . $this->get('color1') . ';}'
            . '.table-list tr:nth-child(even) {background-color: ' . $this->get('color3') . ';}'
            . '.table-list th {background-color: ' . $this->get('color1') . '; color: ' . $this->get('color2') . '; padding: 5px; text-transform: uppercase;}'
            . '.table-list td {padding: 5px;}';
    }

    /**
     * 
     * @return string
     */
    protected function footer(): string
    {
        $html = empty($this->get('thankstitle')) ? '' : '<p class="thanks-title">' . $this->get('thankstitle') . '</p>'
            . '<p class="thanks-text">' . \nl2br($this->get('thankstext')) . '</p>';

        if (!empty($this->get('thankstitle')) && !empty($this->get('footertext'))) {
            $html .= $this->spacer();
        }

        return $html . parent::footer();
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getInvoiceHeaderBilling($model): string
    {
        $subject = $model->getSubject();
        $address = isset($model->codproveedor) && !isset($model->direccion) ? $subject->getDefaultAddress() : $model;
        $customerCode = $this->get('showcustomercode') ? $model->subjectColumnValue() : '';
        $break = empty($model->cifnif) ? '' : '<br/>';
        return '<td>'
            . '<b>' . $this->getSubjectTitle($model) . '</b> ' . $customerCode
            . '<br/>' . $this->getSubjectName($model) . $break . $this->getSubjectIdFiscalStr($model)
            . '<br/>' . $this->combineAddress($address) . $this->getInvoiceHeaderBillingPhones($subject)
            . '</td>';
    }

    /**
     * 
     * @param Cliente|Proveedor $subject
     *
     * @return string
     */
    protected function getInvoiceHeaderBillingPhones($subject): string
    {
        if (true !== $this->get('showcustomerphones')) {
            return '';
        } elseif (empty($subject->telefono1) && empty($subject->telefono2)) {
            return '';
        } elseif (empty($subject->telefono1)) {
            return '<br/>' . $this->toolBox()->i18n()->trans('phones') . ': ' . $subject->telefono2;
        } elseif (empty($subject->telefono2)) {
            return '<br/>' . $this->toolBox()->i18n()->trans('phones') . ': ' . $subject->telefono1;
        }

        return '<br/>' . $this->toolBox()->i18n()->trans('phones') . ': ' . \str_replace(' ', '', $subject->telefono1)
            . ' - ' . \str_replace(' ', '', $subject->telefono2);
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getInvoiceHeaderResume($model): string
    {
        $i18n = $this->toolBox()->i18n();

        /// rectified invoice?
        $extra1 = '';
        if (isset($model->codigorect) && !empty($model->codigorect)) {
            $extra1 .= '<tr>'
                . '<td><b>' . $i18n->trans('original') . '</b>:</td>'
                . '<td align="right">' . $model->codigorect . '</td>'
                . '</tr>';
        }

        /// number2?
        $extra2 = '';
        if (isset($model->numero2) && !empty($model->numero2) && (bool) $this->get('shownumero2')) {
            $extra2 .= '<tr>'
                . '<td><b>' . $i18n->trans('externalordernumber') . '</b>:</td>'
                . '<td align="right">' . $model->numero2 . '</td>'
                . '</tr>';
        }

        $size = empty($extra2) ? 170 : 200;
        return '<td width="' . $size . '">'
            . '<table class="table-big">'
            . $extra1
            . '<tr>'
            . '<td><b>' . $i18n->trans('number') . '</b>:</td>'
            . '<td align="right">' . $model->numero . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td><b>' . $i18n->trans('serie') . '</b>:</td>'
            . '<td align="right">' . $model->codserie . '</td>'
            . '</tr>'
            . $extra2
            . '</table>'
            . '</td>';
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getInvoiceHeaderShipping($model): string
    {
        $contacto = new Contacto();
        if ($this->get('hideshipping') || !isset($model->idcontactoenv) || $model->idcontactoenv == $model->idcontactofact) {
            return '';
        } elseif (!empty($model->idcontactoenv) && $contacto->loadFromCode($model->idcontactoenv)) {
            return '<td>'
                . '<b>' . $this->toolBox()->i18n()->trans('shipping-address') . '</b>'
                . '<br/>' . $this->combineAddress($contacto)
                . '</td>';
        }

        return '';
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getInvoiceTotals($model): string
    {
        $coins = $this->toolBox()->coins();
        $i18n = $this->toolBox()->i18n();
        $numers = $this->toolBox()->numbers();
        $observations = '';
        if (!empty($this->getObservations($model))) {
            $observations .= '<p><b>' . $i18n->trans('observations') . '</b><br/>' . $this->getObservations($model) . '</p>&nbsp;';
        }

        if ($this->format->hidetotals) {
            return $observations;
        }

        $trs = '';
        $fields = [
            'netosindto' => $i18n->trans('subtotal'),
            'dtopor1' => $i18n->trans('global-dto'),
            'dtopor2' => $i18n->trans('global-dto-2'),
            'neto' => $i18n->trans('net'),
            'totaliva' => $i18n->trans('taxes'),
            'totalrecargo' => $i18n->trans('re'),
            'totalirpf' => $i18n->trans('irpf'),
            'totalsuplidos' => $i18n->trans('supplied-amount')
        ];

        /// Hide net and tax total if there is only one
        $taxes = $this->getTaxesRows($model);
        if (\count($taxes) === 1) {
            unset($fields['neto']);
            unset($fields['totaliva']);
        }

        foreach ($fields as $key => $title) {
            if (empty($model->{$key})) {
                continue;
            }

            switch ($key) {
                case 'dtopor1':
                case 'dtopor2':
                    $trs .= '<tr>'
                        . '<td align="right"><b>' . $title . '</b>:</td>'
                        . '<td align="right">' . $numers->format($model->{$key}) . '%</td>'
                        . '</tr>';
                    break;

                case 'netosindto':
                    if ($model->netosindto == $model->neto) {
                        break;
                    }
                /// no break

                default:
                    $trs .= '<tr>'
                        . '<td align="right"><b>' . $title . '</b>:</td>'
                        . '<td align="right">' . $coins->format($model->{$key}) . '</td>'
                        . '</tr>';
            }
        }

        $trs .= '<tr>'
            . '<td align="right" class="primary-box" colspan="2">'
            . $this->toolBox()->i18n()->trans('total') . ': ' . \str_replace(' ', '&nbsp;', $this->toolBox()->coins()->format($model->total))
            . '</td>'
            . '</tr>';

        $text = empty($observations) ? '<p class="end-text">' . $this->get('endtext') . '</p>' : $observations;
        return '<table class="table-big">'
            . '<tr>'
            . '<td valign="top">' . $this->getInvoiceTaxes($model) . '<br/>' . $text . '</td>'
            . '<td align="right" valign="top" width="35%"><table>' . $trs . '</table></td>'
            . '</tr>'
            . '</table>';
    }
}
