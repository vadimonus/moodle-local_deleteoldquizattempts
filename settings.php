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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

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
            'local_deleteoldquizattempts/attempt_lifetime',
            new lang_string('attempt_lifetime', 'local_deleteoldquizattempts'),
            new lang_string('attempt_lifetime_help', 'local_deleteoldquizattempts'),
            0,
            array(
                0 => new lang_string('donotdeleteonschedule', 'local_deleteoldquizattempts'),
                1000 => new lang_string('numdays', '', 1000),
                365 => new lang_string('numdays', '', 365),
                365 => new lang_string('numdays', '', 365),
                180 => new lang_string('numdays', '', 180),
                150 => new lang_string('numdays', '', 150),
                120 => new lang_string('numdays', '', 120),
                90 => new lang_string('numdays', '', 90),
                60 => new lang_string('numdays', '', 60),
                30 => new lang_string('numdays', '', 30)
            )
        )
    );
}
