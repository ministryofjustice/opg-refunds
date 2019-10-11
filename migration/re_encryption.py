import urllib.request
import boto3
import argparse
import json
import os
import pg8000


class ReEncrypter:
    aws_account_id = ''
    aws_iam_session = ''
    old_pg_client = ''
    aws_ecs_cluster = ''

    def __init__(self):
        database = None
        host = None
        # TODO: read env var for host and default to localhost
        # TODO: pull password from secrets manager and default
        pg_parameters = {"user": "user",
                                 "host": host,
                                 "port": 5432,
                                 "database": database,
                                 "password": "password",
                                 "ssl": None,
                                 "timeout": None,
                                 "tcp_keepalive": True}

        database = "applications"
        host = "localhost"
        self.old_pg_client = pg_connect(pg_parameters)

        # database = "caseworker"
        # host = "localhost"
        # self.old_pg_client = pg_connect(pg_parameters)

    def pg_connect(self, parameters):
        conn = None
        try:
            print('Connecting to the PostgreSQL database...')
            conn = pg8000.connect(parameters)
            return conn
        except (Exception, pg8000.DatabaseError) as error:
            print(error)

    def pg_get_version(self, conn):
        cur = conn.cursor()
        print('PostgreSQL database version:')
        cur.execute('SELECT version()')
        db_version = cur.fetchone()
        print(db_version)

    def pg_close(self, conn):
        if conn is not None:
            conn.close()
            print('Database connection closed.')

    def list_records(self, database_name, table):
        print("all_records")
        self.pg_get_version(self.old_pg_client)
        self.pg_close(self.old_pg_client)

    def get_record_from_old_database(self, database_name, record_id):
        print("record")

    def identify_key(self, record):
        print("key")

    def get_kms_key(self, key):
        print("key_arn")

    def decrypt_record(self, record, key_arn):
        print("decrypted_record")

    def encrypt_record(self, decrypted_record, key_arn):
        print("encrypted_record")

    def post_record_to_new_database(self, database_name, encrypted_record):
        print("status")


def main():
    # encryption migration, row by row
    work = ReEncrypter()
    work.list_records("applications_old", "cases")

    # new_key = 12345
    # for record in work.list_records("database_name", "table"):
    #     work.get_record_from_old_database("applications_old", record)
    #     old_key = work.identify_key(record)
    #     key_arn = work.get_kms_key(old_key)
    #     decrypted_record = work.decrypt_record(record, key_arn)
    #     print(decrypted_record)

    #     new_key_arn = work.get_kms_key(new_key)
    #     encrypted_record = work.encrypt_record(decrypted_record, new_key_arn)
    #     work.post_record_to_new_database("applications_new", encrypted_record)


if __name__ == "__main__":
    main()
