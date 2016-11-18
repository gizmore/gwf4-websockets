# gwf4-websockets
GWF4 Module for Ratchet Websockets.

## This module is using Ratchet and Composer

### For TLS You will need NGINX


#### Install Ratchet

http://socketo.me/docs/install

  as apache user

  cd module/Websockets
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('SHA384', 'composer-setup.php') === 'aa96f26c2b67226a324c27919f1eb05f21c248b987e6195cad9690d5c1ff713d53020a02ac8c217dbf90a7eacc9d141d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
  php composer.phar require cboden/ratchet
  
  
#### Create Own Command Handlers on Server


#### Create Own Command Handler on Client

