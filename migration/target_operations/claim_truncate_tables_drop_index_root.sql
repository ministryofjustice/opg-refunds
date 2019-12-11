TRUNCATE TABLE public.finance CASCADE; 
TRUNCATE TABLE public.meris CASCADE;
TRUNCATE TABLE public.sirius CASCADE;

-- ALTER TABLE public.meris DROP CONSTRAINT IF EXISTS donor_dob_meris;
-- ALTER TABLE public.sirius DROP CONSTRAINT IF EXISTS donor_dob_sirius;
-- ALTER TABLE public.finance DROP CONSTRAINT IF EXISTS finance_pkey;
-- ALTER TABLE public.meris DROP CONSTRAINT IF EXISTS meris_pkey;
-- ALTER TABLE public.sirius DROP CONSTRAINT IF EXISTS sirius_pkey;

-- DROP INDEX IF EXISTS donor_dob_meris;
-- DROP INDEX IF EXISTS donor_dob_sirius;
-- DROP INDEX IF EXISTS finance_pkey;
-- DROP INDEX IF EXISTS meris_pkey;
-- DROP INDEX IF EXISTS sirius_pkey;
