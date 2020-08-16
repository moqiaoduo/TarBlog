<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 10:59
 */

namespace App\Admin\Comment;

use App\NoRender;
use Helper\Sync;
use Models\Comment;
use Utils\Auth;
use Utils\DB;

class Reply extends NoRender
{
    public function execute(): bool
    {
        Auth::check('comment');

        $reply = $this->request->get('id');
        $text = $this->request->post('text');

        if (empty($reply) || empty($text))
            back(with_error('没有选中回复项或文本为空'));

        $user = Auth::user();

        $reply_comment = $this->db->table('comments')->when(!Auth::check('post-premium', false),
            function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->where('id', $reply)->first();

        if (is_null($reply_comment))
            back(with_error('未找到评论或评论不可操作'));

        $comment = new Comment(auto_fill_time());
        $comment->cid = $reply_comment['cid'];
        $comment->name = ($user->name ? $user->name : $user->username);
        $comment->authorId = $user->id;
        $comment->ownerId = $reply_comment['ownerId'];
        $comment->email = $user->email;
        $comment->url = $user->url;
        $comment->ip = get_ip();
        $comment->agent = $_SERVER['HTTP_USER_AGENT'];
        $comment->content = $text;
        $comment->parent = $reply;

        DB::saveWithModel('comments', $comment);

        Sync::comment($comment->cid);

        $this->plugin->comment_notify($comment);

        back();

        return true;
    }
}