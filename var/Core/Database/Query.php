<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 20:55
 */

namespace Core\Database;

use Core\Database\Adapter\SQLBuildAdapter;
use Utils\Arr;

/**
 * 目前仅支持构造MySQL语句
 * 下一版本将做迁移
 */
class Query
{
    use Builder, CURD, SelectFunction, Page;

    /**
     * 数据库管理器
     *
     * @var Manager
     */
    protected $manager;

    /**
     * SQL语句构建适配器
     *
     * @var SQLBuildAdapter
     */
    protected $adapter;

    /**
     * 查询子句
     *
     * @var array
     */
    protected $query = [
        'select' => [],
        'where' => [],
        'group' => [],
        'having' => [],
        'order' => [],
        'limit' => null,
        'offset' => null
    ];

    /**
     * 表名
     *
     * @var string
     */
    protected $table;

    /**
     * 表别名
     *
     * @var string
     */
    protected $table_as;

    /**
     * 表名前缀
     *
     * @var string
     */
    protected $prefix;

    /**
     * 执行什么操作
     *
     * @var string
     */
    protected $action = 'select';

    /**
     * 允许执行的操作
     *
     * @var array
     */
    protected $allow_actions = ['select', 'insert', 'update', 'delete'];

    /**
     * 无条件标识
     *
     * @var bool
     */
    protected $noWhere = true;

    /**
     * 解析后的参数
     *
     * @var array
     */
    protected $parsed_parameters = [];

