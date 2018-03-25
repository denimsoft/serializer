<?php

namespace Denimsoft\Serializer\Strategy\Object;

use Denimsoft\Serializer\Exception\PrimitiveNotSupportedException;
use Denimsoft\Serializer\Strategy\AbstractSerializer;
use Denimsoft\Serializer\Strategy\Serializer as RootSerializer;
use Denimsoft\Serializer\Strategy\StrategyService as RootStrategyService;

class StrategyService extends RootStrategyService
{
    /**
     * @var RootStrategyService
     */
    private $rootStrategyService;

    public function __construct(RootStrategyService $rootStrategyService)
    {
        $this->rootStrategyService = $rootStrategyService;

        parent::__construct();
    }

    protected function registerSerializers()
    {
        $this->serializers[] = new ClosureSerializer($this->rootStrategyService);
        $this->serializers[] = new InternalClassSerializer($this->rootStrategyService);
        $this->serializers[] = new ClassSerializer($this->rootStrategyService);
    }

    protected function getSerializer($value): RootSerializer
    {
        /** @var AbstractSerializer $serializer */
        foreach ($this->serializers as $serializer) {
            if ($serializer->canSerialize($value)) {
                return $serializer;
            }
        }

        throw new PrimitiveNotSupportedException();
    }
}
