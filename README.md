Delete old quiz and question attempts Moodle plugin
===================================================

[![Build Status](https://travis-ci.org/vadimonus/moodle-local_deleteoldquizattempts.svg?branch=master)](https://travis-ci.org/vadimonus/moodle-local_deleteoldquizattempts)

Requirements
------------
- Moodle 2.7 (build 2014051200) or later.

Installation
------------
Copy the deleteoldquizattempts folder into your Moodle /local directory and visit your Admin Notification page to
complete the installation.

Usage
-----
Navigate to plugin settings and specify maximum lifetime of quiz attempts. Older attempts will be automatically deleted
with scheduler task.

You can also specify to delete unused hidden questions. Hidden questions are questions, that were logically deleted,
but were not deleted physically, because they were referenced in some quiz attempts. After quiz attempts deletion,
such questions are probably no longer required.

You can also delete quiz attempts and unused hidden questions with command line job.

Deleting quiz attempts with CLI
-------------------------------

Dispay help.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --help`

Delete attempts that are older than 90 days and shows progress.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --verbose`

Delete attempts that are created before specified timestamp.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --timestamp=1514764800 --timelimit=300`

Delete attempts that are created before 2018-01-01 00:00:00 (UTC).

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --date="2018-01-01 00:00:00"`

Delete attempts for specified quiz (check id in *_quiz table).

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --quizid=99`

Delete attempts for all quizzes in specified course.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --courseid=99`

Deleting unused hidden questions with CLI
-----------------------------------------

Dispay help.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_unused_questions.php --help`

Delete unused hidden questions.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_unused_questions.php --timelimit=300 --verbose`

Author
------
- Vadim Dvorovenko (Vadimon@mail.ru)

Links
-----
- Updates: https://moodle.org/plugins/view.php?plugin=local_deleteoldquizattempts
- Latest code: https://github.com/vadimonus/moodle-local_deleteoldquizattempts

Changes
-------
Release 2.2 (build 2020060600):
- CLI options to delete attempts for specified quizzes and courses

Release 2.1 (build 2019032801):
- Do not try o delete questions that are used by slots.
- Privacy API support.

Release 2.0 (build 2019010800):
- Deletion of unused hidden questions.

Release 1.0 (build 2019010600):
- Initial release.
