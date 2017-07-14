# opg-refunds-public-front
opg-refunds-public-front

## GOV.UK Assets

Dependencies: Yarn or NPM

`yarn` to install dependencies

### govuk_template
Version: 0.22.0

The project's default template is based on the `govuk_template` HTML. The `govuk_template`'s assets have been manually copied into `public/assets/govuk_elements` for project expediency.

### govuk_elements
Version: 3.0.2

`build:css:govuk_elements` will build the govuk_elements SASS and store the generated stylesheets in public/. Until there is a CI, the generated CSS will be checked in.

To use a different version of `govuk_elements`, edit the tag number in dependencies section of `package.json` accordingly, re-run `yarn` followed by `build:css:govuk_elements`.

License
-------

The LPA Refund project is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
