# Loose JSON Parser

A flexible JSON parser for PHP that handles malformed and non-standard JSON syntax.

## Overview: What problem does the library actually solve?

This library solves the problem of parsing malformed JSON that doesn't conform to strict JSON standards.

**Input:** Malformed JSON (missing quotes, trailing comma)

```
{
    name: 'Giuseppe',
    age: 38,
}
```

**Output:** PHP

```
[
    "name" => "Giuseppe",
    "age" => 38
]
```

The built-in json_decode() function returns `NULL`

```
json_decode("
    {
        name: 'Giuseppe',
        age: 38,
    }
    ");
```

## Installation

```
composer require gmaccario/loose-json-parser
composer install
```

## Requirements

- PHP 8.4 or higher (tested with PHP 8.4.8)
- No additional extensions required

## Usage
```
<?php

use LooseJsonParser\JsonParsingException;
use LooseJsonParser\LooseJsonParser;

try {
    $parser = new LooseJsonParser('{name: "John", age: 30,}');
    $result = $parser->decode();
    // Returns: ["name" => "John", "age" => 30]
} catch (JsonParsingException $e) {
    echo "Parse error: " . $e->getMessage();
}
```

## Features

- **Flexible Quoting**: Supports single quotes, double quotes, and unquoted keys
- **Trailing Commas**: Handles trailing commas in objects and arrays
- **Mixed Syntax**: Allows mixing of quote types within the same document
- **Case-Insensitive Primitives**: Supports True/FALSE/NULL variations
- **Comprehensive Error Handling**: Detailed error messages with position information

## Supported Syntax

### Flexible Quoting

- **Double quotes**: `{"name": "John"}`
- **Single quotes**: `{'name': 'John'}`
- **Unquoted keys**: `{name: "John"}`
- **Mixed syntax**: `{"name": 'John', age: 30}`

### Trailing Commas

- **Objects**: `{name: "John", age: 30,}`
- **Arrays**: `[1, 2, 3,]`

### Case-Insensitive Primitives

- **Booleans**: `true`, `True`, `TRUE`, `false`, `False`, `FALSE`
- **Null values**: `null`, `Null`, `NULL`

## Error Handling

The parser throws `JsonParsingException` for invalid syntax with detailed position information.

```
try {
    $parser = new LooseJsonParser('invalid json');
    $result = $parser->decode();
} catch (JsonParsingException $e) {
    echo $e->getMessage(); // "Unexpected character: i at position 0"
}
```

## Security

- Input validation: Parser validates syntax but doesn't sanitize content
- Resource limits: No built-in protection against deeply nested structures
- Recommended: Implement application-level limits for untrusted input

## CRITICAL SECURITY WARNING

This library does NOT provide protection against:
- JSON bombs (deeply nested structures causing stack overflow)
- Memory exhaustion attacks
- Resource consumption attacks

## Contributing

- Fork the repository
- Create a feature branch
- Add tests for new functionality
- Run quality tools: composer run-script quality
- Submit a pull request

## TODO

### Version 1.1.0
- [ ] Implement streaming parser for large files
- [ ] Add configuration options for strict/loose parsing modes (object, array, string, unquote)
- [ ] Install Churn to keep under control the Cyclomatic complexity

## Unit Tests

```
./vendor/bin/phpunit --version
./vendor/bin/phpunit tests
```

## Quality Tools

### PHP CS Fixer

```
PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix src
PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix tests
```
**Note:** PHP CS Fixer currently supports PHP 7.4–8.3, but this project requires PHP 8.4+.
The `PHP_CS_FIXER_IGNORE_ENV` flag bypasses this version check.

### PHPStan

You can currently choose from 11 levels (0 is the loosest and 10 is the strictest) by passing `-l|--level` to the analysis command.
```
vendor/bin/phpstan analyse -l 8 src tests
```
Currently level 8.

## Changelog
### [1.0.0] - 2025-07-01
 
- Initial release
- Support for flexible JSON parsing
- Comprehensive error handling

### [1.0.1] - 2025-07-07

- General improvements in README
- Minor changes

## Author

Created by [G.Maccario](https://github.com/gmaccario)

## License

Loose JSON Parser is open-source software licensed under [The GNU General Public License version 3 (GPLv3)](https://www.gnu.org/licenses/gpl-3.0.en.html).
