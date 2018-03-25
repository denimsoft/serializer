<?php

namespace Denimsoft\Serializer\Strategy\Object;

use Denimsoft\Serializer\Strategy\AbstractSerializer;
use ReflectionClass;

class ClassSerializer extends AbstractSerializer
{
    private $reflectors = [];
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
            $this->createCloneWithSerializedValues($value, $serialized);
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

    protected function getReflector($object): ReflectionClass
    {
        $class = get_class($object);
        $reflector = &$this->reflectors[$class];

        if (!$reflector) {
            $reflector = new ReflectionClass($class);
        }

        return $reflector;
    }

    protected function createCloneWithSerializedValues(&$value, &$serialized)
    {
        $reflector = $this->getReflector($value);
        $serialized = $reflector->newInstanceWithoutConstructor();

        for (; $reflector; $reflector = $reflector->getParentClass()) {
            $this->serializeValues($reflector, $value, $serialized);
        }

        return $serialized;
    }

    protected function serializeValues(ReflectionClass $reflector, $object, $copy)
    {
        $strategyService = $this->strategyService;

        foreach ($reflector->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $reflector->getName()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isStatic()) {
                $value = (function & () use ($name) {
                    return self::$$name;
                })->bindTo($object, $reflector->getName())();
                $value = &$this->strategyService->serialize($value);
                (function () use ($name, &$value) {
                    self::$$name = $value;
                })->bindTo($copy, $reflector->getName())();
            } else {
                (function () use ($copy, $strategyService, $name) {
                    $copy->$name = &$strategyService->serialize($this->$name);
                })->bindTo($object, $reflector->getName())();
            }
        }
    }

    protected function unserializeValues(ReflectionClass $reflector, $object)
    {
        $strategyService = $this->strategyService;

        foreach ($reflector->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $reflector->getName()) {
                continue;
            }

            $name = $property->getName();

            if ($property->isStatic()) {
                $value = &$this->strategyService->unserialize($value);
                (function () use ($name, &$value) {
                    self::$$name = $value;
                })->bindTo($object, $reflector->getName())();
            } else {
                (function () use ($strategyService, $name) {
                    $this->$name = $strategyService->unserialize($this->$name);
                })
                ->bindTo($object, $reflector->getName())();
            }
        }
    }
}
