<?php

namespace Denimsoft\Serializer\Strategy\Object;

use ReflectionClass;
use ReflectionMethod;

class InternalClassSerializer extends ClassSerializer
{
    public function canSerialize($value): bool
    {
        return parent::canSerialize($value)
            && $this->getReflector($value)->isInternal();
    }

    protected function createCloneWithSerializedValues(&$value, &$serialized)
    {
        $reflector = $this->getReflector($value);

        switch ($reflector->getName()) {
            case ReflectionClass::class:
                $serialized = $reflector->newInstanceArgs([$value->getName()]);
                break;
            case ReflectionMethod::class:
                $serialized = $reflector->newInstanceArgs([$value->class, $value->getName()]);
                break;
            default:
                throw new \Exception('Not Implemented');
        }

        for (; $reflector; $reflector = $reflector->getParentClass()) {
            $this->serializeValues($reflector, $value, $serialized);
        }

        return $serialized;
    }

    protected function serializeValues(ReflectionClass $reflector, $object, $copy)
    {
        foreach ($reflector->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $reflector->getName()
                || in_array($property->getName(), ['name', 'class'], true)
            ) {
                continue;
            }

            if ($property->isStatic()) {
                // not implemented
            } else {
                $accessible = $property->isPublic();
                $property->setAccessible(true);
                $value = $property->getValue($object);
                $value = &$this->strategyService->serialize($value);
                $property->setValue($copy, $value);
                $property->setAccessible($accessible);
            }
        }
    }

    protected function unserializeValues(ReflectionClass $reflector, $object)
    {
        // not implemented
    }
}
