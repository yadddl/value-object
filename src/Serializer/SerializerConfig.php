<?php

declare(strict_types=1);

namespace Yadddl\ValueObject\Serializer;

class SerializerConfig
{
    /** @psalm-var array<class-string, Serializer> */
    private array $serializers = [];

    /**
     * @param class-string $className
     */
    public function serialize(string $className): ClosureSerializer
    {
        $serializer = new ClosureSerializer();

        $this->serializeWith($className, $serializer);

        return $serializer;
    }

    /**
     * @param class-string $className
     * @param Serializer $serializer
     */
    public function serializeWith(string $className, Serializer $serializer): void
    {
        $this->serializers[$className] = $serializer;
    }

    /**
     * @param object $object
     *
     * @return ?Serializer
     */
    public function serializerFor(object $object): ?Serializer
    {
        $lastSerializer = null;

        foreach ($this->serializers as $className => $serializer) {
            if ($object instanceof $className) {
                $lastSerializer = $serializer;
            }
        }

        return $lastSerializer;
    }
}
