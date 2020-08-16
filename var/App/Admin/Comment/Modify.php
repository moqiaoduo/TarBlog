<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 1:07
 */

namespace App\Admin\Comment;

use App\NoRender;
use Models\Comment;
use Utils\Auth;
use Utils\DB;

class Modify extends NoRender
{
    public function execute(): bool
    {
        $request = $this->request;

        $comment = $this->db->table('comments')->when(!Auth::check('post-premium', false),
            function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->where('id', $request->post('id'))->firstWithModel(Comment::class);

        $comment->name = $request->post('name');
        $comment->content = $request->post('content');
        $comment->email = $request->post('email');
        $comment->url = $request->post('url');
        $comment->content = $request->post('content');

        DB::saveWithModel('comments', $comment, 'id', true);

        json(array(
            'name' => $comment['name'],
            'email' => $comment['email'],
            'url' => $comment['url'],
            'content' => $comment['content']
        ));

        return true;
    }
}