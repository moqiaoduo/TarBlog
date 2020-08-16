<?php
/**
 * Created by tarblog.
 * Date: 2020/6/4
 * Time: 15:10
 */

namespace Core;

trait QueuePaginator
{
    use Queue;

    /**
     * 分页器
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     * 显示分页
     *
     * @param array $params
     */
    public function pageNav($params = [])
    {
        $this->paginator->view($params);
    }
}