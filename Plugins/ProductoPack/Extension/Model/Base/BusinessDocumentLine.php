<?php
/**
 * This file is part of ProductoPack plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ProductoPack    Copyright (C) 2019 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ProductoPack\Extension\Model\Base;

use FacturaScripts\Core\Base\Database\DataBaseWhere;
use FacturaScripts\Dinamic\Model\ProductPack;

/**
 * Description of BusinessDocumentLine
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BusinessDocumentLine
{

    public function saveInsert()
    {
        return function() {
            if (empty($this->referencia)) {
                return true;
            }

            $pack = new ProductPack();
            $where = [new DataBaseWhere('reference', $this->referencia)];
            if ($pack->loadFromCode('', $where)) {
                $this->delete();
                return true;
            }

            /// no pack
            return true;
        };
    }

    public function saveInsertBefore()
    {
        return function() {
            if (empty($this->referencia)) {
                return true;
            }

            $pack = new ProductPack();
            $where = [new DataBaseWhere('reference', $this->referencia)];
            if (!$pack->loadFromCode('', $where)) {
                /// no pack
                return true;
            }

            /// Get business document
            $doc = $this->getDocument();

            /// add pack lines
            foreach ($pack->getLines() as $line) {
                $newLine = $doc->getNewProductLine($line->reference);
                $newLine->cantidad = $this->cantidad * $line->quantity;
                if (!$newLine->save() && $line->required) {
                    return false;
                }
            }

            /// set quantity to 0 to avoid stock problems with product pack
            $this->cantidad = 0;
            return true;
        };
    }
}
