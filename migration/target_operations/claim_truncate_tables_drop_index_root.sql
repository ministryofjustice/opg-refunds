TRUNCATE TABLE public.finance;
TRUNCATE TABLE public.meris;
TRUNCATE TABLE public.sirius;

DROP INDEX donor_dob_meris;
DROP INDEX donor_dob_sirius;
DROP INDEX finance_pkey;
DROP INDEX meris_pkey;
DROP INDEX sirius_pkey;
