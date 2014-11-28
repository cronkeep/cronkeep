CronKeep
========

CronKeep is a web-based crontab management tool which enables teams to have visibility over what cron jobs are scheduled, run jobs on demand, add new cron jobs in a human-friendly way, or pause a cron schedule from going off, without the need for sysadmin-level access.

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

CronKeep fits nicely into your LAMP stack. Apache and PHP 5.3 or newer are required.

The current CronKeep version interacts only with the crontab of the user Apache is running as. This means it will only have access to the jobs added for user `www-data`, `apache` or `nobody`, depending on your system.

## Installation

Application is still in the development phase. An official alpha release will follow soon.

In the meantime, you may install it, provided you have git and Composer already installed on your server.

* Clone the repository into your web root folder:

```Shell
git clone https://github.com/cronkeep/cronkeep.git /var/www/cronkeep
```

* Install dependencies via Composer:

```Shell
cd /var/www/cronkeep/src/ && composer install --no-dev
```

Should you not have git or Composer installed, please refer to their docs for installation instructions ([git](http://git-scm.com/download/linux), [Composer](https://getcomposer.org/doc/00-intro.md#installation-nix)).

* Set up authentication

At this time, the crontab manager does not feature in-app authentication. It is up to the user to set up means of authentication. Please refer to [Installation](INSTALL.MD#set-up-a-virtual-host) for more details.

## License

CronKeep is a free to use application, both for non-profit and commercial organizations, licensed under the terms of Apache License 2.0. Contributions are encouraged.

