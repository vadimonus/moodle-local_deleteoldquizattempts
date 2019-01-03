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

$string['attempt_lifetime'] = 'Удалять попытки старше, чем';
$string['attempt_lifetime_help'] = 'Попытки тестирования, которые старше указанного здесь времени будут автоматически удалаться задачей планировщика. Если выбрано значение "Не удалять старые попытки", по прежнему будет возможно удаление попыток командой через интерфейс командной строки.';
$string['donotdeleteonschedule'] = 'Не удалять старые попытки';
$string['pluginname'] = 'Удаление старых попыток тестирования и попыток ответов на вопросы';
