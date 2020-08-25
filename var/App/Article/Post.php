<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:45
 */

namespace App\Article;

use App\Article;
use Models\Post as PostModel;
use Utils\Route;

class Post extends Article
{
    protected $type = 'post';

    /**
     * 上一篇文章缓存
     *
     * @var PostModel
     */
    private $_prevPost;

    /**
     * 下一篇文章缓存
     *
     * @var PostModel
     */
    private $_nextPost;

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

    private function getPrevPost()
    {
        if (!is_null($this->_prevPost))
            return $this->_prevPost;

        return $this->_prevPost = $this->_data->prev();
    }

    public function hasPrevPost()
    {
        return !is_null($this->getPrevPost());
    }

    private function getNextPost()
    {
        if (!is_null($this->_nextPost))
            return $this->_nextPost;

        return $this->_nextPost = $this->_data->next();
    }

    public function hasNextPost()
    {
        return !is_null($this->getNextPost());
    }

    public function _prevUrl()
    {
        return \route('post', Route::fillPostParams($this->getPrevPost()));
    }

    public function _prevTitle()
    {
        return $this->getPrevPost()->title;
    }

    public function _nextUrl()
    {
        return \route('post', Route::fillPostParams($this->getNextPost()));
    }

    public function _nextTitle()
    {
        return $this->getNextPost()->title;
    }

    public function _respondId()
    {
        return 'respond-post-' . $this->_data->cid;
    }
}