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
 * Unittests for CLI
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_deleteoldquizattempts_delete_attempts_cli_testcase extends advanced_testcase {

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
        $helper = new local_deleteoldquizattempts\helper();
        $helper->delete_attempts_cli_handler($options);
        $output = ob_get_clean();
        $this->assertContains('Delete old quiz and question attempts', $output);
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
        $helper = new local_deleteoldquizattempts\helper();
        $helper->delete_attempts_cli_handler($options);
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
        $helper = new local_deleteoldquizattempts\helper();
        $helper->delete_attempts_cli_handler($options);
        $output = ob_get_clean();
        $this->assertContains('Print out this help', $output);
    }

    /**
     * Tests cli/delete_attempts.php --days=3
     */
    public function test_days() {
        global $DB;

        $this->resetAfterTest(true);
        
        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts'));
        $helper = $mockbuilder->getMock();
        
        $expectedTimestamp = time() - 3 * 3600 * 24;
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->with(
            $this->logicalAnd(
                $this->greaterThanOrEqual($expectedTimestamp),
                $this->lessThan($expectedTimestamp + 5)
            ),
            0,
            null
        );

        $options = array(
            'days' => 3,
            'timestamp' => false,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        $helper->delete_attempts_cli_handler($options);
    }

    /**
     * Tests cli/delete_attempts.php --timestamp=10000
     */
    public function test_timestamp() {
        global $DB;

        $this->resetAfterTest(true);
        
        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts'));
        $helper = $mockbuilder->getMock();
        
        $expectedTimestamp = 10000;
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->with(10000, 0, null);
        
        $options = array(
            'days' => false,
            'timestamp' => 10000,
            'date' => false,
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        $helper->delete_attempts_cli_handler($options);
    }

    /**
     * Tests cli/delete_attempts.php --date="2000-01-01 00:00:00"
     */
    public function test_date() {
        global $DB;

        $this->resetAfterTest(true);

        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts'));
        $helper = $mockbuilder->getMock();
        
        $expectedTimestamp = 946684800; // Timestampt for "2000-01-01 00:00:00".
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->with($expectedTimestamp, 0, null);
        
        $options = array(
            'days' => false,
            'timestamp' => false,
            'date' => '2000-01-01 00:00:00',
            'timelimit' => false,
            'verbose' => false,
            'help' => false
        );
        $helper->delete_attempts_cli_handler($options);
    }

    /**
     * Tests cli/delete_attempts.php --timestamp=10000 --timelimit=300 --verbose
     */
    public function test_timelimit_and_verbose() {
        global $DB;
        
        $this->resetAfterTest(true);
        
        $mockbuilder = $this->getMockBuilder('local_deleteoldquizattempts\helper');
        $mockbuilder->setMethods(array('delete_attempts'));
        $helper = $mockbuilder->getMock();
        
        $expectedstoptime = time() + 300;
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_attempts');
        $expectation1->with(
            10000,
            $this->logicalAnd(
                $this->greaterThanOrEqual($expectedstoptime),
                $this->lessThan($expectedstoptime + 5)
                ),
            $this->isInstanceOf('text_progress_trace')
        );
        
        $options = array(
            'days' => 0,
            'timestamp' => 10000,
            'date' => false,
            'timelimit' => 300,
            'verbose' => true,
            'help' => false
        );
        $helper->delete_attempts_cli_handler($options);
    }
    
}
