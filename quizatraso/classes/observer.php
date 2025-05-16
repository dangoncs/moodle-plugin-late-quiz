<?php
namespace local_quizatraso;
defined('MOODLE_INTERNAL') || die();

class observer {
    public static function quiz_submitted(\mod_quiz\event\attempt_submitted $event) {
        global $DB;

        $attempt = $DB->get_record('quiz_attempts', ['id' => $event->objectid], '*', MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz], '*', MUST_EXIST);
        $config = $DB->get_record('local_quizatraso_config', ['quizid' => $quiz->id]);

        if (!$config || !$config->duedate) return;

        $submitted = $attempt->timefinish;
        $duedate = $config->duedate;

        $delayseconds = $submitted - $duedate;
        $delaydays = $delayseconds > 0 ? ceil($delayseconds / 86400) : 0;

        if ($delaydays > 0 && $attempt->sumgrades !== null) {
            $penaltypercent = $config->penaltypercent ?? 0.0;
            $penalty_per_day = $penaltypercent / 100.0;

            $original_grade = $attempt->sumgrades;
            $formatted_original_grade = number_format($original_grade, 2);
            $final_grade = max(0, $original_grade * (1 - $penalty_per_day * $delaydays));

            $attempt->sumgrades = $final_grade;
            $DB->update_record('quiz_attempts', $attempt);

            $grade = $DB->get_record('quiz_grades', ['quiz' => $quiz->id, 'userid' => $attempt->userid]);
            if ($grade) {
                $grade->grade = $final_grade;
                $DB->update_record('quiz_grades', $grade);
            }

            \core\notification::info("NOTA PENALIZADA: de {$formatted_original_grade} para {$final_grade}, por conta do atraso de {$delaydays} dia(s).");
        }
    }
}
