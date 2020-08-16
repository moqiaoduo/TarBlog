<?php
/**
 * Created by tarblog.
 * Date: 2020/8/1
 * Time: 17:10
 */

namespace App\Admin\Page;

use App\NoRender;
use Helper\Sync;
use Utils\Auth;
use Utils\DB;

class Trash extends NoRender
{
    public function execute(): bool
    {
        Auth::check('page');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        DB::beginTransaction();

        try {
            // “删除”页面的草稿
            $this->db->table('contents')->where('type', 'page_draft')->whereNull('deleted_at')
                ->whereIn('parent', $ids)->update(['deleted_at' => dateX()]);

            // “删除”页面
            $count = $this->db->table('contents')->whereIn('cid', $ids)->whereNull('deleted_at')
                ->where('type', 'page')->update(['deleted_at' => dateX()], true);

            DB::commit();

            $this->request->session()->flash('success', '成功将 ' . $count . ' 个页面移入回收站');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('将页面移入回收站时出现问题');

            back();
        }

        return true;
    }
}