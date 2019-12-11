TRUNCATE TABLE public.claim;
TRUNCATE TABLE public.doctrine_migration_versions;
TRUNCATE TABLE public.duplicate_claims;
TRUNCATE TABLE public.note;
TRUNCATE TABLE public.payment;
TRUNCATE TABLE public.poa;
TRUNCATE TABLE public.report;
TRUNCATE TABLE public.user;
TRUNCATE TABLE public.verification;

-- ALTER TABLE public.claim DROP CONSTRAINT IF EXISTS claim_pkey;
-- ALTER TABLE public.doctrine_migration_versions DROP CONSTRAINT IF EXISTS doctrine_migration_versions_pkey;
-- ALTER TABLE public.duplicate_claims DROP CONSTRAINT IF EXISTS duplicate_claims_pkey;
-- ALTER TABLE public.note DROP CONSTRAINT IF EXISTS log_pkey;
-- ALTER TABLE public.payment DROP CONSTRAINT IF EXISTS payment_pkey;
-- ALTER TABLE public.poa DROP CONSTRAINT IF EXISTS poa_pkey;
-- ALTER TABLE public.report DROP CONSTRAINT IF EXISTS report_pkey;
-- ALTER TABLE public.user DROP CONSTRAINT IF EXISTS user_pkey;
-- ALTER TABLE public.verification DROP CONSTRAINT IF EXISTS verification_pkey;

-- DROP INDEX IF EXISTS claim_pkey;
-- DROP INDEX IF EXISTS doctrine_migration_versions_pkey;
-- DROP INDEX IF EXISTS duplicate_claims_pkey;
-- DROP INDEX IF EXISTS log_pkey;
-- DROP INDEX IF EXISTS payment_pkey;
-- DROP INDEX IF EXISTS poa_pkey;
-- DROP INDEX IF EXISTS report_pkey;
-- DROP INDEX IF EXISTS user_pkey;
-- DROP INDEX IF EXISTS verification_pkey;
