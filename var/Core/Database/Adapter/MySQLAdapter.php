<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/7
 * Time: 19:16
 */

namespace Core\Database\Adapter;

use Core\Database\Query;

class MySQLAdapter extends SQLBuildAdapter
{
    protected $grammar = 'Core\Database\Grammar\MySQLGrammar';

    public function limit($limit, $offset, $noOffset)
    {
        return is_null($offset) || $noOffset ? $limit : $offset . ', ' . $limit;
    }

    /**
     * @param Query $query
     * @param $page
     * @param $perPage
     * @return array|mixed|void|null
     */
    public function page($query, $page, $perPage)
    {
        if ($page < 1) $page = 1; // 防止因为页数问题挂掉

        $offset = ($page - 1) * $perPage;
        $query->offset($offset);
        $query->limit($perPage);

        return $query->get();
    }
}