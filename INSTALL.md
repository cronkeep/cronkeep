Installation
============

Application is still in the development phase. An official alpha release will follow soon. In the meantime, you may still install it, provided you have git and Composer already on your server.

## Download Files

* Clone the repository into your web root folder:

```Shell
git clone https://github.com/cronkeep/cronkeep.git /var/www/cronkeep
```

* Install dependencies via Composer:

```Shell
cd /var/www/cronkeep/src/ && composer install --no-dev
```

Should you not have git or Composer installed, please refer to their docs for installation instructions ([git](http://git-scm.com/download/linux), [Composer](https://getcomposer.org/doc/00-intro.md#installation-nix)).

## Set up a Virtual Host

Note that the following instructions are for Apache only.

Create a ``/etc/apache2/sites-available/cronkeep.conf`` file with your favorite text editor, and paste this content inside the file:

```ApacheConf
<VirtualHost *:80>
    ServerName cronkeep.example.com # adjust accordingly
    DocumentRoot /var/www/cronkeep/src
</VirtualHost>
```

Enable the virtual host by running:

```Shell
sudo a2ensite cronkeep
```

## Set up Authentication

At this time, the crontab manager does not feature in-app authentication. It is up to the user to allow and restrict access to CronKeep in their network.

Here's a guide on how to set up Digest Authentication with Apache under Debian / Ubuntu.

### Digest Access Authentication

This guide is based on an Apache 2.4 installation under Ubuntu 14.04.

* First we'll enable `mod_auth_digest`:

```Shell
sudo a2enmod auth_digest
```

* Next we're setting up the login credentials for a user called **john**.

```Shell
mkdir /etc/htdigest
htdigest -c /etc/htdigest/htdigest "cronkeep" john # enter password at the prompt
chmod 644 /etc/htdigest/htdigest
```

Note that we've put the password file in a place where Apache can't accidentally serve it up from. Also, please be mindful that the `-c` argument should be used only once, to create the password file, and should be left out if you want to onboard additional users, as it will overwrite an existing file of the same name.

* It's in the virtual host we're pointing Apache to the htdigest password file. So let's add these lines to the virtual host that we've previously set up for CronKeep.

Copy these lines to the ```VirtualHost``` section:

```ApacheConf
    <Directory "/var/www/cronkeep/src">
        AllowOverride all
        AuthType Digest
        AuthName "cronkeep"
        AuthUserFile /etc/htdigest/htdigest
        Require valid-user
    </Directory>
```

* Finally, we restart Apache for the changes to take effect.

```Shell
service apache2 restart
```

## Troubleshooting

### /var/spool/cron is not a directory, bailing out

![CronKeep — "/var/spool/cron is not a directory, bailing out" error screen](/docs/screenshots/alert-spool-unreachable.png "CronKeep — "/var/spool/cron is not a directory, bailing out" error screen")

What this error is basically saying is that the `crontab` utility, which CronKeep uses behind the scenes, is denied access to the `/var/spool/cron` directory. This is usually the case in a SELinux-enabled environment running in enforcing mode.

You can quickly validate this assumption by temporarily switching SELinux to permissive mode, like this:
```Shell
$ setenforce permissive
```
Refresh CronKeep to see that the error is gone. Make sure you switch SELinux back to enforcing mode at the end:
```Shell
$ setenforce enforcing
```

The recommended way to allow the `crontab` utility access to its files, when it's invoked by Apache, is by creating and installing a custom SELinux policy module. You may install the one we've already put together (and tested on a CentOS 6.5 installation) using this one-liner:
```Shell
$ curl -OfL https://github.com/cronkeep/cronkeep/raw/master/provision/centos/httpd_crontab.pp && semodule -i httpd_crontab.pp
```
The policy package only contains the minimum security rules needed and nothing more.

### Apache is denied access to read PAM configuration

![CronKeep — "Apache denied access to PAM configuration" error screen](/docs/screenshots/alert-pam-unreadable.png "CronKeep — "Apache denied access to PAM configuration" error screen")

It looks like on a SELinux-enabled environment running in enforcing mode, Apache is denied access to read
the PAM configuration file (usually `/etc/security/access.conf`) which regulates access to the `crontab`
system utility. The following message is then triggered:
```
System error You (apache) are not allowed to access to (crontab) because of pam configuration.
```

Also, further entries from the logs associated with this situation:
```
==> /var/log/secure <==
Dec 25 10:49:48 localhost crontab: pam_access(crond:account): login_access: user=apache, from=cron, file=/etc/security/access.conf

==> /var/log/audit/audit.log <==
type=AVC msg=audit(1419504588.145:797): avc:  denied  { read } for  pid=3191 comm="unix_chkpwd" name="shadow" dev=dm-0 ino=394249 scontext=unconfined_u:system_r:httpd_t:s0 tcontext=system_u:object_r:shadow_t:s0 tclass=file
type=SYSCALL msg=audit(1419504588.145:797): arch=c000003e syscall=2 success=no exit=-13 a0=7ff518d256bb a1=80000 a2=1b6 a3=0 items=0 ppid=3190 pid=3191 auid=500 uid=0 gid=48 euid=0 suid=0 fsuid=0 egid=48 sgid=48 fsgid=48 tty=(none) ses=3 comm="unix_chkpwd" exe="/sbin/unix_chkpwd" subj=unconfined_u:system_r:httpd_t:s0 key=(null)

==> /var/log/secure <==
Dec 25 10:49:48 localhost unix_chkpwd[3191]: could not obtain user info (apache)
Dec 25 10:49:48 localhost crontab: PAM audit_open() failed: Permission denied
Dec 25 10:49:48 localhost crontab: PAM audit_open() failed: Permission denied

==> /var/log/audit/audit.log <==
type=AVC msg=audit(1419504588.146:798): avc:  denied  { create } for  pid=3190 comm="crontab" scontext=unconfined_u:system_r:httpd_t:s0 tcontext=unconfined_u:system_r:httpd_t:s0 tclass=netlink_audit_socket
type=SYSCALL msg=audit(1419504588.146:798): arch=c000003e syscall=41 success=no exit=-13 a0=10 a1=3 a2=9 a3=7fffab7d1ff0 items=0 ppid=2764 pid=3190 auid=500 uid=48 gid=48 euid=0 suid=0 fsuid=0 egid=48 sgid=48 fsgid=48 tty=(none) ses=3 comm="crontab" exe="/usr/bin/crontab" subj=unconfined_u:system_r:httpd_t:s0 key=(null)
```

Luckily, this can be easily fixed as follows:
```Shell
setsebool -P allow_httpd_mod_auth_pam 1
```

*Found a typo in this file or want to propose changes? Just [fork and edit](https://github.com/cronkeep/cronkeep/edit/master/INSTALL.md) this file.*