    /**
     * 初始化查询类
     *
     * @param Manager $manager 数据库管理器
     * @param string $table
     * @param string $as
     * @param string $prefix
     */
    public function __construct(Manager $manager, $table, $as, $prefix = '')
    {
        $this->manager = $manager;
        $this->table = $table;
        $this->table_as = $as;
        $this->prefix = $prefix ?: $this->manager->getPrefix();
        $this->adapter = $this->manager->getAdapter();
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * 设置表名
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * 获取表别名
     *
     * @return string
     */
    public function getTableAs()
    {
        return $this->table_as;
    }

    /**
     * 设置表别名
     *
     * @param string $table_as
     */
    public function setTableAs($table_as)
    {
        $this->table_as = $table_as;
    }

    /**
     * 添加查询
     *
     * @param $type
     * @param $data
     */
    protected function addQuery($type, $data)
    {
        $this->query[$type][] = $data;
    }

    /**
     * where语句
     *
     * @param mixed $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @param string $logic
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $logic = 'and')
    {
        if ($column instanceof Raw) {
            $this->whereRaw($column->getExpression());
        } elseif (func_num_args() == 1) {
            $this->addQuery('where', [
                'type' => 'raw',
                'sql' => '(',
                'logic' => $logic
            ]);

            $column($this);

            $this->addQuery('where', [
                'type' => 'raw',
                'sql' => ')',
            ]);
        } elseif (is_null($value)) {
            $this->addQuery('where', [
                'type' => 'normal',
                'column' => $column,
                'operator' => '=',
                'value' => $operator,
                'logic' => $logic
            ]);
        } else {
            $this->addQuery('where', [
                'type' => 'normal',
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'logic' => $logic
            ]);
        }

        return $this;
    }

    /**
     * or逻辑的where语句
     *
     * @param mixed $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * where in 语句
     *
     * @param mixed $column
     * @param array $values
     * @param bool $not
     * @param string $logic
     * @return $this
     */
    public function whereIn($column, $values, $not = false, $logic = 'and')
    {
        $this->addQuery('where', [
            'type' => 'in',
            'not' => $not,
            'column' => $column,
            'values' => $values,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * where not in 语句
     *
     * @param mixed $column
     * @param array $values
     * @param string $logic
     * @return $this
     */
    public function whereNotIn($column, $values, $logic = 'and')
    {
        return $this->whereIn($column, $values, true, $logic);
    }

    /**
     * or逻辑where in语句
     *
     * @param mixed $column
     * @param array $values
     * @param bool $not
     * @return $this
     */
    public function orWhereIn($column, $values, $not = false)
    {
        return $this->whereIn($column, $values, $not, 'or');
    }

    /**
     * or逻辑where not in 语句
     *
     * @param mixed $column
     * @param array $values
     * @return $this
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereIn($column, $values, true, 'or');
    }

    /**
     * where exists 子句
     *
     * @param callable $callback
     * @param string $logic
     * @return bool
     */
    public function whereExists($callback, $logic = 'and')
    {
        $query = new static($this->manager, $this->table, $this->table_as);

        $callback($query);

        $this->addQuery('where', [
            'type' => 'raw',
            'sql' => 'exists (' . $query->toSql() . ')',
            'logic' => $logic
        ]);

        return true;
    }

    /**
     * or逻辑where exists 子句
     *
     * @param callable $callback
     * @return bool
     */
    public function orWhereExists($callback)
    {
        return $this->whereExists($callback, 'or');
    }

    /**
     * where范围语句
     *
     * @param mixed $column
     * @param array $values
     * @param string $logic
     * @return $this
     */
    public function whereBetween($column, $values, $logic = 'and')
    {
        if (!is_array($values) || count($values) !== 2)
            throw new \InvalidArgumentException("The values must have 2 data.");

        $this->addQuery('where', [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑where范围语句
     *
     * @param mixed $column
     * @param array $values
     * @return $this
     */
    public function orWhereBetween($column, $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * where null 语句
     *
     * @param mixed $column
     * @param bool $not
     * @param string $logic
     * @return $this
     */
    public function whereNull($column, $not = false, $logic = 'and')
    {
        $this->addQuery('where', [
            'type' => 'null',
            'column' => $column,
            'not' => $not,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑where null 语句
     *
     * @param mixed $column
     * @param bool $not
     * @return $this
     */
    public function orWhereNull($column, $not = false)
    {
        return $this->whereNull($column, $not, 'or');
    }

    /**
     * where is not null 语句
     *
     * @param mixed $column
     * @param string $logic
     * @return $this
     */
    public function whereNotNull($column, $logic = 'and')
    {
        return $this->whereNull($column, true, $logic);
    }

    /**
     * or逻辑where is not null 语句
     *
     * @param mixed $column
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNull($column, true, 'or');
    }

    /**
     * 原生where语句
     *
     * @param mixed $expression
     * @param array $binds
     * @param string $logic
     * @return $this
     */
    public function whereRaw($expression, $binds = [], $logic = 'and')
    {
        $this->addQuery('where', [
            'type' => 'raw',
            'sql' => $expression,
            'binds' => $binds,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑原生where语句
     *
     * @param mixed $expression
     * @param array $binds
     * @return $this
     */
    public function orWhereRaw($expression, $binds = [])
    {
        return $this->whereRaw($expression, $binds);
    }

    /**
     * 条件成立时，将加入查询
     *
     * @param bool $condition
     * @param callable $callable
     * @param boolean $prt 在外面加括号
     * @param string $logic 该项只有在外面加括号才会应用
     * @return $this
     */
    public function when($condition, $callable, $prt = false, $logic = 'and')
    {
        if ($condition) {
            if ($prt)
                $this->addQuery('where', [
                    'type' => 'raw',
                    'sql' => '(',
                    'logic' => $logic
                ]);

            $callable($this);

            if ($prt)
                $this->addQuery('where', [
                    'type' => 'raw',
                    'sql' => ')'
                ]);
        }

        return $this;
    }

    /**
     * 切换sql语句模式
     *
     * @param string $action
     */
    public function switchAction($action)
    {
        if (!in_array($action, $this->allow_actions))
            throw new \InvalidArgumentException("$action is not allowed to use.");

        $this->action = $action;
    }

    /**
     * 修改select语句
     *
     * @param array $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->query['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->query['select'][] = $column;
        }

        return $this;
    }

    /**
     * 添加select
     *
     * @param mixed $column
     * @return $this
     */
    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $as => $column) {
            $this->query['select'] = $column;
        }

        return $this;
    }

    /**
     * 添加原生select语句
     *
     * @param mixed $expression
     * @return $this
     */
    public function selectRaw($expression)
    {
        $this->addSelect(new Raw($expression));

        return $this;
    }

    /**
     * order by 语句
     *
     * @param mixed $column
     * @param string $sort
     * @return $this
     */
    public function orderBy($column, $sort = 'asc')
    {
        if ($column instanceof Raw) {
            return $this->orderByRaw($column->getExpression());
        }

        $this->addQuery('order', [
            'type' => 'normal',
            'column' => $column,
            'sort' => $sort
        ]);

        return $this;
    }

    /**
     * 原生order by 语句
     *
     * @param mixed $expression
     * @param array $bindings
     * @return $thisu
     */
    public function orderByRaw($expression, $bindings = [])
    {
        $this->addQuery('order', [
            'type' => 'raw',
            'sql' => $expression,
            'binds' => $bindings
        ]);

        return $this;
    }

    /**
     * order by desc语句
     *
     * @param mixed $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * group by 子句
     *
     * @param mixed $columns
     */
    public function groupBy($columns)
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->addQuery('group', $column);
        }
    }

    /**
     * having子句
     *
     * @param mixed $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @param string $logic
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $logic = 'and')
    {
        if ($column instanceof Raw) {
            $this->havingRaw($column->getExpression());
        } elseif (func_num_args() == 1) {
            $this->addQuery('having', [
                'type' => 'raw',
                'sql' => '('
            ]);

            $column($this);

            $this->addQuery('having', [
                'type' => 'raw',
                'sql' => ')',
                'logic' => $logic
            ]);
        } elseif (is_null($value)) {
            $this->addQuery('having', [
                'type' => 'normal',
                'column' => $column,
                'operator' => '=',
                'value' => $operator,
                'logic' => $logic
            ]);
        } else {
            $this->addQuery('having', [
                'type' => 'normal',
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'logic' => $logic
            ]);
        }

        return $this;
    }

    /**
     * or逻辑having子句
     *
     * @param mixed $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        return $this->having($column, $operator, $value);
    }

    /**
     * having is null 子句
     *
     * @param mixed $column
     * @param bool $not
     * @param string $logic
     * @return $this
     */
    public function havingNull($column, $not = false, $logic = 'and')
    {
        $this->addQuery('having', [
            'type' => 'null',
            'column' => $column,
            'not' => $not,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑having is null 子句
     *
     * @param mixed $column
     * @param bool $not
     * @return $this
     */
    public function orHavingNull($column, $not = false)
    {
        return $this->havingNull($column, $not, 'or');
    }

    /**
     * having is not null 子句
     *
     * @param mixed $column
     * @param string $logic
     * @return $this
     */
    public function havingNotNull($column, $logic = 'and')
    {
        return $this->havingNull($column, true, $logic);
    }

    /**
     * or逻辑having is not null 子句
     *
     * @param mixed $column
     * @return $this
     */
    public function orHavingNotNull($column)
    {
        return $this->havingNull($column, true, 'or');
    }

    /**
     * having 范围语句
     *
     * @param mixed $column
     * @param array $values
     * @param string $logic
     * @return $this
     */
    public function havingBetween($column, $values, $logic = 'and')
    {
        if (!is_array($values) || count($values) !== 2)
            throw new \InvalidArgumentException("The values must have 2 data.");

        $this->addQuery('having', [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑having 范围语句
     *
     * @param mixed $column
     * @param array $values
     * @return $this
     */
    public function orHavingBetween($column, $values)
    {
        return $this->havingBetween($column, $values, 'or');
    }

    /**
     * 原生having子句
     *
     * @param string $expression
     * @param array $binds
     * @param string $logic
     * @return $this
     */
    public function havingRaw($expression, $binds = [], $logic = 'and')
    {
        $this->addQuery('having', [
            'type' => 'raw',
            'sql' => $expression,
            'binds' => $binds,
            'logic' => $logic
        ]);

        return $this;
    }

    /**
     * or逻辑原生having子句
     *
     * @param string $expression
     * @param array $binds
     * @return $this
     */
    public function orHavingRaw($expression, $binds = [])
    {
        return $this->havingRaw($expression, $binds);
    }

    /**
     * 设置limit
     *
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        if ($limit < 0) $limit = 0;

        $this->query['limit'] = $limit; // 防止出现负数

        return $this;
    }

    /**
     * 设置offset
     *
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        if ($offset < 0) $offset = 0; // 防止出现负数

        $this->query['offset'] = $offset;

        return $this;
    }

    /**
     * 获取sql
     *
     * @param array $extra
     * @return string
     */
    public function toSql($extra = [])
    {
        $table = $this->prefix . $this->table;

        switch ($this->action) {
            case 'select':
                $select = $this->buildSelectSql();
                $where = $this->buildWhereSql();
                $group = $this->buildGroupBySql();
                $having = $this->buildHavingSql();
                $order = $this->buildOrderBySql();
                $limit = $this->buildLimitSql();
                $this->noWhere = empty(trim($where));
                $whereSql = $where ? ' where ' . $where : '';
                $groupSql = $group ? ' group by ' . $group : '';
                $havingSql = $having ? ' having ' . $having : '';
                $orderSql = $order ? ' order by ' . $order : '';
                $limitSql = $limit ? ' limit ' . $limit : '';
                $sql = "select {$select} from `{$table}`" . ($this->table_as ? " as `{$this->table_as}`" : "") .
                    "{$whereSql}{$groupSql}{$havingSql}{$orderSql}{$limitSql}";
                break;
            case 'insert':
                $phd = implode(", ", array_fill(0, count($extra), "?"));
                $columns = implode("`, `", $extra);
                $sql = "insert into `{$table}` (`{$columns}`) values ({$phd})";
                break;
            case 'update':
                $sets = '';
                foreach ($extra as $val) {
                    if (!empty($sets))
                        $sets .= ", ";
                    $sets .= "`$val` = ?";
                }
                $where = $this->buildWhereSql();
                $order = $this->buildOrderBySql();
                $limit = $this->buildLimitSql();
                $this->noWhere = empty(trim($where));
                $whereSql = $where ? ' where ' . $where : '';
                $orderSql = $order ? ' order by ' . $order : '';
                $limitSql = $limit ? ' limit ' . $limit : '';
                $sql = "update `{$table}` set {$sets}{$whereSql}{$orderSql}{$limitSql}";
                break;
            case 'delete':
                $where = $this->buildWhereSql();
                $order = $this->buildOrderBySql();
                $limit = $this->buildLimitSql();
                $this->noWhere = empty(trim($where));
                $whereSql = $where ? ' where ' . $where : '';
                $orderSql = $order ? ' order by ' . $order : '';
                $limitSql = $limit ? ' limit ' . $limit : '';
                $sql = "delete from `{$table}`{$whereSql}{$orderSql}{$limitSql}";
                break;
            default:
                $sql = '';
        }
        return $sql;
    }
}