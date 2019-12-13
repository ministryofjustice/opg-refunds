SELECT 'doctrine_migration_versions' AS 'sirius tables', COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'poa' AS 'sirius tables', COUNT(*) FROM public.poa UNION
SELECT 'poa_id_seq' AS 'sirius tables', nextval('public.poa_id_seq')
;
