<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of EmailUsuario
 *
 * @author Athos Online <info@athosonline.com>
 */
class EmailUsuario extends Base\ModelClass
{
    use Base\ModelTrait;
    
    /**
     * ID.
     *
     * @var int
     */
    public $idemailusuario;
    
    /**
     * ID email.
     *
     * @var int
     */
    public $idemail;
    
    /**
     * User.
     *
     * @var string
     */
    public $nick;
    
    public static function primaryColumn(): string {
        return 'idemailusuario';
    }

    public static function tableName(): string {
        return 'emails_usuarios';
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        return 'EditEmail?code='.$this->idemail.'&activetab=EditEmailUsuario';
    }
}