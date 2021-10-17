<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Plugins\MultiEmail\Model\Email;
use FacturaScripts\Core\Lib\Email\NewMail;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of Init
 *
 * @author Athos Online <info@athosonline.com>
 */
class Init extends InitClass
{

    public function init()
    {
        $this->loadExtension(new Extension\Controller\EditEmpresa());
        $this->loadExtension(new Extension\Controller\EditRole());
        $this->loadExtension(new Extension\Controller\EditUser());

        new NewMail();
    }

    public function update()
    {
        $newmail = new NewMail();
        if ($newmail->canSendMail()) {
            $appSettings = $this->toolBox()->appSettings();

            $email = new Email();
            $where = [new DataBaseWhere('email', $appSettings->get('email', 'email'))];
            $email->loadFromCode('', $where);
            
            if (empty($email->idemail)) {
                $email->clear();
                $email->fromname = $appSettings->get('email', 'email');
                $email->password = $appSettings->get('email', 'password');
                $email->signature = $appSettings->get('email', 'signature', '');
                $email->user = $appSettings->get('email', 'user');
                $email->host = $appSettings->get('email', 'host');
                $email->port = $appSettings->get('email', 'port');
                $email->enc = $appSettings->get('email', 'enc', '');
                $email->mailer = $appSettings->get('email', 'mailer');
                $email->authtype = $appSettings->get('email', 'authtype');
                $email->email = $appSettings->get('email', 'email');
                $email->emailcc = $appSettings->get('email', 'emailcc');
                $email->emailbcc = $appSettings->get('email', 'emailbcc');
                $email->lowsecure = $appSettings->get('email', 'lowsecure');
                $email->emaildefault = 1;
                $email->save();
            }
        }
    }
}