<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:32
 */

namespace Core;

class DataContainer
{
    use Queue;

    /**
     * 复制一份对象到变量
     *
     * @param $var
     */
    public function to(&$var)
    {
        $var = $this;
    }
}