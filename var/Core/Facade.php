<?php
/**
 * Created by tarblog.
 * Date: 2020/5/23
 * Time: 22:15
 */

namespace Core;


use Core\Container\Manager;

abstract class Facade
{
    /**
     * 获取门面对应的容器绑定名称
     *
     * @return string
     */
    protected abstract static function getFacadeInstanceAlias();

    /**
     * 魔术方法，用于静态方式调用动态方法
     *
     * @param $name
     * @param $arguments
     * @return mixed|void
     */
    public static function __callStatic($name, $arguments)
    {
        $alias = static::getFacadeInstanceAlias();

        $obj = Manager::getInstance()->make($alias);

        if (method_exists($obj, $name)) {
            return $obj->$name(...$arguments);
        }
    }
}