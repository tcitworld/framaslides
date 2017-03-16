[![](https://framagit.org/assets/favicon-075eba76312e8421991a0c1f89a89ee81678bcde72319dd3e8047e2a47cd3a42.ico)](https://framagit.org)

![English:](https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_the_United_Kingdom.svg/20px-Flag_of_the_United_Kingdom.svg.png) **Framasoft uses GitLab** for the development of its free softwares. Our Github repositories are only mirrors.
If you want to work with us, **fork us on [framagit.org](https://framagit.org)**. (no registration needed, you can sign in with your Github account)

![Français :](https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/20px-Flag_of_France.svg.png) **Framasoft utilise GitLab** pour le développement de ses logiciels libres. Nos dépôts Github ne sont que des miroirs.
Si vous souhaitez travailler avec nous, **forkez-nous sur [framagit.org](https://framagit.org)**. (l'inscription n'est pas nécessaire, vous pouvez vous connecter avec votre compte Github)
* * *

# Framaslides

An online presentation editor service [https://framaslides.org](https://framaslides.org)

[![build status](https://framagit.org/framasoft/framaslides/badges/master/build.svg)](https://framagit.org/framasoft/framaslides/commits/master)
[![coverage report](https://framagit.org/framasoft/framaslides/badges/master/coverage.svg)](https://framagit.org/framasoft/framaslides/commits/master)



## Installation
* Server nginx or Apache (lighttpd should work)
* Requirements: PHP 7, NodeJS >= 6
* Dependencies though composer and npm so you'll need them
* Database PostGreSQL

### Dependencies
* `npm i`
* `composer up` You'll need to do a `grunt build` first then fill in your database informations

### Compilation and bundling
* `grunt build` (for Strut)
* `webpack` (for the manage interface)

### Database installation
* `bin/console doctrine:schema:create`
* `bin/console doctrine:database:create` (If you don't already have created a database. The user will need rights to create a new database)

### Virtual Host
#### Nginx
```
server {
        listen *:80;
        listen [ipv6]:80;

        listen *:443 ssl;
		ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        listen [ipv6]:443 ssl;
        ssl_certificate /path/to/certificate;
        ssl_certificate_key /path/to/associated/key;

        server_name messlides.tld ;

        root   /path/to/framaslides/;

        if ($scheme != "https") {
            rewrite ^ https://$http_host$request_uri? permanent;
        }

        index index.html index.htm index.php index.cgi index.pl index.xhtml;

        location = /favicon.ico {
            log_not_found off;
            access_log off;
        }

        location = /robots.txt {
            allow all;
            log_not_found off;
            access_log off;
        }

        location ~ \.php$ {
            location ~ ^/(app_dev|config)\.php(/|$) {
                root   /path/to/framaslides/web/;
                fastcgi_pass unix:/path/to/sock;
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                fastcgi_param DOCUMENT_ROOT $realpath_root;
            }
            location ~ ^/app\.php(/|$) {
                root   /path/to/framaslides/web/;
                fastcgi_pass unix:/path/to/sock;
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                fastcgi_param DOCUMENT_ROOT $realpath_root;
                internal;
            }
            return 404;
        }

        location @php {
            try_files $uri =404;
            include /etc/nginx/fastcgi_params;
            fastcgi_pass unix:/path/to/sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_intercept_errors on;
        }


        location / {
            root   /path/to/framaslides/web/;
            try_files $uri /app.php$is_args$args;
        }

```
