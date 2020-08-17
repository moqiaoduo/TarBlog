<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 20:55
 */

namespace Core\Database;

use Core\Paginator;
use Utils\Arr;

/**
 * 目前仅支持构造MySQL语句
 */
class Query
{
    /**
     * 数据库管理器
     *
     * @var Manager
     */
    protected $manager;

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

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

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

    public function whereNotIn($column, $values, $logic = 'and')
    {
        return $this->whereIn($column, $values, true, $logic);
    }

    public function orWhereIn($column, $values, $not = false)
    {
        return $this->whereIn($column, $values, $not, 'or');
    }

    public function orWhereNotIn($column, $values)
    {
        return $this->whereIn($column, $values, true, 'or');
    }

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

    public function orWhereExists($callback)
    {
        return $this->whereExists($callback, 'or');
    }

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

    public function orWhereBetween($column, $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

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

    public function orWhereNull($column, $not = false)
    {
        return $this->whereNull($column, $not, 'or');
    }

    public function whereNotNull($column, $logic = 'and')
    {
        return $this->whereNull($column, true, $logic);
    }

    public function orWhereNotNull($column)
    {
        return $this->whereNull($column, true, 'or');
    }

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

    public function orWhereRaw($expression, $binds = [])
    {
        return $this->whereRaw($expression, $binds);
    }

    /**
     * 条件成立时，将加入查询
     *
     * @param $condition
     * @param $callable
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

    public function select($columns = ['*'])
    {
        $this->query['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->query['select'][] = $column;
        }

        return $this;
    }

    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $as => $column) {
            $this->query['select'] = $column;
        }

        return $this;
    }

    public function selectRaw($expression)
    {
        $this->addSelect(new Raw($expression));

        return $this;
    }

    /**
     * 构建select子句
     *
     * @return string
     */
    protected function buildSelectSql()
    {
        $sql = '';

        $select = $this->query['select'];

        end($select);
        $last_k = key($select);

        foreach ($select as $k => $v) {
            if ($v instanceof Raw || $v == "*") {
                $sql .= $v;
            } else {
                $sql .= "`$v`";
            }
            if ($k != $last_k) $sql .= ",";
        }

        if (empty($sql)) $sql = "*";

        return $sql;
    }

    /**
     * 构建where子句
     *
     * @return string
     */
    protected function buildWhereSql()
    {
        $sql = '';
        $last_prt = true;

        foreach ($this->query['where'] as $where) {
            switch ($where['type']) {
                case 'raw':
                    $raw = $where['sql'];
                    $this->parsed_parameters = array_merge($this->parsed_parameters, Arr::wrap($where['binds']));
                    break;
                case 'normal':
                    $raw = '`' . $where['column'] . '` ' . $where['operator'] . ' ?';
                    $this->parsed_parameters[] = $where['value'];
                    break;
                case 'in':
                    if (empty($where['values']) || !is_array($where['values'])) break; // 防止空数组或非数组捣乱
                    $raw = '`' . $where['column'] . '`' . ($where['not'] ? ' not' : '') . ' in (' .
                        implode(", ", array_fill(0, count($where['values']), '?')) . ')';
                    $this->parsed_parameters = array_merge($this->parsed_parameters, $where['values']);
                    break;
                case 'null':
                    $raw = '`' . $where['column'] . '` is ' . ($where['not'] ? 'not ' : '') . 'null';
                    break;
                case 'between':
                    $raw = '`' . $where['column'] . '` between ? and ?';
                    $this->parsed_parameters = array_merge($this->parsed_parameters, $where['values']);
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

    public function orderByRaw($expression, $bindings = [])
    {
        $this->addQuery('order', [
            'type' => 'raw',
            'sql' => $expression,
            'binds' => $bindings
        ]);

        return $this;
    }

    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * 构建order by子句
     *
     * @return string
     */
    protected function buildOrderSql()
    {
        $sql = '';

        foreach ($this->query['order'] as $order) {
            switch ($order['type']) {
                case 'normal':
                    $raw = "`{$order['column']}` {$order['sort']}";
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

    public function groupBy($columns)
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->addQuery('group', $column);
        }
    }

    /**
     * 构建group by子句
     *
     * @return string
     */
    protected function buildGroupBySql()
    {
        $sql = '';

        foreach ($this->query['group'] as $group) {
            if (empty($sql)) $sql = $group;
            else $sql .= ', ' . $group;
        }

        return $sql;
    }

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

    public function orHaving($column, $operator = null, $value = null)
    {
        return $this->having($column, $operator, $value);
    }

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

    public function orHavingNull($column, $not = false)
    {
        return $this->havingNull($column, $not, 'or');
    }

    public function havingNotNull($column, $logic = 'and')
    {
        return $this->havingNull($column, true, $logic);
    }

    public function orHavingNotNull($column)
    {
        return $this->havingNull($column, true, 'or');
    }

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

    public function orHavingBetween($column, $values)
    {
        return $this->havingBetween($column, $values, 'or');
    }

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

    public function orHavingRaw($expression, $binds = [])
    {
        return $this->havingRaw($expression, $binds);
    }

    /**
     * 构建having子句
     * @waring 未验证的代码
     *
     * @return string
     */
    protected function buildHavingSql()
    {
        $sql = '';

        foreach ($this->query['having'] as $having) {
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
     * 设置limit
     *
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->query['limit'] = $limit;

        return $this;
    }

    /**
     * 设置offset
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->query['offset'] = $offset;

        return $this;
    }

    /**
     * 构建limit子句
     *
     * @param bool $noOffset 不加入offset部分
     * @return string
     */
    protected function buildLimitSql($noOffset = false)
    {
        return is_null($this->query['offset']) || $noOffset ?
            $this->query['limit'] :
            $this->query['offset'] . ', ' . $this->query['limit'];
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
                $order = $this->buildOrderSql();
                $limit = $this->buildLimitSql();
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
                $order = $this->buildOrderSql();
                $limit = $this->buildLimitSql();
                $whereSql = $where ? ' where ' . $where : '';
                $orderSql = $order ? ' order by ' . $order : '';
                $limitSql = $limit ? ' limit ' . $limit : '';
                $sql = "update `{$table}` set {$sets}{$whereSql}{$orderSql}{$limitSql}";
                break;
            case 'delete':
                $where = $this->buildWhereSql();
                $order = $this->buildOrderSql();
                $limit = $this->buildLimitSql();
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

    /**
     * 取记录
     *
     * @return mixed|null
     */
    public function get()
    {
        $this->switchAction('select');

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters);
    }

    /**
     * 取第一条记录
     *
     * @return mixed|null
     */
    public function first()
    {
        $this->switchAction('select');

        $this->limit(1); // 为减少获取的数据量，限制只获取一条，大部分数据库组件都会做这个事情

        $sql = $this->toSql();

        return $this->manager->query($sql, $this->parsed_parameters, true);
    }

    /**
     * 取第一条记录并放到模型里面
     *
     * @param string $model 模型类名
     * @return mixed
     */
    public function firstWithModel($model)
    {
        $data = $this->first();

        if (is_null($data)) return null;

        return new $model($data);
    }

    /**
     * 将记录作为键值对（或列表）输出
     * 例如：
     * 原数据：
     * [
     *  ['name'=>'aaa','value'=>'arg'],
     *  ['name'=>'dgc','value'=>'ejf']
     * ]
     * 参数 $value='value', $key='name'
     * 将会转换为：
     * ['aaa'=>'arg','dgc'=>'ejf']
     * $key = null时就会转换为：
     * ['arg','ejf']
     * 与Laravel的pluck是一样的
     *
     * @param string $value
     * @param string|null $key
     * @return array
     */
    public function pluck($value, $key = null)
    {
        $db_data = $this->get();
        $data = [];

        foreach ($db_data as $val) {
            if (is_null($key))
                $data[] = $val[$value];
            else
                $data[$val[$key]] = $val[$value];
        }

        return $data;
    }

    /**
     * 插入数据
     *
     * @param array $data
     * @param bool $batch 是否批量插入
     * @return bool
     */
    public function insert(array $data, $batch = false)
    {
        $this->switchAction('insert');

        $bool = false;

        if ($batch) {
            foreach ($data as $val) {
                $bool = $this->insertSingle($val);
                if (!$bool) break;
            }
        } else {
            $bool = $this->insertSingle($data);
        }

        return $bool;
    }

    /**
     * 单行插入
     *
     * @param $data
     * @return bool
     */
    protected function insertSingle($data)
    {
        $columns = array_keys($data);

        $values = array_values($data);

        return $this->manager->exec($this->toSql($columns), $values);
    }

    /**
     * 更新数据
     *
     * @param array $data
     * @param bool $returnRowCount 是否返回影响行数
     * @return bool|int
     */
    public function update(array $data, $returnRowCount = false)
    {
        $this->switchAction('update');

        $columns = array_keys($data);

        $values = array_values($data);

        $sql = $this->toSql($columns);

        $params = array_merge($values, $this->parsed_parameters);

        return $this->manager->exec($sql, $params, $returnRowCount);
    }

    /**
     * 删除数据
     *
     * @param bool $returnRowCount 是否返回影响行数
     * @return bool|int
     */
    public function delete($returnRowCount = false)
    {
        $this->switchAction('delete');

        $sql = $this->toSql();

        return $this->manager->exec($sql, $this->parsed_parameters, $returnRowCount);
    }

    /**
     * 字段求和
     *
     * @param $column
     * @return mixed
     */
    public function sum($column)
    {
        $this->switchAction('select');

        $this->select(new Raw("sum(`$column`) as sum"));

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

        $this->select(new Raw('count(*) as count'));

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

    /**
     * 按分页获取数据
     * 与构建分页不同，该方法不会计算数据总数，仅为offset limit get的封装
     *
     * @param $page
     * @param $perPage
     * @return array|mixed|null
     */
    public function page($page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        $this->offset($offset);
        $this->limit($perPage);

        return $this->get();
    }

    /**
     * 构建分页
     * 由于大多数情况下无需select，故精简
     *
     * @param int $page
     * @param int $perPage
     * @param string $pageName
     * @return Paginator
     */
    public function paginate($page, $perPage = 20, $pageName = 'page')
    {
        $query = clone $this;

        $total = $query->count(); // 获取总记录值

        return new Paginator($this->page($page, $perPage), $page, $perPage, $total);
    }
}