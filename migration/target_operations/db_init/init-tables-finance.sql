\set DATABASE_FULL_USERNAME `echo ${OPG_REFUNDS_DB_CASES_FULL_USERNAME}`

\c cases

CREATE TABLE IF NOT EXISTS finance (
  case_number     bigint  NOT NULL,
  sequence_number smallint  NOT NULL,
  amount  smallint   NOT NULL,
  received  date   NOT NULL,
  CONSTRAINT finance_pkey PRIMARY KEY (case_number, sequence_number)
);

GRANT SELECT ON finance TO :DATABASE_FULL_USERNAME;
