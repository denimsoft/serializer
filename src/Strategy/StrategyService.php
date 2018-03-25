<?php

namespace Denimsoft\Serializer\Strategy;

use Denimsoft\Serializer\Exception\PrimitiveNotSupportedException;
use Denimsoft\Serializer\Strategy\Object\StrategyService as ObjectStrategyService;

class StrategyService
{
    /**
     * @var Serializer[]
     */
    protected $serializers;

    /**
     * @var array[]
     */
    private $types = [];

    public function __construct()
    {
        $this->registerSerializers();
    }

    public function &serialize(&$value)
    {
        $serializer = $this->getSerializer($value);

        return $serializer->serialize($value);
    }

    public function &unserialize(&$value)
    {
        $serializer = $this->getSerializer($value);

        return $serializer->unserialize($value);
    }

    protected function registerSerializers()
    {
        $this->register(new ScalarSerializer($this));
        $this->register(new NullSerializer($this));
        $this->register(new ArraySerializer($this));
        $this->register(new ObjectSerializer(new ObjectStrategyService($this)));
        $this->register(new ResourceSerializer($this));
    }

    protected function getSerializer($value): Serializer
    {
        $serializer = $this->types[gettype($value)] ?? null;

        if (!$serializer) {
            throw new PrimitiveNotSupportedException();
        }

        return $serializer;
    }

    private function register(PrimitiveSerializer $serializer)
    {
        $types = $serializer->getTypes();
        $this->serializers[] = $serializer;
        $this->types += array_combine($types, array_fill(0, count($types), $serializer));
    }
}
