\c cases

CREATE TABLE IF NOT EXISTS meris (
  case_number     bigint  NOT NULL,
  sequence_number smallint  NOT NULL,
  data  jsonb   NOT NULL,
  CONSTRAINT meris_pkey PRIMARY KEY (case_number, sequence_number)
);

CREATE INDEX IF NOT EXISTS donor_dob_meris ON meris((data->>'donor-dob'));
