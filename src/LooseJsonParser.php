<?php

declare(strict_types=1);

namespace LooseJsonParser;

class LooseJsonParser
{
    private int $pos = 0;

    /**
     * @var array<string>
     */
    private array $chars;
    private int $length;

    public function __construct(public string $text)
    {
        $this->chars = str_split($this->text);
        $this->length = count($this->chars);
    }

    /**
     * The only public method, the entry point of the application.
     *
     * @throws JsonParsingException
     */
    public function decode(): mixed
    {
        $this->skipWhitespace();

        return $this->parseValue();
    }

    /**
     * Core parsing method that determines the type of JSON value at the current position
     * and delegates to the appropriate specialized parser.
     *
     * This method acts as the main dispatcher in our recursive descent parser, analyzing
     * the first character to determine whether we're looking at an object {}, array [],
     * quoted string ("" or ''), or unquoted primitive (numbers, booleans, null, or bare strings).
     *
     * The method handles LooseJSON's flexibility by supporting both standard JSON syntax
     * and relaxed formats like single quotes and unquoted keys/values.
     *
     * @return mixed The parsed value (array, string, int, float, bool, or null)
     *
     * @throws JsonParsingException When encountering invalid syntax or unexpected end of input
     */
    private function parseValue(): mixed
    {
        $this->skipWhitespace();

        if ($this->pos >= $this->length) {
            throw new JsonParsingException('Unexpected end of input');
        }

        $char = $this->chars[$this->pos];

        switch ($char) {
            case '{':
                return $this->parseObject();
            case '[':
                return $this->parseArray();
            case '"':
                return $this->parseString('"');
            case "'":
                return $this->parseString("'");
            default:
                if ($this->isUnquotedChar($char)) {
                    return $this->parseUnquoted();
                }
                throw new JsonParsingException("Unexpected character: $char at position $this->pos");
        }
    }

    /**
     * @return array<string>
     *
     * @throws JsonParsingException
     */
    private function parseObject(): array
    {
        ++$this->pos; // skip '{'
        $result = [];
        $this->skipWhitespace();

        // Handle an empty object
        if ($this->pos < $this->length && '}' === $this->chars[$this->pos]) {
            ++$this->pos;

            return $result;
        }

        while ($this->pos < $this->length) {
            $this->skipWhitespace();

            // Parse key
            $key = $this->parseValue();
            if (!is_string($key) && !is_numeric($key)) {
                $key = (string) $key;
            }

            $this->skipWhitespace();

            // Expect colon
            if ($this->pos >= $this->length || ':' !== $this->chars[$this->pos]) {
                throw new JsonParsingException("Expected ':' after key at position $this->pos");
            }
            ++$this->pos; // skip ':'

            $this->skipWhitespace();

            // Parse value
            $value = $this->parseValue();
            $result[$key] = $value;

            $this->skipWhitespace();

            if ($this->pos >= $this->length) {
                throw new JsonParsingException("Unexpected end of input, expected '}' or ','");
            }

            $char = $this->chars[$this->pos];
            if ('}' === $char) {
                ++$this->pos;

                return $result;
            } elseif (',' === $char) {
                ++$this->pos;
                $this->skipWhitespace();
                // Handle trailing comma
                if ($this->pos < $this->length && '}' === $this->chars[$this->pos]) {
                    ++$this->pos;

                    return $result;
                }
            } else {
                throw new JsonParsingException("Expected ',' or '}' at position $this->pos");
            }
        }

        throw new JsonParsingException("Unexpected end of input, expected '}'");
    }

    /**
     * @return array<string>
     *
     * @throws JsonParsingException
     */
    private function parseArray(): array
    {
        ++$this->pos; // skip '['
        $result = [];
        $this->skipWhitespace();

        // Handle an empty array
        if ($this->pos < $this->length && ']' === $this->chars[$this->pos]) {
            ++$this->pos;

            return $result;
        }

        while ($this->pos < $this->length) {
            $this->skipWhitespace();

            // Parse value
            $value = $this->parseValue();
            $result[] = $value;

            $this->skipWhitespace();

            if ($this->pos >= $this->length) {
                throw new JsonParsingException("Unexpected end of input, expected ']' or ','");
            }

            $char = $this->chars[$this->pos];
            if (']' === $char) {
                ++$this->pos;

                return $result;
            } elseif (',' === $char) {
                ++$this->pos;
                $this->skipWhitespace();
                // Handle trailing comma
                if ($this->pos < $this->length && ']' === $this->chars[$this->pos]) {
                    ++$this->pos;

                    return $result;
                }
            } else {
                throw new JsonParsingException("Expected ',' or ']' at position $this->pos");
            }
        }

        throw new JsonParsingException("Unexpected end of input, expected ']'");
    }

    /**
     * @throws JsonParsingException
     */
    private function parseString(string $quote): string
    {
        ++$this->pos; // skip opening quote
        $result = '';
        $escaped = false;

        while ($this->pos < $this->length) {
            $char = $this->chars[$this->pos];

            if ($escaped) {
                $result .= match ($char) {
                    'n' => "\n",
                    't' => "\t",
                    'r' => "\r",
                    '\\' => '\\',
                    '"' => '"',
                    "'" => "'",
                    default => $char,
                };
                $escaped = false;
            } else {
                if ('\\' === $char) {
                    $escaped = true;
                } elseif ($char === $quote) {
                    ++$this->pos; // skip closing quote

                    return $result;
                } else {
                    $result .= $char;
                }
            }
            ++$this->pos;
        }

        throw new JsonParsingException('Unterminated string');
    }

    private function parseUnquoted(): mixed
    {
        $result = '';

        while ($this->pos < $this->length && $this->isUnquotedChar($this->chars[$this->pos])) {
            $result .= $this->chars[$this->pos];
            ++$this->pos;
        }

        // Try to convert to the appropriate type
        $lower = strtolower($result);

        if ('true' === $lower) {
            return true;
        }

        if ('false' === $lower) {
            return false;
        }

        if ('null' === $lower) {
            return null;
        }

        if (is_numeric($result)) {
            if (str_contains($result, '.')) {
                return (float) $result;
            }

            return (int) $result;
        }

        return $result;
    }

    private function isUnquotedChar(string $char): bool
    {
        return 1 === preg_match('/[a-zA-Z0-9.?!\-_]/', $char);
    }

    /**
     * Advances the parser position past any whitespace characters at the current location.
     *
     * This utility method is essential for robust JSON parsing as it handles the whitespace
     * that can appear between JSON tokens. It uses a regex pattern to match any whitespace
     * character (spaces, tabs, newlines, carriage returns) and advances the position pointer
     * until a non-whitespace character is encountered or the end of input is reached.
     *
     * Called frequently throughout the parsing process to ensure clean token separation,
     * particularly before parsing keys, values, and structural elements like commas and colons.
     * This allows the LooseJSON parser to handle prettified/formatted JSON with arbitrary
     * indentation and spacing.
     */
    private function skipWhitespace(): void
    {
        while ($this->pos < $this->length
            && preg_match('/\s/', $this->chars[$this->pos])) {
            ++$this->pos;
        }
    }
}
