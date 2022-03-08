<?php defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_sms77_apikey',
        get_string('api_key', 'block_sms77'),
        get_string('api_key_desc', 'block_sms77'),
        '',
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_sms77_sms_from',
        get_string('from_sms', 'block_sms77'),
        get_string('from_sms_desc', 'block_sms77'),
        'Moodle',
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_sms77_voice_from',
        get_string('from_voice', 'block_sms77'),
        get_string('from_voice_desc', 'block_sms77'),
        '+491771783130',
        PARAM_TEXT));
}
