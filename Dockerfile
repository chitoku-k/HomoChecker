FROM node:15.0.0-alpine as build
ENV HOMOCHECKER_API_HOST homochecker-api
WORKDIR /usr/src
COPY . /usr/src

RUN apk add --no-cache --virtual build-dependencies \
        git && \
    cd client && \
    touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    npm install && \
    npm run build && \
    apk del --no-cache build-dependencies && \
    rm -rf node_modules

FROM nginx:1.19.3-alpine
COPY client/conf /etc/nginx/conf.d
COPY --from=build /usr/src/client/dist /var/www/html
CMD ["/bin/ash", "-c", "sed -i s/api:/$HOMOCHECKER_API_HOST:/ /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]
