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
 * Unittests for CLI
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_deleteoldquizattempts_cli_testcase extends advanced_testcase {

    /**
     * Tests cli/delete_attempts.php --help
     */
    public function test_help() {
        $options = array(
            'days' => false,
            'timestamp' => false,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        ob_start();
        $output = local_deleteoldquizattempts_cli($options);
        $output = ob_get_clean();
        $this->assertContains('Print out this help', $output);

        $options = array(
            'days' => 1,
            'timestamp' => false,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => true
        );
        ob_start();
        $output = local_deleteoldquizattempts_cli($options);
        $output = ob_get_clean();
        $this->assertContains('Print out this help', $output);

        $options = array(
            'days' => 1,
            'timestamp' => 1,
            'date' => '2000-01-01 00:00:00',
            'timelimit' => false,
            'verbose' => false,
            'help' => true
        );
        ob_start();
        $output = local_deleteoldquizattempts_cli($options);
        $output = ob_get_clean();
        $this->assertContains('Print out this help', $output);
}

    /**
     * Tests cli/delete_attempts.php --days=1
     */
    public function test_days() {
        /** @var moodle_database $DB */
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

        $options = array(
            'days' => 3,
            'timestamp' => false,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        local_deleteoldquizattempts_cli($options);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertNotEmpty($attempt);

        $options = array(
            'days' => 1,
            'timestamp' => false,
            'date' => false,
            'timelimit' => false,
            'verbose' => true,
            'help' => false
        );
        ob_start();
        $output = local_deleteoldquizattempts_cli($options);
        $output = ob_get_clean();
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertEmpty($attempt);
        $this->assertContains('Deleted 1 of 1', $output);
    }

    /**
     * Tests cli/delete_attempts.php --timestamp=10000
     */
    public function test_timestamp() {
        /** @var moodle_database $DB */
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $timestamp = 9999;
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

        $options = array(
            'days' => false,
            'timestamp' => 9999,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        local_deleteoldquizattempts_cli($options);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertNotEmpty($attempt);

        $options = array(
            'days' => false,
            'timestamp' => 10000,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        local_deleteoldquizattempts_cli($options);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertEmpty($attempt);
    }

    /**
     * Tests cli/delete_attempts.php --date="2000-01-01 00:00:00"
     */
    public function test_date() {
        /** @var moodle_database $DB */
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_instance(array('course' => $course->id));

        $timestamp = 946684800; // 2000-01-01 00:00:00.
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

        $options = array(
            'days' => false,
            'timestamp' => false,
            'date' => '2000-01-01 00:00:00',
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        local_deleteoldquizattempts_cli($options);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertNotEmpty($attempt);

        $options = array(
            'days' => false,
            'timestamp' => false,
            'date' => '2000-01-01 00:00:01',
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        local_deleteoldquizattempts_cli($options);
        $attempt = $DB->get_record('quiz_attempts', array('id' => $attemptid));
        $this->assertEmpty($attempt);
    }

}
