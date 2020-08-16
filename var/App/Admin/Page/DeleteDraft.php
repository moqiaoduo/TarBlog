<?php
/**
 * Created by tarblog.
 * Date: 2020/8/6
 * Time: 21:58
 */

namespace App\Admin\Page;

use App\NoRender;
use Helper\Content;
use Utils\Auth;
use Utils\DB;

class DeleteDraft extends NoRender
{
    public function execute(): bool
    {
        Auth::check('page');

        $id = $this->request->get('id');

        $draft = Content::getPageDraft($id);

        $this->db->table('contents')->where('type', 'page_draft')
            ->where('cid', $draft['cid'])->delete(); // 草稿是真实删除的，不会进入回收站

        back(function () {
            $this->request->session()->flash('success', '草稿已删除');
        });

        return true;
    }
}