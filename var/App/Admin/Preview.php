<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/26
 * Time: 11:16
 */

namespace App\Admin;

use App\Article;
use App\Base;
use Collection\Comments;
use Helper\Content as Helper;
use Models\Content as Model;
use Models\Post;
use Utils\Auth;

class Preview extends Article
{
    public function execute(): bool
    {
        if (empty($id = $this->request->get('id')))
            showErrorPage('请指定一个页面', 404);

        $article = $this->db->table('contents')->where('cid', $id)
            ->whereNull('deleted_at')->firstWithModel(Model::class);

        /* @var Model $article */

        if (is_null($article)) showErrorPage('文章不存在', 404);

        if (!in_array($article->type, ['post', 'page', 'post_draft', 'page_draft']))
            showErrorPage('该内容无法预览', 403);

        if (($article->type == 'post' || $article->type == 'page') &&
            $draft = Helper::getDraftByParent($article->type, $id)) {
            $article->title = $draft['title'];
            $article->content = $draft['content'];
            $article->created_at = $draft['created_at'];
            $article->updated_at = $draft['updated_at'];
        }

        $article->allowComment = false;

        $this->type = substr($article->type, 0, 4);
        $this->_archiveTitle = '[预览]' . $article->title;

        // 这里要做个限制，管理不到的文章是不允许预览的
        if (!Auth::check('post-premium', false) // 编辑和以上都可以查看
            && $this->user->id() != $article->uid // 作者本人也可以查看
        ) showErrorPage('您无权查看该页面', 403);

        $this->_data = $article;

        return true;
    }

    public function render()
    {
        if ($this->type == 'post')
            include $this->_themeDir . DIRECTORY_SEPARATOR . 'post.php';

        if ($this->type == 'page') {
            $tpl = $this->_data->template;
            if (!empty($tpl) && file_exists($file = $this->_themeDir . DIRECTORY_SEPARATOR . 'page-' . $tpl. '.php')) {
                include $file;
            } else {
                include $this->_themeDir . DIRECTORY_SEPARATOR . 'page.php';
            }
        }
    }

    /**
     * 这个还是要兼容一下的
     *
     * @return array|mixed|null
     */
    public function categories()
    {
        return (new Post($this->_data->getData()))->getCategories(['model' => true]);
    }

    /**
     * 预览时不显示评论区
     *
     * @return Comments
     */
    public function comments()
    {
        return new Comments();
    }
}