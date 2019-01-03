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
 * CLI tool.
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/local/deleteoldquizattempts/locallib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'days' => false,
        'timestamp' => false,
        'date' => false,
        'verbose' => false,
        'help' => false
    ),
    array(
        'h' => 'help',
        'v' => 'verbose'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

$exclusive_options = array_intersect(
    array_keys(array_filter($options)), 
    array('days', 'timestamp', 'date')
);
if (count($exclusive_options) != 1) {
    $help = "Delete old quiz and question attempts

Options:
--days=               Delete attempts that are older than specified number of days
--timestamp=          Delete attempts that are created before specified UTC timestamp
--date=               Delete attempts that are created before specified date.
                      Use \"YYYY-MM-DD HH:MM:SS\" format in UTC
-v, --verbose         Show progress
-h, --help            Print out this help

Only one of --days, --timestamp and --date options should be specified.

Examples:
 php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --verbose
 php local/deleteoldquizattempts/cli/delete_attempts.php --timestamp=1514764800
 php local/deleteoldquizattempts/cli/delete_attempts.php --date=\"2018-01-01 00:00:00\"
";
    echo $help;
    exit(0);
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

local_purgequestioncategory_delete_attempts($timestamp, $trace);

if ($trace) {
    $trace->finished();
}
