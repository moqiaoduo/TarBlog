<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:44
 */

namespace App;

use App\Article\Post;
use Collection\Comments;
use Core\Database\Manager as Database;
use Core\Errors;
use Models\Category;
use Models\Post as PostModel;
use Models\Page as PageModel;
use Utils\Auth;
use Utils\Route;

abstract class Article extends Base
{
    /**
     * 文章/页面模型
     *
     * @var PostModel|PageModel
     */
    protected $_data;

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
    public function execute(): bool
    {
        if ($this instanceof Post) {
            $type = 'post';
            $model = PostModel::class;
        } else {
            $type = 'page';
            $model = PageModel::class;
        }

        // 假如用页面做首页，则手动添加参数，以免无法识别
        if ($page_index = $this->route->getName() == 'page.index') {
            $this->routeParams['cid'] = $this->options->indexPage;
        }

        $data = $this->checkAndGetArticle($type, $model, $this->routeParams, $this->db);

        if (is_null($data)) return false;

        if ($data && (
            $data->status == 'private' && (
                !Auth::check('post-premium', false) // 编辑和以上都可以查看
                || $this->user->id() != $data->uid // 作者本人也可以查看
            ) || $data->status == 'waiting' // 无论如何，待审核谁都看不了，要看去看后台
            )) showErrorPage('您无权查看该页面', 403);

        if ($this->request->isMethod('post')) {
            // 退出浏览器后，密码失效，需要重新输入
            if ($this->request->post('password') == $data->password)
                $this->request->cookie()::set('_tarblog_article_' . $data->cid . '_password_', $data->password);
            else
                with_error('密码不正确');
            back();
        }

        $this->_data = $data;

        if (!$page_index)
            $this->_archiveTitle = $data->title;

        return true;
    }

    /**
     * @param string $type
     * @param string $model
     * @param array $routeParams
     * @param Database $db
     * @return PostModel|PageModel|null
     */
    public static function checkAndGetArticle($type, $model, $routeParams, $db)
    {
        if ($model == PostModel::class && isset($routeParams['directory'])) {
            $category = Route::verifyDirectory($routeParams['directory']);

            if (!$category) return null;
        }

        if (isset($routeParams['category'])) {
            $category = $db->table('metas')->where('type', 'category')
                ->where('slug', $routeParams['category'])->firstWithModel(Category::class);
            if (is_null($category)) return null;
        }

        if (isset($routeParams['slug'])) {
            $slug = $routeParams['slug'];

            if (isset($category))
                $data = $category->getPostBySlug($slug, $model);
            else
                $data = $db->table('contents')->where('type', $type)->whereNull('deleted_at')
                    ->where('slug', $slug)->firstWithModel($model);
        } elseif (isset($routeParams['cid'])) {
            $cid = $routeParams['cid'];

            if (isset($category))
                $data = $category->getPostById($cid, $model);
            else
                $data = $db->table('contents')->where('type', $type)->whereNull('deleted_at')
                    ->where('cid', $cid)->firstWithModel($model);
        } else {
            return null;
        }

        return $data;
    }

    public function _title()
    {
        return $this->_data->title;
    }

    public function _time($column = 'created_at')
    {
        return dateX(1, $this->_data[$column]);
    }

    public function _timeRaw($column = 'created_at')
    {
        return $this->_data[$column];
    }

    /**
     * 评论数处理
     *
     * @return string
     */
    public function _commentsNum()
    {
        $arg_num = func_num_args();
        $count = $this->_data['commentsNum'];
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

    /**
     * 文章内容处理
     *
     * @return string
     */
    public function _content()
    {
        // 密码不正确的话是不能显示文章内容的，即便是管理员也得输密码（反正知道）
        if ($this->_data->status == 'password' && $this->request->cookie('_tarblog_article_' . $this->_data->cid .
                '_password_') != $this->_data->password) {
            $error = new Errors($this->request->session('errors'));
            $err = $error->first();
            if (function_exists('article_password_form')) {
                return article_password_form($err);
            } else {
                return <<<HTML
<p><b>$err</b></p>
<p>该页面内容已被加密，请输入访问密码：</p>
<form method="post">
<input type="password" name="password">
<button type="submit">提交</button>
</form>
HTML;
            }
        } else {
            $this->plugin->article_content($this->_data); // 处理文章内容，比如加链接
            return $this->_data->content;
        }
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

    public function _prevUrl()
    {
        return \route('post', Route::fillPostParams($this->getPrevPost()));
    }

    public function _prevTitle()
    {
        return $this->getPrevPost()->title;
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

    public function _nextUrl()
    {
        return \route('post', Route::fillPostParams($this->getNextPost()));
    }

    public function _nextTitle()
    {
        return $this->getNextPost()->title;
    }

    public function comments()
    {
        return new Comments($this->_data);
    }

    public function allow($key)
    {
        switch ($key) {
            case 'comment':
                $rs = $this->_data->allowComment;
                if ($rs)
                    $this->enaCommentJS = true;
                break;
            default:
                $rs = false;
        }
        return $rs;
    }

    public function _commentUrl()
    {
        return \route($this->type . '.comment', $this->routeParams);
    }

    public function _id()
    {
        return $this->_data->cid;
    }

    public function author()
    {
        if ($user = $this->_data->author())
            $author = $user['name'] ?: $user['username'];
        else
            $author = '已删除的用户';

        echo '<a href="' . \route('author', $this->_data->uid) . '">' . $author . '</a>';
    }
}