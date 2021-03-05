CREATE TABLE IF NOT EXISTS "users" (
    "id" SERIAL NOT NULL PRIMARY KEY,
    "screen_name" varchar(255) NOT NULL,
    "service" varchar(20) NOT NULL,
    "url" varchar(255) NOT NULL
);
CREATE INDEX ON "users"("screen_name");
