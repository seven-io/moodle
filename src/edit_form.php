<?php

class block_sms77_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader',
            get_string('blocksettings', 'block'));

        $mform->addElement('textarea', 'config_text',
            get_string('embedded_code', 'block_sms77'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_TEXT);

        $mform->addElement(
            'text', 'config_title', get_string('blocktitle', 'block_sms77'));
        $mform->setDefault('config_title', 'default value');
        $mform->setType('config_title', PARAM_TEXT);
    }
}
