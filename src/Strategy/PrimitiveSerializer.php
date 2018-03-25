<?php

namespace Denimsoft\Serializer\Strategy;

abstract class PrimitiveSerializer extends Serializer
{
    abstract public function getTypes(): array;
}
