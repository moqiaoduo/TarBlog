<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/4
 * Time: 11:15
 */

namespace Core\Database;

use Utils\Arr;

/**
 * Trait Builder
 * @package Core\Database
 */
trait Builder
{
    /**
     * 构建select子句
     *
     * @return string
     */
    protected function buildSelectSql(): string
    {
        return $this->adapter->select($this->query['select']);
    }

    /**
     * 构建where子句
     *
     * @return string
     */
    protected function buildWhereSql(): string
    {
        return $this->adapter->where($this->query['where'], $this->parsed_parameters);
    }

    /**
     * 构建order by子句
     *
     * @return string
     */
    protected function buildOrderBySql(): string
    {
        return $this->adapter->orderBy($this->query['order']);
    }

    /**
     * 构建group by子句
     *
     * @return string
     */
    protected function buildGroupBySql(): string
    {
        return $this->adapter->orderBy($this->query['group']);
    }

    /**
     * 构建having子句
     * @waring 未验证的代码
     *
     * @return string
     */
    protected function buildHavingSql(): string
    {
        return $this->adapter->having($this->query['having'], $this->parsed_parameters);
    }

    /**
     * 构建limit子句
     *
     * @param bool $noOffset 不加入offset部分
     * @return mixed
     */
    protected function buildLimitSql($noOffset = false)
    {
        return $this->adapter->limit($this->query['limit'], $this->query['offset'], $noOffset);
    }
}