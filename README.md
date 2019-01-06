Delete old quiz and question attempts Moodle plugin
===================================================

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

CLI usage
---------

Dispay help.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --help`

Delete attempts that are older than 90 days and shows progress.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --days=90 --verbose`

Delete attempts that are created before specified timestamp.

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --timestamp=1514764800 --timelimit=300`

Delete attempts that are created before 2018-01-01 00:00:00 (UTC).

`sudo -u www-data /usr/bin/php local/deleteoldquizattempts/cli/delete_attempts.php --date="2018-01-01 00:00:00"`

Author
------
- Vadim Dvorovenko (Vadimon@mail.ru)

Links
-----
- Updates: https://moodle.org/plugins/view.php?plugin=local_deleteoldquizattempts
- Latest code: https://github.com/vadimonus/moodle-local_deleteoldquizattempts

Changes
-------
Release 1.0 (build 2019010600):
- Initial release.
