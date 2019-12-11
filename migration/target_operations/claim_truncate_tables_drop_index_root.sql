TRUNCATE TABLE public.finance;
TRUNCATE TABLE public.meris;
TRUNCATE TABLE public.sirius;

ALTER TABLE finance DROP CONSTRAINT IF EXISTS finance_pkey;
ALTER TABLE meris DROP CONSTRAINT IF EXISTS meris_pkey;
ALTER TABLE sirius DROP CONSTRAINT IF EXISTS sirius_pkey;

DROP INDEX IF EXISTS donor_dob_meris;
DROP INDEX IF EXISTS donor_dob_sirius;
DROP INDEX IF EXISTS finance_pkey;
DROP INDEX IF EXISTS meris_pkey;
DROP INDEX IF EXISTS sirius_pkey;
