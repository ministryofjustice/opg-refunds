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
    # object to set column order and output formats
    columnMeta = {}

    # find the column letter presuming cols started at A
    def maxLetter(self, data):
        #A = 65
        letter = 65 + len(data)-1
        return chr(letter)

    # find the letter for this column header
    # - assuming cols start at a
    # - used for excel cell locations (A1:G2 etc)
    def letter(self, headers, col):
        index = headers.index(col)
        letter = (65 + index)
        char = chr(letter)
        return char


    def asTableHeading(self, field):
        # get new name
        meta = self.columnMeta[field]
        return meta['name'] if "name" in meta else field.replace('_', ' ').upper()

    # use the data passed to generate header and table body array
    # and provider meta info (num cols, rows etc)
    def forTable(self, data):
        # these are the raw keys - eg date_claim_made
        keys = list ( data[0].keys() )
        # this uses cap / pretty header names
        tableHeaders = list( map(lambda val: {'header': self.asTableHeading(val) }, keys ) )

        # convert the data into table row list for spreadsheet
        tableRows = []
        for row in data: tableRows.append( list(row.values()) )
        # the largest column in use - issue here with wrapping past Z
        maxCol = self.maxLetter(keys)
        maxRow = len(tableRows) +1 # to allow for headers
        selector = f'A1:{maxCol}{maxRow}'

        return keys, tableHeaders, tableRows, selector

    # Sets the width of all columns in the colRangeWidths
    # object
    def colWidths(self, worksheet, keys):

        for field, meta in self.columnMeta.items():
            excluded = True if 'exclude' in meta and meta['exclude'] == True else False
            # if theres a width, set it
            if 'width' in meta and not excluded:
                column = self.letter(keys, field)
                selector = f'{column}:{column}'
                worksheet.set_column(selector, meta['width'])

        return self

    # Create the spreadsheet from the AZ data set
    # - the az data is a dict of lists, with the key being a
    #   letter (A-Z or 'others') and the list being data from
    #   to write in that tab
    def spreadsheet(self, az):
        total = 0
        # now generate the spreadsheet - probably need to add timestamp here
        workbook = xlsxwriter.Workbook('Export.xlsx')
        for letter in az.keys():
            data = az.get(letter) or []
            letter = letter.upper()
            length = len(data)
            total += length
            print(f'[{letter}] has [{length}] records')
            worksheet = workbook.add_worksheet(letter)

            # if there is data, then add to the sheet
            if(len(data) > 0):
                rowKeys, tableHeaders, tableRows, selector = self.forTable(data)
                self.colWidths(worksheet, rowKeys)
                worksheet.add_table(selector, {'data': tableRows, 'columns': tableHeaders })
                print(f'\tAdded table for: {selector}')
        # save the workbook
        workbook.close()
        print(f'[{total}] records added to spreadsheet')
        return self


