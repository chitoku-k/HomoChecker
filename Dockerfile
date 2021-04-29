FROM node:16.0.0-alpine AS build
ENV HOMOCHECKER_API_HOST homochecker-api

RUN apk add --no-cache --virtual build-dependencies \
        git

FROM build AS production
WORKDIR /usr/src/client
COPY . /usr/src

RUN touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    npm install && \
    npm run build && \
    apk del --no-cache build-dependencies && \
    rm -rf node_modules

FROM nginx:1.19.10-alpine
COPY client/conf /etc/nginx/conf.d
COPY --from=production /usr/src/client/dist /var/www/html
CMD ["/bin/ash", "-c", "sed -i s/api:/$HOMOCHECKER_API_HOST:/ /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]

EXPOSE 80
