SELECT 'claim' AS 'cases tables', COUNT(*) FROM public.claim UNION
SELECT 'doctrine_migration_versions' AS 'cases tables', COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'duplicate_claims' AS 'cases tables', COUNT(*) FROM public.duplicate_claims UNION
SELECT 'note' AS 'cases tables', COUNT(*) FROM public.note UNION
SELECT 'payment' AS 'cases tables', COUNT(*) FROM public.payment UNION
SELECT 'poa' AS 'cases tables', COUNT(*) FROM public.poa UNION
SELECT 'report' AS 'cases tables', COUNT(*) FROM public.report UNION
SELECT 'user' AS 'cases tables', COUNT(*) FROM public.user UNION
SELECT 'verification' AS 'cases tables', COUNT(*) FROM public.verification
;
