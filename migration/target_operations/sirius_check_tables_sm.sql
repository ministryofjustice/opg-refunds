SELECT 'finance', COUNT(*) FROM public.doctrine_migration_versions UNION
SELECT 'meris', COUNT(*) FROM public.poa UNION
SELECT 'sirius', COUNT(*) FROM public.poa_id_seq
;
