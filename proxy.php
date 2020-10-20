<?php

require_once '../../config.php';
require_once 'msg_forms.php';
require_once 'lib.php';

$c_id = required_param('c_id', PARAM_INT);
$group = optional_param('group', 0, PARAM_INT);

$attributes = [];

if ($c_id && $group) {
    if ('0' === $group) {
        $attributes = ['1' => 'No Group'];
    } else if ('2' === $group) {
        $attributes = get_groups($c_id);
    }
}

$data = "";
foreach ($attributes as $key => $attrib) {
    $data .= $key . '~' . $attrib . '^';
}

return print_r($data);
