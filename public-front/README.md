# opg-refunds-public-front
opg-refunds-public-front

## GOV.UK Assets

Dependencies: NPM

`npm i` to install dependencies

The project uses the following npm packages to generate the styles and necessary JavaScript.

- govuk_template
- govuk_elements
- govuk_frontend_toolkit

`npm run build` will:

- concatenate and compress the JavaScript into:
  - main.js (all thirdparty scripts)
  - application.js
- generate the CSS files
- copy the assets from govuk_template to the public folder

Until there is a CI, the built files will be checked in.

License
-------

The LPA Refund project is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
