CronKeep
========

CronKeep is a web-based crontab management tool which enables teams to have visibility over what cron jobs are scheduled, run jobs on demand, add new cron jobs in a human-friendly way, or pause a cron schedule from going off, without the need for sysadmin-level access.

![CronKeep — Add Job screen](/docs/screenshots/add-job-screen.png "CronKeep — Add Job screen")

## Features

* Run cron jobs on demand
* Add new jobs in a simple way
* Pause a cron job schedule
* Change or delete existing jobs
* Minimal setup required (no database dependency)

## Live Demo

See the app in action at [demo.cronkeep.com](http://demo.cronkeep.com).
Running cron jobs is disabled in the demo app. 

## Requirements

CronKeep fits nicely into your LAMP stack. Apache and PHP 5.3.23 or newer are required.

The current CronKeep version interacts only with the crontab of the user Apache is running as. This means it will only have access to the jobs added for user `www-data`, `apache` or `nobody`, depending on your system.

## Installation

* Proceed with installing Composer if you don't already have it.

```Shell
curl -sS https://getcomposer.org/installer | php
```

* Now using this one-liner command, Composer will install CronKeep and all of its dependencies onto your directory of choice (preferably, your *www* directory):
```Shell
php composer.phar create-project cronkeep/cronkeep --keep-vcs -s dev /var/www/cronkeep
```

* Set up authentication

At this time, the crontab manager does not feature in-app authentication. It is up to the user to set up means of authentication. Please refer to [Installation](INSTALL.md#set-up-a-virtual-host) for more details.

## License

CronKeep is a free to use application, both for non-profit and commercial organizations, licensed under the terms of Apache License 2.0. Contributions are encouraged.
- - -
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/aa1eeb97-0cf2-410c-851c-6deb6e88b032/big.png)](https://insight.sensiolabs.com/projects/aa1eeb97-0cf2-410c-851c-6deb6e88b032)
