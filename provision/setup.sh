#!/bin/bash

echo "Provisioning virtual machine..."

echo "Installing application stack..."
apt-get install -y apache2 libapache2-mod-php5 screen git > /dev/null

echo "Configuring virtual host..."
cp /var/www/cronman/provision/config/cronman.conf /etc/apache2/sites-available > /dev/null
a2ensite cronman > /dev/null
service apache2 reload > /dev/null

echo "Finished provisioning."