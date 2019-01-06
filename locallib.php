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
function local_deleteoldquizattempts_delete_attempts($timestamp, $timelimit = 0, $trace = null) {
    global $DB;

    if ($trace) {
        $total = $DB->count_records_select('quiz_attempts', "timestart < :timestamp", array('timestamp' => $timestamp));
    } else {
        $total = 0;
    }
    $deleted = 0;
    $starttime = time();
    $rs = $DB->get_recordset_select('quiz_attempts', "timestart < :timestamp", array('timestamp' => $timestamp));
    foreach ($rs as $attempt) {
        $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz));
        quiz_delete_attempt($attempt, $quiz);
        $deleted++;
        if ($trace) {
            $trace->output(get_string('progress', 'local_deleteoldquizattempts', array(
                'deleted' => $deleted,
                'total' => $total,
            )));
        }
        if ($timelimit && (time() > $starttime + $timelimit)) {
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
 * CLI hander
 *
 * @param array $options
 */
function local_deleteoldquizattempts_cli($options) {

    $exclusive_options = array_intersect(
        array_keys(array_filter($options)),
        array('days', 'timestamp', 'date')
    );
    if ($options['help'] || count($exclusive_options) != 1) {
        $help = "Delete old quiz and question attempts

Options:
--days=               Delete attempts that are older than specified number of days
--timestamp=          Delete attempts that are created before specified UTC timestamp
--date=               Delete attempts that are created before specified date.
                      Use \"YYYY-MM-DD HH:MM:SS\" format in UTC
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

    if ($options['days']) {
        $timestamp = time() - ((int)$options['days'] * 3600 * 24);
    } elseif ($options['timestamp']) {
        $timestamp = (int)$options['timestamp'];
    } elseif ($options['date']) {
        $tz = new DateTimeZone('UTC');
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $options['date'], $tz);
        $timestamp = $date->getTimestamp();
    }
    if ($options['verbose']) {
        /** @var text_progress_trace $trace */
        $trace = new text_progress_trace();
    } else {
        $trace = null;
    }

    if ($options['timelimit']) {
        $timelimit = (int)$options['timelimit'];
    } else {
        $timelimit = 0;
    }

    local_deleteoldquizattempts_delete_attempts($timestamp, $timelimit, $trace);

    if ($trace) {
        $trace->finished();
    }

}
