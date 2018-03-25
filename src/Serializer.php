<?php

namespace Denimsoft\Serializer;

use Denimsoft\Serializer\Strategy\StrategyService;

class Serializer
{
    public function serialize($value): string
    {
        $strategyService = $this->createStrategyService();
        $value = $strategyService->serialize($value);

        return serialize($value);
    }

    public function unserialize(string $value)
    {
        $strategyService = $this->createStrategyService();
        $value = unserialize($value);

        return $strategyService->unserialize($value);
    }

    private function createStrategyService(): StrategyService
    {
        return new StrategyService();
    }
}
