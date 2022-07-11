<?php


namespace App\Common;

use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use \Exception;

abstract class BaseType implements ArrayAccess, Arrayable, IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * @return array
     */
    abstract public function getAttributes(): array;

    /**
     * @param mixed $offset
     * @return bool
     */
    final public function offsetExists($offset)
    {
        $attributes = $this->getAttributes();
        return in_array($offset, $attributes);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    final public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->$offset;
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    final public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     * @param mixed $offset
     */
    final public function offsetUnset($offset)
    {
        return;
    }

    /**
     * @return array
     */
    final public function toArray()
    {
        $tmpArray = [];
        $attributes = $this->getAttributes();
        foreach ($attributes as $itemKey) {
            $tmpArray[$itemKey] = $this->$itemKey;
        }
        return $tmpArray;
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }


    /**
     * @param int $options
     * @return string
     * @throws Exception
     */
    final public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception(json_last_error_msg());
        }

        return $json;
    }

    /**
     * @return array|mixed
     */
    final public function jsonSerialize()
    {
        return $this->toArray();
    }
}
