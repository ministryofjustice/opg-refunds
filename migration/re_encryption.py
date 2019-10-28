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
import datetime


class ReEncrypter:
    aws_kms_client = ''
    old_pg_client_cases = ''
    new_kms_key_id = ''
    testing_mode = ''

    def __init__(self, kms_key_arn, test):
        self.testing_mode = test
        if self.testing_mode:
            print("TEST MODE: Changes not comitted!")
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

        self.new_kms_key_id = kms_key_arn[0]

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
            print('Connected to the PostgreSQL database!')
            return conn
        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error)

    def pg_select_records_in_table(self, conn):
        print("Selecting all records...")
        cur = conn.cursor()
        cur.execute(
            "SELECT * FROM claim WHERE json_data->'account'->'details' IS NOT NULL ORDER BY created_datetime;")
        record_select = cur.fetchall()
        return record_select

    def __make_update_sql_statement(self, record_id, encrypted_data):
        update_statement = "UPDATE claim SET json_data="
        update_statement += "jsonb_set(json_data, '{{account}}', json_data -> 'account' || '{{\"details\": \"{0}\"}}') ".format(
            encrypted_data.decode('utf-8'))
        update_statement += "WHERE id={0} RETURNING id,json_data->'account'->'details';".format(
            record_id)
        return update_statement

    def __pg_update_record_in_table(self, conn, record_id, encrypted_data):
        cur = conn.cursor()
        update_statment = self.__make_update_sql_statement(
            record_id=record_id, encrypted_data=encrypted_data)
        if LOGGING_OUTPUT:
            print(update_statment)
        try:
            cur.execute(update_statment)
            if not self.testing_mode:
                conn.commit()
        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error, "\n", update_statment)
            pass
        if LOGGING_OUTPUT:
            record_update = cur.fetchall()
            print(record_update)

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
            print("re-encrypted ", encoded_encrypted_data)

        return encoded_encrypted_data

    def check_key_status(self, records):
        print("Checking Key Status...")
        unique_aws_kms_keys = {}
        for record in records:
            if 'account' in record[5]:
                record_id = record[0]
                if LOGGING_OUTPUT:
                    print("Checking record {0} ...".format(record_id))
                encrypted_data = record[5]['account']['details']
                try:
                    text, key_id = self.__decrypt_data(encrypted_data)
                    if key_id in unique_aws_kms_keys:
                        unique_aws_kms_keys[key_id].append(record_id)
                    else:
                        unique_aws_kms_keys[key_id] = [record_id]
                except:
                    print("Failed to Decrypt record! \n",
                          record_id, "\n", encrypted_data)
                    pass

        for key, value in unique_aws_kms_keys.items():
            print(key, len(value), "\n", value)

    def update_database(self, records):
        print("Updating Records...")
        for record in records:
            if 'account' in record[5]:
                record_id = record[0]
                if LOGGING_OUTPUT:
                    print("ReEncrypting record {0} ...".format(record_id))
                encrypted_data = record[5]['account']['details']
                if LOGGING_OUTPUT:
                    print("  --", record_id, "--", encrypted_data)
                try:
                    re_encrypted_data = self.__re_encrypt_with_cross_account_kms_key(
                        encrypted_data)
                    if LOGGING_OUTPUT:
                        print("\n  --", re_encrypted_data)
                    self.__pg_update_record_in_table(
                        conn=self.old_pg_client_cases,
                        record_id=record_id,
                        encrypted_data=re_encrypted_data)
                except:
                    print("Failed to ReEncrypt record! \n",
                          record_id, "\n", encrypted_data)
                    pass

        print("End of update...")


NUM_BYTES_FOR_LEN = 4
LOGGING_OUTPUT = False


def main():
    start_time = datetime.datetime.now()
    print("Started: ", str(start_time))

    parser = argparse.ArgumentParser(
        description="Re-encrypt records in a database using a new KMS key.")

    parser.add_argument("kms_key_arn",
                        nargs=1,
                        type=str,
                        help="KMS key ARN")

    parser.add_argument('--test',
                        action='store_true',
                        default=False,
                        dest='test',
                        help='Test script by not committing updates')

    args = parser.parse_args()
    # encryption migration, row by row
    work = ReEncrypter(args.kms_key_arn, args.test)

    records = work.pg_select_records_in_table(work.old_pg_client_cases)
    work.check_key_status(records)

    work.update_database(records=records)

    records = work.pg_select_records_in_table(work.old_pg_client_cases)
    work.check_key_status(records)
    work.pg_close(work.old_pg_client_cases)

    end_time = datetime.datetime.now()

    print("Started: ", str(start_time), "\n Ended:", str(end_time))


if __name__ == "__main__":
    main()
