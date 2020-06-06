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

/**
 * Unittests for task
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_deleteoldquizattempts_task_testcase extends advanced_testcase {

    /**
     * All options disabled
     */
    public function test_all_disabled() {
        $this->resetAfterTest(true);

        set_config('attemptlifetime', null, 'local_deleteoldquizattempts');
        set_config('maxexecutiontime', null, 'local_deleteoldquizattempts');
        set_config('deleteunusedquestions', null, 'local_deleteoldquizattempts');

        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts', 'delete_unused_questions'));
        $helper = $mockbuilder->getMock();

        $expectation1 = $helper->expects($this->never());
        $expectation1->method('delete_attempts');

        $expectation2 = $helper->expects($this->never());
        $expectation2->method('delete_unused_questions');

        $helper->task_handler();
    }

    /**
     * Option attemptlifetime is enabled
     */
    public function test_delete_attempts_enabled() {
        global $DB;

        $this->resetAfterTest(true);

        set_config('attemptlifetime', 30, 'local_deleteoldquizattempts');
        set_config('maxexecutiontime', 0, 'local_deleteoldquizattempts');
        set_config('deleteunusedquestions', 0, 'local_deleteoldquizattempts');

        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts', 'delete_unused_questions'));
        $helper = $mockbuilder->getMock();

        $expectedtimestamp = time() - 30 * 3600 * 24;
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->with(
            $this->logicalAnd(
                $this->greaterThanOrEqual($expectedtimestamp),
                $this->lessThan($expectedtimestamp + 5)
            ),
            0
        );
        $expectation1->willReturn(99);

        $expectation2 = $helper->expects($this->never());
        $expectation2->method('delete_unused_questions');

        ob_start();
        $helper->task_handler();
        $output = ob_get_clean();

        $this->assertContains('Deleted 99 quiz attempts.', $output);
    }

    /**
     * Option deleteunusedquestions is enabled
     */
    public function test_delete_questions_enabled() {
        $this->resetAfterTest(true);

        set_config('attemptlifetime', 0, 'local_deleteoldquizattempts');
        set_config('maxexecutiontime', 0, 'local_deleteoldquizattempts');
        set_config('deleteunusedquestions', 1, 'local_deleteoldquizattempts');

        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('task_hander', 'delete_attempts', 'delete_unused_questions'));
        $helper = $mockbuilder->getMock();

        $expectation1 = $helper->expects($this->never());
        $expectation1->method('delete_attempts');

        $expectation2 = $helper->expects($this->once());
        $expectation2->method('delete_unused_questions');
        $expectation2->willReturn(array(88, 77));

        ob_start();
        $helper->task_handler();
        $output = ob_get_clean();

        $this->assertContains('Deleted 88, skipped 77 unused hidden questions.', $output);
    }

    /**
     * Tests delete_unused_questions is not called then timeput on delete_attempts.
     */
    public function test_timeout_on_first() {
        $this->resetAfterTest(true);

        set_config('attemptlifetime', 30, 'local_deleteoldquizattempts');
        set_config('maxexecutiontime', 1, 'local_deleteoldquizattempts');
        set_config('deleteunusedquestions', 1, 'local_deleteoldquizattempts');

        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts', 'delete_unused_questions'));
        $helper = $mockbuilder->getMock();

        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->willReturnCallback(function () {
            sleep(2);
            return 99;
        });

        $expectation2 = $helper->expects($this->never());
        $expectation2->method('delete_unused_questions');

        ob_start();
        $helper->task_handler();
        $output = ob_get_clean();
    }
}
