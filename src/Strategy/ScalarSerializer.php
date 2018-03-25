<?php

namespace Denimsoft\Serializer\Strategy;

class ScalarSerializer extends PrimitiveSerializer
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
        return ['boolean', 'integer', 'double', 'string'];
    }
}
