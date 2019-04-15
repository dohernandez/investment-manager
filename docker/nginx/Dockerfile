FROM debian:jessie

MAINTAINER Darien Hernandez Gonzalez <dohernandez@gmail.com>

ARG SERVER_NAME
RUN echo "Server name: ${SERVER_NAME}"

RUN apt-get update && apt-get install -y \
    nginx

ADD nginx.conf /etc/nginx/
ADD symfony.conf /etc/nginx/sites-available/${SERVER_NAME}.conf

RUN sed -i -e "s/symfony/${SERVER_NAME}/g" /etc/nginx/sites-available/${SERVER_NAME}.conf

RUN ln -s /etc/nginx/sites-available/${SERVER_NAME}.conf /etc/nginx/sites-enabled/${SERVER_NAME} \
&& rm /etc/nginx/sites-enabled/default

RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

CMD ["nginx"]

EXPOSE 80
EXPOSE 443
