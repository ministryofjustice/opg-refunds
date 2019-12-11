--
-- NOTE:
--
-- File paths need to be edited. Search for $$PATH$$ and
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
-- Data for Name: finance; Type: TABLE DATA; Schema: public; Owner: refunds_caseworker_full
--

COPY public.finance (case_number, sequence_number, amount, received) FROM stdin;
\.
COPY public.finance (case_number, sequence_number, amount, received) FROM '/mnt/sql/3265.dat';

--
-- Data for Name: meris; Type: TABLE DATA; Schema: public; Owner: refunds_caseworker_full
--

COPY public.meris (case_number, sequence_number, data) FROM stdin;
\.
COPY public.meris (case_number, sequence_number, data) FROM '/mnt/sql/3263.dat';

--
-- Data for Name: sirius; Type: TABLE DATA; Schema: public; Owner: refunds_caseworker_full
--

COPY public.sirius (case_number, data) FROM stdin;
\.
COPY public.sirius (case_number, data) FROM '/mnt/sql/3264.dat';

--
-- PostgreSQL database dump complete
--