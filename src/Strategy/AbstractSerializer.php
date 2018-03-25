<?php

namespace Denimsoft\Serializer\Strategy;

abstract class AbstractSerializer extends Serializer
{
    abstract public function canSerialize($value): bool;
}
