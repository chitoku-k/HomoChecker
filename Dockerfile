# syntax = docker/dockerfile:experimental
FROM node:18.1.0-slim AS dependencies
WORKDIR /usr/src/client
RUN --mount=type=cache,id=client:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=client:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        git
COPY client/package.json client/yarn.lock /usr/src/client/
RUN --mount=type=tmpfs,target=/tmp \
    --mount=type=cache,id=client:/usr/local/share/.cache/yarn,target=/usr/local/share/.cache/yarn \
    yarn

FROM dependencies AS dev
RUN --mount=type=cache,id=client:/usr/local/share/.cache/yarn,target=/mnt/yarn \
    cp -r /mnt/yarn /usr/local/share/.cache/

FROM dependencies AS build
COPY . /usr/src/
RUN touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    yarn build

FROM nginx:1.21.6-alpine
ENV HOMOCHECKER_API_HOST homochecker-api
COPY client/conf/. /etc/nginx/templates/
COPY --from=build /usr/src/client/dist /var/www/html/
