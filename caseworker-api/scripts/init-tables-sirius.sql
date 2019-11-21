\set DATABASE_FULL_USERNAME `echo ${OPG_REFUNDS_DB_CASES_FULL_USERNAME}`

\c cases

CREATE TABLE IF NOT EXISTS sirius (
  case_number    bigint  NOT NULL,
  data  jsonb   NOT NULL,
  CONSTRAINT sirius_pkey PRIMARY KEY (case_number)
);

CREATE INDEX IF NOT EXISTS donor_dob_sirius ON sirius((data->>'donor-dob'));

GRANT SELECT ON sirius TO :DATABASE_FULL_USERNAME;
