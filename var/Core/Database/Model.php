<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:48
 */

namespace Core\Database;

class Model implements \ArrayAccess
{
    /**
     * 数据集
     *
     * @var array
     */
    private $data;

    /**
     * （通过魔术方法和Array方式）被修改的字段
     *
     * @var array
     */
    private $change_field;

    public function __construct(array $data = [])
    {
        $this->data = $data;

        $this->change_field = [];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;

        $this->change_field = [];
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        if (!in_array($name, $this->change_field))
            $this->change_field[] = $name;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;

        if (!in_array($offset, $this->change_field))
            $this->change_field[] = $offset;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);

        if (!in_array($offset, $this->change_field))
            $this->change_field[] = $offset;
    }

    public function getChangeFields()
    {
        return $this->change_field;
    }
}