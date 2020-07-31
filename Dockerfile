FROM node:14.7.0-alpine as build
ENV HOMOCHECKER_API_HOST homochecker-api
WORKDIR /usr/src
COPY . /usr/src
COPY client/fonts/* /usr/src/client/dist/

RUN apk add --no-cache --virtual build-dependencies \
        git && \
    cd client && \
    npm install && \
    npm run build && \
    apk del --no-cache build-dependencies && \
    rm -rf node_modules

FROM nginx:1.19.1-alpine
COPY client/conf /etc/nginx/conf.d
COPY --from=build /usr/src/client/dist /var/www/html
CMD ["/bin/ash", "-c", "sed -i s/api:/$HOMOCHECKER_API_HOST:/ /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
