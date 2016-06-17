# API Client Foundation for PHP 7

[![Linux Build Status](https://travis-ci.org/WyriHaximus/php-api-client-resource-generator.svg?branch=master)](https://travis-ci.org/WyriHaximus/php-api-client-resource-generator)
[![Windows Build status](https://ci.appveyor.com/api/projects/status/dvcu9l8rm6shy7t3?svg=true)](https://ci.appveyor.com/project/WyriHaximus/php-api-client-resource-generator)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/api-client-resource-generator/v/stable.png)](https://packagist.org/packages/WyriHaximus/api-client-resource-generator)
[![Total Downloads](https://poser.pugx.org/WyriHaximus/api-client-resource-generator/downloads.png)](https://packagist.org/packages/WyriHaximus/api-client-resource-generator)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/php-api-client-resource-generator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/php-api-client-resource-generator/?branch=master)
[![License](https://poser.pugx.org/WyriHaximus/api-client-resource-generator/license.png)](https://packagist.org/packages/wyrihaximus/api-client-resource-generator)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/php-api-client-resource-generator/badge.svg)](https://appveyor-ci.org/WyriHaximus/php-api-client-resource-generator)

# Goals

* Tool for easy wireframing of resources for `wyrihaximus/api-client` based API clients

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/api-client-resource-generator 
```

# Usage

Pas a `definition` `YAML` file and a `path` where to write the generated files.

```
./vendor/bin/api-client-resource-generator [definition] [path]
```

For example: 

```
./vendor/bin/api-client-resource-generator ./yaml/project.yaml ./src/Resource
```

Generate multiple resources at once: 

```
./vendor/bin/api-client-resource-generator ./yaml/*.yaml ./src/Resource
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
