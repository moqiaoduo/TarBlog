<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/4
 * Time: 11:35
 */

namespace Exceptions;

class NotSupportSQLQuery extends \Exception
{
    protected $message = 'The database does not support this operation.';
}