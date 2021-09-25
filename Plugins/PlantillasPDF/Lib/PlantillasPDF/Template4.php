<?php
/**
 * Copyright (C) 2020-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\PlantillasPDF\Lib\PlantillasPDF;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\Proveedor;

/**
 * Description of Template4
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class Template4 extends BaseTemplate
{

    /**
     * 
     * @param BusinessDocument $model
     */
    public function addInvoiceFooter($model)
    {
        $html = '<table class="table-big">'
            . '<tr>'
            . $this->addInvoiceFooterTaxes($model)
            . $this->addInvoiceFooterTotals($model)
            . '</tr>'
            . '<tr>'
            . '<td colspan="2">' . $this->addInvoiceFooterReceipts($model) . '</td>'
            . '</tr>'
            . '</table>';

        if (!empty($this->get('endtext'))) {
            $html .= '<p class="end-text">' . \nl2br($this->get('endtext')) . '</p>';
        } elseif ($this->format->hidetotals) {
            return;
        }

        $this->writeHTML('<br/><div class="border1">' . $html . '</div>');
    }

    /**
     * 
     * @param BusinessDocument $model
     */
    public function addInvoiceHeader($model)
    {
        $html = '<table class="table-big border1">'
            . '<tr>'
            . $this->getInvoiceHeaderResume($model)
            . $this->getInvoiceHeaderShipping($model)
            . $this->getInvoiceHeaderBilling($model)
            . '</tr>'
            . '</table>'
            . '<br/>';

        $this->writeHTML($html);
    }

    /**
     * 
     * @param BusinessDocument $model
     */
    public function addInvoiceLines($model)
    {
        $lines = $model->getLines();
        $this->autoHideLineColumns($lines);

        $html = '<div class="table-lines"><table class="table-big table-list"><thead><tr>';
        foreach ($this->getInvoiceLineFields() as $field) {
            $html .= '<th align="' . $field['align'] . '">' . $field['title'] . '</th>';
        }
        $html .= '</tr></thead>';

        foreach ($lines as $line) {
            $html .= '<tr>';
            foreach ($this->getInvoiceLineFields() as $field) {
                $html .= '<td align="' . $field['align'] . '" valign="top">' . $this->getInvoiceLineValue($line, $field) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table></div>';

        $observations = $this->getObservations($model);
        if (!empty($observations)) {
            $html .= '<p class="p5"><b>' . $this->toolBox()->i18n()->trans('observations') . '</b><br/>' . $observations . '</p>';
        }

        $this->writeHTML('<div class="border1">' . $html . '</div>');
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function addInvoiceFooterReceipts($model)
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

            return '<table class="table-big table-list">' . $trs . '</table>';
        } elseif (!isset($model->codcliente)) {
            return '';
        }

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

        return '<table class="table-big table-list">' . $trs . '</table>';
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function addInvoiceFooterTaxes($model)
    {
        return $this->format->hidetotals ? '' : '<td valign="top">' . $this->getInvoiceTaxes($model) . '</td>';
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function addInvoiceFooterTotals($model)
    {
        if ($this->format->hidetotals) {
            return '';
        }

        $coins = $this->toolBox()->coins();
        $i18n = $this->toolBox()->i18n();
        $numers = $this->toolBox()->numbers();

        $trs = '<tr>'
            . '<td align="right" colspan="2" class="primary-box">'
            . $this->toolBox()->i18n()->trans('total') . ': ' . \str_replace(' ', '&nbsp;', $coins->format($model->total)) . '</td>'
            . '</tr>';
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
        } else {
            $trs .= '<tr><td colspan="2"><br/></td></tr>';
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
                    break;
            }
        }

        return '<td align="right" valign="top" width="35%"><table>' . $trs . '</table></td>';
    }

    /**
     * 
     * @return string
     */
    protected function css(): string
    {
        return parent::css()
            . '.title {border-bottom: 2px solid ' . $this->get('color1') . ';}'
            . '.end-text {padding: 2px 5px 2px 5px;}'
            . '.table-list tr:nth-child(even) {background-color: ' . $this->get('color3') . ';}'
            . '.table-list th {background-color: ' . $this->get('color1') . '; color: ' . $this->get('color2') . '; padding: 5px; text-transform: uppercase;}'
            . '.table-list td {padding: 5px;}'
            . '.thanks-title {font-size: ' . $this->get('titlefontsize') . 'px; font-weight: bold; color: ' . $this->get('color1') . '; text-align: center;}'
            . '.thanks-text {text-align: center;}';
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
            $html .= '<br/>';
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
        $address = isset($model->codproveedor) && !isset($model->direccion) ? $model->getSubject()->getDefaultAddress() : $model;
        return '<td class="p3 text-right" valign="top">'
            . $this->getSubjectName($model) . '<br/>' . $this->combineAddress($address)
            . '</td>';
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

        $extra1 = '';
        if ($this->get('logoalign') === 'full-size') {
            $title = empty($this->format->titulo) ? $i18n->trans($model->modelClassName() . '-min') : $this->format->titulo;
            $extra1 .= '<tr>'
                . '<td><b>' . $title . '</b>:</td>'
                . '<td>' . $model->codigo . '</td>'
                . '</tr>';
        }

        /// rectified invoice?
        if (isset($model->codigorect) && !empty($model->codigorect)) {
            $extra1 .= '<tr>'
                . '<td><b>' . $i18n->trans('original') . '</b>:</td>'
                . '<td>' . $model->codigorect . '</td>'
                . '</tr>';
        }

        /// number2?
        $extra2 = '';
        if (isset($model->numero2) && !empty($model->numero2) && (bool) $this->get('shownumero2')) {
            $extra2 .= '<tr>'
                . '<td><b>' . $i18n->trans('externalordernumber') . '</b>:</td>'
                . '<td>' . $model->numero2 . '</td>'
                . '</tr>';
        }

        /// cif/nif?
        $extra3 = empty($model->cifnif) ? '' : '<tr>'
            . '<td><b>' . $model->getSubject()->tipoidfiscal . '</b>:</td>'
            . '<td>' . $model->cifnif . '</td>'
            . '</tr>';

        $size = empty($extra2) ? 170 : 200;
        return '<td valign="top" width="' . $size . '">'
            . '<table class="table-big">'
            . $extra1
            . '<tr>'
            . '<td><b>' . $i18n->trans('date') . '</b>:</td>'
            . '<td>' . $model->fecha . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td><b>' . $i18n->trans('number') . '</b>:</td>'
            . '<td>' . $model->numero . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td><b>' . $i18n->trans('serie') . '</b>:</td>'
            . '<td>' . $model->codserie . '</td>'
            . '</tr>'
            . $extra2
            . $extra3
            . $this->getInvoiceHeaderResumePhones($model->getSubject())
            . '</table>'
            . '</td>';
    }

    /**
     * 
     * @param Cliente|Proveedor $subject
     *
     * @return string
     */
    protected function getInvoiceHeaderResumePhones($subject): string
    {
        $phone1 = \str_replace(' ', '', $subject->telefono1);
        $phone2 = \str_replace(' ', '', $subject->telefono2);
        if (true !== $this->get('showcustomerphones')) {
            return '';
        } elseif (empty($subject->telefono1) && empty($subject->telefono2)) {
            return '';
        } elseif (empty($subject->telefono1)) {
            return '<tr><td><b>' . $this->toolBox()->i18n()->trans('phones') . '</b>:</td><td>' . $phone2 . '</td></tr>';
        } elseif (empty($subject->telefono2)) {
            return '<tr><td><b>' . $this->toolBox()->i18n()->trans('phones') . '</b>:</td><td>' . $phone1 . '</td></tr>';
        }

        return '<tr><td><b>' . $this->toolBox()->i18n()->trans('phone') . '</b>:</td><td>' . $phone1 . '</td></tr>'
            . '<tr><td><b>' . $this->toolBox()->i18n()->trans('phone2') . '</b>:</td><td>' . $phone2 . '</td></tr>';
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
            return '<td class="p3" valign="top">'
                . '<table class="table-big">'
                . '<tr><td><b>' . $this->toolBox()->i18n()->trans('shipping-address') . '</b></td></tr>'
                . '<tr><td>' . $this->combineAddress($contacto) . '</td></tr>'
                . '</table>'
                . '</td>';
        }

        return '';
    }
}
