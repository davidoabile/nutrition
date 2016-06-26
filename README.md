## Is your project Healthy ?

Analysis is performed on Master Branch (with some exceptions)

Code Analysis | Performance Tests | Deployments |
------------- |-----------| ------------|
Insight Medal~* - [![SensioLabsInsight](https://insight.sensiolabs.com/projects/4695fcfe-96a0-4b43-bc22-9915597ff4a3/big.png)](https://insight.sensiolabs.com/projects/4695fcfe-96a0-4b43-bc22-9915597ff4a3) | Blackfire.io - Coming soon! | UAT - Coming soon! |  
Jenkins~* - [![Build Status](http://jenkins.balancenet.com.au/buildStatus/icon?job=insight%20-%20nutritionwarehouse)](http://jenkins.balancenet.com.au/job/insight%20-%20nutritionwarehouse/) || Production - Coming soon! |

~Minimum requirement for Insight: Gold

~Minimum requirement for Jenkins: Stable-Green

*[Please refer to this page to get an idea on how these medals are awarded!](https://confluence.balancenet.com.au/display/BALDEV/Code+Analysis+Medals)

___

##NutritionWarehouse
====================
..ssh-add ~/.ssh/bitbucket_rsa

INSTALLING REDIS - Amazon
sudo yum -y update
sudo ln -sf /usr/share/zoneinfo/Autralia/Queensland /etc/localtime
sudo yum -y install gcc make
wget http://download.redis.io/releases/redis-3.0.6.tar.gz
tar -zxf redis-3.0.6.tar.gz
make
sudo mkdir /etc/redis /var/lib/redis
sudo cp src/redis-server src/redis-cli /usr/local/bin
sudo cp redis.conf /etc/redis
wget https://raw.github.com/saxenap/install-redis-amazon-linux-centos/master/redis-server
sudo mv redis-server /etc/init.d
sudo chmod 755 /etc/init.d/redis-server
sudo nano /etc/init.d/redis-server
redis="/usr/local/bin/redis-server"
sudo chkconfig --add redis-server
sudo chkconfig --level 345 redis-server on



cd /tmp
wget https://github.com/nicolasff/phpredis/zipball/master -O phpredis.zip
unzip phpredis.zip
cd phpredis-*
phpize
./configure
make && make install
#touch /etc/php.d/redis.ini
#echo "extension=redis.so" > /etc/php.d/redis.ini
touch /etc/ /etc/php-5.6.d/redis.ini
echo "extension=redis.so" > /etc/php-5.6.d/redis.ini


.. upgrade php
sudo service httpd stop
sudo yum remove php php-gd php-mysql php-common
sudo yum erase httpd httpd-tools apr apr-util
yum install php56 php56-gd php56-mysql php56-fpm.x86_64 php56-pdo.x86_64 php56-mysqlnd.x86_64 php56-opcache.x86_64
sudo yum install php56-mcrypt

sudo yum update
sudo service httpd start


/////
edit nano /etc/php-fpm-5.6.d/www.conf
Change user and group to www


sudo yum install -y php-devel php56-mysql php56-pdo php56-pear php56-mbstring php56-cli php56-odbc php56-imap php56-gd php56-xml php56-soap
yum install  php56-cli

GO Live

Disable redirect to cart


//design/footer/absolute_footer


COMPILING mod_security
wget https://www.modsecurity.org/tarball/2.9.1/modsecurity-2.9.1.tar.gz or latest
./configure --enable-standalone-module --disable-mlogc
make


compiling NGNIX 

./configure --with-http_realip_module --add-module=../modsecurity/nginx/modsecurity
make
sudo make install

//installing php with apache
sudo apt-get install php5-fpm php5


sudo apt-get install vsftp
change root dir local_root=/home/www/data/nutritionwarehouse.com.au/compdata

change /etc/vsftpd.conf  write_enable=NO to write_enable=YES

see doc;;;; http://www.krizna.com/ubuntu/setup-ftp-server-on-ubuntu-14-04-vsftpd/



//UPDATE REPLACE(value , 'http://www.nutritionwarehouse.com.au/upload/image', 'https://nwhserver.s3-ap-southeast-2.amazonaws.com/upload/image') FROM `catalog_product_entity_text` where value like '%src="/upload/image/%'
//UPDATE REPLACE(value , 'http://www.nutritionwarehouse.com.au/upload/image', 'https://nwhserver.s3-ap-southeast-2.amazonaws.com/upload/image') FROM `catalog_product_entity_text` where value like '%http://www.nutritionwarehouse.com.au/upload/image'

UPDATE  catalog_product_entity_text SET value = REPLACE(value , 'http://www.nutritionwarehouse.com.au/upload/image', 'https://cdn.nutritionwarehouse.com.au/upload/image') where value like '%http://www.nutritionwarehouse.com.au/upload/image%'


UPDATE catalog_product_entity_text SET value = REPLACE(value , 'src="/upload/image', 'src="https://cdn.nutritionwarehouse.com.au/upload/image') WHERE value like '%src="/upload/image/%'
UPDATE  catalog_product_entity_text SET value = REPLACE(value , 'value like 'src="/upload/image', 'src="https://cdn.nutritionwarehouse.com.au/upload/image') value like '%src="/upload/image/%'





/usr/bin/rsync -avvzru -e "/usr/bin/ssh -i /home/david/.ssh/id_rsa" /home/www/nutritionwarehouse/compdata/  david@52.64.192.150:/home/www/nutritionwarehouse/compdata/


/usr/bin/rsync -avvzru -e "/usr/bin/ssh -i /home/david/.ssh/id_rsa"  david@52.64.192.150:/home/www/nutritionwarehouse/compdata/  /home/www/nutritionwarehouse/compdata/ 
