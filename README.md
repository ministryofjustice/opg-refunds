# opg-refunds-public-front
opg-refunds-public-front

## Generate Private Beta Invite Links

This script will generate invite links, given:

- key: A key used to sign (hmac) the links. Should be a 256bit hex value.
- startId: The ID of the first link to generate.
- count: The number of links to generate
- The date at the end of which the links will expire.

`php scripts/generate-beta-links.php --hex-key <key> --start-from <startId> --number-to-generate <count> --expire <date>`

For example:
```
php scripts/generate-beta-links.php \
--hex-key 58bdb3300a4bf0710746d01cd513ddb232aaf2f80ab55f03f53d4029ac8541ce \
--start-from 100 \
--number-to-generate 10 \
--expire 2018-01-15
```

This generates 10 links, IDs between 100 - 109, which expire at 2018-01-15 23:59:59, all signed with the key.


## GOV.UK Assets

Dependencies: Yarn

`yarn` to install dependencies

The project uses the following npm packages to generate the styles and necessary JavaScript.

- govuk_template
- govuk_elements
- govuk_frontend_toolkit

`yarn run build` will:

- concatenate and compress the JavaScript into:
  - main.js (all thirdparty scripts)
  - application.js
- generate the CSS files
- copy the assets from govuk_template to the public folder

Until there is a CI, the built files will be checked in.

License
-------

The LPA Refund project is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
