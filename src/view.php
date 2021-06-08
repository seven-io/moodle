<?php
require_once 'lib.php';
require_once 'msg_forms.php';
require_once 'template_form.php';

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

            for (var i = 0; i < checkList.length; i++)
                checkList[i].checked = !checkList[i].checked;
        }
    </script>
<?php
$msgViewHandler = static function ($form, $submitId, $apiAction) use ($DB, $viewPage) {
    $form->display();

    $table = html_writer::table($form->display_report());
    $submitText = get_string('send', 'block_sms77');

    echo "<form action='' method='post'>
            <div id='table-change'>$table</div>

            <input 
                id='$submitId' 
                name='submit' 
                style='margin-left: 700px'
                type='submit' 
                value='$submitText'
             />

            <input type='hidden' name='viewpage' id='viewpage' value='$viewPage' />
         </form>";

    if (!isset($_REQUEST['submit'])) {
        return;
    }

    $user = $_REQUEST['user']; // User ID
    if ('' === $user) {
        echo '<p>You did not select any user.</p>';
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
    $N = count($user);
    for ($a = 0; $a < $N; $a++) {
        $sql = 'SELECT usr.firstname, usr.id, usr.lastname, usr.email,usr.phone2'
            . ' FROM {user} usr WHERE usr.id = ?';
        $no = $DB->get_record_sql($sql, [$user[$a]])->phone2;

        if (!empty($no)) {
            $number[] = $no;
        }
    }

    $res = $apiAction($number, $_REQUEST['msg']);
    echo "<script>window.alert(JSON.stringify($res, null, 2))</script>";
};

if (1 === $viewPage) {
    $msgViewHandler(new voice_send, 'submit_send_voice', 'sms77_send_voice');
} elseif (2 === $viewPage) {
    $msgViewHandler(new sms_send, 'submit_send_sms', 'sms77_send_sms');
} else if (3 === $viewPage) {
    $form = new template_form;

    if (optional_param('rem', null, PARAM_RAW)) {
        $delete = optional_param('delete', null, PARAM_RAW);

        if ($delete) {
            $DB->delete_records('block_sms77_template', ['id' => $delete]);

            redirect($redirectTo);
        } else {
            $base = '/blocks/sms77/view.php?viewpage=3';

            echo $OUTPUT->confirm(
                get_string('tpl_confirm_del', 'block_sms77'),
                "$base&rem=rem&delete=$id", $base);
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

$PAGE->requires->js_init_call('M.block_sms77.init', [$viewPage], true);

echo $OUTPUT->footer();
