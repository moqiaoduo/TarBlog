<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/7
 * Time: 15:37
 */

namespace Core\Database\Adapter;

use Core\Database\Grammar\Grammar;
use Core\Database\Raw;
use Utils\Arr;

abstract class SQLBuildAdapter
{
    /**
     * 语法适配，便于直接使用现成通用构建方法而不是每次都要重写
     *
     * @var Grammar
     */
    protected $grammar;

    public function __construct()
    {
        if (is_string($this->grammar) && class_exists($this->grammar)) {
            // 自动初始化grammar
            $this->grammar = new $this->grammar;
        }
    }

    /**
     * select语句的构建
     *
     * @param array $select
     * @return string
     */
    public function select(array $select): string
    {
        $sql = '';
        $quote = $this->grammar->getQuote();

        end($select);
        $last_k = key($select);

        foreach ($select as $k => $v) {
            if ($v instanceof Raw || $v == "*") {
                $sql .= $v;
            } else {
                $sql .= $quote . $v . $quote;
            }
            if ($k != $last_k) $sql .= ",";
        }

        if (empty($sql)) $sql = "*";

        return $sql;
    }

    /**
     * where语句的构建
     *
     * @param array $wheres
     * @param $parsedParameters
     * @return string
     */
    public function where(array $wheres, &$parsedParameters): string
    {
        $sql = '';
        $last_prt = true;
        $quote = $this->grammar->getQuote();

        foreach ($wheres as $where) {
            switch ($where['type']) {
                case 'raw':
                    $raw = $where['sql'];
                    $parsedParameters = array_merge($parsedParameters, Arr::wrap($where['binds']));
                    break;
                case 'normal':
                    $raw = $quote . $where['column'] . $quote . ' ' . $where['operator'] . ' ?';
                    $parsedParameters[] = $where['value'];
                    break;
                case 'in':
                    if (empty($where['values']) || !is_array($where['values'])) break; // 防止空数组或非数组捣乱
                    $raw = $quote . $where['column'] . $quote . ($where['not'] ? ' not' : '') . ' in (' .
                        implode(", ", array_fill(0, count($where['values']), '?')) . ')';
                    $parsedParameters = array_merge($parsedParameters, $where['values']);
                    break;
                case 'null':
                    $raw = $quote . $where['column'] . $quote . ' is ' . ($where['not'] ? 'not ' : '') . 'null';
                    break;
                case 'between':
                    $raw = $quote . $where['column'] . $quote . ' between ? and ?';
                    $parsedParameters = array_merge($parsedParameters, $where['values']);
                    break;
                default:
                    $raw = '';
            }

            if (!empty($raw)) {
                $sql .= (empty($where['logic']) || $last_prt ? '' : ' ' . $where['logic'] . ' ') . $raw;
                $last_prt = trim($raw) == '(';
            }
        }

        return $sql;
    }

    /**
     * order by语句的构建
     *
     * @param array $orders
     * @return string
     */
    public function orderBy(array $orders): string
    {
        $sql = '';
        $quote = $this->grammar->getQuote();

        foreach ($orders as $order) {
            switch ($order['type']) {
                case 'normal':
                    $raw = "{$quote}{$order['column']}{$quote} {$order['sort']}";
                    break;
                case 'raw':
                    $raw = $order['sql'];
                    break;
                default:
                    $raw = '';
            }

            if (empty($sql)) {
                $sql = $raw;
            } elseif (!empty($raw)) {
                $sql .= ", " . $raw;
            }
        }

        return $sql;
    }

    /**
     * group by语句的构建
     *
     * @param array $groups
     * @return string
     */
    public function groupBy(array $groups): string
    {
        $sql = '';

        foreach ($groups as $group) {
            if (empty($sql)) $sql = $group;
            else $sql .= ', ' . $group;
        }

        return $sql;
    }

    /**
     * having语句的构建
     *
     * @return string
     */
    public function having(array $havings, &$parsedParameters)
    {
        $sql = '';

        foreach ($havings as $having) {
            switch ($having['type']) {
                case 'raw':
                    $raw = $having['sql'];
                    $this->parsed_parameters = array_merge($this->parsed_parameters, $having['binds']);
                    break;
                case 'normal':
                    $raw = '`' . $having['column'] . '` ' . $having['operator'] . ' ?';
                    $this->parsed_parameters[] = $having['value'];
                    break;
                case 'null':
                    $raw = '`' . $having['column'] . '` is ' . ($having['not'] ? 'not ' : '') . 'null';
                    break;
                case 'between':
                    $raw = '`' . $having['column'] . '` between ? and ?';
                    $this->parsed_parameters = array_merge($this->parsed_parameters, $having['values']);
                    break;
                default:
                    $raw = '';
            }

            if (empty($sql)) {
                $sql = $raw;
            } else {
                $sql .= (empty($having['logic']) ? '' : ' ' . $having['logic'] . ' ') . $raw;
            }
        }

        return $sql;
    }

    /**
     * 一些数据库不支持该用法，可以在方法内抛出NotSupportSQLQuery异常，或者返回null
     *
     * @return string
     */
    abstract public function limit($limit, $offset, $noOffset);

    /**
     * 各数据库实现方法不同，因此要求适配器来实现
     *
     * @param $query
     * @param $page
     * @param $perPage
     * @return array|mixed|null
     */
    abstract public function page($query, $page, $perPage);
}