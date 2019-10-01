\c cases

DROP TABLE IF EXISTS finance;

CREATE TABLE finance (
  case_number     bigint  NOT NULL,
  sequence_number smallint  NOT NULL,
  amount  smallint   NOT NULL,
  received  date   NOT NULL,
  CONSTRAINT finance_pkey PRIMARY KEY (case_number, sequence_number)
);
