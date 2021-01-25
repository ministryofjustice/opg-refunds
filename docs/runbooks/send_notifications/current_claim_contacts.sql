CREATE OR REPLACE TEMPORARY view current_claim_contacts_view AS
(SELECT
    json_data -> 'contact' ->> 'email' AS contact,
    "email" as contact_type,
    CASE json_data->>applicant
    WHEN 'attorney' THEN
    CONCAT
    (
        json_data -> 'attorney' -> 'current' -> 'name' ->> 'title' , ' ',
        json_data -> 'attorney' -> 'current' -> 'name'  ->> 'first' , ' ',
        json_data -> 'attorney' -> 'current' -> 'name' ->> 'last'
    )
    WHEN 'executor' THEN
    CONCAT
    (
        json_data -> 'executor' -> 'name' ->> 'title' , ' ',
        json_data -> 'executor' -> 'name' ->> 'first' , ' ',
        json_data -> 'executor' -> 'name' ->> 'last'
    )
    WHEN 'donor' then donor_name
    as claimant_name

FROM claim
WHERE status IN ('pending')
AND json_data -> 'contact' ->> 'receive-notifications' = 'True'
AND json_data -> 'contact' ->> 'email' IS NOT NULL

UNION
SELECT
    json_data -> 'contact' ->> 'telephone' AS contact,
    "phone" as contact_type,
    CASE json_data->>applicant
    WHEN 'attorney' THEN
    CONCAT
    (
        json_data -> 'attorney' -> 'current' -> 'name' ->> 'title' , ' ',
        json_data -> 'attorney' -> 'current' -> 'name'  ->> 'first' , ' ',
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
    AS claimant_name

FROM claim
WHERE status IN ('pending')
AND json_data -> 'contact' ->> 'receive-notifications' = 'True'
AND json_data -> 'contact' ->> 'email' IS NULL


 );


\copy (SELECT * FROM current_claim_contacts_view) TO 'contact_list.csv' WITH DELIMITER ',' CSV HEADER;

DROP VIEW IF EXISTS current_claim_contacts_view;
