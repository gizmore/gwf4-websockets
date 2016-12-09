# gwf4-websockets

GWF4 Module for Ratchet Websockets.

## https://github.com/gizmore/gwf4

## https://gwf4.gizmore.org


## This module is using Ratchet and Composer


### Install Ratchet

http://socketo.me/docs/install

as apache user do

    cd module/Websockets
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === 'aa96f26c2b67226a324c27919f1eb05f21c248b987e6195cad9690d5c1ff713d53020a02ac8c217dbf90a7eacc9d141d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    php composer.phar require cboden/ratchet
    

### Patch Ratchet

Sadly, ratchet needs a patch in 2 files, so we can get the remote address of clients.


Add to Ratchet/AbstractConnectionDecorator.php Line 26:
    
    public function getRemoteAddress()
    {
        return $this->wrappedConn->getConnection()->getRemoteAddress();
    }
    

Add to Ratchet/Server/IoConnection.php Line 42:

    public function getConnection()
    {
        return $this->conn;
    }



### nginx tls websocket server config

    upstream websocketserver {
        server localhost:34543;
    }

    server {
    
    server_name gwf4.gizmore.org;

    listen 61221;
    ssl on;
    ssl_certificate /root/.acme.sh/gwf4.gizmore.org/gwf4.gizmore.org.public.pem;
    ssl_certificate_key /root/.acme.sh/gwf4.gizmore.org/gwf4.gizmore.org.key;

    access_log /var/log/lup-wss-access-ssl.log;
    error_log /var/log/lup-wss-error-ssl.log;

    location / {
        proxy_pass http://websocketserver;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;

        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_read_timeout 86400; # neccessary to avoid websocket timeout disconnect
        proxy_redirect off;
    }
    }

### Tutorials

#### Create Own Command Handlers on Server


#### Create Own Command Handler on Client

