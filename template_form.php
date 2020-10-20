<?php

global $CFG, $PAGE;

require_once "$CFG->libdir/formslib.php";
require_once "lib.php";

require_login();

$PAGE->set_context(context_system::instance());

class template_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('header',
            'msg_template_header',
            get_string('msg_template_header', 'block_sms77'));

        $mform->addElement('text', 'tname', 'Name:', ['size' => 44]);
        $mform->addRule(
            'tname',
            get_string('tpl_specify_name', 'block_sms77'),
            'required',
            'client');
        $mform->setType('tname', PARAM_TEXT);

        $mform->addElement(
            'textarea',
            'template',
            get_string('msg', 'block_sms77') . ':',
            ['rows' => '6', 'cols' => '47']);
        $mform->addRule(
            'template',
            get_string('tpl_specify_msg', 'block_sms77'),
            'required',
            'client');
        $mform->setType('template', PARAM_TEXT);

        $mform->addElement('hidden', 'viewpage', '2');
        $mform->setType('viewpage', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;

        $errors = [];

        if ('' === $data['tname']) {
            $errors['tname'] = "Missing template name!";

            if ($DB->record_exists('block_sms77_template', ['tname' => $data['tname']])) {
                $errors['template'] = 'Template Name exists already!';
            }

            return $errors;
        }

        return true;
    }

    public function display_report() {
        global $CFG, $DB, $OUTPUT;

        $editTrans = get_string('edit', 'block_sms77');
        $delTrans = get_string('delete', 'block_sms77');

        $table = new html_table();
        $table->align = ['center', 'left', 'left', 'center', 'center'];
        $table->attributes = ["class" => "display", "style" => "width: 100%;"];
        $table->head = [
            get_string('serial_no', 'block_sms77'),
            get_string('name', 'block_sms77'),
            get_string('msg_body', 'block_sms77'),
            $editTrans,
            $delTrans,
        ];
        $table->size = ['10%', '20%', '50%', '10%', '10%'];

        $i = 0;
        foreach ($DB->get_recordset_sql("SELECT * FROM block_sms77_template") as $log) {
            $row = [];
            $row[] = ++$i;
            $row[] = $log->tname;
            $row[] = $log->template;
            $row[] = "<a title='$editTrans' href='$CFG->wwwroot/blocks/sms77/view.php?viewpage=3&edit=edit&id=$log->id' /><img alt='$editTrans' src='" . $OUTPUT->image_url('t/edit') . "' class='iconsmall' /></a>";
            $row[] = "<a title='$delTrans' href='$CFG->wwwroot/blocks/sms77/view.php?viewpage=3&rem=remove&id=$log->id' /><img alt='$delTrans' src='" . $OUTPUT->image_url('t/delete') . "' class='iconsmall' /></a>";
            $table->data[] = $row;
        }

        return $table;
    }
}