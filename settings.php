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
 * @package local_deleteoldquizattempts
 * @copyright 2019 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_deleteoldquizattempts', get_string('pluginname', 'local_deleteoldquizattempts'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(
        new admin_setting_configselect(
            'local_deleteoldquizattempts/attemptlifetime',
            new lang_string('attemptlifetime', 'local_deleteoldquizattempts'),
            new lang_string('attemptlifetime_help', 'local_deleteoldquizattempts'),
            0,
            array(
                0 => new lang_string('donotdeleteonschedule', 'local_deleteoldquizattempts'),
                365 * 5 => new lang_string('numyears', '', 5),
                365 * 3 => new lang_string('numyears', '', 3),
                365 * 2 => new lang_string('numyears', '', 2),
                365 => new lang_string('numyears', '', 1),
                180 => new lang_string('numdays', '', 180),
                150 => new lang_string('numdays', '', 150),
                120 => new lang_string('numdays', '', 120),
                90 => new lang_string('numdays', '', 90),
                60 => new lang_string('numdays', '', 60),
                30 => new lang_string('numdays', '', 30)
            )
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_deleteoldquizattempts/deleteunusedquestions',
            new lang_string('deleteunusedhiddenquestions', 'local_deleteoldquizattempts'),
            new lang_string('deleteunusedhiddenquestions_help', 'local_deleteoldquizattempts'),
            0
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'local_deleteoldquizattempts/maxexecutiontime',
            new lang_string('maxexecutiontime', 'local_deleteoldquizattempts'),
            new lang_string('maxexecutiontime_help', 'local_deleteoldquizattempts'),
            0,
            array(
                0 => new lang_string('notlimited', 'local_deleteoldquizattempts'),
                30 => new lang_string('numseconds', '', 30),
                60 => new lang_string('numminutes', '', 1),
                60 * 5 => new lang_string('numminutes', '', 5),
                60 * 10 => new lang_string('numminutes', '', 10),
                60 * 15 => new lang_string('numminutes', '', 15),
                60 * 30 => new lang_string('numminutes', '', 30),
                60 * 60 => new lang_string('numhours', '', 1),
                60 * 60 * 2 => new lang_string('numhours', '', 2),
                60 * 60 * 3 => new lang_string('numhours', '', 3),
                60 * 60 * 4 => new lang_string('numhours', '', 4),
                60 * 60 * 8 => new lang_string('numhours', '', 8),
                60 * 60 * 12 => new lang_string('numhours', '', 12),
            )
        )
    );
}
