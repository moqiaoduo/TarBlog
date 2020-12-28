<?php
/**
 * Created by TarBlog.
 * Date: 2020/12/28
 * Time: 13:52
 */

namespace App\Admin\Comment;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;

abstract class StatusBatch extends NoRender
{
    /**
     * 各子类定义自己的状态
     *
     * @var string
     */
    protected $status = '';

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('comment');

        if (empty($ids = $this->request->post('ids')))
            return back();

        $types = ['approved' => "已通过", "pending" => "待审核", "spam" => "垃圾"];

        $comments = $this->db->table('comments')->when(!Auth::user()->isAdmin(), function ($query) {
            $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
        }, true)->whereIn('id', $ids)->select('id', 'cid')->get(); // 只获取id cid 减少查询压力

        $comment_ids = array_column($comments, 'id');

        if (empty($comment_ids)) return back();

        $count = $this->db->table('comments')->when(!Auth::user()->isAdmin(), function ($query) {
            $query->where('ownerId', $uid = Auth::id())->orWhere('authorId', $uid);
        }, true)->whereIn('id', $comment_ids)->update(['status' => $this->status], true);

        $this->request->session()->flash('success', "已将{$count}条评论标注为{$types[$this->status]}");

        Sync::comment(array_unique(array_column($comments, 'cid'))); // 加入去重

        back(); // 批量操作的话，就直接返回上一页

        return true;
    }
}