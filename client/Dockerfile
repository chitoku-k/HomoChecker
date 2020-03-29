FROM node:13.10-alpine as build
ENV HOMOCHECKER_API_HOST homochecker-api
WORKDIR /usr/src
COPY . /usr/src
COPY fonts/* /usr/src/dist/

RUN npm install && \
    npm run build && \
    rm -rf node_modules

FROM nginx:1.16-alpine
COPY conf /etc/nginx/conf.d
COPY --from=build /usr/src/dist /var/www/html
CMD ["/bin/ash", "-c", "sed -i s/api:/$HOMOCHECKER_API_HOST:/ /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
