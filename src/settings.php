<?php defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_seven_apikey',
        get_string('api_key', 'block_seven'),
        get_string('api_key_desc', 'block_seven'),
        '',
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_seven_sms_from',
        get_string('from_sms', 'block_seven'),
        get_string('from_sms_desc', 'block_seven'),
        'Moodle',
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_seven_voice_from',
        get_string('from_voice', 'block_seven'),
        get_string('from_voice_desc', 'block_seven'),
        '+4915126716517',
        PARAM_TEXT));
}
