# JSON Guard

[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Documentation][ico-docs]][link-docs]

This package is a validator for [JSON Schema](http://json-schema.org/).  It fully supports draft 4 of the specification.

Notable Features:

- Passes the entire [draft 4 JSON Schema Test Suite](https://github.com/json-schema/JSON-Schema-Test-Suite).
- Fully customizable with custom rule sets.
- Helpful error messages with JSON Pointers.

## Install

### Via Composer

```bash
composer require league/json-guard
```

## Usage

Complete documentation is available [here](http://json-guard.thephpleague.com/).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Testing

You need to run a web server while testing.

```bash
$ composer test-server
```

Once the server is running, you can run the test suite.

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email matthew.james.allan@gmail.com instead of using the issue tracker.

## Credits

- [Matt Allan][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-travis]: https://travis-ci.org/thephpleague/json-guard
[link-scrutinizer]: https://scrutinizer-ci.com/g/thephpleague/json-guard/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thephpleague/json-guard
[link-docs]: http://json-guard.thephpleague.com/
[link-author]: https://github.com/thephpleague
[link-contributors]: ../../contributors

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thephpleague/json-guard/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thephpleague/json-guard.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thephpleague/json-guard.svg?style=flat-square
[ico-docs]: https://img.shields.io/badge/Docs-Latest-brightgreen.svg?style=flat-square
