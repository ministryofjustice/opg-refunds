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

        contact = row["contact"]
        contact_type = row["contact_type"]

        for col in row:
            #personalisation
            if col!= 'contact_type' or col != 'contact':
                personalisation[col] = row[col]
            else:
                continue

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

    processor = ProcessNotifications(args)

    processor.ProcessFile()

