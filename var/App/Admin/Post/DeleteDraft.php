<?php
/**
 * Created by tarblog.
 * Date: 2020/8/6
 * Time: 21:58
 */

namespace App\Admin\Post;

use App\NoRender;
use Helper\Content;
use Utils\Auth;

class DeleteDraft extends NoRender
{
    public function execute(): bool
    {
        Auth::check('post-base');

        $id = $this->request->get('id');

        $draft = Content::getPostDraft($id);

        if ($draft['uid'] != $this->user->id())
            back(function () {
                with_error(['您没有权限删除这份草稿!']);
            });

        $this->db->table('contents')->where('type', 'post_draft')
            ->where('cid', $draft['cid'])->delete(); // 草稿是真实删除的，不会进入回收站

        back(function () {
            $this->request->session()->flash('success', '草稿已删除');
        });

        return true;
    }
}