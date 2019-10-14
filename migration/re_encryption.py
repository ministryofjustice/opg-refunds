import urllib.request
import boto3
import argparse
import json
import os
import pg8000
import pprint
pp = pprint.PrettyPrinter(width=41, compact=True)


class ReEncrypter:
    aws_account_id = ''
    aws_iam_session = ''
    old_pg_client_applications = ''
    old_pg_client_cases = ''
    aws_ecs_cluster = ''

    def __init__(self):
        # TODO: pull password from secrets manager and default
        applications = {}
        applications['host'] = os.getenv('APPLICATIONS_HOST', 'localhost')
        applications['user'] = self.set_env('APPLICATIONS_USER')
        applications['password'] = self.set_env('APPLICATIONS_PASSWORD')
        cases = {}
        cases['host'] = os.getenv('CASES_HOST', 'localhost')
        cases['user'] = self.set_env('CASES_USER')
        cases['password'] = self.set_env('CASES_PASSWORD')

        self.old_pg_client_applications = self.pg_connect(
            user=applications['user'],
            host=applications['host'],
            port=5432,
            database="applications",
            password=applications['password'],
            tcp_keepalive=True)
        self.old_pg_client_cases = self.pg_connect(
            user=cases['user'],
            host=cases['host'],
            port=5432,
            database="cases",
            password=cases['password'],
            tcp_keepalive=True)

    def set_env(self, env_var):
        if env_var not in os.environ:
            print('{} must be set'.format(env_var))
            exit(1)
        env_var_returned = os.getenv(env_var, '')
        if env_var_returned == '':
            print('{} must have a value'.format(env_var))
            exit(1)
        return env_var_returned

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

    def pg_select_records_in_table(self, conn, table, limit):
        cur = conn.cursor()
        cur.execute('SELECT * FROM {0} LIMIT {1}'.format(table, limit))
        record_select = cur.fetchall()
        return record_select

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

    def close_db_conections(self):
        self.pg_close(self.old_pg_client_applications)
        self.pg_close(self.old_pg_client_cases)

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

    new_key = 12345
    for record in work.pg_select_records_in_table(work.old_pg_client_cases, "sirius", 100):
        print(record[0])
    #     old_key = work.identify_key(record)
    #     key_arn = work.get_kms_key(old_key)
    #     decrypted_record = work.decrypt_record(record, key_arn)
    #     print(decrypted_record)

    #     new_key_arn = work.get_kms_key(new_key)
    #     encrypted_record = work.encrypt_record(decrypted_record, new_key_arn)
    #     work.post_record_to_new_database("applications_new", encrypted_record)

    work.close_db_conections()


if __name__ == "__main__":
    main()
