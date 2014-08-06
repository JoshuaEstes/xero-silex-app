####
#
# General purpose puppet file
#

# Required packages
$requiredPackages = [
  'apache2',
  'mysql-client',
  'mysql-server',
  'mysql-common',
]
package { $requiredPackages:
  ensure => latest,
}

# Extra packages that make me happy
$extraPackages = [
  'vim',
]
package { $extraPackages:
  ensure => latest,
}

# Packages that need to notify apache2
$notifyApache2Packages = [
  'libapache2-mod-php5',
  'php5-mcrypt',
  'php5-curl',
  'php5-sqlite',
  'php5-mysql',
]
package { $notifyApache2Packages:
  ensure => latest,
  notify => Service['apache2'],
}

# Apache Stuff
service { 'apache2':
  ensure  => running,
  require => Package['apache2'],
}
$vhost = "
<VirtualHost *:80>
  ServerName www.localhost.com
  DocumentRoot /var/www/web
  <Directory /var/www/web>
    AllowOverride All
  </Directory>
</VirtualHost>
"
file { '/etc/apache2/sites-available/000-default.conf':
  ensure  => present,
  content => "$vhost",
  notify  => Service['apache2'],
}
