<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Model;

use FacturaScripts\Core\Model\Base;

/**
 * Description of Email
 *
 * @author Athos Online <info@athosonline.com>
 */
class Email extends Base\ModelClass
{
    use Base\ModelTrait;

    /**
     * From name email.
     *
     * @var string
     */
    public $fromname;
    
    /**
     * ID.
     *
     * @var serial
     */
    public $idemail;
    
    /**
     * Emial.
     *
     * @var string
     */
    public $email;
    
    /**
     * Emial CC
     *
     * @var string
     */
    public $emailcc;
    
    /**
     * Emial BCC
     *
     * @var string
     */
    public $emailbcc;
    
    /**
     * Password.
     *
     * @var string
     */
    public $password;
    
    /**
     * Signature.
     *
     * @var string
     */
    public $signature;
    
    /**
     * User.
     *
     * @var string
     */
    public $user;
    
    /**
     * Host.
     *
     * @var string
     */
    public $host;
    
    /**
     * Port.
     *
     * @var int
     */
    public $port;
    
    /**
     * Encrypt.
     *
     * @var string
     */
    public $enc;
    
    /**
     * Mailer.
     *
     * @var string
     */
    public $mailer;

    /**
     * Authentication.
     *
     * @var string
     */
    public $authtype;
    
    /**
     * Security.
     *
     * @var int
     */
    public $lowsecure;
    
    /**
     * Default email.
     *
     * @var int
     */
    public $emaildefault;
    
    public static function primaryColumn(): string {
        return 'idemail';
    }
    
    public function primaryDescriptionColumn(): string {
        return 'email';
    }
    
    public static function tableName(): string {
        return 'emails';
    }
    
    public function url(string $type = 'auto', string $list = 'EditSettings')
    {
        return parent::url($type, $list.'?activetab=List');
    }
    
    public function save() {
        if (isset($this->emaildefault) && $this->emaildefault) {
            $this->setDefaultEmail();
        } else {
            $this->primaryEmailDefault();
        }
        
        return parent::save();
    }
    
    public function delete() {
        if (isset($this->emaildefault) && $this->emaildefault) {
            $modelEmails = new Email();
            $allEmails = $modelEmails->all([]);
            if ($allEmails > 0) {
                $allEmails[0]->emaildefault = 1;
                $allEmails[0]->saveUpdate();
            }
        }
        return parent::delete();
    }
    
    private function primaryEmailDefault()
    {
        $modelEmails = new Email();
        $allEmails = $modelEmails->count([]);

        if ($allEmails === 0) {
            $this->emaildefault = 1;
        }
    }
    
    private function setDefaultEmail()
    {
        $modelEmails = new Email();
        $allEmails = $modelEmails->all([]);

        $aux = new Email();
        foreach ($allEmails as $email) {
            $aux->clear();
            $aux->loadFromCode($email->idemail);
            $aux->emaildefault = null;
            $aux->saveUpdate();
        }
    }
    
    public function clear() {
        parent::clear();
        $this->port = 465;
        $this->enc = 'ssl';
        $this->mailer = 'smtp';
    }
}