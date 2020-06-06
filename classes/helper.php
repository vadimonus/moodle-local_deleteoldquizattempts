<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tool for deleting old quiz and question attempts.
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_deleteoldquizattempts;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

/**
 * Class with main functions
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * @var int|null optional quiz id to filter attempts
     */
    public $quizid = null;

    /**
     * @var int|null optional course id to filter attempts
     */
    public $courseid = null;

    /**
     * Deletes quiz attempts older than timestamp
     *
     * @param int $timestamp
     * @param int $stoptime
     * @param \progress_trace|null $trace
     * @return int deleted attempts count
     */
    public function delete_attempts($timestamp, $stoptime = 0, $trace = null) {
        global $DB;

        $where = "timestart < :timestamp";
        $params = array('timestamp' => $timestamp);
        if ($this->courseid) {
            $quizids = $DB->get_fieldset_select('quiz', 'id', 'course = :course', array(
                'course' => $this->courseid
            ));
            list($quizwhere, $qizparams) = $DB->get_in_or_equal($quizids, SQL_PARAMS_NAMED, 'quiz');
            $where .= ' AND quiz ' . $quizwhere;
            $params = array_merge($params, $qizparams);
        } else if ($this->quizid) {
            $where .= ' AND quiz = :quizid';
            $params = array_merge($params, array('quizid' => $this->quizid));
        }
        if ($trace) {
            $total = $DB->count_records_select('quiz_attempts', $where, $params);
        } else {
            $total = 0;
        }
        $deleted = 0;
        $rs = $DB->get_recordset_select('quiz_attempts', $where, $params);
        foreach ($rs as $attempt) {
            $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz));
            quiz_delete_attempt($attempt, $quiz);
            $deleted++;
            if ($trace) {
                $trace->output(get_string('attemptsprogress', 'local_deleteoldquizattempts', array(
                    'deleted' => $deleted,
                    'total' => $total,
                )));
            }
            if ($stoptime && (time() >= $stoptime)) {
                if ($trace) {
                    $trace->output(get_string('maxexecutiontime_reached', 'local_deleteoldquizattempts'));
                }
                break;
            }
        }
        $rs->close();
        return $deleted;
    }

    /**
     * Deletes unused hidden questions
     *
     * @param int $stoptime
     * @param \progress_trace|null $trace
     * @return array deleted and skipped questions count
     */
    public function delete_unused_questions($stoptime = 0, $trace = null) {
        global $DB;

        $where = "
            hidden = :hidden
            AND NOT EXISTS (
                SELECT 1
                FROM {question_attempts}
                WHERE {question_attempts}.questionid = {question}.id
            )
            AND NOT EXISTS (
                SELECT 1
                FROM {quiz_slots}
                WHERE {quiz_slots}.questionid = {question}.id
            )";
        $params = array('hidden' => true);
        if ($trace) {
            $total = $DB->count_records_select('question', $where, $params);
        } else {
            $total = 0;
        }
        $deleted = 0;
        $skipped = 0;
        $rs = $DB->get_recordset_select('question', $where, $params);
        foreach ($rs as $question) {
            question_delete_question($question->id);
            if ($DB->record_exists('question', array('id' => $question->id))) {
                $skipped++;
            } else {
                $deleted++;
            }
            if ($trace) {
                $trace->output(get_string('questionsprogress', 'local_deleteoldquizattempts', array(
                    'deleted' => $deleted,
                    'skipped' => $skipped,
                    'total' => $total,
                )));
            }
            if ($stoptime && (time() >= $stoptime)) {
                if ($trace) {
                    $trace->output(get_string('maxexecutiontime_reached', 'local_deleteoldquizattempts'));
                }
                break;
            }
        }
        $rs->close();
        return array($deleted, $skipped);
    }

    /**
     * Task hander
     */
    public function task_handler() {
        $timelimit = (int)get_config('local_deleteoldquizattempts', 'maxexecutiontime');
        $lifetime = (int)get_config('local_deleteoldquizattempts', 'attemptlifetime');
        $deletequestions = (int)get_config('local_deleteoldquizattempts', 'deleteunusedquestions');

        if ($timelimit) {
            $stoptime = time() + $timelimit;
        } else {
            $stoptime = 0;
        }

        if (!empty($lifetime)) {
            $timestamp = time() - ($lifetime * 3600 * 24);

            $attempts = $this->delete_attempts($timestamp, $stoptime);
            mtrace('    ' . get_string('attemptsdeleted', 'local_deleteoldquizattempts', $attempts));
        }

        if ($stoptime && time() > $stoptime) {
            return;
        }

        if ($deletequestions) {
            list($deleted, $skipped) = $this->delete_unused_questions($stoptime);
            mtrace('    ' . get_string('questionsdeleted', 'local_deleteoldquizattempts', array(
                'deleted' => $deleted,
                'skipped' => $skipped
            )));
        }
    }

    /**
     * CLI hander for delete_attempts
     *
     * @param array $options
     */
    public function delete_attempts_cli_handler($options) {

        $exclusiveoptions = array_intersect(
            array_keys(array_filter($options)),
            array('days', 'timestamp', 'date')
        );
        $exclusiveoptions2 = array_intersect(
            array_keys(array_filter($options)),
            array('courseid', 'quizid')
        );
        if (!empty($options['help']) || count($exclusiveoptions) != 1 || count($exclusiveoptions2) > 1) {
            $help = "Delete old quiz and question attempts

Options:
--days=               Delete attempts that are older than specified number of days
--timestamp=          Delete attempts that are created before specified UTC timestamp
--date=               Delete attempts that are created before specified date.
                      Use \"YYYY-MM-DD HH:MM:SS\" format in UTC
--courseid=           Delete only attempts for quizzes in course with specified id.
--quizid=             Delete only attempts for quiz with specified id.
--timelimit=          Stop execution after specified number of seconds
-v, --verbose         Show progress
-h, --help            Print out this help

Only one of --days, --timestamp and --date options should be specified.

Examples:
 php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --verbose
 php local/deleteoldquizattempts/cli/delete_attempts.php --timestamp=1514764800 --timelimit=300
 php local/deleteoldquizattempts/cli/delete_attempts.php --date=\"2018-01-01 00:00:00\"
";
            echo $help;
            return;
        }

        // Ensure errors are well explained.
        set_debugging(DEBUG_DEVELOPER, true);

        if (!empty($options['days'])) {
            $timestamp = time() - ((int)$options['days'] * 3600 * 24);
        } else if (!empty($options['timestamp'])) {
            $timestamp = (int)$options['timestamp'];
        } else if (!empty($options['date'])) {
            $tz = new \DateTimeZone('UTC');
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $options['date'], $tz);
            $timestamp = $date->getTimestamp();
        }
        if (!empty($options['quizid'])) {
            $this->quizid = $options['quizid'];
        } else if (!empty($options['courseid'])) {
            $this->courseid = $options['courseid'];
        }
        if (!empty($options['verbose'])) {
            /** @var text_progress_trace $trace */
            $trace = new \text_progress_trace();
        } else {
            $trace = null;
        }

        if (!empty($options['timelimit'])) {
            $stoptime = time() + (int)$options['timelimit'];
        } else {
            $stoptime = 0;
        }

        $this->delete_attempts($timestamp, $stoptime, $trace);

        if ($trace) {
            $trace->finished();
        }
    }

    /**
     * CLI hander for delete_unused_questions
     *
     * @param array $options
     */
    public function delete_questions_cli_handler($options) {
        if ($options['help']) {
            $help = "Delete unused hidden questions

Options:
--timelimit=          Stop execution after specified number of seconds
-v, --verbose         Show progress
-h, --help            Print out this help

Examples:
 php local/deleteoldquizattempts/cli/delete_attempts.php --timelimit=300 --verbose
";
            echo $help;
            return;
        }

        // Ensure errors are well explained.
        set_debugging(DEBUG_DEVELOPER, true);

        if ($options['verbose']) {
            /** @var text_progress_trace $trace */
            $trace = new \text_progress_trace();
        } else {
            $trace = null;
        }

        if ($options['timelimit']) {
            $stoptime = time() + (int)$options['timelimit'];
        } else {
            $stoptime = 0;
        }

        $this->delete_unused_questions($stoptime, $trace);

        if ($trace) {
            $trace->finished();
        }
    }

}
