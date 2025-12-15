# syntax = docker/dockerfile:1
FROM node:25.2.1-slim AS dependencies
WORKDIR /usr/src
COPY package.json yarn.lock .yarnrc.yml /usr/src/
RUN --mount=type=tmpfs,target=/tmp \
    --mount=type=cache,id=web:/usr/local/share/.cache/yarn,target=/usr/local/share/.cache/yarn \
    npm install -g -f corepack && \
    corepack enable && \
    yarn

FROM dependencies AS dev
RUN --mount=type=cache,id=client:/usr/local/share/.cache/yarn,target=/mnt/yarn \
    cp -r /mnt/yarn /usr/local/share/.cache/

FROM dependencies AS build
ARG SCM_URL
COPY . /usr/src/
RUN --mount=type=secret,id=web:src/fonts.tar.gz,target=/run/secrets/fonts.tar.gz \
    [ -f /run/secrets/fonts.tar.gz ] && tar xzf /run/secrets/fonts.tar.gz -C src; \
    yarn build

FROM nginx:1.29.4 AS production
ENV HOMOCHECKER_API_HOST=localhost
COPY conf /etc/nginx/templates/
COPY --from=build /usr/src/dist /var/www/html/
