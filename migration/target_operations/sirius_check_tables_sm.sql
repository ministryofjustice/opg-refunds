SELECT 'doctrine_migration_versions', COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'poa', COUNT(*) FROM public.poa UNION
SELECT 'poa_id_seq', nextval('public.poa_id_seq')
;
