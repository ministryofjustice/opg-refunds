SELECT 'claim', COUNT(*) FROM public.claim UNION
SELECT 'doctrine_migration_versions', COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'duplicate_claims', COUNT(*) FROM public.duplicate_claims UNION
SELECT 'note', COUNT(*) FROM public.note UNION
SELECT 'payment', COUNT(*) FROM public.payment UNION
SELECT 'poa', COUNT(*) FROM public.poa UNION
SELECT 'report', COUNT(*) FROM public.report UNION
SELECT 'user', COUNT(*) FROM public.user UNION
SELECT 'verification', COUNT(*) FROM public.verification
;
