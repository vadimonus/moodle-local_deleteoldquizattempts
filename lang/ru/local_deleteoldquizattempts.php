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

$string['attemptlifetime'] = 'Удалять попытки старше, чем';
$string['attemptlifetime_help'] = 'Попытки тестирования, которые старше указанного здесь времени будут автоматически удалаться задачей планировщика. Если выбрано значение "Не удалять старые попытки", по прежнему будет возможно удаление попыток командой через интерфейс командной строки.';
$string['attemptsdeleted'] = 'Удалено попыток тестирования: {$a}.';
$string['attemptsprogress'] = 'Удалено {$a->deleted} из {$a->total}';
$string['deleteunusedhiddenquestions'] = 'Удалять неиспользуемые скрытые вопросы';
$string['deleteunusedhiddenquestions_help'] = 'Скрытые вопросы - это вопросы, которые были удалены логически, но не были удалены физически, потому что на них ссылались некоторые попытки тестирования. После удаления попыток тестирования, такие вопросы, вероятно, больше не потребуются.';
$string['donotdeleteonschedule'] = 'Не удалять старые попытки';
$string['maxexecutiontime'] = 'Максимальное время выполнения';
$string['maxexecutiontime_help'] = 'Удаление старых попыток может сильно нагружать сервер. Этот параметр ограничивает максимальное время работы задачи планировщика.';
$string['maxexecutiontime_reached'] = 'Операция остановлена из-за ограничения времени выполнения';
$string['notlimited'] = 'Не ограничено';
$string['pluginname'] = 'Удаление старых попыток тестирования и попыток ответов на вопросы';
$string['privacy:metadata'] = 'Плагин не хранит персональные данные.';
$string['questionsdeleted'] = 'Неиспользуемых скрытых вопросов удалено: {$a->deleted}, пропущено: {$a->skipped}';
$string['questionsprogress'] = 'Удалено {$a->deleted}, пропущено {$a->skipped} из {$a->total}';
$string['taskname'] = 'Удаление старых попыток тестирования и попыток ответов на вопросы';
