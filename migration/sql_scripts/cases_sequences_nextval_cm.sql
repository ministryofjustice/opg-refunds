SELECT 'report_id_seq' AS next_val, nextval('report_id_seq') UNION
SELECT 'note_id_seq' AS next_val, nextval('note_id_seq') UNION
SELECT 'verification_id_seq' AS next_val, nextval('verification_id_seq') UNION
SELECT 'user_id_seq' AS next_val, nextval('user_id_seq') UNION
SELECT 'payment_id_seq' AS next_val, nextval('payment_id_seq') UNION
SELECT 'poa_id_seq' AS next_val, nextval('poa_id_seq')
;
