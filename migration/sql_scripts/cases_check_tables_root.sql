SELECT 'finance' AS cases_tables, COUNT(*) FROM public.finance UNION
SELECT 'meris' AS cases_tables, COUNT(*) FROM public.meris UNION
SELECT 'sirius' AS cases_tables, COUNT(*) FROM public.sirius
;
