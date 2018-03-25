<?php

use Denimsoft\Serializer\Serializer;

require_once __DIR__ . '/vendor/autoload.php';

class ComplexClass
{
    /**
     * @var Closure
     */
    private $incrementer;

    /**
     * @var array
     */
    private $array = [];

    /**
     * @var ComplexClass
     */
    private $self;

    /**
     * @var ComplexClass
     */
    private $clone;

    public function __construct()
    {
        $count =& $this->increment();
        $circularReferenceArray1 = [null, &$count];
        $circularReferenceArray2 = [&$circularReferenceArray1, &$count];
        $circularReferenceArray1[0] = &$circularReferenceArray2;
        $this->array = [&$circularReferenceArray1, &$circularReferenceArray2];
        $this->self = $this;
        $this->clone = clone $this;
    }

    public function &increment()
    {
        if (!$this->incrementer) {
            $count = 0;
            $self = $this;

            $incrementer = function & () use ($self, &$count, &$incrementer): int {
                $count = $count + 1;

                return $count;
            };

            $this->incrementer = $incrementer;
        }

        $fn = $this->incrementer;

        return $fn();
    }
}

$serializer = new Serializer();
$complexClass = new ComplexClass();
$serialized = $serializer->serialize($complexClass);
var_dump($serialized);
$unserialized = $serializer->unserialize($serialized);
$unserialized->increment();
$unserialized->increment();

var_dump($unserialized);
