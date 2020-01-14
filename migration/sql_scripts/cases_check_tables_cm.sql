SELECT 'claim' AS cases_tables, COUNT(*) FROM public.claim UNION
SELECT 'doctrine_migration_versions' AS cases_tables, COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'duplicate_claims' AS cases_tables, COUNT(*) FROM public.duplicate_claims UNION
SELECT 'note' AS cases_tables, COUNT(*) FROM public.note UNION
SELECT 'payment' AS cases_tables, COUNT(*) FROM public.payment UNION
SELECT 'poa' AS cases_tables, COUNT(*) FROM public.poa UNION
SELECT 'report' AS cases_tables, COUNT(*) FROM public.report UNION
SELECT 'user' AS cases_tables, COUNT(*) FROM public.user UNION
SELECT 'verification' AS cases_tables, COUNT(*) FROM public.verification
;
