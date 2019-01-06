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

global $CFG;

require_once($CFG->dirroot . '/local/deleteoldquizattempts/locallib.php');

/**
 * Unittests for task
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_deleteoldquizattempts_task_testcase extends advanced_testcase {

    /**
     * Tests task::execute
     */
    public function test_execute() {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $timestamp = time() - 60 * 60 * 24 * 2; // Two days old.
        $attemptid = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $timestamp,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => 0,
            'uniqueid' => 0
        ));

        $task = new local_deleteoldquizattempts\task\delete_attempts_task();

        set_config('attemptlifetime', null, 'local_deleteoldquizattempts');
        set_config('max', null, 'local_deleteoldquizattempts');
        $task->execute();
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertNotEmpty($attempt);

        set_config('attemptlifetime', 1, 'local_deleteoldquizattempts');
        ob_start();
        $task->execute();
        $output = ob_get_clean();
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertEmpty($attempt);
        $this->assertContains('Deleted 1 old quiz attempts.', $output);
    }

}
