<?php

defined('MOODLE_INTERNAL') || die;

$createAdminSetting = static function ($name, $identifier, $description, $defaultValue) {
    return new admin_setting_configtext(
        $name,
        get_string($identifier, 'block_sms77'),
        get_string($description, 'block_sms77'),
        $defaultValue,
        PARAM_TEXT);
};

if ($ADMIN->fulltree) {
    $settings->add(
        $createAdminSetting('block_sms77_apikey', 'api_key', 'api_key_desc', ''));
    $settings->add(
        $createAdminSetting('block_sms77_from', 'from', 'from_desc', 'Moodle'));
}