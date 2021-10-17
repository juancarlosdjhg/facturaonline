<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Lib\Email;

use FacturaScripts\Core\Lib\Email\NewMail as ParentNewMail;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\RoleUser;
use FacturaScripts\Plugins\MultiEmail\Model\Email;
use FacturaScripts\Plugins\MultiEmail\Model\EmailEmpresa;
use FacturaScripts\Plugins\MultiEmail\Model\EmailGrupo;
use FacturaScripts\Plugins\MultiEmail\Model\EmailUsuario;
use FacturaScripts\Core\App\WebRender;

/**
 * Description of NewMail
 *
 * @author Athos Online <info@athosonline.com>
 */
Class NewMail extends ParentNewMail {
    //private $lowsecure;
    private $nick;
    private $idempresa;
    private $mailboxes = [];
    private $email;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->nick = $_COOKIE["fsNick"];
        $this->idempresa = $_COOKIE["fsCompany"];
        
        $this->setMailUser();
        $this->setMailRole();
        $this->setMailCompany();
        $this->setMailCompanyDefault();
    }
    
    /*
     * We look for configured emails
     */
    public function canSendMail(): bool
    {
        if (count($this->mailboxes) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /*
     * We get all the emails this user has configured
     */
    private function setMailUser()
    {
        $modelEmailUser = new EmailUsuario();
        $EmailsUser = $modelEmailUser->all([new DataBaseWhere('nick', $this->nick)]);
        $this->setMailBoxes($EmailsUser);
    }
    
    /*
     * We get all the emails that this user's group has configured
     */
    private function setMailRole()
    {
        $modelRolesUser = new RoleUser();
        $RolesUser = $modelRolesUser->all([new DataBaseWhere('nick', $this->nick)]);
        $modelEmailGrupo = new EmailGrupo();
        foreach($RolesUser as $grupo) {
            $modelEmailGrupo->clear();
            $EmailsGrupo = $modelEmailGrupo->all([new DataBaseWhere('codrole', $grupo->codrole)]);
            $this->setMailBoxes($EmailsGrupo);
        }
    }
    
    /*
     * We get all the emails that this user's company has configured
     */
    private function setMailCompany()
    {
        $modelEmailEmpresa = new EmailEmpresa();
        $EmailsEmpresa = $modelEmailEmpresa->all([new DataBaseWhere('idempresa', $this->idempresa)]);
        $this->setMailBoxes($EmailsEmpresa);
    }
    
    /*
     * Default email is obtained
     */
    private function setMailCompanyDefault()
    {
        $modelEmailDefault = new Email();
        $EmailsDefault = $modelEmailDefault->all([new DataBaseWhere('emaildefault', 1)]);
        $this->setMailBoxes($EmailsDefault);
    }
    
    /*
     * We establish all the emails available for shipping
     */
    private function setMailBoxes($array)
    {
        $Email = new Email();
        foreach ($array as $e) {
            $Email->loadFromCode($e->idemail);
            if (!in_array($Email->email, $this->mailboxes)) {
                $this->mailboxes[] = $Email->email;
            } 
        }
    }
    
    /*
     * We get all the configured emails available
     */
    public function getAvailableMailboxes(): array
    {
        return $this->mailboxes;
    }
    
    /*
     * We preload the selected email configuration when sending an email
     */
    public function setMailbox($emailFrom)
    {
        $email = New Email();
        $email->loadFromCode('', [new DataBaseWhere('email', $emailFrom)]);
        $this->configEmail($email->idemail);
    }
    
    /*
     * Configuration of the selected email parameters
     */
    public function configEmail($idemail)
    {
        $email = New Email();
        $email->loadFromCode($idemail);
        $this->fromName = $email->fromname;
        $this->lowsecure = $email->lowsecure;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->WordWrap = 50;
        $this->mail->Mailer = $email->mailer;
        $this->mail->SMTPAuth = true;
        $this->mail->AuthType = $email->authtype;
        $this->mail->SMTPSecure = $email->enc;
        $this->mail->Host = $email->host;
        $this->mail->Port = $email->port;
        $this->mail->Username = $email->user ? $email->user : $email->email;
        $this->mail->Password = $email->password;
        $this->mail->Email = $email->email;
        
        foreach (static::splitEmails($email->emailcc) as $emailcc) {
            $this->addCC($emailcc);
        }

        foreach (static::splitEmails($email->emailbcc) as $emailbcc) {
            $this->addBCC($emailbcc);
        }
        
        $this->signature = $email->signature;
        $this->template = self::DEFAULT_TEMPLATE;
        $this->verificode = $this->toolBox()->utils()->randomString(20);
    }
    
    /**
     * Sending email
     * 
     * @return bool
     */
    public function send(): bool
    {
        $email = New Email();
        $email->loadFromCode('', [new DataBaseWhere('email', $this->mail->Email)]);
        
        if (empty($this->mail->Host)) {
            $this->toolBox()->i18nLog()->warning('email-not-configured');
            return false;
        }

        $this->mail->setFrom($this->mail->Email, $email->fromname);
        $this->mail->Subject = $this->title;
        $this->mail->msgHTML($this->renderHTML());

        if ('smtp' === $this->mail->Mailer && !$this->mail->smtpConnect($this->smtpOptions())) {
            $this->toolBox()->i18nLog()->error('error', ['%error%' => $this->mail->ErrorInfo]);
            return false;
        }

        if ($this->mail->send()) {
            $this->saveMailSent();
            return true;
        }

        $this->toolBox()->i18nLog()->error('error', ['%error%' => $this->mail->ErrorInfo]);
        return false;
    }
    
    /**
     * Test the PHPMailer connection. Return the result of the connection.
     *
     * @return bool
     */
    public function test(): bool
    {        
        switch ($this->mail->Mailer) {
            case 'smtp':
                $this->mail->SMTPDebug = 3;
                return $this->mail->smtpConnect($this->smtpOptions());

            default:
                $this->toolBox()->i18nLog()->warning('not-implemented');
                return false;
        }
    }

    /**
     * 
     * @return string
     */
    protected function renderHTML(): string
    {
        $webRender = new WebRender();
        $webRender->loadPluginFolders();

        $email = New Email();
        $email->loadFromCode('', [new DataBaseWhere('email', $this->mail->Email)]);

        $params = [
            //'empresa' => $this->empresa,
            'email' => $email,
            'footerBlocks' => $this->getFooterBlocks(),
            'mainBlocks' => $this->getMainBlocks(),
            'title' => $this->title
        ];
        return $webRender->render('Email/' . $this->template, $params);
    }
    
    /**
     * Returns the SMTP Options.
     *
     * @return array
     */
    /*protected function smtpOptions(): array
    {
        if ($this->lowsecure) {
            return [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        }

        return [];
    }*/
}