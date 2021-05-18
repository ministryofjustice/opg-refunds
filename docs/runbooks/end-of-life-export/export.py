import argparse
import boto3
from botocore.exceptions import ClientError
import pg8000
import string
import json



class AwsBase:
    aws_region = 'eu-west-1'
    aws_iam_session = ''
    aws_secret = 'opg_refunds_db_cases_migration_password'

    def set_iam_role_session(self, account, role):
        role_arn = 'arn:aws:iam::{}:role/{}'.format(account, role)
        sts = boto3.client(
            'sts',
            region_name=self.aws_region,
        )
        session = sts.assume_role(
            RoleArn=role_arn,
            RoleSessionName='exporting_data_for_refunds',
            DurationSeconds=900
        )
        self.aws_iam_session = session
        return self

    def get_aws_client(self, namespace, session):
        return boto3.client(
            namespace,
            region_name=self.aws_region,
            aws_access_key_id=session['Credentials']['AccessKeyId'],
            aws_secret_access_key=session['Credentials']['SecretAccessKey'],
            aws_session_token=session['Credentials']['SessionToken'])


    def aws_rds_cluster(self, filterField, filterName):
        client = self.get_aws_client('rds', self.aws_iam_session)

        response = client.describe_db_clusters(
            MaxRecords=100,
            Marker=""
        ).get('DBClusters')

        filtered = list(filter(lambda x: filterName in x[filterField], response)).pop()
        return filtered['ReaderEndpoint'], filtered['Port'], filtered['DatabaseName']


    def aws_secrets_rds_login(self, environment):
        client = self.get_aws_client('secretsmanager', self.aws_iam_session)
        res = client.get_secret_value(SecretId = environment + '/' + self.aws_secret)
        return 'cases_migration', res['SecretString']


    def connect(self, host, port, user, password, database):
        return pg8000.connect(
                user=user,
                host=host,
                port=port,
                database=database,
                password=password,
                tcp_keepalive=True)


    def aws_rds_connection(self, environment):
        host, port, database = self.aws_rds_cluster('Endpoint', 'caseworker-' + self.environment)
        user, password = self.aws_secrets_rds_login(environment)
        try:
            print('Connecting to the PostgreSQL database...')
            connection = self.connect(
                host,
                port,
                user,
                password,
                database
            )
            print('Connected to the PostgreSQL database!')
            return connection
        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error)
        return


class Exporter(AwsBase):


    def generateDirect(self, host, port, user, password, database):
        try:
            print('Connecting to the PostgreSQL database...')
            connection = self.connect(
                host,
                port,
                user,
                password,
                database
            )
            print('Connected to the PostgreSQL database!')
            self.runner(connection)
        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error)
        return self


    def getAllClaims(self, cur):
        select = "SELECT claim.id as ID, status, json_data->'applicant' as applicant_type, json_data->'donor'->'current'->'name' as donor_name,  json_data->'donor'->'current'->'dob' as donor_dob, json_data->'attorney'->'current'->'name' as attorney_name, json_data->'case-number'->'poa-case-number' as lpa_id, payment.amount as amount,payment.processed_datetime as date_paid FROM claim LEFT JOIN payment on payment.claim_id = claim.id ORDER BY id DESC LIMIT 5"
        print('Running SQL:\n' + select)

        cur.execute(select)

        # map the res to a dict
        cols = ['ID', 'status', 'applicant_type', 'donor_name', 'donor_dob', 'attorney_name', 'lpa_id', 'amount', 'date_paid']
        records = [dict(zip(cols, row)) for row in cur.fetchall()]

        return records

    def runner(self, connection):
        cur = connection.cursor()
        print('Finding all claims')
        records = self.getAllClaims(cur)
        print('Found {} records'.format( len(records) ) )

        az = dict.fromkeys(string.ascii_lowercase, [] )
        az['others'] = []

        for row in records:
            donorName = "{} {} {}".format(row['donor_name']['title'], row['donor_name']['first'], row['donor_name']['last'])
            attorneyName = "{} {} {}".format(row['attorney_name']['title'], row['attorney_name']['first'], row['attorney_name']['last'])
            last = row['donor_name']['last']
            char = last[0].lower()
            # replace the names
            row['donor_name'] = donorName
            row['attorney_name'] = attorneyName

            key = char if char in az.keys() else 'others'
            print('{} = {} --> {}'.format(row['ID'], last, key))
            found = az.get(key) or []
            found.append(row)
            az[key] = found

        print(az)
        return


def main():
    parser = argparse.ArgumentParser(description="Exports a spreadsheet containing all claims.")

    parser.add_argument("--dbHost",
                        default="",
                        help="The postgres host")
    parser.add_argument("--dbPort",
                        default="",
                        help="The postgres port")
    parser.add_argument("--dbUser",
                        default="",
                        help="The postgres user")
    parser.add_argument("--dbPwd",
                        default="",
                        help="The postgres password")
    parser.add_argument("--db",
                        default="",
                        help="The postgres database")



    args = parser.parse_args()
    runner = Exporter()

    if args.dbUser and args.dbHost:
        runner.generateDirect(
            args.dbHost,
            args.dbPort,
            args.dbUser,
            args.dbPwd,
            args.db
        )


if __name__ == "__main__":
    main()
