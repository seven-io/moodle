<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'block/sms77:myaddinstance' => [
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
        'captype' => 'write',
        'clonepermissionsfrom' => 'moodle/my:manageblocks',
        'contextlevel' => CONTEXT_COURSE,
    ],

    'block/sms77:addinstance' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],

        'clonepermissionsfrom' => 'moodle/site:manageblocks',
    ],

    'block/sms77:viewpages' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => [
            'coursecreator' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
            'manager' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'teacher' => CAP_ALLOW,

        ],
    ],

    'block/sms77:managepages' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => [
            'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'guest' => CAP_PREVENT,
            'manager' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'teacher' => CAP_ALLOW,
        ],
    ],
];