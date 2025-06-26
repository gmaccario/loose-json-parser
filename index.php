<?php

declare(strict_types=1);

/**
 * This is a JSON parser that isn't as strict as the normal JSON libraries. (quote by LooseJSON)
 * Inspired by https://github.com/FlorianDietz/loosejson
 *
 * Well... this is a porting of the Florian Dietz library, from Python to PHP.
 *
 * The idea: Hey, look what I shipped this weekend!
 *
 * ✅ Project Overview: LooseJSON (forgiving JSON decoder in PHP)
 * 🔧 What it does
 * Accepts invalid or "sloppy" JSON (e.g., unquoted keys, single quotes, trailing commas)
 * Cleans/fixes it internally
 * Passes it to json_decode() and returns the result
 *
 * By default, json_decode() returns NULL if the JSON is malformed!
 *
 *
 * Input - JSON +++ Wrong: missing quotes, and final comma:
 * {
 *  name: 'Giuseppe',
 *  age: 38,
 * }
 *
 * Output - PHP:
 * [
 *  "name" => "Giuseppe",
 *  "age" => 38
 * ]
 */


class JsonParsingException extends Exception {};

class LooseJsonParser
{
    private const string TASK_BUILDING_LIST = 'building_list';
    private const string TASK_BUILDING_DICT = 'building_dict';
    private const string TASK_BUILDING_PRIMITIVE = 'building_primitive';
    private const string STAGE_EXPECTING_KEY = 'expecting_key';
    private const string STAGE_EXPECTING_COMMA = 'expecting_comma';
    private const string STAGE_EXPECTING_COLON = 'expecting_colon';
    private const string STAGE_EXPECTING_VALUE = 'expecting_value';
    private const string QUOTES_NO_QUOTES = 'no_quotes';
    private const string QUOTES_SINGLE_QUOTES = 'single_quotes';
    private const string QUOTES_DOUBLE_QUOTES = 'double_quotes';

    private int    $pos = 0;
    private int    $line = 1;
    private int    $col = 1;
    private string $unquotedCharacters = '[a-zA-Z0-9.?!\-_]';
    private object $EOF;
    private array  $chars = [];

    public function __construct(
        public string  $text,
    ) {
        $this->chars = str_split($this->text);

        $this->EOF = new stdClass();

        $this->chars[] = $this->EOF;
    }

