<?php
defined('MOODLE_INTERNAL') || die();
$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'local_quizatraso\observer::quiz_submitted',
    ],
];
