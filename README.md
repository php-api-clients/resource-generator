# API Client Foundation for PHP 7

[![Linux Build Status](https://travis-ci.org/php-api-clients/resource-generator.svg?branch=master)](https://travis-ci.org/php-api-clients/resource-generator)
[![Windows Build status](https://ci.appveyor.com/api/projects/status/dvcu9l8rm6shy7t3?svg=true)](https://ci.appveyor.com/project/php-api-clients/resource-generator)
[![Latest Stable Version](https://poser.pugx.org/api-clients/resource-generator/v/stable.png)](https://packagist.org/packages/api-clients/resource-generator)
[![Total Downloads](https://poser.pugx.org/api-clients/resource-generator/downloads.png)](https://packagist.org/packages/api-clients/resource-generator)
[![Code Coverage](https://scrutinizer-ci.com/g/php-api-clients/resource-generator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-api-clients/resource-generator/?branch=master)
[![License](https://poser.pugx.org/api-clients/resource-generator/license.png)](https://packagist.org/packages/api-clients/resource-generator)
[![PHP 7 ready](http://php7ready.timesplinter.ch/php-api-clients/resource-generator/badge.svg)](https://appveyor-ci.org/php-api-clients/resource-generator)

# Goals

* Tool for easy wireframing of resources for `wyrihaximus/api-client` based API clients

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require api-clients/resource-generator 
```

# Usage

Pas a `definition` [`YAML`](#YAML) file.

```
./vendor/bin/api-client-resource-generator [definition]
```

For example: 

```
./vendor/bin/api-client-resource-generator ./yaml/project.yml
```

# YAML

The [`YAML`](https://en.wikipedia.org/wiki/YAML) format allows for quick and easy generation of resources, empty resources, and resource tests. The spec consists out of two or more files. 

## `resource.yaml`

First there is the `resource.yml` file with the meta data regarding all resources:

```YAML
yaml_location: yaml                        # The location where the YAML files for the Resources are 
api_settings: Example\Client\ApiSettings   # API Settings file location
src:                                       # SRC 
  path: src/Resources                      # Path where the resources should be placed
  namespace: Example\Client\Resource       # The namespace for the resources
tests:                                     # TESTS
  path: tests/Resources                    # Path where the resources tests should be placed
  namespace: Example\Tests\Client\Resource # The namespace for the tests
```

## `yaml/project.yaml`

The following `YAML` is an example resource file:

```YAML
class: Project                             # Resource classname
properties:                                # The resource's properties
  id: int                                  # Simple id field with type int
  name: string                             # Any scalar types or classes work
  plugins: SplObjectStorage                #
  builds:                                  # But differet types `special` properties are supported.
    type: array                            # For example the collection expects the type to be an array
    annotations:                           # and it uses an annotation name `collection` to     
      collection: Project\Build            # transform all items in the array to the specified resource.
  latestBuild:                             # Another supported annotation is `nested`, `nested` allows you to
    type: Project\Build                    # turn a properties that would otherwise be an array into a
    annotations:                           # resource. With it's own properties and methods
      nested: Project\Build                #
  updated:                                 # Normally all methods are generated to their camelCase property name
    method: updatedAt                      # but with `method` you can specify a custom method
    type: DateTimeInterface                # When for example dealing with time, you can specify to return an interface
    wrap: DateTimeImmutable                # while wrapping the value in a concrete class
```

# License

The MIT License (MIT)

Copyright (c) 2016 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
