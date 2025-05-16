<?php
defined('MOODLE_INTERNAL') || die();

function local_quizatraso_coursemodule_standard_elements($formwrapper, $mform) {
    global $PAGE, $CFG;

    if ($PAGE->context->contextlevel == CONTEXT_MODULE) {
        require_once($CFG->dirroot.'/course/modlib.php');
        $cm = get_coursemodule_from_id('quiz', $PAGE->context->instanceid);
        if ($cm) {
            require_once(__DIR__.'/locallib.php');
            local_quizatraso_add_form_elements($formwrapper, $mform);

            global $DB;
            if ($quiz = $DB->get_record('quiz', ['id' => $cm->instance])) {
                local_quizatraso_load_settings($quiz, $mform);
            }
        }
    }
}

function local_quizatraso_coursemodule_edit_post_actions($data, $course) {
    require_once(__DIR__.'/locallib.php');
    local_quizatraso_process_form_data($data);
    return $data;
}
