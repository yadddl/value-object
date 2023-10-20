<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Yadddl\ValueObject\Primitive\Text;

// Creating the value object
$text1 = Text::create('Hello');
$text2 = Text::create('World');

echo $text1->value . PHP_EOL;
// prints "Hello"

echo (string)$text1 . PHP_EOL;
// prints "Hello"

echo "{$text1}, {$text2}!\n";
// prints "Hello, World!"

echo ($text1->equals($text1) ? 'Yes' : 'No') . PHP_EOL;
// prints "Yes"

echo ($text1->equals($text2) ? 'Yes' : 'No') . PHP_EOL;
// prints "No"