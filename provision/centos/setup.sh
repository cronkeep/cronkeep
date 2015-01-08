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
yum install -y httpd screen git vim
chkconfig httpd on

# Install latest PHP 5.3
rpm -Uvh http://dl.iuscommunity.org/pub/ius/stable/CentOS/6/x86_64/epel-release-6-5.noarch.rpm
rpm -Uvh http://dl.iuscommunity.org/pub/ius/archive/CentOS/6/x86_64/ius-release-1.0-11.ius.centos6.noarch.rpm
yum --enablerepo=ius-archive install -y php53

service httpd start

# Allow Apache access to vmblock_t security context
# More info in the Developer guide:
# https://github.com/cronkeep/cronkeep/wiki/Developer-Guide
semodule -i /vagrant/provision/centos/httpd_vboxsf.pp

echo "Configuring virtual host..."
cp /var/www/cronkeep/provision/centos/virtual-host.conf /etc/httpd/conf.d/cronkeep.conf
service httpd reload

echo "Installing Composer..."
sed -i "s/allow_url_fopen = Off/allow_url_fopen = On/" /etc/php.ini
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
PATH=$PATH:/usr/local/bin
composer install

echo "Installing test crontab..."
crontab -u apache /var/www/cronkeep/provision/crontabfile

echo "Finished provisioning."