<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 0:51
 */

namespace App\Admin\Comment;

use App\NoRender;
use Utils\Auth;
use Utils\DB;

class Info extends NoRender
{
    public function execute(): bool
    {
        Auth::check('comment');

        $comment = $this->db->table('comments')->when(!Auth::check('post-premium', false),
            function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->where('id', $this->request->get('id'))->first();

        json([
            'name' => $comment['name'],
            'email' => $comment['email'],
            'url' => $comment['url'],
            'content' => $comment['content']
        ]);

        return true;
    }
}