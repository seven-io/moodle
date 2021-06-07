<?php
require_once '../../config.php';
require_once 'msg_forms.php';
require_once 'template_form.php';
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $CFG, $USER;

require_login();

$viewPage = (int)required_param('viewpage', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);
$redirectTo = new moodle_url("/blocks/sms77/view.php?viewpage=$viewPage");

if (1 === $viewPage) {
    $heading = 'voice_notifications';
} elseif (2 === $viewPage) {
    $heading = 'sms_notifications';
} else {
    $heading = 'msg_templates';
}

$PAGE->set_pagelayout('standard');
$PAGE->set_title('Sms77');
$PAGE->set_heading(get_string($heading, 'block_sms77'));
$PAGE->set_url($redirectTo);

echo $OUTPUT->header();
?>
    <script type="text/javascript">
        function toggleRecipients() {
            var checkList = document.getElementsByClassName('check_list');

            for (var i = 0; i < checkList.length; i++) {
                checkList[i].checked = !checkList[i].checked;
            }
        }
    </script>
<?php
$messageViewHandler = static function ($form, $submitText, $apiFunction)
use ($DB, $viewPage) {
    $form->display();
    $a = html_writer::table($form->display_report());

    echo "<form action='' method='post'>
            <div id='table-change'>$a</div>
            <input type='submit' style='margin-left: 700px;'
             name='submit' id='submit_send_voice' 
             value='" . get_string($submitText, 'block_sms77') . "'/>
            <input type='hidden' name='viewpage' id='viewpage' value='$viewPage' />
         </form>";
    $N = '';

    if (isset($_REQUEST['submit'])) {
        $msg = $_REQUEST['msg']; // SMS message

        if ('' !== $_REQUEST['user']) {
            $user = $_REQUEST['user']; // User ID.
            $N = count($user);
        } else {
            echo "You didn't select any user.";
            return;
        }

        $table = new html_table();

        $table->align = ['center', 'left', 'center', 'center'];
        $table->attributes = ['style' => 'width: 100%;'];
        $table->head = [
            get_string('serial_no', 'block_sms77'),
            get_string('moodleuser', 'block_sms77'),
            get_string('usernumber', 'block_sms77'),
            get_string('status', 'block_sms77'),
        ];
        $table->size = ['10%', '40%', '30%', '20%'];

        $number = [];

        for ($a = 0; $a < $N; $a++) {
            $id = $user[$a];
            $sql = 'SELECT usr.firstname, usr.id, usr.lastname, usr.email,usr.phone2'
            . ' FROM {user} usr WHERE usr.id = ?';
            $no = $DB->get_record_sql($sql, [$id])->phone2;

            if (!empty($no)) {
                $number[] = $no;
            }
        }

        $response = $apiFunction($number, $msg);
        echo "<script>window.alert(JSON.stringify($response, null, 2))</script>";
    }
};

if (1 === $viewPage) {
    $messageViewHandler(new voice_send, 'voice_send', 'sms77_send_voice');
} elseif (2 === $viewPage) {
    $messageViewHandler(new sms_send, 'sms_send', 'sms77_send_sms');
} else if (3 === $viewPage) {
    $form = new template_form();

    if (optional_param('rem', null, PARAM_RAW)) {
        $delete = optional_param('delete', null, PARAM_RAW);

        if ($delete) {
            $DB->delete_records('block_sms77_template', ['id' => $delete]);

            redirect($redirectTo);
        } else {
            echo $OUTPUT->confirm(
                get_string('tpl_confirm_del', 'block_sms77'),
                "/blocks/sms77/view.php?viewpage=3&rem=rem&delete=$id",
                '/blocks/sms77/view.php?viewpage=3'
            );
        }
    }

    if (optional_param('edit', null, PARAM_RAW)) { // edit message template
        $form = new template_form();
        $form->set_data($DB->get_record('block_sms77_template', ['id' => $id], '*'));
    }

    $form->set_data(['viewpage' => $viewPage]);
    $form->display();

    echo html_writer::table($form->display_report());
}

if (isset($form) && 3 === $viewPage) {
    $fromForm = $form->get_data();

    if ($fromForm) {
        $DB->{$fromForm->id ? 'update_record' : 'insert_record'}
        ('block_sms77_template', $fromForm);

        redirect($redirectTo);
    }
}

$PAGE->requires->js_init_call('M.block_sms77.init', [$viewPage]);

echo $OUTPUT->footer();
