<?php

require_once __DIR__ . '/../../config.php';

function sms77_msg_defaults($to, $text) {
    global $CFG;

    if (is_array($to)) {
        $to = implode(',', $to);
    }

    $text = urlencode($text);

    return "to=$to&text=$text&from=$CFG->block_sms77_from";
}

function sms77_send_sms($recipients, $text) {
    return sms77_api_post('sms', sms77_msg_defaults($recipients, $text) . "&json=1");
}

function sms77_send_voice($recipients, $text) {
    $results = [];

    foreach ($recipients as $recipient) {
        $results[] = sms77_api_post(
            'voice', sms77_msg_defaults($recipient, $text));
    }

    return json_encode($results);
}

function sms77_api_post($endpoint, $params) {
    global $CFG;

    $ch = curl_init("https://gateway.sms77.io/api/$endpoint");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "p=$CFG->block_sms77_apikey&$params");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}