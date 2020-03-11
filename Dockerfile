FROM php:7.3-apache

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
  && apt-get install -y nodejs \
  && apt-get --purge autoremove -y

RUN apt-get update \
    && apt-get install -y wget --no-install-recommends \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list' \
    && apt-get update \
    && apt-get install -y google-chrome-unstable --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

RUN npm install --global --unsafe-perm puppeteer \
    && chmod -R o+rx /usr/lib/node_modules/puppeteer/.local-chromium

COPY config/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY config/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

EXPOSE 80
