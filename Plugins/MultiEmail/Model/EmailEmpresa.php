<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of EmailEmpresa
 *
 * @author Athos Online <info@athosonline.com>
 */
class EmailEmpresa extends Base\ModelClass
{
    use Base\ModelTrait;
    
    /**
     * ID.
     *
     * @var int
     */
    public $idemailempresa;
    
    /**
     * ID email.
     *
     * @var int
     */
    public $idemail;
    
    /**
     * Role.
     *
     * @var string
     */
    public $codrole;
    
    public static function primaryColumn(): string {
        return 'idemailempresa';
    }

    public static function tableName(): string {
        return 'emails_empresas';
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        return 'EditEmail?code='.$this->idemail.'&activetab=EditEmailEmpresa';
    }
}