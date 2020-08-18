<?php
/**
 * Created by tarblog.
 * Date: 2020/5/27
 * Time: 1:29
 */

namespace App;

use Core\QueuePaginator;
use Models\Post;
use Utils\Route;
use Utils\Str;

abstract class Archive extends Base
{
    use QueuePaginator;

    public function render()
    {
        include $this->_themeDir . DIRECTORY_SEPARATOR . 'index.php';
    }

    public function categories()
    {
        return (new Post($this->row))->getCategories(['model' => true]);
    }

    public function _title()
    {
        return $this->row['title'];
    }

    public function _link()
    {
        return route('post', Route::fillPostParams(new Post($this->row)));
    }

    public function _time($column = 'created_at')
    {
        return dateX(1, $this->row[$column]);
    }

    public function _timeRaw($column = 'created_at')
    {
        return $this->row[$column];
    }

    public function _commentsNum()
    {
        $arg_num = func_num_args();
        $count = $this->row['commentsNum'];
        if ($arg_num > 0) {
            if ($count >= $arg_num)
                $show = func_get_arg($arg_num - 1);
            else
                $show = func_get_arg($count);

            $show = sprintf($show, $count);
        } else {
            $show = sprintf("%d 条评论", $count);
        }

        return $show;
    }

    public function _preview()
    {
        if ($this->row['status'] == 'password') {
            return '此文章已加密，无法预览';
        } else {
            $params = func_get_args();

            return Str::limit($this->row['content'], ...$params);
        }
    }
}