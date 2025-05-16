<?php
namespace local_quizatraso;
defined('MOODLE_INTERNAL') || die();

class quizatraso_functions {
    
    public static function apply_penalty($attempt, $quiz, $settings) {
        global $DB;

        if ((!$settings) || ($settings->penaltypercent <= 0) || ($attempt->timefinish <= $settings->duedate)) {
            return;
        }
        
        $original_grade = $attempt->sumgrades;
        $penalty_factor = 1 - ($penaltypercent / 100.0);
        $penalized_grade = $original_grade * $penalty_factor;
        $final_grade = max(0, $penalized_grade);
        
        $formatted_original_grade = number_format($original_grade, 2);
        
        // TODO: Test what happens when recalculation of grades is triggered by Moodle.
        // If that happens, the grade we are setting here at the end of this function might be overriden.
        // Therefore, we would need to find a better way to set the grade.
        // For now, we are just updating the quiz_attempts table directly.
        // Test this with a quiz that has a gradebook item.
        // quiz_save_best_grade($quiz, $attempt->userid);

        $attempt->sumgrades = $final_grade;
        $DB->update_record('quiz_attempts', $attempt);

        $grade = $DB->get_record('quiz_grades', ['quiz' => $quiz->id, 'userid' => $attempt->userid]);
        if ($grade) {
            $grade->grade = $final_grade;
            $DB->update_record('quiz_grades', $grade);
        }

        \core\notification::info("NOTA PENALIZADA: de {$formatted_original_grade} para {$final_grade}, por conta do atraso de {$delaydays} dia(s).");
    }
    
    public static function get_due_date_display($quizid) {
        global $DB, $USER;
        
        $settings = $DB->get_record('local_quizatraso', ['quizid' => $quizid]);
        if (!$settings || $settings->duedate == 0) {
            return '';
        }
        
        return userdate($settings->duedate, get_string('strftimedatetime', 'langconfig'));
    }

    public static function get_attempt_feedback($attempt) {
        global $DB;
        
        $settings = $DB->get_record('local_quizatraso', ['quizid' => $attempt->quiz]);
        if (!$settings || $settings->duedate == 0) {
            return '';
        }
        
        if ($attempt->timefinish > $settings->duedate) {
            return get_string('late_submission', 'local_quizatraso') . ' (' . 
                get_string('penalty_applied', 'local_quizatraso', $settings->penaltypercent) . ')';
        }
        
        return '';
    }
}
