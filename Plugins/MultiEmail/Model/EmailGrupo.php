<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of EmailGrupo
 *
 * @author Athos Online <info@athosonline.com>
 */
class EmailGrupo extends Base\ModelClass
{
    use Base\ModelTrait;
    
    /**
     * ID.
     *
     * @var int
     */
    public $idemailgrupo;
    
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
        return 'idemailgrupo';
    }

    public static function tableName(): string {
        return 'emails_grupos';
    }
    
    public function url(string $type = 'auto', string $list = 'List'): string {
        return 'EditEmail?code='.$this->idemail.'&activetab=EditEmailGrupo';
    }
}