<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Model\Contacto as ParentModel;

/**
 * Description of Contacto
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class Contacto extends ParentModel
{

    /**
     *
     * @var int
     */
    public $idfuente;

    /**
     * 
     * @return CrmFuente
     */
    public function getFuente()
    {
        $fuente = new CrmFuente();
        $fuente->loadFromCode($this->idfuente);
        return $fuente;
    }

    /**
     * 
     * @return bool
     */
    public function delete()
    {
        if (parent::delete()) {
            if (!empty($this->idfuente)) {
                /// update source update
                $this->getFuente()->save();
            }

            return true;
        }

        return false;
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
    {
        return parent::url($type, $list);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveInsert(array $values = [])
    {
        if (parent::saveInsert($values)) {
            if (!empty($this->idfuente)) {
                /// update source update
                $this->getFuente()->save();
            }

            return true;
        }

        return false;
    }
}
