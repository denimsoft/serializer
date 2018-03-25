<?php

namespace Denimsoft\Serializer\Strategy;

class ResourceSerializer extends PrimitiveSerializer
{
    public function &serialize(&$value)
    {
        return $value;
    }

    public function &unserialize(&$value)
    {
        return $value;
    }

    public function getTypes(): array
    {
        return ['resource'];
    }
}
