--
-- NOTE:
--
-- File paths need to be edited. Search for /mnt/sql and
-- replace it with the path to the directory containing
-- the extracted data files.
--
--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.11
-- Dumped by pg_dump version 10.10 (Ubuntu 10.10-0ubuntu0.18.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public."user" (id, name, email, password_hash, status, roles, token, token_expires, password_reset_expires, failed_login_attempts) FROM stdin;
\.
COPY public."user" (id, name, email, password_hash, status, roles, token, token_expires, password_reset_expires, failed_login_attempts) FROM '/mnt/sql/3253.dat';

--
-- Data for Name: claim; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.claim (id, assigned_to_id, created_datetime, updated_datetime, received_datetime, json_data, status, assigned_datetime, finished_datetime, donor_name, account_hash, no_sirius_poas, no_meris_poas, rejection_reason, rejection_reason_description, finished_by_id, outcome_email_sent, outcome_text_sent, outcome_letter_sent, outcome_phone_called) FROM stdin;
\.
COPY public.claim (id, assigned_to_id, created_datetime, updated_datetime, received_datetime, json_data, status, assigned_datetime, finished_datetime, donor_name, account_hash, no_sirius_poas, no_meris_poas, rejection_reason, rejection_reason_description, finished_by_id, outcome_email_sent, outcome_text_sent, outcome_letter_sent, outcome_phone_called) FROM '/mnt/sql/3258.dat';

--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.doctrine_migration_versions (version) FROM stdin;
\.
COPY public.doctrine_migration_versions (version) FROM '/mnt/sql/3248.dat';

--
-- Data for Name: duplicate_claims; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.duplicate_claims (claim_id, duplicate_claim_id) FROM stdin;
\.
COPY public.duplicate_claims (claim_id, duplicate_claim_id) FROM '/mnt/sql/3260.dat';

--
-- Data for Name: poa; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.poa (id, claim_id, system, case_number, received_date, original_payment_amount, case_number_rejection_count) FROM stdin;
\.
COPY public.poa (id, claim_id, system, case_number, received_date, original_payment_amount, case_number_rejection_count) FROM '/mnt/sql/3257.dat';

--
-- Data for Name: note; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.note (id, claim_id, user_id, poa_id, created_datetime, type, message) FROM stdin;
\.
COPY public.note (id, claim_id, user_id, poa_id, created_datetime, type, message) FROM '/mnt/sql/3251.dat';

--
-- Data for Name: payment; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.payment (id, amount, method, added_datetime, processed_datetime, spreadsheet_hash, claim_id) FROM stdin;
\.
COPY public.payment (id, amount, method, added_datetime, processed_datetime, spreadsheet_hash, claim_id) FROM '/mnt/sql/3255.dat';

--
-- Data for Name: report; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.report (id, type, title, start_datetime, end_datetime, data, generated_datetime, generation_time_ms) FROM stdin;
\.
COPY public.report (id, type, title, start_datetime, end_datetime, data, generated_datetime, generation_time_ms) FROM '/mnt/sql/3262.dat';

--
-- Data for Name: verification; Type: TABLE DATA; Schema: public; Owner: cases_migration
--

COPY public.verification (id, poa_id, type, passes) FROM stdin;
\.
COPY public.verification (id, poa_id, type, passes) FROM '/mnt/sql/3250.dat';

--
-- Name: note_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.note_id_seq', 3149795, true);


--
-- Name: payment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.payment_id_seq', 231673, true);


--
-- Name: poa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.poa_id_seq', 404109, true);


--
-- Name: report_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.report_id_seq', 6772, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.user_id_seq', 265, true);


--
-- Name: verification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cases_migration
--

SELECT pg_catalog.setval('public.verification_id_seq', 1321300, true);


--
-- PostgreSQL database dump complete
--