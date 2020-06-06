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
 * Unittests for delete_attempts
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_deleteoldquizattempts_locallib_testcase extends advanced_testcase {

    /**
     * Tests delete_attempts
     */
    public function test_delete_attempts() {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $now = time();
        $attempt = 0;
        $timestamp1 = $now - 2000;
        $attemptid1 = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $timestamp1,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => $attempt,
            'uniqueid' => $attempt
        ));

        $timestamp2 = $now - 1000;
        $attempt++;
        $attemptid2 = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $timestamp2,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => $attempt,
            'uniqueid' => $attempt
        ));

        $helper = new local_deleteoldquizattempts\helper();

        $helper->delete_attempts($timestamp1);
        $attempt1 = $DB->get_record('quiz_attempts', array('id' => $attemptid1));
        $attempt2 = $DB->get_record('quiz_attempts', array('id' => $attemptid2));
        $this->assertNotEmpty($attempt1);
        $this->assertNotEmpty($attempt2);

        $helper->delete_attempts($timestamp2);
        $attempt1 = $DB->get_record('quiz_attempts', array('id' => $attemptid1));
        $attempt2 = $DB->get_record('quiz_attempts', array('id' => $attemptid2));
        $this->assertEmpty($attempt1);
        $this->assertNotEmpty($attempt2);

        $helper->delete_attempts($timestamp2 + 1);
        $attempt1 = $DB->get_record('quiz_attempts', array('id' => $attemptid1));
        $attempt2 = $DB->get_record('quiz_attempts', array('id' => $attemptid2));
        $this->assertEmpty($attempt1);
        $this->assertEmpty($attempt2);
    }

    /**
     * Tests delete_attempts call with trace and timelimit
     */
    public function test_delete_attempts_with_timelimit() {
        global $DB;

        $this->resetAfterTest(true);

        $trace = $this->getMockBuilder('null_progress_trace')->setMethods(array('output'))->getMock();

        $expectation1 = $trace->expects($this->at(0));
        $expectation1->method('output');
        $expectation1->with($this->stringContains('Deleted 1 of 1'));

        $expectation2 = $trace->expects($this->at(1));
        $expectation2->method('output');
        $expectation2->with($this->stringContains('Operation stopped due to time limit'));

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $now = time();
        $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $now,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => 0,
            'uniqueid' => 0
        ));

        $helper = new local_deleteoldquizattempts\helper();
        $helper->delete_attempts($now + 1, $now, $trace);
    }

    /**
     * Tests delete_attempts call with with quizid
     */
    public function test_delete_attempts_with_quizid() {
        global $DB;

        $this->resetAfterTest(true);

        $trace = $this->getMockBuilder('null_progress_trace')->setMethods(array('output'))->getMock();

        $expectation1 = $trace->expects($this->at(0));
        $expectation1->method('output');
        $expectation1->with($this->stringContains('Deleted 1 of 1'));

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $now = time();
        $attemptid = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $now,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => 0,
            'uniqueid' => 0
        ));

        $helper = new local_deleteoldquizattempts\helper();
        $helper->quizid = $quiz->id;
        $helper->delete_attempts($now + 1, 0, $trace);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertEmpty($attempt);
    }

    /**
     * Tests delete_attempts call with with courseid
     */
    public function test_delete_attempts_with_courseid() {
        global $DB;

        $this->resetAfterTest(true);

        $trace = $this->getMockBuilder('null_progress_trace')->setMethods(array('output'))->getMock();

        $expectation1 = $trace->expects($this->at(0));
        $expectation1->method('output');
        $expectation1->with($this->stringContains('Deleted 1 of 2'));

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz1 = $generator->create_instance(array('course' => $course->id));
        $quiz2 = $generator->create_instance(array('course' => $course->id));

        $now = time();
        $attemptid1 = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz1->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $now,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => 0,
            'uniqueid' => 0
        ));
        $attemptid2 = $DB->insert_record('quiz_attempts', array(
            'quiz' => $quiz2->id,
            'userid' => $user->id,
            'state' => 'inprogress',
            'timestart' => $now,
            'timecheckstate' => 0,
            'layout' => '',
            'attempt' => 0,
            'uniqueid' => 1
        ));

        $helper = new local_deleteoldquizattempts\helper();
        $helper->courseid = $course->id;
        $helper->delete_attempts($now + 1, 0, $trace);

        $attempt1 = $DB->get_record('quiz_attempts', array('id' => $attemptid1));
        $this->assertEmpty($attempt1);
        $attempt2 = $DB->get_record('quiz_attempts', array('id' => $attemptid2));
        $this->assertEmpty($attempt2);
    }
}
