SELECT 'doctrine_migration_versions' AS sirius_tables, COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'poa' AS sirius_tables, COUNT(*) FROM public.poa UNION
SELECT 'poa_id_seq' AS sirius_tables, nextval('public.poa_id_seq')
;
