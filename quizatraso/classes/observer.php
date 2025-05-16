<?php
namespace local_quizatraso;
defined('MOODLE_INTERNAL') || die();

class observer {
    public static function quiz_submitted($event) {
        global $DB;
    
        $attempt = $DB->get_record('quiz_attempts', ['id' => $event->objectid], '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
        $settings = $DB->get_record('local_quizatraso', ['quizid' => $quiz->id]);

        if ((!$settings) || ($settings->penaltypercent <= 0) || ($attempt->timefinish <= $settings->duedate)) {
            return;
        }
        
        require_once(__DIR__.'/quizatraso.php');
        local_quizatraso::apply_penalty($quiz, $attempt, $penaltypercent);
    }
}
