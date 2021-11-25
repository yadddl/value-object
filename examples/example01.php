<?php

require_once (__DIR__ . '/../vendor/autoload.php');

use Yadddl\DDD\Primitive\Text;
use Yadddl\DDD\Serializer\SerializerBaseFactory;

// Getting the base serializer (you can customize it, if you want)
$serializer = SerializerBaseFactory::make();

// Creating the value object
$text1 = Text::create('Hello, world!');
$text2 = Text::create('World, Hello!');

// Serializing it
var_dump($serializer->serialize($text1)); // string(13) "Hello, world!"
var_dump((string)$text1);                 // string(13) "Hello, world!"
var_dump($text1->equalsTo($text1));      //bool(true)
var_dump($text1->equalsTo($text2));      //bool(false)