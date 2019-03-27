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

$string['attemptlifetime'] = 'Delete attempts older than';
$string['attemptlifetime_help'] = 'Quiz аttempts that are older than specified value will be deleted with scheduler task. If "Do not delete old attempts" value is selected, you can still delete atttempts with CLI command.';
$string['attemptsdeleted'] = 'Deleted {$a} quiz attempts.';
$string['attemptsprogress'] = 'Deleted {$a->deleted} of {$a->total}';
$string['deleteunusedhiddenquestions'] = 'Delete unused hidden questions';
$string['deleteunusedhiddenquestions_help'] = 'Hidden questions are questions, that were logically deleted, but were not deleted physically, because they were referenced in some quiz attempts. After quiz attempts deletion, such questions are probably no longer required.';
$string['donotdeleteonschedule'] = 'Do not delete old attempts';
$string['maxexecutiontime'] = 'Max execution time';
$string['maxexecutiontime_help'] = 'Deleting old attempts can cause high server load. This parameter limits the maximum execution time of scheduler task.';
$string['maxexecutiontime_reached'] = 'Operation stopped due to time limit';
$string['notlimited'] = 'Not limited';
$string['pluginname'] = 'Old quiz and question attempts deletion';
$string['privacy:metadata'] = 'The plugin does not store any personal data.';
$string['questionsdeleted'] = 'Deleted {$a->deleted}, skipped {$a->skipped} unused hidden questions.';
$string['questionsprogress'] = 'Deleted {$a->deleted}, skipped {$a->skipped} of {$a->total}';
$string['taskname'] = 'Old quiz and question attempts deletion';
