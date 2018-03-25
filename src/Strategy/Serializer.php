<?php

namespace Denimsoft\Serializer\Strategy;

abstract class Serializer
{
    /**
     * @var StrategyService
     */
    protected $strategyService;

    public function __construct(StrategyService $strategyService)
    {
        $this->strategyService = $strategyService;
    }

    abstract public function &serialize(&$value);

    abstract public function &unserialize(&$value);
}
