# syntax = docker/dockerfile:experimental
FROM node:17.0.1-slim AS dependencies
WORKDIR /usr/src/client
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        git
COPY client/package.json client/yarn.lock /usr/src/client/
RUN --mount=type=tmpfs,target=/tmp \
    --mount=type=cache,target=/usr/local/share/.cache/yarn \
    yarn

FROM dependencies AS dev
RUN --mount=type=cache,target=/mnt/yarn,id=/usr/local/share/.cache/yarn \
    cp -r /mnt/yarn /usr/local/share/.cache/

FROM dependencies AS build
COPY . /usr/src/
RUN touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    yarn build

FROM nginx:1.21.3-alpine
ENV HOMOCHECKER_API_HOST homochecker-api
COPY client/conf/. /etc/nginx/templates/
COPY --from=build /usr/src/client/dist /var/www/html/
