<?php
require_once __DIR__ . '/../../config.php';
require_once "$CFG->libdir/formslib.php";

require_login();

$PAGE->set_context(context_system::instance());

class msg_send extends moodleform {
    protected static $_header;
    protected static $_viewpage;

    public function definition() {
        global $DB, $CFG;

        $cid = isset($c_id) ? $c_id : null;

        $mform =& $this->_form;
        $mform->addElement(
            'header', static::$_header, get_string(static::$_header, 'block_sms77'));

        $sql = 'SELECT id, fullname FROM {course}';
        if (null !== $cid) {
            $sql .= ' WHERE id = ?';
        }
        $attributes = $DB->get_records_sql_menu($sql, [$cid]);

        $mform->addElement(
            'select', 'c_id', get_string('select_course', 'block_sms77'), $attributes);
        $mform->setType('c_id', PARAM_INT);

        $mform->addElement('select', 'r_id', get_string('role_select', 'block_sms77'),
            array_intersect($DB->get_records_sql_menu('SELECT id, shortname FROM {role}'),
                null !== $cid ? $DB->get_records_sql_menu(
                    'SELECT id, level_name FROM {competency_level} WHERE id = ?',
                    [$l_id]) : ['teacher', 'student']));
        $mform->setType('r_id', PARAM_INT);

        $mform->addElement('selectwithlink', 'm_id',
            get_string('msg_select', 'block_sms77'), $DB->get_records_sql_menu(
                'SELECT id, tname FROM {block_sms77_template}'), null,
            ['link' => "$CFG->wwwroot/blocks/sms77/view.php?viewpage=3",
                'label' => get_string('template', 'block_sms77')]);

        $mform->addElement('textarea', 'msg_body', get_string('msg_body', 'block_sms77'),
            ['rows' => '6', 'cols' => '45']);
        $mform->addRule('msg_body',
            get_string('msg_write', 'block_sms77'), 'required', 'client');
        $mform->addRule('msg_body', null, 'required');
        $mform->setType('msg_body', PARAM_TEXT);

        $mform->addElement('html',
            '<img src="loading.gif" id="load" style="margin-left: 6cm;" />');

        $mform->addElement('hidden', 'viewpage', static::$_viewpage);
        $mform->setType('viewpage', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('button', 'nextbtn',
            get_string('show_users', 'block_sms77'), ['id' => 'load_user_list']);
    }

    public function display_report($c_id = null, $r_id = null) {
        global $DB;

        $table = new html_table;
        $table->attributes = [
            'class' => 'display',
            'id' => 'userlist',
            'name' => 'userlist',
            'style' => 'width: 100%;',
        ];
        $table->data = [];

        if (empty($c_id)) {
            $c_id = 1; // first course
            $r_id = 3; // role = editingteacher
        }

        $sql = "SELECT u.firstname, u.id, u.lastname, u.email, u.phone2, c.fullname
            FROM {course} c
            INNER JOIN context cx ON c.id = cx.instanceid
            AND cx.contextlevel = '50' and c.id=$c_id
            INNER JOIN role_assignments ra ON cx.id = ra.contextid
            INNER JOIN role r ON ra.roleid = r.id
            INNER JOIN user u ON ra.userid = u.id
            WHERE r.id = $r_id";
        $count = $DB->record_exists_sql($sql);
        if ($count >= 1) {
            $table->align = ['center', 'left', 'center', 'center'];
            $table->head = [
                get_string('id', 'block_sms77'),
                get_string('name', 'block_sms77'),
                get_string('cell_no', 'block_sms77'),
                "<a href='javascript:toggleRecipients()' style='color: #333;'>"
                . get_string('toggle_all', 'block_sms77') . "</a>",
            ];
            $table->size = ['10%', '20%', '20%', '20%'];

            $i = 0;
            foreach ($DB->get_recordset_sql($sql) as $log) {
                if (empty($log->phone2)) {
                    continue;
                }

                $row = [];
                $row[] = $log->id;
                $row[] = "$log->firstname $log->lastname";
                $row[] = $log->phone2;
                $row[] = "<input type='checkbox' name='user[]' value='$log->id'
                            class='check_list form-control' />";
                $table->data[] = $row;
            }
        } else {
            $table->data[] = ["<div style='margin: 10px 0; padding:15px 10px 15px 50px;
                        background-repeat: no-repeat; background-position: 10px center; 
                        color: #00529B; background-image: url(info.png); 
                        background-color: #BDE5F8; border: 1px solid #3b8eb5;'>"
                . get_string('record_not_found', 'block_sms77') . "</div>"];
        }

        return $table;
    }
}

class sms_send extends msg_send {
    protected static $_header = 'sms_send';
    protected static $_viewpage = 2;
}

class voice_send extends msg_send {
    protected static $_header = 'voice_send';
    protected static $_viewpage = 1;
}
