<?php
/**
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CRM\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of CrmInteresContacto
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class CrmInteresContacto extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $idcontacto;

    /**
     *
     * @var int
     */
    public $idinteres;

    public function clear()
    {
        parent::clear();
        $this->fecha = \date(self::DATE_STYLE);
    }

    /**
     * 
     * @return bool
     */
    public function delete()
    {
        if (parent::delete()) {
            /// force interest update
            $this->getInteres()->save();

            return true;
        }

        return false;
    }

    /**
     * 
     * @return Contacto
     */
    public function getContact()
    {
        $contact = new Contacto();
        $contact->loadFromCode($this->idcontacto);
        return $contact;
    }

    /**
     * 
     * @return CrmInteres
     */
    public function getInteres()
    {
        $interes = new CrmInteres();
        $interes->loadFromCode($this->idinteres);
        return $interes;
    }

    /**
     * 
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     * 
     * @return string
     */
    public static function tableName(): string
    {
        return 'crm_intereses_contactos';
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return empty($this->idcontacto) ? parent::url($type, $list) : $this->getContact()->url();
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
            /// force interest update
            $this->getInteres()->save();

            return true;
        }

        return false;
    }
}
