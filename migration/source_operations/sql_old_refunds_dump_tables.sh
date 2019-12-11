#!/usr/bin/env bash

mkdir -p /mnt/sql
# Find DB PASSWORDs and enter below
# grep database_master_password opg-refund-deploy/ansible/production/env_vars.yml
APP_DB_PASS=
CASES_DB_PASS=

# grep caseworker_logical_dbs -a6 opg-refund-deploy/ansible/production/env_vars.yml | grep sirius -a2 -b1| grep full_password
SIRIUS_DB_PASS=

# Applications DBs
echo "applications"
export PGPASSWORD=${APP_DB_PASS}
pg_dump --data-only -h applications.preprod.internal -U refunds_master_full --file=/mnt/sql/applications.sql applications

#Caseworker DBs

#     Name    |          Owner          | Encoding |   Collate   |    Ctype    |                  Access privileges                  
# ------------+-------------------------+----------+-------------+-------------+-----------------------------------------------------
# cases      | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =Tc/refunds_caseworker_full                        +
#            |                         |          |             |             | refunds_caseworker_full=CTc/refunds_caseworker_full+
#            |                         |          |             |             | cases_full=c/refunds_caseworker_full               +
#            |                         |          |             |             | cases_migration=CTc/refunds_caseworker_full
# caseworker | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
# finance    | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =Tc/refunds_caseworker_full                        +
#            |                         |          |             |             | refunds_caseworker_full=CTc/refunds_caseworker_full+
#            |                         |          |             |             | finance_full=c/refunds_caseworker_full             +
#            |                         |          |             |             | finance_migration=CTc/refunds_caseworker_full
# meris      | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =Tc/refunds_caseworker_full                        +
#            |                         |          |             |             | refunds_caseworker_full=CTc/refunds_caseworker_full+
#            |                         |          |             |             | meris_full=c/refunds_caseworker_full               +
#            |                         |          |             |             | meris_migration=CTc/refunds_caseworker_full
# postgres   | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
# rdsadmin   | rdsadmin                | UTF8     | en_US.UTF-8 | en_US.UTF-8 | rdsadmin=CTc/rdsadmin
# sirius     | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =Tc/refunds_caseworker_full                        +
#            |                         |          |             |             | refunds_caseworker_full=CTc/refunds_caseworker_full+
#            |                         |          |             |             | sirius_full=c/refunds_caseworker_full              +
#            |                         |          |             |             | sirius_migration=CTc/refunds_caseworker_full
# template0  | rdsadmin                | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/rdsadmin                                        +
#            |                         |          |             |             | rdsadmin=CTc/rdsadmin
# template1  | refunds_caseworker_full | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/refunds_caseworker_full                         +
#            |                         |          |             |             | refunds_caseworker_full=CTc/refunds_caseworker_full

#dump_cmd='pg_dump --column-inserts --data-only -h caseworker.preprod.internal '
dump_cmd='pg_dump --data-only -h caseworker.preprod.internal '

export PGPASSWORD=${CASES_DB_PASS}
echo "cases"
$dump_cmd -U cases_full --file=/mnt/sql/cases.sql cases
echo "caseworker"
$dump_cmd -U cases_full --file=/mnt/sql/caseworker.sql caseworker
echo "finance"
$dump_cmd -U cases_full --file=/mnt/sql/finance.sql finance
echo "meris"
$dump_cmd -U cases_full --file=/mnt/sql/meris.sql meris


export PGPASSWORD=${SIRIUS_DB_PASS}
echo "sirius"
$dump_cmd -U sirius_full --file=/mnt/sql/sirius.sql sirius
