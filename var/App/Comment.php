<?php
/**
 * Created by tarblog.
 * Date: 2020/6/7
 * Time: 23:55
 */

namespace App;

use Core\Http\Cookie;
use Core\Http\Token;
use Helper\HTMLPurifier;
use Helper\Sync;
use Models\Comment as Model;
use Models\Page;
use Models\Post;
use Utils\Auth;
use Helper\Comment as Helper;
use Utils\DB;

class Comment extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $options = $this->options;

        HTMLPurifier::load();

        HTMLPurifier::config(['HTML.Allowed' => $options->get('html_purifier_comment_allow_html'),
            'CSS.AllowedProperties' => $options->get('html_purifier_comment_allow_css'),
            'AutoFormat.AutoParagraph' => $options->get('html_purifier_comment_auto_para') == 1]);

        $this->plugin->comment_html_purifier(); // 可更改评论时的HTML Purifier设置

        $request = $this->request;

        if (!Token::verify($request->post('_token')))
            showErrorPage('页面已过期，请返回刷新后重新提交', 419);

        $parent = $request->post('parent', 0);

        if ($parent > 0 && !DB::table('comments')->where('id', $parent)
            ->where('status', 'approved')->exists())
            showErrorPage('该评论无法回复，可能的原因：<br>1.该评论未审核通过<br>2.该评论被标记为垃圾<br>3.该评论已被删除');

        $author = $request->post('author');
        $mail = $request->post('mail');
        $url = $request->post('url');
        $text = HTMLPurifier::clean($request->post('text'));
        $slug = $this->routeParams['slug'];
        $cid = $this->routeParams['cid'];

        Cookie::set('_tarblog_remember_author', $author, time() + 31536000); // 一年
        Cookie::set('_tarblog_remember_mail', $mail, time() + 31536000);
        Cookie::set('_tarblog_remember_url', $url, time() + 31536000);

        if (empty($text)) showErrorPage('必须填写评论内容', 403);

        if (!$slug && !$cid) return false;

        $type = substr($this->route->getName(), 0, 4);

        $article = Article::checkAndGetArticle($type,
            $type == 'post' ? Post::class : Page::class, $this->routeParams, $this->db);

        if (is_null($article)) return false;

        $comment = new Model(auto_fill_time());

        $comment->cid = $article->cid;

        if ($user = Auth::user()) {
            $author = ($user->name ?: $user->username);
            $mail = $user->email;
            $url = $user->url;
            $comment->authorId = $user->id;
        } else {
            if (empty($author))
                showErrorPage('必须填写称呼', 403);
            if ($options->commentsRequireMail && empty($mail))
                showErrorPage('必须填写电子邮箱', 403);
            if ($options->commentsRequireUrl && empty($url))
                showErrorPage('必须填写URL', 403);
        }

        if (Helper::checkTooFast($user, $article, $options))
            showErrorPage($options->commentsPostInterval . "分钟内只能发送一条评论！", 403);

        // 检查评论来源是否合法
        if ($options->commentsCheckReferer && route($type, $this->routeParams) != $_SERVER['HTTP_REFERER'])
            showErrorPage('Your request is not valid', 403);

        $comment->cid = $article->cid;
        $comment->name = $author;
        $comment->ownerId = $article->uid;
        $comment->email = $mail;
        $comment->url = $url;
        $comment->ip = get_ip();
        $comment->agent = $_SERVER['HTTP_USER_AGENT'];
        $comment->content = $text;

        if (Helper::checkNeedPending($user, $article, $options, $author, $mail))
            $comment->status = 'pending';

        $this->plugin->comment_spam($comment); // 因为传入的是对象，所以插件可以直接修改，不需要再判断结果

        $comment->parent = $parent;

        DB::saveWithModel('comments', $comment);

        Sync::comment($article->cid);

        $this->plugin->comment_notify($comment);

        back();

        return true;
    }
}