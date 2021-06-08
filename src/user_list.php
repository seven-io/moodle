<?php
require_once '../../config.php';
require_once 'msg_forms.php';

$report = (new sms_send)->display_report(
    required_param('c_id', PARAM_INT), required_param('r_id', PARAM_INT));

$msg = addslashes((string)$_REQUEST['msg']);

echo html_writer::table($report)
    . "<input type='hidden' value='$msg' name='msg' />";
