# JSON diff/rearrange/patch/pointer library for PHP

A PHP implementation for finding unordered diff between two `JSON` documents.

[![Build Status](https://travis-ci.org/swaggest/json-diff.svg?branch=master)](https://travis-ci.org/swaggest/json-diff)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swaggest/json-diff/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swaggest/json-diff/?branch=master)
[![Code Climate](https://codeclimate.com/github/swaggest/json-diff/badges/gpa.svg)](https://codeclimate.com/github/swaggest/json-diff)
[![Code Coverage](https://scrutinizer-ci.com/g/swaggest/json-diff/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/swaggest/json-diff/code-structure/master/code-coverage)

## Purpose

 * To simplify changes review between two `JSON` files you can use a standard `diff` tool on rearranged pretty-printed `JSON`.
 * To detect breaking changes by analyzing removals and changes from original `JSON`.
 * To keep original order of object sets (for example `swagger.json` [parameters](https://swagger.io/docs/specification/describing-parameters/) list).
 * To make and apply JSON Patches, specified in [RFC 6902](http://tools.ietf.org/html/rfc6902) from the IETF.
 * To retrieve and modify data by [JSON Pointer](http://tools.ietf.org/html/rfc6901).
 * To recursively replace by JSON value.

## Installation

### Library

```bash
git clone https://github.com/swaggest/json-diff.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require swaggest/json-diff
```

## Library usage

### `JsonDiff`

Create `JsonDiff` object from two values (`original` and `new`).

```php
$r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
```

On construction `JsonDiff` will build `rearranged` value of `new` recursively keeping `original` keys order where possible. 
Keys that are missing in `original` will be appended to the end of `rearranged` value in same order they had in `new` value.

If two values are arrays of objects, `JsonDiff` will try to find a common unique field in those objects and use it as criteria for rearranging. 
You can enable this behaviour with `JsonDiff::REARRANGE_ARRAYS` option:
```php
$r = new JsonDiff(
    json_decode($originalJson), 
    json_decode($newJson),
    JsonDiff::REARRANGE_ARRAYS
);
```

On created object you have several handy methods.

#### `getPatch`
Returns [`JsonPatch`](#jsonpatch) of difference

#### `getRearranged`
Returns new value, rearranged with original order.

#### `getRemoved`
Returns removals as partial value of original.

#### `getRemovedPaths`
Returns list of `JSON` paths that were removed from original.

#### `getRemovedCnt`
Returns number of removals.

#### `getAdded`
Returns additions as partial value of new.

#### `getAddedPaths`
Returns list of `JSON` paths that were added to new.

#### `getAddedCnt`
Returns number of additions.

#### `getModifiedOriginal`
Returns modifications as partial value of original.

#### `getModifiedNew`
Returns modifications as partial value of new.

#### `getModifiedPaths`
Returns list of `JSON` paths that were modified from original to new.

#### `getModifiedCnt`
Returns number of modifications.

### `JsonPatch`

#### `import`
Creates `JsonPatch` instance from `JSON`-decoded data.

#### `export`
Creates patch data from `JsonPatch` object.

#### `op`
Adds operation to `JsonPatch`.

#### `apply`
Applies patch to `JSON`-decoded data.

#### `setFlags`
Alters default behavior.

Available flag:

* `JsonPatch::STRICT_MODE` Disallow converting empty array to object for key creation.

### `JsonPointer`

#### `escapeSegment`
Escapes path segment.

#### `splitPath`
Creates array of unescaped segments from `JSON Pointer` string.

#### `buildPath`
Creates `JSON Pointer` string from array of unescaped segments.

#### `add`
Adds value to data at path specified by segments.

#### `get`
Gets value from data at path specified by segments.

#### `getByPointer`
Gets value from data at path specified `JSON Pointer` string.

#### `remove`
Removes value from data at path specified by segments.

### `JsonValueReplace`

#### `process`
Recursively replaces all nodes equal to `search` value with `replace` value.

## Example

```php
$originalJson = <<<'JSON'
{
    "key1": [4, 1, 2, 3],
    "key2": 2,
    "key3": {
        "sub0": 0,
        "sub1": "a",
        "sub2": "b"
    },
    "key4": [
        {"a":1, "b":true, "subs": [{"s":1}, {"s":2}, {"s":3}]}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

$newJson = <<<'JSON'
{
    "key5": "wat",
    "key1": [5, 1, 2, 3],
    "key4": [
        {"c":false, "a":2}, {"a":1, "b":true, "subs": [{"s":3, "add": true}, {"s":2}, {"s":1}]}, {"c":1, "a":3}
    ],
    "key3": {
        "sub3": 0,
        "sub2": false,
        "sub1": "c"
    }
}
JSON;

$patchJson = <<<'JSON'
[
    {"value":4,"op":"test","path":"/key1/0"},
    {"value":5,"op":"replace","path":"/key1/0"},
    
    {"op":"remove","path":"/key2"},
    
    {"op":"remove","path":"/key3/sub0"},
    
    {"value":"a","op":"test","path":"/key3/sub1"},
    {"value":"c","op":"replace","path":"/key3/sub1"},
    
    {"value":"b","op":"test","path":"/key3/sub2"},
    {"value":false,"op":"replace","path":"/key3/sub2"},
    
    {"value":0,"op":"add","path":"/key3/sub3"},

    {"value":true,"op":"add","path":"/key4/0/subs/2/add"},
    
    {"op":"remove","path":"/key4/1/b"},
    
    {"value":false,"op":"add","path":"/key4/1/c"},
    
    {"value":1,"op":"add","path":"/key4/2/c"},
    
    {"value":"wat","op":"add","path":"/key5"}
]
JSON;

$diff = new JsonDiff(json_decode($originalJson), json_decode($newJson), JsonDiff::REARRANGE_ARRAYS);
$this->assertEquals(json_decode($patchJson), $diff->getPatch()->jsonSerialize());

$original = json_decode($originalJson);
$patch = JsonPatch::import(json_decode($patchJson));
$patch->apply($original);
$this->assertEquals($diff->getRearranged(), $original);
```

## CLI tool

Moved to [`swaggest/json-cli`](https://github.com/swaggest/json-cli)