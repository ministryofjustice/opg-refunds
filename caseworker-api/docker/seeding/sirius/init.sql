\c cases

DROP TABLE IF EXISTS sirius;

CREATE TABLE sirius (
  case_number    bigint  NOT NULL,
  data  jsonb   NOT NULL,
  CONSTRAINT sirius_pkey PRIMARY KEY (case_number)
);

CREATE INDEX donor_dob_sirius ON sirius((data->>'donor-dob'));
