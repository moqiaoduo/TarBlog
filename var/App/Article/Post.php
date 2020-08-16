<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:45
 */

namespace App\Article;

class Post extends \App\Article
{
    protected $type = 'post';

    /**
     * @inheritDoc
     */
    public function render()
    {
        include $this->_themeDir . DIRECTORY_SEPARATOR . 'post.php';
    }

    public function categories()
    {
        return ($this->_data)->getCategories(['model' => true]);
    }

    public function _respondId()
    {
        return 'respond-post-' . $this->_data->cid;
    }
}