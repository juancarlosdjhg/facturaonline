<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * Description of EditCrmOportunidadEstado
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class EditCrmOportunidadEstado extends EditController
{

    /**
     * 
     * @return string
     */
    public function getModelClassName()
    {
        return 'CrmOportunidadEstado';
    }

    /**
     * 
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'crm';
        $data['title'] = 'status';
        $data['icon'] = 'fas fa-tags';
        return $data;
    }
}
