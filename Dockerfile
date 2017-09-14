FROM debian:9

MAINTAINER Robert <mrthinlt@gmail.com>

LABEL Description="Cutting-edge LAMP stack, based on Ubuntu 16.04 LTS. Includes .htaccess support and popular PHP7 features, including composer and mail() function." \
	License="Apache License 2.0" \
	Usage="docker run -d -p [HOST WWW PORT NUMBER]:80 -p [HOST WWW SECURE PORT NUMBER]:443 -p [HOST DB PORT NUMBER]:3306 -v [HOST WWW DOCUMENT ROOT]:/var/www/html -v [HOST DB DOCUMENT ROOT]:/var/lib/mysql fauria/lamp" \
	Version="1.0"

RUN apt-get update
RUN apt-get upgrade -y
# open for ssh client to connect this server
RUN apt-get install -y openssh-server
RUN mkdir /var/run/sshd
RUN echo 'root:magestoreomni123@' | chpasswd
RUN sed -i 's/PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
RUN sed -i 's@\#PermitRootLogin@PermitRootLogin@' /etc/ssh/sshd_config
# SSH login fix. Otherwise user is kicked off after login
RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd
ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile
# SSH Authorization for python_api container
RUN mkdir -p /root/.ssh
RUN echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDMMp+x3Rt5CP9Ongx2Nqej28qtv5z18SgtB823TsvViRYAhwJ6rLUZrYASyc33vAeOu+HdXrFG8o91uPqJJ+He7vI1kR73qEFIkkBnrMCQnkTxv61uYeG8Rz/jFSYLrPAgsUScELLap0wX65/mHcHzhuljU6hItWCqbpXHed1da4poU5mPSkwPSZ7i+RZ8TZbCe0ynJQsU4yOP9QzDhRYJUswNwlBa8t9m40LkOzcdLEAjTj2phQfJoe2NKIpL1nTc1rcCymc3xTX8gepovV4HRxHqE6UmOpUlkHAW01RwZiyMJypX5CWKl90WMy/OYLd7BO2AXYc4KIazMAPAzBf1 root@python_api" >> /root/.ssh/authorized_keys

RUN apt-get install -y \
	php7.0 \
	php7.0-bz2 \
	php7.0-cgi \
	php7.0-cli \
	php7.0-common \
	php7.0-curl \
	php7.0-dev \
	php7.0-enchant \
	php7.0-fpm \
	php7.0-gd \
	php7.0-gmp \
	php7.0-imap \
	php7.0-interbase \
	php7.0-intl \
	php7.0-json \
	php7.0-ldap \
	php7.0-mcrypt \
	php7.0-mysql \
	php7.0-odbc \
	php7.0-opcache \
	php7.0-pgsql \
	php7.0-phpdbg \
	php7.0-pspell \
	php7.0-readline \
	php7.0-recode \
	php7.0-snmp \
	php7.0-sqlite3 \
	php7.0-sybase \
	php7.0-tidy \
	php7.0-xmlrpc \
	php7.0-xsl

RUN apt-get install -y apache2 libapache2-mod-php7.0
RUN apt-get install -y mariadb-common mariadb-server mariadb-client
RUN apt-get install -y git vim composer curl

# set no bind ip mysql
RUN sed 's@bind-address@#bind-address@' -i /etc/mysql/mariadb.conf.d/50-server.cnf

ENV LOG_STDOUT **Boolean**
ENV LOG_STDERR **Boolean**
ENV LOG_LEVEL warn
ENV ALLOW_OVERRIDE All
ENV DATE_TIMEZONE UTC
ENV TERM dumb

COPY index.php /var/www/html/
COPY run-lamp.sh /usr/sbin/

RUN a2enmod rewrite
RUN ln -s /usr/bin/nodejs /usr/bin/node
RUN chmod +x /usr/sbin/run-lamp.sh
RUN chown -R www-data:www-data /var/www/html

VOLUME /etc/apache2
VOLUME /var/www/html
VOLUME /var/log/httpd
VOLUME /var/lib/mysql
VOLUME /var/log/mysql

EXPOSE 22
EXPOSE 80
EXPOSE 443
EXPOSE 3306

CMD ["/usr/sbin/run-lamp.sh"]
