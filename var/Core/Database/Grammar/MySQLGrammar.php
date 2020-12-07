<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/7
 * Time: 19:25
 */

namespace Core\Database\Grammar;

class MySQLGrammar implements Grammar
{
    /**
     * @inheritDoc
     */
    public function getQuote(): string
    {
        return '`';
    }
}