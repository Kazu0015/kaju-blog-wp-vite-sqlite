FROM wordpress:latest

RUN apt-get update && apt-get install -y unzip curl

RUN curl -L https://downloads.wordpress.org/plugin/sqlite-database-integration.zip -o /tmp/sqlite.zip \
    && unzip /tmp/sqlite.zip -d /tmp/sqlite-plugin \
    && mkdir -p /var/www/html/wp-content/plugins/sqlite-database-integration \
    && mv /tmp/sqlite-plugin/sqlite-database-integration/* /var/www/html/wp-content/plugins/sqlite-database-integration/ \
    && rm -rf /tmp/sqlite.zip /tmp/sqlite-plugin

# db.php ドロップインを wp-content に設置し、バインドマウント用にイメージ内へバックアップ
RUN cp /var/www/html/wp-content/plugins/sqlite-database-integration/db.copy \
       /var/www/html/wp-content/db.php \
    && mkdir -p /opt/wp-sqlite \
    && cp -a /var/www/html/wp-content/plugins/sqlite-database-integration /opt/wp-sqlite/ \
    && cp /var/www/html/wp-content/db.php /opt/wp-sqlite/db.php

COPY docker-entrypoint-sqlite.sh /usr/local/bin/docker-entrypoint-sqlite.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-sqlite.sh

ENTRYPOINT ["docker-entrypoint-sqlite.sh"]
CMD ["apache2-foreground"]