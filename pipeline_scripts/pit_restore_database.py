""" Point In Time Restore database """

import logging
import argparse
import boto3
from boto3.session import Session
from botocore.exceptions import ClientError
import yaml

# Configuration
account = 'production'
source_environment = 'production'
assumed_role_arn = 'arn:aws:iam::792093328875:role/account-write'
session_name = 'pit_restore_database.py'
boto3.set_stream_logger('boto3', logging.INFO)

# Parse arguments and set runtime config
parser = argparse.ArgumentParser(description='Process some parameters.')
parser.add_argument('-t', '--target_environment',
    required=True,
    help='Target environment to restore to'
)
parser.add_argument('-r', '--restore_time',
    required=True,
    help='Point in time restore time'
)
args = parser.parse_args()
target_environment = args.target_environment
restore_time = args.restore_time
api_target_db = 'applications-{0}'.format(target_environment)
membrane_target_db = 'caseworker-{0}'.format(target_environment)
target_dbs = [api_target_db, membrane_target_db]

# Don't allow production overwrite
if 'production' in target_environment:
    print("Can not restore to production")
    exit(1)


def get_db_name(dbstring):
    ''' Parse the datbase name from the database string '''
    return dbstring.replace('opgcore', '').replace('-'+target_environment, '')


# Create RDS client objects
rds_client = boto3.client('rds')
rds_waiter_available = rds_client.get_waiter('db_instance_available')
rds_waiter_deleted = rds_client.get_waiter('db_instance_deleted')
ec2_client = boto3.client('ec2')


# # Delete target databases
# print('1. Deleting databases')
# try:
#     for db in target_dbs:
#         print('--Deleting database {0}'.format(db))
#         response = rds_client.delete_db_instance(
#             DBInstanceIdentifier=db,
#             SkipFinalSnapshot=True
#         )
# except ClientError as error:
#     if 'DBInstanceNotFound' in error.response['Error']['Code']:
#         print('--Database {0} not present. Continuing'.format(db))
#     else:
#         print('--Exception: {0}'.format(error.response['Error']['Code']))
#         print('--' + error.response['Error']['Message'])
#         exit(1)


# # Wait for deletion
# for db in target_dbs:
#     print('--Waiting for deletion of database {0}'.format(db))
#     rds_waiter_deleted.wait(
#         DBInstanceIdentifier=db,
#         WaiterConfig={
#             'Delay': 60,
#             'MaxAttempts': 60
#         }
#     )

# # Point in time restore
# print('2. Point in Time restore')
# for db in target_dbs:
#     source_db =db.replace(target_environment, source_environment)
#     print('--Point in time restore database from {0} to {1} at {2}'.format(source_db, db, restore_time))
#     response = rds_client.restore_db_instance_to_point_in_time(
#         SourceDBInstanceIdentifier=source_db,
#         TargetDBInstanceIdentifier=db,
#         RestoreTime=restore_time,
#         DBSubnetGroupName='rds-private-subnets-{}-applications'.format(target_environment),
#         MultiAZ=True,
#         Tags=[
#             {
#                 'Key': 'Environment',
#                 'Value': 'production'
#             },
#             {
#                 'Key': 'Application',
#                 'Value': 'core'
#             },
#             {
#                 'Key': 'Name',
#                 'Value': '{0}.sirius.opg.digital'.format(get_db_name(db))
#             },
#             {
#                 'Key': 'Stack',
#                 'Value': target_environment
#             },
#         ]
#     )


# Wait for Availability
for db in target_dbs:
    print('--Waiting for availability of database {0}'.format(db))
    rds_waiter_available.wait(
        DBInstanceIdentifier=db,
        WaiterConfig={
            'Delay': 60,
            'MaxAttempts': 120
        }
    )


# Modify target databases
print('3. Modify Database')
for db in target_dbs:

    # Lookup VPC security groups
    print('--lookup SG for database {0}'.format(db))

    dbname = get_db_name(db)
    if dbname == 'applications':
        dbname = 'application'
    print('rds-{0}-{1}'.format(dbname, target_environment))


    response = ec2_client.describe_security_groups(
        Filters=[
            {
                'Name': 'vpc-id',
                'Values': [
                    'vpc-d5f298b2',
                ]
            },
            {
                'Name': 'group-name',
                'Values': [
                    'rds-{0}-{1}'.format(dbname, target_environment),
                ]
            },
        ],
    )
    if len(response['SecurityGroups']) != 1:
        print('--Error: None or >1 Security groups found')
        print('--' + str(response['SecurityGroups']))
        exit(1)
    else:
        rds_sg = response['SecurityGroups'][0]['GroupId']
        print('--Found SG {0} {1}'.format(rds_sg, response['SecurityGroups'][0]['GroupName']))

    print('--Modifying database {0}'.format(db))
    response = rds_client.modify_db_instance(
        DBInstanceIdentifier=db,
        VpcSecurityGroupIds=[
            rds_sg,
        ],
        ApplyImmediately=True,
        BackupRetentionPeriod=0,
        MultiAZ=True
    )

# Wait for Availability
for db in target_dbs:
    print('--Waiting for availability of database {0}'.format(db))
    rds_waiter_available.wait(
        DBInstanceIdentifier=db,
        WaiterConfig={
            'Delay': 30,
            'MaxAttempts': 60
        }
    )

# Reboot to activate multi-AZ
print('3. Reboot Database')
for db in target_dbs:
    print('--Rebooting database {0}'.format(db))
    response = rds_client.reboot_db_instance(
        DBInstanceIdentifier=db,
    )

# Wait for Availability
for db in target_dbs:
    print('--Waiting for availability of database {0}'.format(db))
    rds_waiter_available.wait(
        DBInstanceIdentifier=db,
        WaiterConfig={
            'Delay': 30,
            'MaxAttempts': 60
        }
    )

print('Finished!')
