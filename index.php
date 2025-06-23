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

}