    /**
     * Starting at the current position, continues parsing new characters until it has parsed a complete object, then returns that object.
     * When this starts, self.pos should be at the first character of the object (or leading whitespace)
     * and when it returns `self.pos` will be at the last character of the object.
     *
     * @return string|array
     * @throws JsonParsingException
     */
    public function decode(): string|array
    {
        $task = '';
        $stage = '';
        $resBuilder = [];
        $expectingComma = false;
        $quoteType = '';
        $stringEscape = false;
        $isFinished = false;
        $res = false;
        $nextDictKey = null;

        while($this->pos < count($this->chars)) {
            $char = $this->chars[$this->pos];

            if($char === $this->EOF) {
                throw new JsonParsingException("Reached the end of the file without encountering anything to parse.");
            }

            // Update line and column on a linebreak
            if($char === '\n') {
                $this->line += 1;
                $this->col = 1;
            }

            // How to handle the character depends on what is currently being done
            if($task === '') {
                if (preg_match('/\s/', $char)) {
                    // While there is no task yet, ignore whitespace and continue looking for an object
                } elseif ($char === '[') {
                    $task = self::TASK_BUILDING_LIST;
                    $resBuilder = [];
                    $expectingComma = false;
                } elseif ($char === '{') {
                    $task = self::TASK_BUILDING_DICT;
                    $resBuilder = [];
                    $stage = self::STAGE_EXPECTING_KEY;
                } elseif ($char === '"') {
                    $task = self::TASK_BUILDING_PRIMITIVE;
                    $quoteType = self::QUOTES_DOUBLE_QUOTES;
                    $resBuilder = [];
                    $stringEscape = false;
                } elseif ($char === "'") {
                    $task = self::TASK_BUILDING_PRIMITIVE;
                    $quoteType = self::QUOTES_SINGLE_QUOTES;
                    $resBuilder = [];
                    $stringEscape = false;
                } elseif (preg_match('/^' . $this->unquotedCharacters . '/', $char)) {
                    $task = self::TASK_BUILDING_PRIMITIVE;
                    $quoteType = self::QUOTES_NO_QUOTES;
                    $resBuilder = [$char];
                    $stringEscape = false;
                    $isFinished = $res = $this->unquotedTextLookaheadAndOptionallyFinish($resBuilder);
                    if($isFinished) {
                        return $res;
                    }
                } else {
                    throw new JsonParsingException("Reached an unexpected character while looking for the start of the next object: " . $char);
                }
            } elseif($task === self::TASK_BUILDING_LIST) {
                if (preg_match('/\s/', $char)) {
                    // Skip whitespace in a list
                } elseif($char === ',') {
                    if($expectingComma) {
                        $expectingComma = false;
                    } else {
                        throw new JsonParsingException("Encountered multiple commas after another while parsing a list. Did you forget a list element?");
                    }
                } elseif($char == ']') {
                    // The end of the list has been reached.
                    return $resBuilder;
                } else {
                    if($expectingComma) {
                        throw new JsonParsingException("Expected a comma before the next list element.");
                    } else {
                        // Recurse to get the next element
                        $resBuilder[] = $this->decode();
                        $expectingComma = true;
                    }
                }
            } elseif($task === self::TASK_BUILDING_DICT) {
                if (preg_match('/\s/', $char)) {
                    // Skip whitespace in a dictionary
                } elseif($char === '}') {
                    if(in_array($stage, [
                        self::STAGE_EXPECTING_KEY,
                        self::STAGE_EXPECTING_COMMA
                    ])) {
                        return $resBuilder;
                    } else {
                        throw new JsonParsingException("The dictionary was closed too early. It's missing a value to go with the last key.");
                    }
                } else {
                    if($stage === self::STAGE_EXPECTING_KEY) {
                        // Recurse to get the next element and verify it's a string, and it's new
                        $nextDictKey = $this->decode();

                        if (is_string($nextDictKey) === false) {
                            if (is_int($nextDictKey) || is_float($nextDictKey) || is_bool($nextDictKey)) {
                                $nextDictKey = json_encode($nextDictKey);
                            }
                        }

                        if(in_array($nextDictKey, $resBuilder)) {
                            throw new JsonParsingException("This string has already been used as a key of this dictionary. No duplicate keys are allowed: " . $nextDictKey);
                        }

                        $stage = self::STAGE_EXPECTING_COLON;
                    } elseif($stage === self::STAGE_EXPECTING_COLON) {
                        if($char === ':') {
                            $stage = self::STAGE_EXPECTING_VALUE;
                        } else {
                            throw new JsonParsingException("Expected a colon separating the dictionary's key from its value");
                        }
                    } elseif($stage === self::STAGE_EXPECTING_VALUE) {
                        // Recurse to get the next element
                        $resBuilder[$nextDictKey] = $this->decode();
                        $stage = self::STAGE_EXPECTING_COMMA;
                    } elseif($stage === self::STAGE_EXPECTING_COMMA) {
                        if($char === ',') {
                            $stage = self::STAGE_EXPECTING_KEY;
                        } else {
                            throw new JsonParsingException("Expected a comma before the next dictionary key.");
                        }
                    } else {
                        throw new JsonParsingException("Expected a comma before the next dictionary key.");
                    }
                }
            } elseif($task === self::TASK_BUILDING_PRIMITIVE) {
                if(in_array($quoteType, [
                    self::QUOTES_DOUBLE_QUOTES,
                    self::QUOTES_SINGLE_QUOTES,
                ])
                ) {
                    if($quoteType === self::QUOTES_DOUBLE_QUOTES) {
                        $limitingQuote = '"';
                    } else {
                        $limitingQuote = "'";
                    }

                    if($char === $limitingQuote
                        && $stringEscape === false) {
                        // The end of the string has been reached. Build the string.
                        // Before evaluating the string, do some preprocessing that makes linebreaks possible.
                        $tmp = [];
                        $encounteredLinebreak = false;
                        foreach($resBuilder as $char) {
                            if($char === '\n') {
                                $encounteredLinebreak = true;
                                $tmp[] = '\\';
                                $tmp[] = 'n';
                            } elseif (($char === ' ' || $char === '\t') && $encounteredLinebreak) {
                                // Ignore any spaces and tabs following a linebreak
                            } else {
                                $encounteredLinebreak = false;
                                $tmp[] = $char;
                            }
                        }

                        // Combine the characters into a string and evaluate it
                        return $this->safeDecode($limitingQuote, implode('', $tmp));
                    }

                    // Add the current character to the list
                    // (we already know it's valid because of an earlier call to
                    // $this->.unquotedTextLookaheadAndOptionallyFinish())
                    $resBuilder[] = $char;

                    // If a backslash occurs, enter escape mode unless escape mode is already active,
                    // else deactivate escape mode
                    if($char === '\\' && $stringEscape === false) {
                        $stringEscape = true;
                    } else {
                        $stringEscape = false;
                    }
                } elseif($quoteType === self::QUOTES_NO_QUOTES) {
                    if (!preg_match('/^' . $this->unquotedCharacters . '/', $char)) {
                        throw new JsonParsingException("Programming error: this should have never been reached because of unquotedTextLookaheadAndOptionallyFinish().");
                    }
                    // Add the element
                    $resBuilder[] = $char;
                    // Look ahead and possibly finish up
                    $isFinished = $res = $this->unquotedTextLookaheadAndOptionallyFinish($resBuilder);
                    if($isFinished) {
                        return $res;
                    }
                } else {
                    throw new JsonParsingException("Programming error: undefined kind of string quotation: " . $quoteType);
                }

                // Increment the position and column
                $this->pos += 1;
                $this->col += 1;
            }
        }

        throw new JsonParsingException("Programming Error: reached the end of the file, but this should have been noticed earlier, when reaching the self.EOF object.");
    }

