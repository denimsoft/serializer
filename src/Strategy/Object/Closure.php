<?php

namespace Denimsoft\Serializer\Strategy\Object;

class Closure
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $static;

    /**
     * @var object
     */
    private $closureThis;

    public function __construct(string $source, array $static, $closureThis)
    {
        $this->source = $source;
        $this->static = $static;
        $this->closureThis = $closureThis;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStaticVariables(): array
    {
        return $this->static;
    }

    public function getClosureThis()
    {
        return $this->closureThis;
    }
}
