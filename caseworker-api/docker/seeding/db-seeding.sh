#!/bin/sh

psql -h ${OPG_REFUNDS_DB_FINANCE_HOSTNAME} -p ${OPG_REFUNDS_DB_FINANCE_PORT} -U ${POSTGRES_USER} -d postgres -f finance/develop/data.sql
psql -h ${OPG_REFUNDS_DB_MERIS_HOSTNAME} -p ${OPG_REFUNDS_DB_MERIS_PORT} -U ${POSTGRES_USER} -d postgres -f meris/develop/data.sql
psql -h ${OPG_REFUNDS_DB_SIRIUS_HOSTNAME} -p ${OPG_REFUNDS_DB_SIRIUS_PORT} -U ${POSTGRES_USER} -d postgres -f sirius/develop/data.sql

exit 0
