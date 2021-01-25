import csv
import argparse
from notifications_python_client.notifications import NotificationsAPIClient

class ProcessNotifications:

    def __init__(self, args):
        self.filename = args.filename
        self.notifications_client = NotificationsAPIClient(args.notify_api_key)
        self.sms_template_id = args.telephone_template_id
        self.email_template_id = args.email_template_id

    def processRow(self,row):
        personalisation = {}
        # print('processing row..')
        # print (row)
        contact = row["contact"]
        contact_type = row["contact_type"]
        # print (row['contact'])
        # print (row['contact_type'])
        for col in row:
            #personalisation
            if (col!= 'contact_type' and col != 'contact'):
                # print("{0}:{1}".format(col,row.get(col)))
                personalisation[col] = row.get(col)
            else:
                continue

        # print(personalisation)
        response = {}
        if contact_type == "telephone":
            response = self.notifications_client.send_sms_notification(
            phone_number=contact,
            template_id=self.sms_template_id,
            personalisation = personalisation
        )
        elif contact_type == "email":
            response = self.notifications_client.send_email_notification(
            email_address=contact,
            template_id=self.email_template_id,
            personalisation=personalisation
        )
        else:
            print ("unknown type of contact: {}".format(row["contact_type"]))

        print(response)

    def ProcessFile(self):
        print("processing file:{}".format(self.filename))
        with open(self.filename, 'r') as file:
            csv_file = csv.DictReader(file)
            for row in csv_file:
                self.processRow(dict(row))

def main():
    parser = argparse.ArgumentParser(
        description="Send notifications to .gov notify")

    parser.add_argument(
        "--notify_api_key",
        help=".Gov Notify API Key",
    )
    parser.add_argument(
        "--filename",
        help="Filename to process",
    )

    parser.add_argument(
        "--telephone_template_id",
        help="telephone template id"
    )

    parser.add_argument(
        "--email_template_id",
        help="email template id"
    )

    args = parser.parse_args()
    print(args)
    processor = ProcessNotifications(args)

    processor.ProcessFile()

if __name__ == "__main__":
    main()
