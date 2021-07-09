# syntax = docker/dockerfile:experimental
FROM node:16.4.2-slim AS dependencies
WORKDIR /usr/src/client
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        git
COPY client/package.json client/package-lock.json /usr/src/client/
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-update-notifier --no-audit --no-fund

FROM dependencies AS dev
RUN --mount=type=cache,target=/mnt/.npm,id=/root/.npm \
    cp -r /mnt/.npm /root/

FROM dependencies AS build
COPY . /usr/src/
RUN touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    npm run --no-update-notifier build

FROM nginx:1.21.0-alpine
ENV HOMOCHECKER_API_HOST homochecker-api
COPY client/conf/. /etc/nginx/templates/
COPY --from=build /usr/src/client/dist /var/www/html/
