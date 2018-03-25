<?php

namespace Denimsoft\Serializer\Strategy;

class ArraySerializer extends PrimitiveSerializer
{
    /**
     * @var array
     */
    private $references = [];

    /**
     * @var array
     */
    private $serialized = [];

    public function &serialize(&$value)
    {
        if (($offset = $this->getCopyOffset($value)) === null) {
            $this->references[] = &$value;
            $result = [];
            $this->serialized[] = &$result;
            $offset = count($this->serialized) - 1;

            foreach ($value as $k => &$v) {
                $result[$k] = &$this->strategyService->serialize($v);
            }
        }

        return $this->serialized[$offset];
    }

    public function &unserialize(&$value)
    {
        if (($offset = $this->getCopyOffset($value)) === null) {
            $this->references[] = &$value;

            foreach ($value as $k => &$v) {
                $v = $this->strategyService->unserialize($v);
            }
        }

        return $value;
    }

    public function getTypes(): array
    {
        return ['array'];
    }

    /**
     * Prevents indefinitely recursing into an array if it is already being processed;
     * this is determined by setting the reference to `NULL` and checking whether it
     * exists in the `references` array.
     *
     * @param mixed $value
     *
     * @return int|null
     */
    private function getCopyOffset(&$value)
    {
        $original = $value;
        $value = null;
        $offset = array_search(null, $this->references, true);
        $value = $original;

        return $offset !== false ? $offset : null;
    }
}
