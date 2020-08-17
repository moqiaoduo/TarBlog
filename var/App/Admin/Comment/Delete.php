<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 11:14
 */

namespace App\Admin\Comment;

use App\NoRender;
use Utils\Auth;

class Delete extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('comment');

        if (empty($ids = $this->request->post('ids'))) back(); // 防止误删整个表

        $count = $this->db->table('comments')->when(!Auth::check('post-premium', false),
            function ($query) {
                $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
            }, true)->whereIn('id', $ids)->delete(true);

        $count += $this->findChildrenAndDelete($ids);

        $this->request->session()->flash('success', '已成功删除 ' . $count . ' 条评论');

        back();

        return true;
    }

    public function findChildrenAndDelete($parent)
    {
        static $count;

        $query = $this->db->table('comments');

        if (is_array($parent))
            $query->whereIn('parent', $parent);
        else
            $query->where('parent', $parent);

        foreach ($ids = $query->pluck('id') as $id) {
            $this->findChildrenAndDelete($id);
        }

        if (empty($ids)) return $count;

        $count += $this->db->table('comments')->whereIn('id', $ids)->delete(true);

        return $count;
    }
}