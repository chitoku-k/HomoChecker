# syntax = docker/dockerfile:1
FROM node:25.2.1-slim AS dependencies
WORKDIR /usr/src
COPY client/package.json client/yarn.lock client/.yarnrc.yml /usr/src/
RUN --mount=type=tmpfs,target=/tmp \
    --mount=type=cache,id=client:/usr/local/share/.cache/yarn,target=/usr/local/share/.cache/yarn \
    npm install -g -f corepack && \
    corepack enable && \
    yarn

FROM dependencies AS dev
RUN --mount=type=cache,id=client:/usr/local/share/.cache/yarn,target=/mnt/yarn \
    cp -r /mnt/yarn /usr/local/share/.cache/

FROM dependencies AS build
ARG SCM_URL
COPY client /usr/src/
RUN touch fonts/atlan.svg fonts/atlan.ttf fonts/atlan.woff && \
    yarn build

FROM nginx:1.29.4
ENV HOMOCHECKER_API_HOST=homochecker-api
COPY client/conf /etc/nginx/templates/
COPY --from=build /usr/src/dist /var/www/html/
