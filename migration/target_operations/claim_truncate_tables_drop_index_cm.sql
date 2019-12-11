
# cases migration
TRUNCATE TABLE claim;
TRUNCATE TABLE doctrine_migration_versions;
TRUNCATE TABLE duplicate_claims;
TRUNCATE TABLE note;
TRUNCATE TABLE payment;
TRUNCATE TABLE poa;
TRUNCATE TABLE report;
TRUNCATE TABLE user;
TRUNCATE TABLE verification;

# cases migration
DROP INDEX claim_pkey;
DROP INDEX doctrine_migration_versions_pkey;
DROP INDEX duplicate_claims_pkey;
DROP INDEX log_pkey;
DROP INDEX payment_pkey;
DROP INDEX poa_pkey;
DROP INDEX report_pkey;
DROP INDEX user_pkey;
DROP INDEX verification_pkey;