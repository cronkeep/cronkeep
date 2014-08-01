#!/bin/bash

echo "Provisioning virtual machine..."

echo "Installing application stack..."
apt-get install -y apache2 libapache2-mod-php5 screen git

echo "Configuring virtual host..."
cp /var/www/cronman/provision/config/cronman.conf /etc/apache2/sites-available
a2ensite cronman
a2enmod rewrite
service apache2 reload

echo "Installing Xdebug..."
apt-get install -y php5-dev
pecl install xdebug
cat <<EOF > /etc/php5/mods-available/xdebug.ini
zend_extension=xdebug.so
xdebug.var_display_max_data=4096
EOF
php5enmod xdebug
service apache2 reload

echo "Installing test crontab..."
crontab -u www-data /var/www/cronman/provision/config/crontabfile

echo "Finished provisioning."