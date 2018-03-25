<?php

namespace Denimsoft\Serializer\Strategy;

class NullSerializer extends PrimitiveSerializer
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
        return ['NULL'];
    }
}
