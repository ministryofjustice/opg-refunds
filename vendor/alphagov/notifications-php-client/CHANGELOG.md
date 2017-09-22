## [3.1.0] - 2017-05-10
### Changed

* Added new methods for managing templates:
    * `$this->getTemplate` - retrieve a single template
    * `$this->getTemplateVersion` - retrieve a specific version for a desired template
    * `$this->listTemplates` - retrieve all templates (can filter by type)
    * `$this->previewTemplate` - preview a template with personalisation applied

## [1.0.0] - 2016-12-16
### Changed
* Using v2 of the notification-api.

* Update to `$this->sendSms()`:
    * Added `reference`: an optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single notification or a batch of notifications.
    * Updated method signature:

 ```php
public function sendSms( $phoneNumber, $templateId, array $personalisation = array(), $reference = '' )
```
     * Where `$personalisation` and `$reference` can be omitted.

* Update to `$this->sendEmail()`:
    * Added `reference`: an optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single notification or a batch of notifications.
    * Updated method signature:

 ```php
public function sendEmail( $emailAddress, $templateId, array $personalisation = array(), $reference = '' )
```
     * Where `$personalisation` and `$reference` can be omitted.
* Updated `$this->listNotifications()`
    * Notifications can now be filtered by `reference` and `older_than`, see the README for details.

# Prior versions

Changelog not recorded - please see pull requests on github.
