CREATE TABLE "Member" (
  "id" serial PRIMARY KEY,
  "username" varchar(250) DEFAULT NULL,
  "firstname" varchar(100) DEFAULT NULL,
  "lastname" varchar(100) DEFAULT NULL,
  "description" text,
  "is_admin" boolean DEFAULT NULL,
  "created_at" timestamp DEFAULT NULL,
  "resume" bytea
);

CREATE TABLE "Post" (
  "id" serial PRIMARY KEY,
  "title" varchar(255) DEFAULT NULL,
  "content" bytea,
  "member_id" integer DEFAULT NULL,
  CONSTRAINT member_id FOREIGN KEY (member_id)
      REFERENCES public."Member" (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);
