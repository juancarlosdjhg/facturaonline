<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Extension\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\CRM\Model\CrmOportunidad;

/**
 * Description of PresupuestoCliente
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class PresupuestoCliente
{

    public function deleteBefore()
    {
        return function() {
            $oportunity = new CrmOportunidad();
            $where = [new DataBaseWhere('idpresupuesto', $this->idpresupuesto)];
            if ($oportunity->loadFromCode('', $where)) {
                $oportunity->neto = 0;
                $oportunity->netoeuros = 0;
                $oportunity->tasaconv = 1;
                $oportunity->save();
            }

            return true;
        };
    }

    public function save()
    {
        return function() {
            $oportunity = new CrmOportunidad();
            $where = [new DataBaseWhere('idpresupuesto', $this->idpresupuesto)];
            if ($oportunity->loadFromCode('', $where)) {
                $oportunity->coddivisa = $this->coddivisa;
                $oportunity->neto = $this->neto;
                $oportunity->netoeuros = empty($this->tasaconv) ? 0 : round($this->neto / $this->tasaconv, 5);
                $oportunity->tasaconv = $this->tasaconv;
                $oportunity->save();
            }

            return true;
        };
    }
}
