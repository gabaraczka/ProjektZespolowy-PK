FROM nginx:1.17.8-alpine

COPY . /app/

COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

RUN echo "client_max_body_size 10M;" >> /etc/nginx/conf.d/default.conf
RUN echo "proxy_read_timeout 300;" >> /etc/nginx/conf.d/default.conf
RUN echo "proxy_connect_timeout 300;" >> /etc/nginx/conf.d/default.conf
RUN echo "proxy_send_timeout 300;" >> /etc/nginx/conf.d/default.conf
