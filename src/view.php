<?php
require_once 'msg_forms.php';
require_once 'template_form.php';

global $DB, $OUTPUT, $PAGE, $CFG, $USER;

require_login();

$viewPage = (int)required_param('viewpage', PARAM_INT);
$templateId = optional_param('id', null, PARAM_INT);
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

if (1 === $viewPage) {
    sms77MsgViewHandler(new voice_send, 'submit_send_voice',
        static function ($users, $text) {
            $results = [];

            foreach ($users as $user) {
                $results[] = sms77ApiPost('voice', sms77MsgDefaults($user->phone2,
                    sms77ReplacePlaceholders($user, $text), 'block_sms77_voice_from'));
            }

            return $results;
        });
} elseif (2 === $viewPage) {
    sms77MsgViewHandler(new sms_send, 'submit_send_sms', static function ($users, $text) {
        $results = [];

        foreach ($users as $user) {
            $results[] = sms77ApiPost('sms', sms77MsgDefaults($user->phone2,
                sms77ReplacePlaceholders($user, $text), 'block_sms77_sms_from'));
        }

        return $results;
    });
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
                "$base&rem=rem&delete=$templateId", $base);
        }
    }

    if (optional_param('edit', null, PARAM_RAW)) { // edit message template
        $form = new template_form;
        $form->set_data($DB->get_record(
            'block_sms77_template', ['id' => $templateId], '*'));
    }

    $form->set_data(['viewpage' => $viewPage]);
    $form->display();

    echo html_writer::table($form->display_report());

    if (isset($form)) {
        $fromForm = $form->get_data();

        if ($fromForm) {
            $DB->{$fromForm->id ? 'update_record' : 'insert_record'}
            ('block_sms77_template', $fromForm);

            redirect($redirectTo);
        }
    }
}

echo $OUTPUT->footer();

function sms77MsgViewHandler($form, $submitId, $apiAction) {
    global $DB, $PAGE, $viewPage;

    $PAGE->requires->js_init_call('M.block_sms77.init', [$viewPage]);

    $form->display();

    $table = html_writer::table($form->display_report());
    $submitText = get_string('send', 'block_sms77');

    echo "<form method='post'>
            <div id='table-change'>$table</div>

            <input 
                id='$submitId' 
                name='submit' 
                style='margin-left: 700px'
                type='submit' 
                value='$submitText'
             />

            <input type='hidden' name='viewpage' id='viewpage' value='$viewPage' />
         </form>
         <script>
         function toggleRecipients() {
            var checkList = document.getElementsByClassName('check_list');

            for (var i = 0; i < checkList.length; i++)
                checkList[i].checked = !checkList[i].checked;
        }
</script>
         ";

    if (!isset($_REQUEST['submit'])) {
        return;
    }

    $user = $_REQUEST['user']; // User IDs
    if ('' === $user) {
        echo '<p>You did not select any user.</p>';
        return;
    }

    $table = new html_table;
    $table->align = ['center', 'left', 'center', 'center'];
    $table->attributes = ['style' => 'width: 100%;'];
    $table->head = [
        get_string('serial_no', 'block_sms77'),
        get_string('moodleuser', 'block_sms77'),
        get_string('usernumber', 'block_sms77'),
        get_string('status', 'block_sms77'),
    ];
    $table->size = ['10%', '40%', '30%', '20%'];

    $users = [];
    $userCount = count($user);
    for ($i = 0; $i < $userCount; $i++) {
        $user = $DB->get_record_sql('SELECT * FROM {user} u WHERE u.id = ?', [$user[$i]]);

        if (empty($user->phone2)) {
            continue;
        }

        $users[] = $user;
    }

    if (empty($users)) {
        echo '<p>No users with phone number found.</p>';
        return;
    }

    $res = json_encode($apiAction($users, $_REQUEST['msg']));
    echo "<script>window.alert(JSON.stringify($res, null, 2))</script>";
}

function sms77MsgDefaults($to, $text, $from) {
    global $CFG;

    if (is_array($to)) {
        $to = implode(',', $to);
    }

    $text = urlencode($text);
    $from = $CFG->$from;

    return "to=$to&text=$text&from=$from&json=1";
}

function sms77ApiPost($endpoint, $params) {
    global $CFG;

    $ch = curl_init("https://gateway.sms77.io/api/$endpoint");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        ['sentWith: moodle', "X-Api-Key: $CFG->block_sms77_apikey"]);
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function sms77ReplacePlaceholders($user, $text) {
    foreach (['username', 'firstname', 'lastname', 'email', 'phone1', 'phone2',
                 'institution', 'department', 'address', 'city', 'country',] as $k) {
        $text = str_replace("{{{$k}}}", $user->{$k}, $text);
    }

    return $text;
}
