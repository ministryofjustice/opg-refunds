import urllib.request
import boto3
import argparse
import json
import os


class ReEncrypter:
    aws_account_id = ''
    aws_iam_session = ''
    aws_ecs_client = ''
    aws_ecs_cluster = ''

    def __init__(self):
        self.set_iam_role_session()

    def list_records(self, database_name, table):
        print("all_records")

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
    new_key = 12345
    for record in work.list_records("database_name", "table"):
        work.get_record_from_old_database("applications_old", record)
        old_key = work.identify_key(record)
        key_arn = work.get_kms_key(old_key)
        decrypted_record = work.decrypt_record(record, key_arn)
        print(decrypted_record)

        new_key_arn = work.get_kms_key(new_key)
        encrypted_record = work.encrypt_record(decrypted_record, new_key_arn)
        work.post_record_to_new_database("applications_new", encrypted_record)


if __name__ == "__main__":
    main()
