<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/4
 * Time: 11:24
 */

namespace Core\Database;

use Core\Paginator;

trait Page
{
    /**
     * 按分页获取数据
     * 与构建分页不同，该方法不会计算数据总数
     * 后续版本，将会调用SQLBuilderAdapter的page方法来实现，因为不同数据的实现方法可能不同
     *
     * @param $page
     * @param $perPage
     * @return array|mixed|null
     */
    public function page($page, $perPage)
    {
        return $this->adapter->page($this, $page, $perPage);
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