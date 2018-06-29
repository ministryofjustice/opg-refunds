<?php

namespace PackageVersions;

/**
 * This class is generated by ocramius/package-versions, specifically by
 * @see \PackageVersions\Installer
 *
 * This file is overwritten at every run of `composer install` or `composer update`.
 */
final class Versions
{
    const ROOT_PACKAGE_NAME = 'ministryofjustice/opg-refunds-caseworker-api';
    const VERSIONS = array (
  'alphagov/notifications-php-client' => '1.6.2@66f5051fe549a5c84fc16844c6577dfa709d707f',
  'aws/aws-sdk-php' => '3.62.5@75113b0ba22fffd968c45f06ba20fa94509dc973',
  'container-interop/container-interop' => '1.2.0@79cbf1341c22ec75643d841642dd5d6acd83bdb8',
  'dasprid/container-interop-doctrine' => '1.1.0@b9f3afc00ce997e469d7fdd6fed7b8d400763290',
  'doctrine/annotations' => 'v1.6.0@c7f2050c68a9ab0bdb0f98567ec08d80ea7d24d5',
  'doctrine/cache' => 'v1.7.1@b3217d58609e9c8e661cd41357a54d926c4a2a1a',
  'doctrine/collections' => 'v1.5.0@a01ee38fcd999f34d9bfbcee59dbda5105449cbf',
  'doctrine/common' => 'v2.8.1@f68c297ce6455e8fd794aa8ffaf9fa458f6ade66',
  'doctrine/dbal' => 'v2.7.1@11037b4352c008373561dc6fc836834eed80c3b5',
  'doctrine/inflector' => 'v1.3.0@5527a48b7313d15261292c149e55e26eae771b0a',
  'doctrine/instantiator' => '1.1.0@185b8868aa9bf7159f5f953ed5afb2d7fcdc3bda',
  'doctrine/lexer' => 'v1.0.1@83893c552fd2045dd78aef794c31e694c37c0b8c',
  'doctrine/migrations' => 'v1.8.1@215438c0eef3e5f9b7da7d09c6b90756071b43e6',
  'doctrine/orm' => 'v2.6.1@87ee409783a4a322b5597ebaae558661404055a7',
  'fig/http-message-util' => '1.1.2@20b2c280cb6914b7b83089720df44e490f4b42f0',
  'firebase/php-jwt' => 'v3.0.0@fa8a06e96526eb7c0eeaa47e4f39be59d21f16e1',
  'guzzlehttp/guzzle' => '6.3.3@407b0cb880ace85c9b63c5f9551db498cb2d50ba',
  'guzzlehttp/promises' => 'v1.3.1@a59da6cf61d80060647ff4d3eb2c03a2bc694646',
  'guzzlehttp/psr7' => '1.4.2@f5b8a8512e2b58b0071a7280e39f14f72e05d87c',
  'http-interop/http-middleware' => '0.4.1@9a801fe60e70d5d608b61d56b2dcde29516c81b9',
  'ministryofjustice/opg-refunds-caseworker-datamodels' => '2.1.0@3578f2cfe90c9e329e9e6575deb3079bb88999e7',
  'ministryofjustice/opg-refunds-logger' => '2.0.1@3ff07c27536232a29a5ea23efb22a8819892e291',
  'mtdowling/jmespath.php' => '2.4.0@adcc9531682cf87dfda21e1fd5d0e7a41d292fac',
  'nikic/fast-route' => 'v1.3.0@181d480e08d9476e61381e04a71b34dc0432e812',
  'ocramius/package-versions' => '1.3.0@4489d5002c49d55576fa0ba786f42dbb009be46f',
  'ocramius/proxy-manager' => '2.2.0@81d53b2878f1d1c40ad27270e64b51798485dfc5',
  'opsway/doctrine-dbal-postgresql' => 'v0.8.1@fda403a60653c09637403384485f6db3c2e4ff73',
  'paragonie/random_compat' => 'v2.0.15@10bcb46e8f3d365170f6de9d05245aa066b81f09',
  'php-http/guzzle6-adapter' => 'v1.1.1@a56941f9dc6110409cfcddc91546ee97039277ab',
  'php-http/httplug' => 'v1.1.0@1c6381726c18579c4ca2ef1ec1498fdae8bdf018',
  'php-http/promise' => 'v1.0.0@dc494cdc9d7160b9a09bd5573272195242ce7980',
  'phpoffice/phpspreadsheet' => '1.3.1@aa5b0d0236c907fd8dba0883f3ceb97cc52e46ec',
  'psr/container' => '1.0.0@b7ce3b176482dbbc1245ebf52b181af44c2cf55f',
  'psr/http-message' => '1.0.1@f6561bf28d520154e4b0ec72be95418abe6d9363',
  'psr/log' => '1.0.2@4ebe3a8bf773a19edfe0a84b6585ba3d401b724d',
  'psr/simple-cache' => '1.0.1@408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
  'roave/security-advisories' => 'dev-master@0e4ea9f9e1fd3c6a563524f8f399696d98c7c85a',
  'symfony/console' => 'v4.1.1@70591cda56b4b47c55776ac78e157c4bb6c8b43f',
  'symfony/polyfill-ctype' => 'v1.8.0@7cc359f1b7b80fc25ed7796be7d96adc9b354bae',
  'symfony/polyfill-mbstring' => 'v1.8.0@3296adf6a6454a050679cde90f95350ad604b171',
  'symfony/yaml' => 'v3.4.12@c5010cc1692ce1fa328b1fb666961eb3d4a85bb0',
  'webimpress/composer-extra-dependency' => '0.2.2@31fa56391d30f03b1180c87610cbe22254780ad9',
  'webimpress/http-middleware-compatibility' => '0.1.4@8ed1c2c7523dce0035b98bc4f3a73ca9cd1d3717',
  'wp-cli/php-cli-tools' => 'v0.11.9@766653b45f99c817edb2b05dc23f7ee9a893768d',
  'zendframework/zend-code' => '3.3.0@6b1059db5b368db769e4392c6cb6cc139e56640d',
  'zendframework/zend-component-installer' => '1.1.1@5e9beda3b81d29d4d080b110d67f8c8c44d93605',
  'zendframework/zend-config-aggregator' => '1.1.1@2a08547b64119a73b6700bde3301d978258dfcb5',
  'zendframework/zend-crypt' => '3.3.0@9c2916faa9b2132a0f91cdca8e95b025c352f065',
  'zendframework/zend-diactoros' => '1.8.0@11c9c1835e60eef6f9234377a480fcec096ebd9e',
  'zendframework/zend-escaper' => '2.6.0@31d8aafae982f9568287cb4dce987e6aff8fd074',
  'zendframework/zend-eventmanager' => '3.2.1@a5e2583a211f73604691586b8406ff7296a946dd',
  'zendframework/zend-expressive' => '2.2.1@7d43f0c5e23013be86202f6cf36d9344d99dcc24',
  'zendframework/zend-expressive-fastroute' => '2.2.1@7567d8e53e7f92b740c937e2215d393cdb65feb6',
  'zendframework/zend-expressive-helpers' => '4.2.0@137d863d4741210d05297b4bb1c30264f100ba8f',
  'zendframework/zend-expressive-router' => '2.4.1@e1a00596aa20a29968bdc6ecdf0256c8bfd6e0b5',
  'zendframework/zend-expressive-template' => '1.0.4@23922f96b32ab6e64fc551ec06b81fd047828765',
  'zendframework/zend-log' => '2.10.0@9cec3b092acb39963659c2f32441cccc56b3f430',
  'zendframework/zend-math' => '3.1.0@558806e338ee68575fbe69489c9dcb6d57a1dae0',
  'zendframework/zend-permissions-rbac' => '2.6.0@c10ad55e50f402bf14eb2eb9bc424dd9a44dfc78',
  'zendframework/zend-servicemanager' => '3.3.2@9f35a104b8d4d3b32da5f4a3b6efc0dd62e5af42',
  'zendframework/zend-stdlib' => '3.2.0@cd164b4a18b5d1aeb69be2c26db035b5ed6925ae',
  'zendframework/zend-stratigility' => '2.2.2@840e41d1984e8845c5539c769fedc5e7bb00a4d5',
  'filp/whoops' => '2.2.0@181c4502d8f34db7aed7bfe88d4f87875b8e947a',
  'hamcrest/hamcrest-php' => 'v2.0.0@776503d3a8e85d4f9a1148614f95b7a608b046ad',
  'mockery/mockery' => '1.1.0@99e29d3596b16dabe4982548527d5ddf90232e99',
  'myclabs/deep-copy' => '1.8.1@3e01bdad3e18354c3dce54466b7fbe33a9f9f7f8',
  'phar-io/manifest' => '1.0.1@2df402786ab5368a0169091f61a7c1e0eb6852d0',
  'phar-io/version' => '1.0.1@a70c0ced4be299a63d32fa96d9281d03e94041df',
  'phpdocumentor/reflection-common' => '1.0.1@21bdeb5f65d7ebf9f43b1b25d404f87deab5bfb6',
  'phpdocumentor/reflection-docblock' => '4.3.0@94fd0001232e47129dd3504189fa1c7225010d08',
  'phpdocumentor/type-resolver' => '0.4.0@9c977708995954784726e25d0cd1dddf4e65b0f7',
  'phpspec/prophecy' => '1.7.6@33a7e3c4fda54e912ff6338c48823bd5c0f0b712',
  'phpunit/php-code-coverage' => '6.0.7@865662550c384bc1db7e51d29aeda1c2c161d69a',
  'phpunit/php-file-iterator' => '2.0.1@cecbc684605bb0cc288828eb5d65d93d5c676d3c',
  'phpunit/php-text-template' => '1.2.1@31f8b717e51d9a2afca6c9f046f5d69fc27c8686',
  'phpunit/php-timer' => '2.0.0@8b8454ea6958c3dee38453d3bd571e023108c91f',
  'phpunit/php-token-stream' => '3.0.0@21ad88bbba7c3d93530d93994e0a33cd45f02ace',
  'phpunit/phpunit' => '7.2.6@400a3836ee549ae6f665323ac3f21e27eac7155f',
  'sebastian/code-unit-reverse-lookup' => '1.0.1@4419fcdb5eabb9caa61a27c7a1db532a6b55dd18',
  'sebastian/comparator' => '3.0.1@591a30922f54656695e59b1f39501aec513403da',
  'sebastian/diff' => '3.0.1@366541b989927187c4ca70490a35615d3fef2dce',
  'sebastian/environment' => '3.1.0@cd0871b3975fb7fc44d11314fd1ee20925fce4f5',
  'sebastian/exporter' => '3.1.0@234199f4528de6d12aaa58b612e98f7d36adb937',
  'sebastian/global-state' => '2.0.0@e8ba02eed7bbbb9e59e43dedd3dddeff4a56b0c4',
  'sebastian/object-enumerator' => '3.0.3@7cfd9e65d11ffb5af41198476395774d4c8a84c5',
  'sebastian/object-reflector' => '1.1.1@773f97c67f28de00d397be301821b06708fca0be',
  'sebastian/recursion-context' => '3.0.0@5b0cd723502bac3b006cbf3dbf7a1e3fcefe4fa8',
  'sebastian/resource-operations' => '1.0.0@ce990bb21759f94aeafd30209e8cfcdfa8bc3f52',
  'sebastian/version' => '2.0.1@99732be0ddb3361e16ad77b68ba41efc8e979019',
  'squizlabs/php_codesniffer' => '2.9.1@dcbed1074f8244661eecddfc2a675430d8d33f62',
  'theseer/tokenizer' => '1.1.0@cb2f008f3f05af2893a87208fe6a6c4985483f8b',
  'webmozart/assert' => '1.3.0@0df1908962e7a3071564e857d86874dad1ef204a',
  'zendframework/zend-expressive-tooling' => '0.4.7@ed7a89e5e839b7f71c9cf61a6fba4654031cafa4',
  'zfcampus/zf-composer-autoloading' => '2.1.0@537145efec53c784ddc06f1af93102ddede52ceb',
  'zfcampus/zf-development-mode' => '3.2.0@419004a320bab017d28f2bc5e7857dde7e19aecf',
  'ministryofjustice/opg-refunds-caseworker-api' => 'dev-LPA-2758-deprecated-warnings-zend-expressive-2@d0f4fd47c02ab74827939a53f23dc7b21f7812b7',
);

    private function __construct()
    {
    }

    /**
     * @throws \OutOfBoundsException if a version cannot be located
     */
    public static function getVersion(string $packageName) : string
    {
        if (isset(self::VERSIONS[$packageName])) {
            return self::VERSIONS[$packageName];
        }

        throw new \OutOfBoundsException(
            'Required package "' . $packageName . '" is not installed: cannot detect its version'
        );
    }
}
