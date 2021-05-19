import argparse
import boto3
from botocore.exceptions import ClientError
import pg8000
import string
import xlsxwriter


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


class SpreadsheetBase:

    colRangeWidths = {}

    def maxLetter(self, data):
        #A = 65
        letter = 65 + len(data)-1
        return chr(letter)


    def letter(self, headers, col):
        index = headers.index(col.upper())
        letter = (65 + index)
        char = chr(letter)
        print(f'\t[{col}]--> [char:{letter}] = [col:{char}]' )
        return char

    def forTable(self, data):
        # generate flat headers with _ and title case
        keys = list( map(lambda val:  val.replace('_', ' ').upper() , data[0].keys() ) )
        # convert for add_table columns option
        headers = list( map(lambda val: {'header': val}, keys ) )
        # generate the table body
        table = []
        for row in data: table.append( list(row.values()) )
        # get max letter of the table
        col = self.maxLetter(headers)
        row = len(table) + 1
        selector = 'A1:{}{}'.format(col, row)
        # return headers, table body and the excel selector
        return headers, keys, table, selector


    def colWidths(self, worksheet, headers):
        # set column widths
        for col, width in self.colRangeWidths.items():
            a = self.letter(headers, col)
            selectors = f'{a}:{a}'
            print(f'\tSetting col width to [{width}] for [{selectors}]')
            worksheet.set_column(selectors, width)

        return self

    def spreadsheet(self, az):
        # now generate the spreadsheet - probably need to add timestamp here
        workbook = xlsxwriter.Workbook('Export.xlsx', {'remove_timezone': True})
        for letter in az.keys():
            data = az.get(letter) or []
            letter = letter.upper()
            length = len(data)
            print(f'[{letter}] has [{length}] records')
            worksheet = workbook.add_worksheet(letter)
            # if there is data, then add to the sheet
            if(len(data) > 0):
                formattedHeaders, flatHeaders, table, selector = self.forTable(data)
                print(f'\tTable range [{letter}] -> {selector}')
                self.colWidths(worksheet, flatHeaders)
                print('\tAdding table.')
                worksheet.add_table(selector, {'data': table, 'columns': formattedHeaders })

        # save the workbook
        workbook.close()
        return self


class Exporter(AwsBase, SpreadsheetBase):

    colRangeWidths = {
        'Id': 15,
        'Status': 12,
        'Applicant Type': 18,
        'Donor Name': 35,
        'Donor Dob': 15,
        'Attorney Name': 35,
        'Lpa Id': 20,
        'Amount': 15,
        'Date Paid': 15,
        'Applicant': 35,
        'Created': 18
    }


    def generate(self, host, port, user, password, database):
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
        select = "SELECT claim.id as ID, status, 'app' applicant, json_data->'donor'->'current'->'name' as donor_name,  json_data->'donor'->'current'->'dob' as donor_dob, json_data->'attorney'->'current'->'name' as attorney_name, json_data->'case-number'->'poa-case-number' as lpa_Id, json_data->'applicant' as applicant_type, payment.amount as amount,payment.processed_datetime as date_paid, claim.created_datetime as created FROM claim LEFT JOIN payment on payment.claim_id = claim.id ORDER BY created_datetime DESC LIMIT 5"

        cur.execute(select)

        # map the res to a dict
        cols = ['ID', 'status', 'applicant', 'donor_name', 'donor_dob', 'attorney_name', 'lpa_Id', 'applicant_type', 'amount', 'date_paid', 'created']
        records = [dict(zip(cols, row)) for row in cur.fetchall()]

        return records

    def data(self, records):
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
            row['applicant'] = row['donor_name'] if row['applicant_type'] == 'donor' else row['attorney_name']

            key = char if char in az.keys() else 'others'
            found = az.get(key) or []
            found.append(row)
            az[key] = found

        return az


    def runner(self, connection):
        cur = connection.cursor()
        print('Finding all claims')
        records = self.getAllClaims(cur)
        length = len(records)
        print(f'Found {length} records')
        az = self.data(records)
        self.spreadsheet(az)

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
        runner.generate(
            args.dbHost,
            args.dbPort,
            args.dbUser,
            args.dbPwd,
            args.db
        )


if __name__ == "__main__":
    main()
