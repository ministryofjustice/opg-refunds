import urllib.request
import argparse
import boto3
import base64
import cryptography
import json
import os
import pg8000
import logging
from botocore.exceptions import ClientError
from pprint import pprint


class ReEncrypter:
    aws_account_id = ''
    aws_iam_session = ''
    aws_kms_client_old = ''
    aws_kms_client_new = ''
    old_pg_client_applications = ''
    old_pg_client_cases = ''
    new_kms_key_id = ''
    aws_kms_client = ''

    def __init__(self):
        # applications = {}
        # applications['host'] = os.getenv(
        #     'OPG_REFUNDS_DB_APPLICATIONS_HOSTNAME', 'localhost')
        # applications['user'] = self.set_env('POSTGRES_USER')
        # applications['password'] = self.set_env('PGPASSWORD')
        # self.old_pg_client_applications = self.pg_connect(
        #     user=applications['user'],
        #     host=applications['host'],
        #     port=5432,
        #     database="applications",
        #     password=applications['password'],
        #     tcp_keepalive=True)

        cases = {}
        env_vars = [
            "OPG_REFUNDS_DB_CASES_FULL_PASSWORD",
            "OPG_REFUNDS_DB_CASES_FULL_USERNAME",
            "OPG_REFUNDS_DB_CASES_HOSTNAME",
            "OPG_REFUNDS_DB_CASES_NAME"
        ]

        # path = "api.env"
        path = "/etc/docker-compose/caseworker-api/api.env"
        with open(path, "r") as f:
            for x in f:
                if x in ['\n', '\r\n']:
                    continue
                env_var = x.split("=", 1)
                a = env_var[0]
                cases[a] = env_var[1].strip('\n')

        for x in env_vars:
            if x not in cases:
                print("required env var not found")
                exit(1)

        self.old_pg_client_cases = self.pg_connect(
            user=cases['OPG_REFUNDS_DB_CASES_FULL_USERNAME'],
            host=cases['OPG_REFUNDS_DB_CASES_HOSTNAME'],
            port=5432,
            database=cases['OPG_REFUNDS_DB_CASES_NAME'],
            password=cases['OPG_REFUNDS_DB_CASES_FULL_PASSWORD'],
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
        print(user, host, port, database, password, tcp_keepalive)
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

    def decrypt_data(self, encrypted_data):
        """Decrypt an encrypted data key

        :param data_key_encrypted: Encrypted ciphertext data key.
        :return Plaintext base64-encoded binary data key as binary string
        :return None if error
        """
        # Decrypt the data key
        kms_client = boto3.client('kms',
                                  region_name='eu-west-1')
        print("client created")
        decoded_encrypted_data = base64.b64decode(encrypted_data)
        response = kms_client.decrypt(CiphertextBlob=decoded_encrypted_data)
        plaintext = response['Plaintext'].decode('utf-8')
        key_id = response['KeyId']
        print(key_id)

        # Return plaintext base64-encoded binary data key
        return plaintext

    def get_kms_key(self, key):
        print("key_arn")

    def decrypt_record(self, record, key_arn):
        print("decrypted_record")

    def encrypt_record(self, decrypted_record, key_arn):
        print("encrypted_record")

    def post_record_to_new_database(self, database_name, encrypted_record):
        print("status")


NUM_BYTES_FOR_LEN = 4


def main():
    # logging.basicConfig(level=logging.DEBUG,
    #                     format='%(levelname)s: %(asctime)s: %(message)s')
    # encryption migration, row by row
    work = ReEncrypter()

    # new_key = 12345
    for record in work.pg_select_records_in_table(work.old_pg_client_cases, "claim", 10):
        if 'account' in record[5]:
            encrypted_data = record[5]['account']['details']
            decrypted_record = work.decrypt_data(encrypted_data)
            print(decrypted_record)
    #     key_arn = work.get_kms_key(old_key)
    #     decrypted_record = work.decrypt_record(record, key_arn)
    #     print(decrypted_record)

    #     new_key_arn = work.get_kms_key(new_key)
    #     encrypted_record = work.encrypt_record(decrypted_record, new_key_arn)
    #     work.post_record_to_new_database("applications_new", encrypted_record)

    # work.pg_close(work.old_pg_client_applications)
    work.pg_close(work.old_pg_client_cases)


if __name__ == "__main__":
    main()
