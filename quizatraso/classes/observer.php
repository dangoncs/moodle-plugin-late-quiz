<?php
namespace local_quizatraso;
defined('MOODLE_INTERNAL') || die();

class observer {

    public static function quiz_submitted($event) {
        global $DB;
    
        $attempt = $DB->get_record('quiz_attempts', ['id' => $event->objectid], '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
        $settings = $DB->get_record('local_quizatraso', ['quizid' => $quiz->id]);
        
        require_once(__DIR__.'/quizatraso_functions.php');
        quizatraso_functions::apply_penalty($attempt, $quiz, $settings);
    }
}
