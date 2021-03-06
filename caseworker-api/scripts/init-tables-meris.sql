\set DATABASE_FULL_USERNAME `echo ${OPG_REFUNDS_DB_CASES_FULL_USERNAME}`

\c cases

CREATE TABLE IF NOT EXISTS meris (
  case_number     bigint  NOT NULL,
  sequence_number smallint  NOT NULL,
  data  jsonb   NOT NULL,
  CONSTRAINT meris_pkey PRIMARY KEY (case_number, sequence_number)
);

CREATE INDEX IF NOT EXISTS donor_dob_meris ON meris((data->>'donor-dob'));

GRANT SELECT ON meris TO :DATABASE_FULL_USERNAME;
