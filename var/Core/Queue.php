<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:29
 */

namespace Core;

trait Queue
{
    /**
     * 数据队列
     *
     * @var array
     */
    protected $queue = [];

    /**
     * 最后出队数据
     *
     * @var mixed
     */
    protected $row;

    /**
     * 检测队列是否存在数据
     *
     * @return bool
     */
    public function have()
    {
        return !empty($this->queue);
    }

    /**
     * 出队
     *
     * @return mixed|null
     */
    public function next()
    {
        if (count($this->queue) > 0) {
            $this->row = array_shift($this->queue);
        } else {
            $this->row = null;
        }

        return $this->row;
    }

    /**
     * 获取队列
     *
     * @return array
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * 设置队列
     *
     * @param array $queue
     */
    public function setQueue(array $queue)
    {
        $this->queue = $queue;
    }
}