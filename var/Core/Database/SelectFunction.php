<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/4
 * Time: 11:23
 */

namespace Core\Database;

trait SelectFunction
{
    /**
     * 字段求和
     *
     * @param string $column
     * @return mixed
     */
    public function sum($column)
    {
        $this->switchAction('select');

        $this->select(new Raw("sum(`$column`) as `sum`"));

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters, true)['sum'];
    }

    /**
     * max函数
     *
     * @param string $column
     * @return mixed
     */
    public function max($column)
    {
        $this->switchAction('select');

        $this->select(new Raw("max(`$column`) as `max`"));

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters, true)['max'];
    }

    /**
     * min函数
     *
     * @param string $column
     * @return mixed
     */
    public function min($column)
    {
        $this->switchAction('select');

        $this->select(new Raw("min(`$column`) as `sum`"));

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters, true)['sum'];
    }

    /**
     * 获取记录总数
     *
     * @return mixed
     */
    public function count()
    {
        $this->switchAction('select');

        $this->select(new Raw('count(*) as `count`'));

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters, true)['count'];
    }

    /**
     * 根据count结果判断记录是否存在
     *
     * @return bool
     */
    public function exists()
    {
        return $this->count() > 0;
    }
}