<?php

namespace Denimsoft\Serializer\Strategy\Object;

use Denimsoft\Serializer\Strategy\AbstractSerializer;
use ReflectionClass;

class ClassSerializer extends AbstractSerializer
{
    private $serialized = [];

    public function canSerialize($value): bool
    {
        return get_class($value) !== false;
    }

    public function &serialize(&$value)
    {
        $hash = spl_object_hash($value);
        $serialized = &$this->serialized[$hash];

        if (!$serialized) {
            $reflector = new ReflectionClass($value);
            $serialized = $reflector->newInstanceWithoutConstructor();

            for (; $reflector; $reflector = $reflector->getParentClass()) {
                $this->serializeValuesIntoCopy($reflector, $value, $serialized);
            }
        }

        return $serialized;
    }

    public function &unserialize(&$value)
    {
        $hash = spl_object_hash($value);
        $serialized = &$this->serialized[$hash];

        if (!$serialized) {
            $reflector = new ReflectionClass($value);
            $serialized = $value;

            for (; $reflector; $reflector = $reflector->getParentClass()) {
                $this->unserializeValues($reflector, $value);
            }
        }

        return $serialized;
    }

    private function serializeValuesIntoCopy(ReflectionClass $reflector, $object, $copy)
    {
        $strategyService = $this->strategyService;

        foreach ($reflector->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $reflector->getName()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isStatic()) {
                (function & () use ($copy, $strategyService, $name) {
                    $copy::$$name = $strategyService->serialize($this::$$name);

                })
                ->bindTo($object, $reflector->getName())();
            } else {
                (function & () use ($copy, $strategyService, $name) {
                    $copy->$name = $strategyService->serialize($this->$name);

                })
                ->bindTo($object, $reflector->getName())();
            }
        }
    }

    private function unserializeValues(ReflectionClass $reflector, $object)
    {
        $strategyService = $this->strategyService;

        foreach ($reflector->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $reflector->getName()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isStatic()) {
                (function & () use ($strategyService, $name) {
                    $this::$$name = $strategyService->unserialize($this::$$name);

                })
                ->bindTo($object, $reflector->getName())();
            } else {
                (function & () use ($strategyService, $name) {
                    $this->$name = $strategyService->unserialize($this->$name);

                })
                ->bindTo($object, $reflector->getName())();
            }
        }
    }
}
