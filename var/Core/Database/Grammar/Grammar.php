<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/7
 * Time: 15:44
 */

namespace Core\Database\Grammar;

interface Grammar
{
    /**
     * 与保留字区别的“括号”
     * 一般是用于字段、表名和别名
     *
     * @return string
     */
    public function getQuote(): string;
}