<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/4
 * Time: 11:22
 */

namespace Core\Database;

trait CURD
{
    /**
     * 取所有记录
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
     * 新版改用array_column实现，效率也许更高
     *
     * @param string $value
     * @param string|null $key
     * @return array
     */
    public function pluck($value, $key = null)
    {
        return array_column($this->get(), $value, $key);
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
     * @param bool $force 是否强制更新（即使没有条件）
     * @return bool|int
     */
    public function update(array $data, $returnRowCount = false, $force = false)
    {
        $this->switchAction('update');

        $columns = array_keys($data);

        $values = array_values($data);

        $sql = $this->toSql($columns);

        if ($this->noWhere && !$force) return false;

        $params = array_merge($values, $this->parsed_parameters);

        return $this->manager->exec($sql, $params, $returnRowCount);
    }

    /**
     * 删除数据
     *
     * @param bool $returnRowCount 是否返回影响行数
     * @param bool $force 是否强制更新（即使没有条件）
     * @return bool|int
     */
    public function delete($returnRowCount = false, $force = false)
    {
        $this->switchAction('delete');

        $sql = $this->toSql();

        if ($this->noWhere && !$force) return false;

        return $this->manager->exec($sql, $this->parsed_parameters, $returnRowCount);
    }
}