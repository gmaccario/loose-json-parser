<?php

declare(strict_types=1);

namespace LooseJsonParserTest\Unit\Tests;

use LooseJsonParser\JsonParsingException;
use LooseJsonParser\LooseJsonParser;
use PHPUnit\Framework\TestCase;

class LooseJsonParserTest extends TestCase
{
    /**
     * @throws JsonParsingException
     */
    private function parse(string $json): mixed
    {
        $parser = new LooseJsonParser($json);

        return $parser->decode();
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseEmptyObject(): void
    {
        $result = $this->parse('{}');
        $this->assertSame([], $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseEmptyObjectWithWhitespace(): void
    {
        $result = $this->parse('  {  }  ');
        $this->assertSame([], $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseSimpleObject(): void
    {
        $result = $this->parse('{"name": "John", "age": 30}');
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithSingleQuotes(): void
    {
        $result = $this->parse("{'name': 'John', 'age': 30}");
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithUnquotedKeys(): void
    {
        $result = $this->parse('{name: "John", age: 30}');
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithUnquotedValues(): void
    {
        $result = $this->parse('{"name": John, "active": true}');
        $expected = ['name' => 'John', 'active' => true];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithTrailingComma(): void
    {
        $result = $this->parse('{"name": "John", "age": 30,}');
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseEmptyArray(): void
    {
        $result = $this->parse('[]');
        $this->assertSame([], $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseSimpleArray(): void
    {
        $result = $this->parse('[1, 2, 3]');
        $expected = [1, 2, 3];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseArrayWithMixedTypes(): void
    {
        $result = $this->parse('["hello", 123, true, null]');
        $expected = ['hello', 123, true, null];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseArrayWithSingleQuotes(): void
    {
        $result = $this->parse("['hello', 'world']");
        $expected = ['hello', 'world'];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseArrayWithUnquotedStrings(): void
    {
        $result = $this->parse('[hello, world]');
        $expected = ['hello', 'world'];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseArrayWithTrailingComma(): void
    {
        $result = $this->parse('[1, 2, 3,]');
        $expected = [1, 2, 3];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseStringWithDoubleQuotes(): void
    {
        $result = $this->parse('"hello world"');
        $this->assertSame('hello world', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseStringWithSingleQuotes(): void
    {
        $result = $this->parse("'hello world'");
        $this->assertSame('hello world', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseStringWithEscapeSequences(): void
    {
        $result = $this->parse('"hello\\nworld\\ttab\\r\\n"');
        $this->assertSame("hello\nworld\ttab\r\n", $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseStringWithEscapedQuotes(): void
    {
        $result = $this->parse('"He said \\"Hello\\""');
        $this->assertSame('He said "Hello"', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseStringWithEscapedBackslash(): void
    {
        $result = $this->parse('"path\\\\to\\\\file"');
        $this->assertSame('path\\to\\file', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseInteger(): void
    {
        $result = $this->parse('42');
        $this->assertSame(42, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNegativeInteger(): void
    {
        $result = $this->parse('-42');
        $this->assertSame(-42, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseFloat(): void
    {
        $result = $this->parse('3.14');
        $this->assertSame(3.14, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNegativeFloat(): void
    {
        $result = $this->parse('-2.5');
        $this->assertSame(-2.5, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseBooleanTrue(): void
    {
        $result = $this->parse('true');
        $this->assertTrue($result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseBooleanFalse(): void
    {
        $result = $this->parse('false');
        $this->assertFalse($result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseBooleanTrueCaseInsensitive(): void
    {
        $result = $this->parse('TRUE');
        $this->assertTrue($result);
    }

    public function testParseBooleanFalseCaseInsensitive(): void
    {
        $result = $this->parse('False');
        $this->assertFalse($result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNull(): void
    {
        $result = $this->parse('null');
        $this->assertNull($result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNullCaseInsensitive(): void
    {
        $result = $this->parse('NULL');
        $this->assertNull($result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseUnquotedString(): void
    {
        $result = $this->parse('hello');
        $this->assertSame('hello', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseUnquotedStringWithSpecialChars(): void
    {
        $result = $this->parse('hello-world_123');
        $this->assertSame('hello-world_123', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNestedObject(): void
    {
        $json = '{"user": {"name": "John", "age": 30}, "active": true}';
        $result = $this->parse($json);
        $expected = [
            'user' => ['name' => 'John', 'age' => 30],
            'active' => true,
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseNestedArray(): void
    {
        $json = '[[1, 2], [3, 4]]';
        $result = $this->parse($json);
        $expected = [[1, 2], [3, 4]];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseComplexMixedStructure(): void
    {
        $json = '{
            "users": [
                {"name": "John", "active": true},
                {"name": "Jane", "active": false}
            ],
            "count": 2,
            "message": "Success"
        }';
        $result = $this->parse($json);
        $expected = [
            'users' => [
                ['name' => 'John', 'active' => true],
                ['name' => 'Jane', 'active' => false],
            ],
            'count' => 2,
            'message' => 'Success',
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseWithExtraWhitespace(): void
    {
        $json = '  {  "name"  :  "John"  ,  "age"  :  30  }  ';
        $result = $this->parse($json);
        $expected = ['name' => 'John', 'age' => 30];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testNumericKeysConvertedToString(): void
    {
        $result = $this->parse('{123: "value", 456: "another"}');
        $expected = ['123' => 'value', '456' => 'another'];
        $this->assertSame($expected, $result);
    }

    /**
     * Exception Tests.
     */
    public function testThrowsExceptionOnEmptyInput(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage('Unexpected end of input');
        $this->parse('');
    }

    public function testThrowsExceptionOnWhitespaceOnlyInput(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage('Unexpected end of input');
        $this->parse('   ');
    }

    public function testThrowsExceptionOnUnexpectedCharacter(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage('Unexpected character');
        $this->parse('@');
    }

    public function testThrowsExceptionOnMissingColon(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage("Expected ':' after key");
        $this->parse('{"name" "John"}');
    }

    public function testThrowsExceptionOnMissingClosingBrace(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage("Unexpected end of input, expected '}'");
        $this->parse('{"name": "John"');
    }

    public function testThrowsExceptionOnMissingClosingBracket(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage("Unexpected end of input, expected ']'");
        $this->parse('[1, 2, 3');
    }

    public function testThrowsExceptionOnUnterminatedString(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage('Unterminated string');
        $this->parse('"hello');
    }

    public function testThrowsExceptionOnInvalidObjectSeparator(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage("Expected ',' or '}'");
        $this->parse('{"name": "John" "age": 30}');
    }

    public function testThrowsExceptionOnInvalidArraySeparator(): void
    {
        $this->expectException(JsonParsingException::class);
        $this->expectExceptionMessage("Expected ',' or ']'");
        $this->parse('[1 2 3]');
    }

    // Edge Cases

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithEmptyStringKey(): void
    {
        $result = $this->parse('{"": "empty key"}');
        $expected = ['' => 'empty key'];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseZeroValue(): void
    {
        $result = $this->parse('0');
        $this->assertSame(0, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseZeroFloatValue(): void
    {
        $result = $this->parse('0.0');
        $this->assertSame(0.0, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseEmptyString(): void
    {
        $result = $this->parse('""');
        $this->assertSame('', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseSingleCharacterString(): void
    {
        $result = $this->parse('"a"');
        $this->assertSame('a', $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testParseObjectWithMixedQuoteStyles(): void
    {
        $result = $this->parse('{"double": "quotes", \'single\': \'quotes\', unquoted: value}');
        $expected = [
            'double' => 'quotes',
            'single' => 'quotes',
            'unquoted' => 'value',
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testUnquotedKeysAndSingleQuotes(): void
    {
        $result = $this->parse("{name: 'John Doe',age: 30,active: true,department: 'Engineering',}");
        $expected = [
            'name' => 'John Doe',
            'age' => 30,
            'active' => true,
            'department' => 'Engineering',
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testMixedQuoteTypesAndTrailingCommas(): void
    {
        $result = $this->parse("{users: [{id: 1,name: \"Alice\",email: 'alice@example.com',},{id: 2,name: 'Bob',email: \"bob@example.com\",}, ],total: 2,}");
        $expected = [
            'users' => [
                [
                    'id' => 1,
                    'name' => 'Alice',
                    'email' => 'alice@example.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Bob',
                    'email' => 'bob@example.com',
                ],
            ],
            'total' => 2,
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @throws JsonParsingException
     */
    public function testPythonStyleSyntax(): void
    {
        $result = $this->parse('{config: {debug: True,cache: False,timeout: 30,retries: None,server: localhost,port: 8080,},features: [authentication,logging,monitoring,],}');
        $expected = [
            'config' => [
                'debug' => true,
                'cache' => false,
                'timeout' => 30,
                'retries' => 'None',
                'server' => 'localhost',
                'port' => 8080,
            ],
            'features' => [
                'authentication',
                'logging',
                'monitoring',
            ],
        ];
        $this->assertSame($expected, $result);
    }
}
