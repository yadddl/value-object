<?php

declare(strict_types=1);

namespace Yadddl\DDD\Serializer;

use Yadddl\DDD\Primitive\DateTime;
use Yadddl\DDD\Primitive\Integer;

final class SerializerBaseFactory implements SerializerFactory
{
    public function __invoke(): SerializerImpl
    {
        $config = new SerializerConfig();

        $this->configure($config);

        return new SerializerImpl($config);
    }

    protected function configure(SerializerConfig $config): void
    {
        $config->serialize(Integer::class)
              ->with(fn (Integer $integer) => $integer->toInt());

        $config->serialize(DateTime::class)
            ->with(fn (DateTime $dateTime) => $dateTime->format('Y-m-d'));
    }

    public static function make(): SerializerImpl
    {
        return (new self())();
    }
}
