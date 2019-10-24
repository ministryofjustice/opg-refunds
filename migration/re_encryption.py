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
    aws_kms_client = ''
    old_pg_client_applications = ''
    old_pg_client_cases = ''
    new_kms_key_id = ''
    aws_kms_client = ''
    unique_aws_kms_keys = []

    def __init__(self):
        cases = {}
        env_vars = [
            "OPG_REFUNDS_DB_CASES_FULL_PASSWORD",
            "OPG_REFUNDS_DB_CASES_FULL_USERNAME",
            "OPG_REFUNDS_DB_CASES_HOSTNAME",
            "OPG_REFUNDS_DB_CASES_NAME"
        ]
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

        self.old_pg_client_cases = self.__pg_connect(
            user=cases['OPG_REFUNDS_DB_CASES_FULL_USERNAME'],
            host=cases['OPG_REFUNDS_DB_CASES_HOSTNAME'],
            port=5432,
            database=cases['OPG_REFUNDS_DB_CASES_NAME'],
            password=cases['OPG_REFUNDS_DB_CASES_FULL_PASSWORD'],
            tcp_keepalive=True)

        self.aws_kms_client = boto3.client('kms',
                                           region_name='eu-west-1')

        self.new_kms_key_id = 'arn:aws:kms:eu-west-1:936779158973:key/bf7e1724-1cad-42d5-b1f1-79e996efa63d'

    def check_cross_account_key_access(self):
        response = self.aws_kms_client.describe_key(
            KeyId=self.new_kms_key_id)
        print(response)

    def set_env(self, env_var):
        if env_var not in os.environ:
            print('{} must be set'.format(env_var))
            exit(1)
        env_var_returned = os.getenv(env_var, '')
        if env_var_returned == '':
            print('{} must have a value'.format(env_var))
            exit(1)
        return env_var_returned

    def __pg_connect(self, user, host, port, database, password, tcp_keepalive):
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

    def pg_select_records_in_table(self, conn, table, limit):
        cur = conn.cursor()
        cur.execute(
            'SELECT * FROM {0} ORDER BY created_datetime DESC LIMIT {1}'.format(table, limit))
        record_select = cur.fetchall()
        return record_select

    def __make_update_sql_statement(self, record_id, encrypted_data):
        update_statement = "UPDATE claim SET json_data="
        update_statement += "jsonb_set(json_data, '{{account}}', json_data -> 'account' || '{{\"details\": \"{0}\"}}') ".format(
            encrypted_data)
        update_statement += "WHERE id={0};".format(record_id)
        return update_statement

    def __pg_update_record_in_table(self, conn, record_id, encrypted_data):
        cur = conn.cursor()
        update_statment = self.__make_update_sql_statement(
            record_id=record_id, encrypted_data=encrypted_data)
        print(update_statment)
        response = cur.execute(update_statment)
        print("UPDATE RECORD for {0}: {1}".format(record_id, response))

    def pg_close(self, conn):
        if conn is not None:
            conn.close()
            print('Database connection closed.')

    def __decrypt_data(self, encrypted_data):
        decoded_encrypted_data = base64.b64decode(encrypted_data)
        response = self.aws_kms_client.decrypt(
            CiphertextBlob=decoded_encrypted_data)
        plaintext = response['Plaintext'].decode('utf-8')
        key_id = response['KeyId']
        if LOGGING_OUTPUT:
            print(plaintext)
        return plaintext, key_id

    def __re_encrypt_with_cross_account_kms_key(self, encrypted_data):
        decoded_encrypted_data = base64.b64decode(encrypted_data)
        response = self.aws_kms_client.re_encrypt(
            CiphertextBlob=decoded_encrypted_data,
            DestinationKeyId=self.new_kms_key_id
        )
        encoded_encrypted_data = base64.b64encode(response['CiphertextBlob'])
        if LOGGING_OUTPUT:
            print("reencrypted ", encoded_encrypted_data)
        return encoded_encrypted_data

    def check_key_status(self, records):
        unique_aws_kms_keys = {}
        for record in records:
            if 'account' in record[5]:
                encrypted_data = record[5]['account']['details']
                text, key_id = self.__decrypt_data(encrypted_data)
                unique_aws_kms_keys[key_id] = True

        if len(unique_aws_kms_keys) < 2:
            print("Only 1 key in use: ", unique_aws_kms_keys)
        else:
            print(len(unique_aws_kms_keys),
                  " keys in use; \n", unique_aws_kms_keys)

    def update_database(self, records):
        for record in records:
            if 'account' in record[5]:
                record_id = record[0]
                encrypted_data = record[5]['account']['details']
                re_encrypted_data = self.__re_encrypt_with_cross_account_kms_key(
                    encrypted_data)
                self.__pg_update_record_in_table(
                    conn=self.old_pg_client_cases,
                    record_id=record_id,
                    encrypted_data=encrypted_data)


NUM_BYTES_FOR_LEN = 4
LOGGING_OUTPUT = False

# TODO: Decrypt must handle failures, print exception and continue


def main():
    # encryption migration, row by row
    work = ReEncrypter()
    records = work.pg_select_records_in_table(
        work.old_pg_client_cases, "claim", 100)
    work.check_key_status(records)
    work.update_database(records)
    records = work.pg_select_records_in_table(
        work.old_pg_client_cases, "claim", 100)
    work.check_key_status(records)
    work.pg_close(work.old_pg_client_cases)


if __name__ == "__main__":
    main()
