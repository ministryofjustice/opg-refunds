SELECT 'finance' AS 'cases tables', COUNT(*) FROM public.finance UNION
SELECT 'meris' AS 'cases tables', COUNT(*) FROM public.meris UNION
SELECT 'sirius' AS 'cases tables', COUNT(*) FROM public.sirius
;
