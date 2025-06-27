<?php

require_once __DIR__ . '/vendor/autoload.php';

use LooseJsonParser\JsonParsingException;
use LooseJsonParser\LooseJsonParser;

$parser = new LooseJsonParser("
{
    name: 'Giuseppe',
    age: 38,
}
");

try {
    print_r($parser->decode());
} catch (JsonParsingException $e) {
    die($e->getMessage());
}
