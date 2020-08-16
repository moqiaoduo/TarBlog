<?php
/**
 * Created by tarblog.
 * Date: 2020/5/23
 * Time: 22:13
 */

namespace Utils;

use Core\Database\Model;
use Core\Database\Query;
use Core\Database\Raw;
use Core\Facade;

/**
 * @method static bool beginTransaction()
 * @method static bool commit()
 * @method static bool rollback()
 * @method static mixed query($sql, $bindParams = [], $single = false)
 * @method static bool exec($sql, $bindParams = [])
 * @method static Query table($table, $as = '')
 * @method static int lastInsertId($name = null)
 */
class DB extends Facade
{
    protected static function getFacadeInstanceAlias()
    {
        return 'db';
    }

    /**
     * 不转义内容
     *
     * @param $sql
     * @return Raw
     */
    public static function raw($sql)
    {
        return new Raw($sql);
    }

    /**
     * @param string $table 表名
     * @param Model $model 模型对象
     * @param string $pk 主键，默认为id
     * @param bool $update 是否为更新
     * @param bool $loadFirst 重新读取数据库后再更新，以防并发造成的数据错误
     * @return Model|null
     */
    public static function saveWithModel($table, $model, $pk = 'id', $update = false, $loadFirst = false)
    {
        $data = [];

        if ($update) {
            if ($loadFirst) {
                $old_model = DB::table($table)->where($pk, $model->$pk)->firstWithModel(get_class($model));
                if (is_null($old_model)) return null;
                foreach ($model->getData() as $key => $val) {
                    if ($val == $old_model->$key) continue;
                    $data[$key] = $val;
                }
            } else {
                foreach ($model->getChangeFields() as $changeField) {
                    $data[$changeField] = $model[$changeField];
                }
            }

            if (empty($data)) return $model;
            $bool = DB::table($table)->where($pk, $model->$pk)->update($data);
        } else {
            foreach ($model->getData() as $key => $val) {
                if (empty($val)) continue;
                $data[$key] = $val;
            }
            $bool = DB::table($table)->insert($data);
            $id = DB::lastInsertId();
        }

        if (!empty($id)) $model->$pk = $id;

        return $bool ? $model : null;
    }
}