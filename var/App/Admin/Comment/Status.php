<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 1:24
 */

namespace App\Admin\Comment;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Status extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('comment');

        $types = ['approved' => "已通过", "pending" => "待审核", "spam" => "垃圾"];

        $allComment = $this->request->get('allComment');

        if (!array_key_exists($status = $this->request->get('status'), $types))
            redirect('comments.php?allComment=' . $allComment);

        $count = $this->db->table('comments')->when(!Auth::user()->isAdmin(), function ($query) {
            $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
        }, true)->where('id', $id = $this->request->get('id'))
            ->update(['status' => $status], true);

        $this->request->session()->flash('success', '已将评论标注为' . $types[$status]);

        if ($count > 0)
            Sync::comment($id);

        redirect('comments.php?allComment=' . $allComment . '&status=' . $status);

        return true;
    }
}