# extends multiple classes and adjust the values to get data and save it as a
# spreadsheet
class Exporter(AwsBase, DBConnect, SpreadsheetBase):
    # Meta data to describe a mapping between the sql call
    # and how to output to the spreadsheet
    columnMeta = {
        "r_ref": { "width": 18, "callback": "idToRRef" },
        "lpa_ref": { "width": 20}, # add a callback to getLPARef to get ref with counter ($meris/$count)
        "status": {"width": 12, "exclude": True},
        "applicant": {"width": 35, "name": "APPLICANT NAME", "callback": "getApplicant,prettyName"},
        "donor_name": {"width": 35, "callback": "prettyName"},
        "donor_dob": {"width": 15},
        "attorney_name": {"width": 35, "callback": "prettyName", "exclude": True},
        "applicant_type": {"width": 18, "exclude": True},
        "amount": {"width": 18},
        "date_claim_made": {"width": 19, "callback": "asDateStr"},
        "date_finished": {"width": 18, "callback": "asDateStr"},
        "ID": {"width": 15, "exclude": True},
        "system": {"width": 12, "exclude": True},
        "poa_case_number": {"exclude": True},
        "json_data": {"exclude": True}
    }

    #
    def prettyName(self, row, field):
        name = row[field]
        row[field] = '{} {} {}'.format(
            name['title'], name['first'], name['last']
        )
        return row

    # create the R ref using the DB claim.id and formatting it with
    # a 'R' prefix and then space every 4 chars
    def idToRRef(self, row, field):
        n=4
        id = 'R' + str(row['ID'])
        ref = ' '.join([id[i:i+n] for i in range(0, len(id), n)])
        row[field] = ref
        return row

    #
    def getApplicant(self, row, field):
        type = row[field]
        if type == 'donor' :
            row[field] = row['donor_name']
        elif type == 'attorney':
            row[field] = row['attorney_name']
        elif row['json_data'][type]:
            row[field] = row['json_data'][type]['name']
        return row

    #
    def getLPARef(self, row, field):
        if 'poa_case_number' in row and row['poa_case_number'] is not None:
            row[field] = row['poa_case_number']
        return row
    #
    def asDateStr(self, row, field):
        if field in row and row[field] is not None:
            row[field] = row[field].strftime("%Y-%m-%d %H:%M")
        return row
    # SQL call
    # uses zip to create a list of dicts with column names
    def getAllClaims(self, cur, restricted):
        where = "AND claim.id IN (48277952311,86189719970,84007461967,23453788854,49291803796) " if restricted == True else ""

        select = f"SELECT 'R' as r_ref, json_data->'case-number'->'poa-case-number' as lpa_ref, status, json_data->'applicant' as applicant, json_data->'donor'->'current'->'name' as donor_name,  json_data->'donor'->'current'->'dob' as donor_dob, json_data->'attorney'->'current'->'name' as attorney_name, json_data->'applicant' as applicant_type, payment.amount as amount,claim.created_datetime as date_claim_made, claim.finished_datetime as date_finished, claim.id as ID, poa.system as system, poa.case_number as poa_case_number, json_data FROM claim LEFT JOIN payment on payment.claim_id = claim.id LEFT JOIN poa on poa.claim_id = claim.id WHERE claim.status = 'accepted' {where}ORDER BY created_datetime DESC"

        cur.execute(select)

        # map the res to a dict
        cols = self.columnMeta.keys()
        records = [dict(zip(cols, row)) for row in cur.fetchall()]

        return records


    def processRow(self, row):
        char = row['donor_name']['last'][0].lower()
        processed = {}
        for field, meta  in self.columnMeta.items():
            # update the row based on the callback functions listed against this column
            # - generally to convert names json & dates to strings for excel
            if 'callback' in meta:
                for funcName in meta['callback'].split(','):
                    row = getattr(self, funcName)(row, field)

            # dont add this data to the processed dict if this flag is set
            # but use the same field name
            if 'exclude' not in meta: processed[field] = row[field]

        return char, processed

    # Get and format the data into a A-Z dict with each being a list
    # - does some formatting on the data for output to excel
    def data(self, records):
        az = dict.fromkeys(string.ascii_lowercase, [] )
        az['others'] = []

        for row in records:
            # get the letter
            char, row = self.processRow(row)

            key = char if char in az.keys() else 'others'
            found = az.get(key) or []
            found.append(row)
            az[key] = found

        return az

    # main function
    def generate(self, host, port, user, password, database, restricted):
        try:
            print('Connecting to the PostgreSQL database...')
            connection = self.connect(
                host, port, user, password, database
            )
            print('Connected to the PostgreSQL database!')

            cur = connection.cursor()
            print('Finding all claims')
            records = self.getAllClaims(cur, restricted)

            length = len(records)
            print(f'Found {length} records')

            az = self.data(records)
            self.spreadsheet(az)

        except (Exception, pg8000.Error) as error:
            print("an error...")
            print(error)
        return self


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

    # flat to limit the sql with a where for certain accounts
    parser.add_argument('--restrictions', dest='restricted', action='store_true')
    parser.add_argument('--no-restrictions', dest='restricted', action='store_false')
    parser.set_defaults(restricted=True)


    args = parser.parse_args()
    runner = Exporter()

    if args.dbUser and args.dbHost:
        runner.generate(
            args.dbHost,
            args.dbPort,
            args.dbUser,
            args.dbPwd,
            args.db,
            args.restricted
        )


if __name__ == "__main__":
    main()
