from faker.providers import person
from faker.providers import address
from realbrowserlocusts import FirefoxLocust
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC


from locust import TaskSet, task
from random import seed
from random import randint


from faker import Faker
fake = Faker('en_GB')
fake.add_provider(person)
fake.add_provider(address)


class LocustUserBehavior(TaskSet):
    def refuse_protected(self):
        protected_environments = [
            "https://claim-power-of-attorney-refund.service.gov.uk/",
            "https://public-front.refunds.opg.service.justice.gov.uk/start",
        ]
        if self.locust.host in protected_environments:
            print("do not run load tests against protected environments")
            exit(1)

    def define_actors(self):
        seed(1)
        self.donor = {
            'last_name': fake.last_name(),
            'first_name': fake.first_name(),
            'title': fake.prefix(),
            'phone_number': "07700900000",
            'address_1': fake.street_address(),
            'address_2': fake.secondary_address(),
            'address_3': fake.city_prefix(),
            'postcode': fake.postcode(),
            'email': "simulate-delivered@notifications.service.gov.uk",
            'dob_day': randint(1, 28),
            'dob_month': randint(1, 12),
            'dob_year': randint(1930, 1990),
            'casenumber': randint(700000000000, 739999999999),
            'account': randint(10000000, 99999999),
            'sort_code': "205132"
        }
        self.attorney = {
            'last_name': fake.last_name(),
            'first_name': fake.first_name(),
            'title': fake.prefix(),
            'phone_number': "07700900111",
            'address_1': fake.street_address(),
            'address_2': fake.secondary_address(),
            'address_3': fake.city_prefix(),
            'postcode': fake.postcode(),
            'email': "simulate-delivered-2@notifications.service.gov.uk",
            'dob_day': randint(1, 28),
            'dob_month': randint(1, 12),
            'dob_year': randint(1930, 1990),
            'casenumber': randint(700000000000, 739999999999),
            'account': randint(10000000, 99999999),
            'sort_code': "205132"
        }

    def open_start_homepage(self):
        self.client.get("{}/when-were-fees-paid".format(self.locust.host))

    def when_were_fees_paid(self):
        self.client.find_element(By.ID, 'id-fees-in-range-yes').click()
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def donor_is_applying(self):
        self.client.find_element(
            By.CSS_SELECTOR, ".multiple-choice:nth-child(2)").click()
        self.client.find_element(By.ID, "id-who-donor").click()
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def your_details_donor(self):
        self.client.find_element(By.ID, "id-title").click()
        self.client.find_element(
            By.ID, "id-title").send_keys(self.donor['title'])
        self.client.find_element(
            By.ID, "id-first").send_keys(self.donor['first_name'])
        self.client.find_element(
            By.ID, "id-last").send_keys(self.donor['last_name'])
        self.client.find_element(
            By.ID, "id-day-dob").send_keys(self.donor['dob_day'])
        self.client.find_element(
            By.ID, "id-month-dob").send_keys(self.donor['dob_month'])
        self.client.find_element(
            By.ID, "id-year-dob").send_keys(self.donor['dob_year'])
        self.client.find_element(By.ID, "id-address-1").click()
        self.client.find_element(
            By.ID, "id-address-1").send_keys(self.donor['address_1'])
        self.client.find_element(
            By.ID, "id-address-2").send_keys(self.donor['address_2'])
        self.client.find_element(
            By.CSS_SELECTOR, "fieldset:nth-child(5)").click()
        self.client.find_element(By.ID, "id-address-3").click()
        self.client.find_element(
            By.ID, "id-address-3").send_keys(self.donor['address_3'])
        self.client.find_element(
            By.ID, "id-address-postcode").send_keys(self.donor['postcode'])
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def attorney_details(self):
        self.client.find_element(By.ID, "id-title").click()
        self.client.find_element(
            By.ID, "id-title").send_keys(self.attorney['title'])
        self.client.find_element(
            By.ID, "id-first").send_keys(self.attorney['first_name'])
        self.client.find_element(
            By.ID, "id-last").send_keys(self.attorney['last_name'])
        self.client.find_element(By.ID, "id-day-dob").click()
        self.client.find_element(
            By.ID, "id-day-dob").send_keys(self.attorney['dob_day'])
        self.client.find_element(
            By.ID, "id-month-dob").send_keys(self.attorney['dob_month'])
        self.client.find_element(
            By.ID, "id-year-dob").send_keys(self.attorney['dob_year'])
        self.client.find_element(By.CSS_SELECTOR, ".form").click()
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def i_have_case_number(self):
        self.client.find_element(
            By.ID, "id-have-poa-case-number-yes").click()
        self.client.find_element(By.ID, "id-poa-case-number").click()
        self.client.find_element(
            By.ID, "id-poa-case-number").send_keys(self.donor['casenumber'])
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def give_donor_postcode(self):
        self.client.find_element(
            By.ID, "id-postcode-options-donor-postcode").click()
        self.client.find_element(By.ID, "id-donor-postcode").click()
        self.client.find_element(
            By.ID, "id-donor-postcode").send_keys(self.donor['postcode'])
        self.client.find_element(
            By.CSS_SELECTOR, "#id-postcode-options-donor-postcode-info > .form-group").click()
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def your_contact_details(self):
        self.client.find_element(By.ID, "id-email").click()
        self.client.find_element(
            By.ID, "id-email").send_keys(self.donor['email'])
        self.client.find_element(
            By.ID, "id-phone").send_keys(self.donor['phone_number'])
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def donors_bank_details(self):
        self.client.find_element(By.ID, "id-name").click()
        self.client.find_element(
            By.ID, "id-name").send_keys(self.donor['first_name'])
        self.client.find_element(
            By.ID, "id-account-number").send_keys(self.donor['account'])
        self.client.find_element(By.ID, "id-sort-code").click()
        self.client.find_element(
            By.ID, "id-sort-code").send_keys(self.donor['sort_code'])
        self.client.find_element(
            By.CSS_SELECTOR, ".form-group:nth-child(6)").click()
        self.client.find_element(By.CSS_SELECTOR, ".button").click()

    def submit_claim(self):
        self.client.find_element(By.CSS_SELECTOR, ".button").click()
        self.client.find_element(By.LINK_TEXT, "Done").click()

    def public_front_journey_donor(self):
        self.refuse_protected()
        self.define_actors()
        self.open_start_homepage()
        self.when_were_fees_paid()
        self.donor_is_applying()
        self.your_details_donor()
        self.attorney_details()
        self.i_have_case_number()
        self.give_donor_postcode()
        self.your_contact_details()
        self.donors_bank_details()
        self.submit_claim()

    @task(1)
    def user_journey_public_front(self):
        self.client.timed_event_for_locust(
            "Go to", "start page", self.public_front_journey_donor)


class LocustUser(FirefoxLocust):

    timeout = 20
    min_wait = 1
    max_wait = 10
    screen_width = 1200
    screen_height = 600
    task_set = LocustUserBehavior
