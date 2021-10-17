<?php
/**
 * Copyright (C) ATHOS TRADER SL <info@athosonline.com>
 */
namespace FacturaScripts\Plugins\MultiEmail\Controller;

use FacturaScripts\Core\Controller\EditSettings as parentEditSettings;

/**
 * Description of EditSettings
 *
 * @author Athos Online <info@athosonline.com>
 */
class EditSettings extends parentEditSettings {
    protected function createViews() {
        parent::createViews();
        unset($this->views['SettingsEmail']);
        $this->addListView('ListEmail', 'Email', 'emails', 'fas fa-envelope');
        $this->views['ListEmail']->addOrderBy(['email'], 'email');
    }
}