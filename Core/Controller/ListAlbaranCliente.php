<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Dinamic\Lib\ExtendedController\ListBusinessDocument;

/**
 * Controller to list the items in the AlbaranCliente model
 *
 * @author Carlos García Gómez          <carlos@facturascripts.com>
 * @author Artex Trading sa             <jcuello@artextrading.com>
 * @author Cristo M. Estévez Hernández  <cristom.estevez@gmail.com>
 */
class ListAlbaranCliente extends ListBusinessDocument
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'delivery-notes';
        $data['icon'] = 'fas fa-copy';
        return $data;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->createViewSales('ListAlbaranCliente', 'AlbaranCliente', 'delivery-notes');
        $this->setSettings('ListAlbaranCliente', 'btnDelete', false);
        $this->addButtonGroupDocument('ListAlbaranCliente');
        $this->addButtonApproveDocument('ListAlbaranCliente');

        $this->createViewLines('ListLineaAlbaranCliente', 'LineaAlbaranCliente');
    }
}
