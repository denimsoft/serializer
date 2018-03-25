<?php

namespace Denimsoft\Serializer\Strategy;

use Denimsoft\Serializer\Strategy\Object\StrategyService;

class ObjectSerializer extends PrimitiveSerializer
{
    public function __construct(StrategyService $strategyService)
    {
        parent::__construct($strategyService);
    }

    public function &serialize(&$value)
    {
        return $this->strategyService->serialize($value);
    }

    public function &unserialize(&$value)
    {
        return $this->strategyService->unserialize($value);
    }

    public function getTypes(): array
    {
        return ['object'];
    }
}
