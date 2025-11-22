<?php

namespace Tests\Helpers\Traits;

use Config;

trait FaxSettingFactoryHelper
{
    protected function faxSettingFactory(string $fax = null)
    {
        if (!$fax) {
            $fax = '123456798';
        }

        Config::set('fax.contacts.from', $fax);

        Config::set('fax.driver', 'fake');
        Config::set('mail.driver', 'log');
    }
}
