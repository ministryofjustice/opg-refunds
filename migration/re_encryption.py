import urllib.request
import boto3
import argparse
import json
import os
import pg8000


class ReEncrypter:
    aws_account_id = ''
    aws_iam_session = ''
    old_pg_client_applications = ''
    old_pg_client_cases = ''
    aws_ecs_cluster = ''

    def __init__(self):
        # TODO: read env var for host and default to localhost
        # TODO: pull password from secrets manager and default
        self.old_pg_client_applications = self.pg_connect(
            user="admin",
            host='localhost',
            port=5432,
            database="applications",
            password="admin",
            tcp_keepalive=True)
        self.old_pg_client_cases = self.pg_connect(
            user="admin",
            host='localhost',
            port=5432,
            database="cases",
            password="admin",
            tcp_keepalive=True)

        self.count_records()
        self.close_db_conections()

    def pg_connect(self, user, host, port, database, password, tcp_keepalive):
        conn = None
        try:
            print('Connecting to the PostgreSQL database...')
            conn = pg8000.connect(
                user=user,
                host=host,
                port=port,
                database=database,
                password=password,
                tcp_keepalive=tcp_keepalive)
            return conn
        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error)

    def pg_count_records_in_table(self, conn, table):
        cur = conn.cursor()
        print('Records in database {}:'.format(table))
        cur.execute('SELECT COUNT (*) FROM {}'.format(table))
        record_count = cur.fetchone()
        print(record_count)

    def pg_close(self, conn):
        if conn is not None:
            conn.close()
            print('Database connection closed.')

    def count_records(self):
        print("count records in each table...")
        self.pg_count_records_in_table(
            self.old_pg_client_applications, "application")
        self.pg_count_records_in_table(self.old_pg_client_cases, "sirius")
        self.pg_count_records_in_table(self.old_pg_client_cases, "meris")
        self.pg_count_records_in_table(self.old_pg_client_cases, "finance")

    def list_records(self, client, table):
        print("all_records...")
        self.pg_count_records_in_table(client, table)

    def close_db_conections(self):
        self.pg_close(self.old_pg_client_applications)
        self.pg_close(self.old_pg_client_cases)

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
    # work.list_records("applications_old", "cases")

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
