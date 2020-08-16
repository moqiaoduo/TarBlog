<?php
/**
 * Created by TarBlog.
 * Date: 2020/4/3
 * Time: 19:42
 */

namespace Exceptions;

use Exception;

class FacadeNotBindContainer extends Exception
{
    protected $message = 'This facade has not been bound to a container.';
}