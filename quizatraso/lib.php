<?php
defined('MOODLE_INTERNAL') || die();

function local_quizatraso_coursemodule_standard_elements(\moodleform_mod $formwrapper, MoodleQuickForm $mform) {
    $mform->addElement('date_time_selector', 'quizatraso_duedate', get_string('duedate', 'local_quizatraso'));
    $mform->addHelpButton('quizatraso_duedate', 'duedate', 'local_quizatraso');

    $mform->addElement('text', 'quizatraso_penaltypercent', get_string('penaltypercent', 'local_quizatraso'));
    $mform->setType('quizatraso_penaltypercent', PARAM_FLOAT);
    $mform->setDefault('quizatraso_penaltypercent', 0.0);
    $mform->addHelpButton('quizatraso_penaltypercent', 'penaltypercent', 'local_quizatraso');

    $cm = $formwrapper->get_coursemodule();
    if ($cm->instance) {
        global $DB;
        $record = $DB->get_record('local_quizatraso_config', ['quizid' => $cm->instance]);
        if ($record) {
            $mform->setDefault('quizatraso_duedate', $record->duedate);
            $mform->setDefault('quizatraso_penaltypercent', $record->penaltypercent);
        }
    }
}

function local_quizatraso_coursemodule_edit_post_actions($data, $course) {
    global $DB;

    if ($data->modulename === 'quiz' && isset($data->quizatraso_duedate)) {
        $record = $DB->get_record('local_quizatraso_config', ['quizid' => $data->instance]);
        $newdata = (object)[
            'quizid' => $data->instance,
            'duedate' => $data->quizatraso_duedate,
            'penaltypercent' => $data->quizatraso_penaltypercent ?? 0.0
        ];

        if ($record) {
            $newdata->id = $record->id;
            $DB->update_record('local_quizatraso_config', $newdata);
        } else {
            $DB->insert_record('local_quizatraso_config', $newdata);
        }
    }
}
