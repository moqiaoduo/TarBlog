<?php
/**
 * Created by tarblog.
 * Date: 2020/7/30
 * Time: 15:00
 */

namespace Core;

use Utils\Arr;

class Errors
{
    private $errors;

    public function __construct($errors = [])
    {
        $this->errors = Arr::wrap($errors);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->errors);
    }

    public function first()
    {
        if (empty($this->errors)) return null;
        reset($this->errors);
        return current($this->errors);
    }

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->errors[$key] : $default;
    }

    public function all()
    {
        return $this->errors;
    }
}