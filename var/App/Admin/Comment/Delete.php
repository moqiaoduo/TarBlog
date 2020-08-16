<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 11:14
 */

namespace App\Admin\Comment;

use App\NoRender;
use Utils\Auth;
use Utils\DB;

class Delete extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('comment');

        $count = $this->db->table('comments')->when(!Auth::check('post-premium', false),
            function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->whereIn('id', $this->request->post('ids'))->delete(true);

        $this->request->session()->flash('success', '已成功删除 ' . $count . ' 条评论');

        back();

        return true;
    }
}