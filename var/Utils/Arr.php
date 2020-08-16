<?php
/**
 * Created by tarblog.
 * Date: 2020/5/23
 * Time: 21:59
 */

namespace Utils;

class Arr
{
    /**
     * 包裹成数组
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) return [];

        if (is_array($value)) return $value;

        return [$value];
    }
}