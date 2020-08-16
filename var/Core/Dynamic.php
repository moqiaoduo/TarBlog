<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 22:30
 */

namespace Core;

/**
 * 动态数据
 * 使用 _方法名 命名方法，访问方法时显示内容，访问成员变量时返回内容
 */
trait Dynamic
{
    /**
     * 用于获取某些数据的魔术方法
     *
     * @param string $name
     * @return mixed|null|void
     */
    public function __get($name)
    {
        $data_name = '_' . $name;
        if (method_exists($this, $data_name))
            return $this->$data_name();
    }

    /**
     * 用于显示某些数据的魔术方法
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $data_name = '_' . $name;
        if (method_exists($this, $data_name))
            echo $this->$data_name(...$arguments);
    }
}