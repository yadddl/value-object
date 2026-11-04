<?php

declare(strict_types=1);

namespace Yadddl\Serializer;

/**
 * Reconstructs objects through domain factories (`create` / matching `create*`).
 * Does not validate: may return ErrorsBag / MultipleFieldErrorsBag from factories.
 */
interface Deserializer
{
    /**
     * @param class-string $type
     */
    public function deserialize(mixed $data, string $type): mixed;
}
