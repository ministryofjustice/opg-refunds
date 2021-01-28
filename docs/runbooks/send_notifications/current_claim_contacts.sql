CREATE OR REPLACE TEMPORARY view current_claim_contacts_view_email AS
(SELECT
    json_data -> 'contact' ->> 'email' AS contact,
    'email'::text as contact_type,
    CASE json_data->>'applicant'
        WHEN 'attorney' THEN
        CONCAT
        (
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'title' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'first' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'last'
        )
        WHEN 'executor' THEN
        CONCAT
        (
            json_data -> 'executor' -> 'name' ->> 'title' , ' ',
            json_data -> 'executor' -> 'name' ->> 'first' , ' ',
            json_data -> 'executor' -> 'name' ->> 'last'
        )
        WHEN 'donor' THEN donor_name
    END AS claimant_name,
    TO_CHAR(received_datetime :: DATE, 'dd/mm/yyyy') as date_of_claim
FROM claim
WHERE status IN ('pending')
AND json_data -> 'contact' ->> 'receive-notifications' = 'true'
AND json_data -> 'contact' ->> 'email' IS NOT NULL
order by id);

CREATE OR REPLACE TEMPORARY view current_claim_contacts_view_letter AS
(SELECT
    json_data -> 'contact' ->> 'phone' as contact,
    'letter'::text as contact_type,
    CASE json_data->>'applicant'
        WHEN 'attorney' THEN
        CONCAT
        (
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'title' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'first' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'last'
        )
        WHEN 'executor' THEN
        CONCAT
        (
            json_data -> 'executor' -> 'name' ->> 'title' , ' ',
            json_data -> 'executor' -> 'name' ->> 'first' , ' ',
            json_data -> 'executor' -> 'name' ->> 'last'
        )
        WHEN 'donor' THEN donor_name
    END AS claimant_name,
    TO_CHAR(received_datetime :: DATE, 'dd/mm/yyyy') as date_of_claim
FROM claim
WHERE status IN ('pending')
AND json_data -> 'contact' ->> 'receive-notifications' = 'true'
AND json_data -> 'contact' ->> 'email' IS NULL
AND json_data -> 'contact' ->> 'phone' IS NULL
ORDER BY id);

CREATE OR REPLACE TEMPORARY view current_claim_contacts_view_phone AS
(SELECT
    json_data -> 'contact' ->> 'phone' as contact,
    'telephone'::text as contact_type,
    CASE json_data->>'applicant'
        WHEN 'attorney' THEN
        CONCAT
        (
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'title' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'first' , ' ',
            json_data -> 'attorney' -> 'current' -> 'name' ->> 'last'
        )
        WHEN 'executor' THEN
        CONCAT
        (
            json_data -> 'executor' -> 'name' ->> 'title' , ' ',
            json_data -> 'executor' -> 'name' ->> 'first' , ' ',
            json_data -> 'executor' -> 'name' ->> 'last'
        )
        WHEN 'donor' THEN donor_name
    END AS claimant_name,
    TO_CHAR(received_datetime :: DATE, 'dd/mm/yyyy') as date_of_claim
FROM claim
WHERE status IN ('pending')
AND json_data -> 'contact' ->> 'receive-notifications' = 'true'
AND json_data -> 'contact' ->> 'email' IS NULL
AND json_data -> 'contact' ->> 'phone' IS NOT NULL
ORDER BY ID);

SELECT COUNT (1), MAX(TO_DATE(date_of_claim,'dd/mm/yyyy')), contact_type FROM current_claim_contacts_view_email
GROUP BY contact_type
UNION
SELECT COUNT (1), MAX(TO_DATE(date_of_claim,'dd/mm/yyyy')), contact_type FROM current_claim_contacts_view_letter
GROUP BY contact_type
UNION
SELECT COUNT (1), MAX(TO_DATE(date_of_claim,'dd/mm/yyyy')) ,contact_type FROM current_claim_contacts_view_phone
GROUP BY contact_type;


\copy (SELECT * FROM current_claim_contacts_view_email LIMIT 3000) TO 'contact_list_email_1.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 3000 LIMIT 3000) TO 'contact_list_email_2.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 6000 LIMIT 3000) TO 'contact_list_email_3.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 9000 LIMIT 3000) TO 'contact_list_email_4.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 12000 LIMIT 3000) TO 'contact_list_email_5.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 15000 LIMIT 3000) TO 'contact_list_email_6.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_email OFFSET 18000 LIMIT 3000) TO 'contact_list_email_7.csv' WITH DELIMITER ',' CSV HEADER;
\copy (SELECT * FROM current_claim_contacts_view_phone LIMIT 3000) TO 'contact_list_phone_1.csv' WITH DELIMITER ',' CSV HEADER;


