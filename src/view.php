<?php
require_once 'msg_forms.php';
require_once 'template_form.php';

require_login();

$viewPage = (int)required_param('viewpage', PARAM_INT);
$templateId = optional_param('id', null, PARAM_INT);
$redirectTo = new moodle_url('/blocks/seven/view.php?viewpage=' . $viewPage);

if (1 === $viewPage) $heading = 'voice_notifications';
elseif (2 === $viewPage) $heading = 'sms_notifications';
else $heading = 'msg_templates';

$PAGE->set_pagelayout('standard');
$PAGE->set_title('seven');
$PAGE->set_heading(get_string($heading, 'block_seven'));
$PAGE->set_url($redirectTo);

echo $OUTPUT->header();

if (1 === $viewPage) sevenMsgViewHandler(new voice_send, 'submit_send_voice',
    static function ($users, $text) {
        $results = [];

        foreach ($users as $user) {
            $results[] = sevenApiPost('voice', sevenMsgDefaults($user->phone2,
                sevenReplacePlaceholders($user, $text), 'block_seven_voice_from'));
        }

        return $results;
    });
elseif (2 === $viewPage) sevenMsgViewHandler(
    new sms_send, 'submit_send_sms', static function ($users, $text) {
    $results = [];

    foreach ($users as $user) {
        $results[] = sevenApiPost('sms', sevenMsgDefaults($user->phone2,
            sevenReplacePlaceholders($user, $text), 'block_seven_sms_from'));
    }

    return $results;
});
elseif (3 === $viewPage) {
    $form = new template_form;

    if (optional_param('rem', null, PARAM_RAW)) {
        $delete = optional_param('delete', null, PARAM_RAW);

        if ($delete) {
            $DB->delete_records('block_seven_template', ['id' => $delete]);

            redirect($redirectTo);
        } else {
            $base = '/blocks/seven/view.php?viewpage=3';

            echo $OUTPUT->confirm(
                get_string('tpl_confirm_del', 'block_seven'),
                $base . '&rem=rem&delete=' . $templateId, $base);
        }
    }

    if (optional_param('edit', null, PARAM_RAW)) { // edit message template
        $form = new template_form;
        $form->set_data($DB->get_record(
            'block_seven_template', ['id' => $templateId], '*'));
    }

    $form->set_data(['viewpage' => $viewPage]);
    $form->display();

    echo html_writer::table($form->display_report());

    if (isset($form)) {
        $fromForm = $form->get_data();

        if ($fromForm) {
            $DB->{$fromForm->id ? 'update_record' : 'insert_record'}
            ('block_seven_template', $fromForm);

            redirect($redirectTo);
        }
    }
}

echo $OUTPUT->footer();

function sevenMsgViewHandler($form, $submitId, $apiAction) {
    global $DB, $PAGE, $viewPage;

    $PAGE->requires->js_init_call('M.block_seven.init', [$viewPage]);

    $form->display();

    $table = html_writer::table($form->display_report());
    $submitText = get_string('send', 'block_seven');

    echo sprintf('<form method=\'post\'>
            <div id=\'table-change\'>%s</div>

            <input 
                id=\'%s\' 
                name=\'submit\' 
                style=\'margin-left: 700px\'
                type=\'submit\' 
                value=\'%s\'
             />

            <input type=\'hidden\' name=\'viewpage\' id=\'viewpage\' value=\'%s\' />
         </form>
         <script>
             function toggleRecipients() {
                var checkList = document.getElementsByClassName(\'check_list\');
    
                for (var i = 0; i < checkList.length; i++) 
                    checkList[i].checked = !checkList[i].checked;
            }
        </script>
         ', $table, $submitId, $submitText, $viewPage);

    if (!isset($_REQUEST['submit'])) return;

    $userIds = $_REQUEST['user']; // array of user IDs
    if (empty($userIds)) {
        echo '<p>' . get_string('no_user_selected', 'block_seven') . '</p>';
        return;
    }

    $table = new html_table;
    $table->align = ['center', 'left', 'center', 'center'];
    $table->attributes = ['style' => 'width: 100%;'];
    $table->head = [
        get_string('serial_no', 'block_seven'),
        get_string('moodleuser', 'block_seven'),
        get_string('usernumber', 'block_seven'),
        get_string('status', 'block_seven'),
    ];
    $table->size = ['10%', '40%', '30%', '20%'];

    $users = [];
    foreach ($userIds as $userId) {
        $user = $DB->get_record_sql('SELECT * FROM {user} u WHERE u.id = ?', [$userId]);
        $phone = null;
        if (!empty($user->phone2)) $phone = $user->phone2;
        elseif (!empty($user->phone1)) $phone = $user->phone1;

        if (!$phone) continue;

        $users[] = $user;
    }

    if (empty($users)) {
        echo '<p>' . get_string('no_phone_users_found', 'block_seven') . '</p>';
        return;
    }

    $res = json_encode($apiAction($users, $_REQUEST['msg']));
    echo '<script>window.alert(JSON.stringify(' . $res . ', null, 2))</script>';
}

function sevenMsgDefaults($to, $text, $from) {
    global $CFG;

    if (is_array($to)) $to = implode(',', $to);

    $text = urlencode($text);
    $from = $CFG->$from;

    return 'to=' . $to . '&text=' . $text . '&from=' . $from . '&json=1';
}

function sevenApiPost($endpoint, $params) {
    global $CFG;

    $ch = curl_init('https://gateway.seven.io/api/' . $endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        [
            'sentWith: moodle',
            'X-Api-Key: ' . $CFG->block_seven_apikey,
        ]);
    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function sevenReplacePlaceholders($user, $text) {
    $keys = [
        'username',
        'firstname',
        'lastname',
        'email',
        'phone1',
        'phone2',
        'institution',
        'department',
        'address',
        'city',
        'country',
    ];

    foreach ($keys as $k) $text = str_replace('{{' . $k . '}}', $user->{$k}, $text);

    return $text;
}
