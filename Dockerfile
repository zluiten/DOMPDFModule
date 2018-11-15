ARG PHP_VERSION=7.1
FROM php:${PHP_VERSION}

ENV COMPOSER_HOME=/var/lib/composer
WORKDIR /opt/app

RUN apt-get update -y \
   && apt-get install -y git zlib1g-dev libfreetype6-dev libjpeg62-turbo-dev \
   && pecl install xdebug-2.5.0 \
   && docker-php-ext-install -j$(nproc) zip gd \
   && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
   && docker-php-ext-enable xdebug

RUN { \
        echo '#!/bin/sh'; \
        echo 'EXPECTED_SIGNATURE="$(curl -s https://composer.github.io/installer.sig)"'; \
        echo 'php -r "copy('\''https://getcomposer.org/installer'\'', '\''composer-setup.php'\'');"'; \
        echo 'ACTUAL_SIGNATURE="$(php -r "echo hash_file('\''sha384'\'', '\''composer-setup.php'\'');")"'; \
        echo 'if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then'; \
        echo '  >&2 echo '\''ERROR: Invalid installer signature'\'''; \
        echo '  rm composer-setup.php'; \
        echo '  exit 1'; \
        echo 'fi'; \
        echo 'php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer'; \
        echo 'RESULT=$?'; \
        echo 'rm composer-setup.php'; \
        echo 'exit $RESULT'; \
   } | tee /composer-install.sh \
   && chmod +x /composer-install.sh \
   && /composer-install.sh \
   && rm /composer-install.sh

ENTRYPOINT ["./build.sh" ]
