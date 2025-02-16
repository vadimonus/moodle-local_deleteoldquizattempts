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

namespace local_deleteoldquizattempts;

use advanced_testcase;

/**
 * Unittests for CLI
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_deleteoldquizattempts\helper::delete_questions_cli_handler
 */
final class delete_questions_cli_test extends advanced_testcase {

    /**
     * Tests cli/delete_unused_questions.php --help
     */
    public function test_help(): void {
        $options = [
            'timelimit' => false,
            'verbose' => false,
            'help' => true,
        ];
        ob_start();
        $helper = new helper();
        $helper->delete_questions_cli_handler($options);
        $output = ob_get_clean();
        $this->assertStringContainsString('Delete unused hidden questions', $output);
        $this->assertStringContainsString('Print out this help', $output);
    }

    /**
     * Tests cli/delete_unused_questions.php --timelimit=300
     */
    public function test_timelimit(): void {
        $this->resetAfterTest(true);

        $mockbuilder = $this->getMockBuilder(helper::class);
        $mockbuilder->onlyMethods(['delete_unused_questions']);
        $helper = $mockbuilder->getMock();

        $expectedstoptime = time() + 300;
        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_unused_questions');
        $expectation1->with(
            $this->logicalAnd(
                $this->greaterThanOrEqual($expectedstoptime),
                $this->lessThan($expectedstoptime + 5)
            ),
            null
        );

        $options = [
            'timelimit' => 300,
            'verbose' => false,
            'help' => false,
        ];
        $helper->delete_questions_cli_handler($options);
    }

    /**
     * Tests cli/delete_unused_questions.php --verbose
     */
    public function test_verbose(): void {
        $this->resetAfterTest(true);

        $mockbuilder = $this->getMockBuilder(helper::class);
        $mockbuilder->onlyMethods(['delete_unused_questions']);
        $helper = $mockbuilder->getMock();

        $expectation1 = $helper->expects($this->once());
        $expectation1->method('delete_unused_questions');
        $expectation1->with(
            0,
            $this->isInstanceOf('text_progress_trace')
        );

        $options = [
            'timelimit' => false,
            'verbose' => true,
            'help' => false,
        ];
        $helper->delete_questions_cli_handler($options);
    }

}
