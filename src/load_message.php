<?php require_once '../../config.php';

$msg_id = required_param('m_id', PARAM_INT);

if ($msg_id) {
    $result = $DB->get_record_sql(
        'SELECT template FROM {block_seven_template} WHERE id = ?', [$msg_id]);
} else {
    $result = new stdClass;
    $result->template = get_string('edit_me', 'block_seven');
}

echo $result->template;
