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
use core_question\local\bank\question_version_status;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

/**
 * Unittests for delete_unused_question
 *
 * @package    local_deleteoldquizattempts
 * @copyright  2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_deleteoldquizattempts\helper::delete_unused_questions
 */
final class delete_questions_test extends advanced_testcase {

    /**
     * Tests delete_attempts
     */
    public function test_delete_questions(): void {
        global $DB;

        $this->resetAfterTest(true);

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question(
            'truefalse',
            null,
            ['category' => $category->id]
        );
        $DB->set_field(
            'question_versions',
            'status',
            question_version_status::QUESTION_STATUS_HIDDEN,
            [
                'questionid' => $question->id,
            ]);

        $helper = new helper();
        [$deleted, $skipped] = $helper->delete_unused_questions();
        $question = $DB->get_record('question', ['id' => $question->id]);
        $this->assertEmpty($question);
        $this->assertEquals(1, $deleted);
        $this->assertEquals(0, $skipped);
    }

    /**
     * Tests delete_attempts call with trace and timelimit
     */
    public function test_delete_questions_with_timelimit(): void {
        global $DB;

        $this->resetAfterTest(true);

        $trace = $this->getMockBuilder('null_progress_trace')->onlyMethods(['output'])->getMock();

        $trace
            ->expects($this->exactly(2))
            ->method('output')
            ->with($this->logicalOr(
                $this->stringContains('Deleted 1, skipped 0 of 1'),
                $this->stringContains('Operation stopped due to time limit')
            ));

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question(
            'truefalse',
            null,
            ['category' => $category->id]
            );
        $DB->set_field(
            'question_versions',
            'status',
            question_version_status::QUESTION_STATUS_HIDDEN,
            [
                'questionid' => $question->id,
            ]);

        $helper = new helper();
        [$deleted, $skipped] = $helper->delete_unused_questions(time(), $trace);
        $question = $DB->get_record('question', ['id' => $question->id]);
        $this->assertEmpty($question);
        $this->assertEquals(1, $deleted);
        $this->assertEquals(0, $skipped);
    }

}
