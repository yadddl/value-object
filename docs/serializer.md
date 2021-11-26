[Home](../README.md)

# Serializer

The simplest way to retrieve the serializer is this.

```php
use \Yadddl\ValueObject\Serializer\SerializerBaseFactory;

$serializer = SerializerBaseFactory::make();
```
It provides a basic configuration. Then, if you want to serialize a **Value Object**, you just need to call `serialize`.

```php
$data = /* associative array with all the data */

$person = Person::create(...$data);

$serializedData = $serializer->serialize($person);
```
With `$serializedData` that is the same of `$data`.
