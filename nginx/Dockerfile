FROM debian:jessie

RUN apt-get update && apt-get install -y \
    nginx

ADD nginx.conf /etc/nginx/
ADD symfony.conf /etc/nginx/sites-available/
ADD supervisord.conf /etc/nginx/sites-available/

RUN ln -s /etc/nginx/sites-available/symfony.conf /etc/nginx/sites-enabled/symfony
RUN ln -s /etc/nginx/sites-available/supervisord.conf /etc/nginx/sites-enabled/supervisord
RUN rm /etc/nginx/sites-enabled/default

RUN echo "upstream php-upstream { server iq-php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

CMD ["nginx"]

EXPOSE 8080
EXPOSE 443