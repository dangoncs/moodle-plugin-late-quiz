<?php
defined('MOODLE_INTERNAL') || die();

function local_quizatraso_before_standard_html_head() {
    global $PAGE, $OUTPUT;
    
    if ($PAGE->cm && $PAGE->cm->modname === 'quiz') {
        require_once(__DIR__.'/classes/quizatraso.php');
        $duedate = local_quizatraso::get_due_date_display($PAGE->cm->instance);
        
        if ($duedate) {
            $message = get_string('duedate_info', 'local_quizatraso', $duedate);
            echo $OUTPUT->notification($message, 'info');
        }
    }
}

function local_quizatraso_add_form_elements($formwrapper, $mform) {
    $mform->addElement('header', 'local_quizatraso_settings_header', get_string('settingsheader', 'local_quizatraso'));
    $mform->setExpanded('local_quizatraso_settings_header');
    
    $mform->addElement('date_time_selector', 'quizatraso_duedate', get_string('duedate', 'local_quizatraso'));
    $mform->addHelpButton('quizatraso_duedate', 'duedate', 'local_quizatraso');
    
    $mform->addElement('text', 'quizatraso_penaltypercent', get_string('penaltypercent', 'local_quizatraso'));
    $mform->setType('quizatraso_penaltypercent', PARAM_FLOAT);
    $mform->setDefault('quizatraso_penaltypercent', 0.0);
    $mform->addHelpButton('quizatraso_penaltypercent', 'penaltypercent', 'local_quizatraso');
    $mform->addRule('quizatraso_penaltypercent', get_string('penaltypercent_error', 'local_quizatraso'), 'numeric', null, 'client');
    //$mform->addRule('quizatraso_penaltypercent', null, '', null, 'client');
    //getRegisteredRules()
}

function local_quizatraso_load_settings($quiz, $mform) {
    global $DB;
    
    if ($settings = $DB->get_record('local_quizatraso', ['quizid' => $quiz->id])) {
        $mform->setDefault('quizatraso_duedate', $settings->duedate);
        $mform->setDefault('quizatraso_penaltypercent', $settings->penaltypercent);
    }
}

function local_quizatraso_process_form_data($data) {
    global $DB;
    
    $existing = $DB->get_record('local_quizatraso', ['quizid' => $data->instance]);

    $record = new stdClass();
    $record->quizid = $data->instance;
    $record->duedate = $data->quizatraso_duedate ?? 0;
    $record->penaltypercent = $data->quizatraso_penaltypercent ?? 0.0;

    if ($existing) {
        $record->id = $existing->id;
        $DB->update_record('local_quizatraso', $record);
    } else {
        $DB->insert_record('local_quizatraso', $record);
    }
}
