<?php

namespace Denimsoft\Serializer\Strategy\Object;

use Closure;
use Denimsoft\Serializer\Strategy\AbstractSerializer;
use Denimsoft\Serializer\Strategy\Object\Closure as SerializedClosure;
use ReflectionFunction;

class ClosureSerializer extends AbstractSerializer
{
    private $serialized = [];

    public function canSerialize($value): bool
    {
        return $value instanceof Closure
            || $value instanceof SerializedClosure;
    }

    /**
     * @param Closure $value
     *
     * @return SerializedClosure
     */
    public function &serialize(&$value)
    {
        $hash = spl_object_hash($value);
        $serialized = &$this->serialized[$hash];

        if (!$serialized) {
            $serialized = true;
            $reflector = new ReflectionFunction($value);
            $source = $this->getSource($reflector);
            $static = $reflector->getStaticVariables();
            $static = $this->strategyService->serialize($static);
            $closureThis = $reflector->getClosureThis();
            $closureThis = $this->strategyService->serialize($closureThis);

            $serialized = new SerializedClosure($source, $static, $closureThis);
        }

        return $serialized;
    }

    /**
     * @param SerializedClosure $value
     *
     * @return Closure
     */
    public function &unserialize(&$value)
    {
        if ($value instanceof Closure) {
            return $value;
        }

        $hash = spl_object_hash($value);
        $serialized = &$this->serialized[$hash];

        if (!$serialized) {
            $serialized = true;

            $unserializer = new class() {
                public $source;
                public $static;
                public $closureThis;
                private $closure;

                public function __invoke(): Closure
                {
                    extract($this->static, EXTR_REFS);
                    eval('$this->closure = ' . $this->source . ';');

                    return $this->closure->bindTo($this->closureThis);
                }
            };

            $unserializer->source = $value->getSource();
            $closureThis = $value->getClosureThis();
            $unserializer->closureThis = $this->strategyService->unserialize($closureThis);

            // static must be last in case of self-referential static variable
            $static = $value->getStaticVariables();
            $unserializer->static = $this->strategyService->unserialize($static);

            $serialized = $unserializer();
        }

        return $serialized;
    }

    private function getSource(ReflectionFunction $reflector): string
    {
        $definition = implode(
            '',
            array_slice(
                file($reflector->getFileName()),
                $reflector->getStartLine() - 1,
                $reflector->getEndLine() - $reflector->getStartLine() + 1
            )
        );

        $definition = preg_replace('/^.*?function/', 'function', $definition);
        $definition = preg_replace('/[) \t\r\n]*[;,][ \t\r\n]*$/', ';', $definition);

        return $definition;
    }
}
