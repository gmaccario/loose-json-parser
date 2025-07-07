# Loose JSON Parser

This is a JSON parser that isn't as strict as the normal JSON libraries. (quote by LooseJSON)

Inspired by https://github.com/FlorianDietz/loosejson

Well... this is a porting of the Florian Dietz library, from Python to PHP.

The idea: Hey, look what I shipped this weekend!

I'm joking: The idea is to build a robust and flexible solution for parsing malformed JSON data.

## Project Overview: LooseJSON (forgiving JSON decoder in PHP)

Version 2: Pure Custom Parser
- Strategy: Complete custom recursive descent parser
- Philosophy: "Parse everything ourselves from scratch"
- Complexity: Clean recursive structure with specialized methods

```
Input - JSON +++ Wrong: missing quotes, and final comma:
{
    name: 'Giuseppe',
    age: 38,
}

Output - PHP:
[
    "name" => "Giuseppe",
    "age" => 38
]
```

## 🛠 Installation

```
git clone https://github.com/gmaccario/loose-json-parser.git
cd loose-json-parser
composer install
```

Or using Composer in your project

```
composer require gmaccario/loose-json-parser
```

## 📦 Example Usage
Check `sample.php` in the root directory.

## TODO
- Implement Strategy Pattern

## Unit Tests
```
./vendor/bin/phpunit --version
./vendor/bin/phpunit tests
```

### Quality Tools
#### PHP CS Fixer
```
PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix src
PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix tests
```
PHP needs to be a minimum version of PHP 7.4.0 and the maximum version of PHP 8.3.*
Current PHP version: 8.4.4
To ignore this requirement please set `PHP_CS_FIXER_IGNORE_ENV`.

#### PHPStan
You can currently choose from 11 levels (0 is the loosest and 10 is the strictest) by passing `-l|--level` to the analysis command.
```
vendor/bin/phpstan analyse -l 8 src tests
```
Currently level 8.

## 📝 License
Loose JSON Parser is open-source software licensed under [The GNU General Public License version 3 (GPLv3)](https://www.gnu.org/licenses/gpl-3.0.en.html).
