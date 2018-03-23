CREATE TABLE IF NOT EXISTS file (
  "id" serial PRIMARY KEY NOT NULL,
  "name" character(50) NOT NULL,
  "path" character(50) NOT NULL,
  "url" character(50) NOT NULL,
  "public" boolean NOT NULL DEFAULT true,
  "size" integer NOT NULL
);