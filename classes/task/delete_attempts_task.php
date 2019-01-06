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
 * Scheduler task.
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_deleteoldquizattempts\task;

require_once($CFG->dirroot . '/local/deleteoldquizattempts/locallib.php');

/**
 * Scheduler task.
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_attempts_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('taskname', 'local_deleteoldquizattempts');
    }

    public function execute() {
        $lifetime = (int)get_config('local_deleteoldquizattempts', 'attemptlifetime');
        $timelimit = (int)get_config('local_deleteoldquizattempts', 'maxexecutiontime');
        if (empty($lifetime) || $lifetime < 0) {
            return;
        }

        $timestamp = time() - ($lifetime  * 3600 * 24);
        $attempts = local_deleteoldquizattempts_delete_attempts($timestamp, $timelimit);
        mtrace("    Deleted $attempts old quiz attempts.");
    }

}
