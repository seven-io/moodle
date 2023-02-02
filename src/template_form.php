<?php
require_once $CFG->libdir . '/formslib.php';

require_login();

$PAGE->set_context(context_system::instance());

class template_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('header',
            'msg_template_header',
            get_string('msg_template_header', 'block_seven'));

        $mform->addElement('text', 'tname', 'Name:', ['size' => 44]);
        $mform->addRule(
            'tname',
            get_string('tpl_specify_name', 'block_seven'),
            'required',
            'client');
        $mform->setType('tname', PARAM_TEXT);

        $mform->addElement(
            'textarea',
            'template',
            get_string('msg', 'block_seven') . ':',
            ['rows' => '6', 'cols' => '47']);
        $mform->addRule(
            'template',
            get_string('tpl_specify_msg', 'block_seven'),
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
            $errors['tname'] = get_string('missing_template_name', 'block_seven');

            if ($DB->record_exists('block_seven_template', ['tname' => $data['tname']]))
                $errors['template'] = 'Template Name exists already!';

            return $errors;
        }

        return true;
    }

    public function display_report() {
        global $CFG, $DB, $OUTPUT;

        $editTrans = get_string('edit', 'block_seven');
        $delTrans = get_string('delete', 'block_seven');

        $table = new html_table;
        $table->align = ['center', 'left', 'left', 'center', 'center'];
        $table->attributes = ['class' => 'display', 'style' => 'width: 100%;'];
        $table->head = [
            get_string('serial_no', 'block_seven'),
            get_string('name', 'block_seven'),
            get_string('msg_body', 'block_seven'),
            $editTrans,
            $delTrans,
        ];
        $table->size = ['10%', '20%', '50%', '10%', '10%'];

        $i = 0;
        foreach ($DB->get_recordset_sql('SELECT * FROM {block_seven_template}') as $log) {
            $urlFn = method_exists($OUTPUT, 'image_url') ? 'image_url' : 'pix_url';
            $edit = $OUTPUT->$urlFn('t/edit');
            $delete = $OUTPUT->$urlFn('t/delete');
            $prefix = $CFG->wwwroot . '/blocks/seven/view.php?viewpage=3&';

            $row = [];
            $row[] = ++$i;
            $row[] = $log->tname;
            $row[] = $log->template;
            $row[] = sprintf('<a title=\'%s\' href=\'%sedit=edit&id=%s\'>
                <img alt=\'%s\' src=\'%s\' class=\'iconsmall\' /></a>',
                $editTrans, $prefix, $log->id, $editTrans, $edit);
            $row[] = sprintf('<a title=\'%s\' href=\'%srem=remove&id=%s\'>
                <img alt=\'%s\' src=\'%s\' class=\'iconsmall\' /></a>',
                $delTrans, $prefix, $log->id, $delTrans, $delete);
            $table->data[] = $row;
        }

        return $table;
    }
}
