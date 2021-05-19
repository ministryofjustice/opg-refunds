import argparse
import boto3
from botocore.exceptions import ClientError
import pg8000
import string
import xlsxwriter
from datetime import datetime

# Group all the AWS related tooling to this class
class AwsBase:
    aws_region = 'eu-west-1'
    aws_iam_session = ''
    aws_secret = 'opg_refunds_db_cases_migration_password'
    # create a session from account / role arn
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

    # use the session and namespace to create a client,
    # allow easy creation for sts / rds etc
    def get_aws_client(self, namespace, session):
        return boto3.client(
            namespace,
            region_name=self.aws_region,
            aws_access_key_id=session['Credentials']['AccessKeyId'],
            aws_secret_access_key=session['Credentials']['SecretAccessKey'],
            aws_session_token=session['Credentials']['SessionToken'])

# tooling for conneting diretly to psql database
class DBConnect:
    def connect(self, host, port, user, password, database):
        return pg8000.connect(
                user=user,
                host=host,
                port=port,
                database=database,
                password=password,
                tcp_keepalive=True)

# generic spreadsheet tooling
class SpreadsheetBase:
    # object to set widths of each column
    colRangeWidths = {}

    # find the column letter presuming cols started at A
    def maxLetter(self, data):
        #A = 65
        letter = 65 + len(data)-1
        return chr(letter)

    # find the letter for this column header
    # - assuming cols start at a
    # - used for excel cell locations (A1:G2 etc)
    def letter(self, headers, col):
        index = headers.index(col.upper())
        letter = (65 + index)
        char = chr(letter)
        return char

    # use the data passed to create
    # - headers (formatted for use in .add_table)
    # - keys (flat headers)
    # - table (objects converted to lists of values in order)
    # - selector (the excel range selector that covers table range - A1:C7 etc)
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

    # Sets the width of all columns in the colRangeWidths
    # object
    def colWidths(self, worksheet, headers):
        # set column widths
        for col, width in self.colRangeWidths.items():
            a = self.letter(headers, col)
            selectors = f'{a}:{a}'
            worksheet.set_column(selectors, width)
        return self

    # Create the spreadsheet from the AZ data set
    # - the az data is a dict of lists, with the key being a
    #   letter (A-Z or 'others') and the list being data from
    #   to write in that tab
    def spreadsheet(self, az):
        # now generate the spreadsheet - probably need to add timestamp here
        workbook = xlsxwriter.Workbook('Export.xlsx')
        for letter in az.keys():
            data = az.get(letter) or []
            letter = letter.upper()
            length = len(data)
            print(f'[{letter}] has [{length}] records')
            worksheet = workbook.add_worksheet(letter)
            # if there is data, then add to the sheet
            if(len(data) > 0):
                formattedHeaders, flatHeaders, table, selector = self.forTable(data)
                self.colWidths(worksheet, flatHeaders)
                print('\tAdding table.')
                worksheet.add_table(selector, {'data': table, 'columns': formattedHeaders })

        # save the workbook
        workbook.close()
        return self


# extends multiple classes and adjust the values to get data and save it as a
# spreadsheet
class Exporter(AwsBase, DBConnect, SpreadsheetBase):

    # set widths for the excel columns
    colRangeWidths = {
        'R Ref': 18,
        'Id': 15,
        'Status': 12,
        'Applicant Type': 18,
        'Donor Name': 35,
        'Donor Dob': 15,
        'Attorney Name': 35,
        'Lpa Ref': 20,
        'Amount': 15,
        'Date Paid': 15,
        'Applicant': 35,
        'Created': 18,
        'System': 12
    }

    # main function
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

    # return the single character for grouping if this row
    # - currently uses the first letter of the last name
    def char(self, row):
        last = row['donor_name']['last']
        return last[0].lower()

    # create the single string name for donor & attorney based on the json object from the db
    def names(self, row):
        donorName = "{} {} {}".format(row['donor_name']['title'], row['donor_name']['first'], row['donor_name']['last'])
        attorneyName = "{} {} {}".format(row['attorney_name']['title'], row['attorney_name']['first'], row['attorney_name']['last'])
        return donorName, attorneyName

    # create the R ref using the DB claim.id and formatting it with
    # a 'R' prefix and then space every 4 chars
    def ref(self, row):
        n=4
        id = 'R' + str(row['ID'])
        return ' '.join([id[i:i+n] for i in range(0, len(id), n)])

    # SQL call
    # uses zip to create a list of dicts with column names
    def getAllClaims(self, cur):
        select = "SELECT 'R' as r_ref, json_data->'case-number'->'poa-case-number' as lpa_ref, status, 'app' applicant, json_data->'donor'->'current'->'name' as donor_name,  json_data->'donor'->'current'->'dob' as donor_dob, json_data->'attorney'->'current'->'name' as attorney_name, json_data->'applicant' as applicant_type, payment.amount as amount,payment.processed_datetime as date_paid, claim.created_datetime as created, claim.id as ID, poa.system as system, poa.case_number as poa_case_number FROM claim LEFT JOIN payment on payment.claim_id = claim.id LEFT JOIN poa on poa.claim_id = claim.id ORDER BY created_datetime DESC LIMIT 5"

        cur.execute(select)

        # map the res to a dict
        cols = ['r_ref', 'lpa_ref', 'status', 'applicant', 'donor_name', 'donor_dob', 'attorney_name', 'applicant_type', 'amount', 'date_paid', 'created', 'ID', 'system', 'poa_case_number']
        records = [dict(zip(cols, row)) for row in cur.fetchall()]

        return records

    # Get and format the data into a A-Z dict with each being a list
    # - does some formatting on the data for output to excel
    def data(self, records):
        az = dict.fromkeys(string.ascii_lowercase, [] )
        az['others'] = []

        for row in records:
            # get the letter
            char = self.char(row)
            # replace the names
            row['donor_name'], row['attorney_name'] = self.names(row)
            # set the applicant name
            row['applicant'] = row['donor_name'] if row['applicant_type'] == 'donor' else row['attorney_name']
            # generate the R-Ref
            row['r_ref'] = self.ref(row)
            # convert date
            row['created'] = row['created'].strftime("%Y-%m-%d %H:%M")
            # change the case number to the poa reference and remove row['poa_case_number']
            if row['poa_case_number']: row['lpa_ref'] = row['poa_case_number']
            del row['poa_case_number']

            key = char if char in az.keys() else 'others'
            found = az.get(key) or []
            found.append(row)
            az[key] = found

        return az

    # wrapping function
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
