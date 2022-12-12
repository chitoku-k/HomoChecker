CREATE TABLE IF NOT EXISTS "users" (
    "id" serial NOT NULL PRIMARY KEY,
    "screen_name" varchar(255) NOT NULL,
    "service" varchar(20) NOT NULL,
    "url" varchar(255) NOT NULL
);
CREATE INDEX ON "users" ("screen_name");

CREATE TABLE IF NOT EXISTS "profiles" (
    "screen_name" varchar(255) NOT NULL,
    "icon_url" text NOT NULL,
    "expires_at" timestamp NOT NULL,
    UNIQUE ("screen_name")
);

CREATE TABLE IF NOT EXISTS "altsvcs" (
    "url" varchar(255) NOT NULL PRIMARY KEY,
    "protocol" varchar(20) NOT NULL,
    "expires_at" timestamp NOT NULL
);