    /**
     * Check if the next position is EOF or a character that is invalid for unquoted objects.
     * If so, finish up and return the unquoted object.
     *
     * @param array $resBuilder
     * @return bool
     * @throws Exception
     */
    private function unquotedTextLookaheadAndOptionallyFinish(array &$resBuilder): bool
    {
        $nextChar = $this->chars[$this->pos+1];

        if($nextChar !== $this->EOF
            && preg_match('/^' . $this->unquotedCharacters . '/', $nextChar)) {
            return false;
        }

        // We have encountered a value that is not a valid part of the parser
        // try parsing the result in various ways before returning it
        $res = implode('', $resBuilder);

        // Check for boolean values
        if (in_array($res, ['true', 'True'], true)) {
            return true;
        }
        if (in_array($res, ['false', 'False'], true)) {
            return false;
        }

        // Check for null/None values
        if (in_array($res, ['null', 'None'], true)) {
            return true;
        }

        // int
        try {
            (int)$res;
        } catch (\Exception $e) {}

        // float
        $error = null;
        $flt = null;

        // Try to convert the string to a float
        try {
            $flt = floatval($res);

            // Check if the float is NaN or infinite
            if (is_nan($flt)) {
                $error = "NaN is not a valid JSON value!";
            } elseif (is_infinite($flt)) {
                $error = "Infinite is not a valid JSON value!";
            } else {
                return true;
            }
        } catch (Exception $e) {
            // Pass silently if conversion fails
        }

        // Raise an exception if an error is found
        if ($error !== null) {
            throw new Exception($error);
        }

        // Default: return as string
        return true;
    }

    /**
     * Safely decodes a string into a PHP data structure by adding the provided limiting quotes
     * and validating the JSON format. If decoding fails, an error message is returned.
     *
     * Implemented to replace the Python expression
     * res = ast.literal_eval(limiting_quote + res + limiting_quote)
     *
     * @param string $limitingQuote The type of quote to use for enclosing the JSON string (e.g., single or double quotes).
     * @param string $res The string value to be wrapped and decoded as JSON.
     * @return mixed The decoded PHP data structure if successful, or an error message string if decoding fails.
     */
    private function safeDecode(string $limitingQuote, string $res): mixed
    {
        // Construct the string with the limiting quotes
        $jsonString = $limitingQuote . $res . $limitingQuote;

        // Decode the JSON string
        $result = json_decode($jsonString, true);

        // Check if json_decode was successful
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result; // Return the decoded data
        } else {
            // Handle JSON decode error
            return "Error decoding JSON: " . json_last_error_msg();
        }
    }
}

/**
 * APPLICATION RUN!
 */
$parser = new LooseJsonParser(
    "
      {
  name: 'Giuseppe',
   age: 38,
  }
 "
);

print_r($parser->decode());
