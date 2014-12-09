#!/bin/bash
#
# Copyright 2014 Bogdan Ghervan
# 
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

echo "Provisioning virtual machine..."

echo "Installing application stack..."
apt-get update
apt-get install -y apache2 libapache2-mod-php5 screen git

echo "Configuring virtual host..."
cp /var/www/cronkeep/provision/ubuntu/virtual-host.conf /etc/apache2/sites-available/cronkeep.conf
a2ensite cronkeep
a2enmod rewrite
service apache2 reload

echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer install

echo "Installing Xdebug..."
apt-get install -y php5-dev
pecl install xdebug
cat <<EOF > /etc/php5/mods-available/xdebug.ini
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_host=192.168.50.1
xdebug.var_display_max_data=4096
xdebug.var_display_max_depth=4
EOF
php5enmod xdebug
service apache2 reload

echo "Installing test crontab..."
crontab -u www-data /var/www/cronkeep/provision/crontabfile

echo "Finished provisioning."