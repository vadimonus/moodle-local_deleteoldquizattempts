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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

/**
 * Deletes quiz attempts older than timestamp
 *
 * @param int $timestamp
 * @param progress_trace|null $trace
 * @return int deleted attempts count
 */
function local_purgequestioncategory_delete_attempts($timestamp, $trace = null) {
    global $DB;
    
    if ($trace) {
        $total = $DB->count_records_select('quiz_attempts', "timestart < :timestamp", array('timestamp' => $timestamp));
    } else {
        $total = 0;
    }
    $deleted = 0;
    $rs = $DB->get_recordset_select('quiz_attempts', "timestart < :timestamp", array('timestamp' => $timestamp));
    foreach ($rs as $attempt) {
        $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz));
        quiz_delete_attempt($attempt, $quiz);
        $deleted++;
        if ($trace) {
            $trace->output(get_string('progress', 'local_purgequestioncategory', array(
                'deleted' => $deleted,
                'total' => $total,
            )));
        }
    }
    $rs->close();
    return $deleted;
}
