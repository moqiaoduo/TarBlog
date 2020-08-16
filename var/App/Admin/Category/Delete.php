<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 10:25
 */

namespace App\Admin\Category;

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
        Auth::check('category');

        $ids = $this->request->post('ids', []);

        if (empty($ids)) back();

        $default_category = $this->options->get('defaultCategory', 1);

        DB::beginTransaction();

        try {
            foreach ($ids as $mid) {
                $cate = $this->db->table('metas')->where('mid', $mid)->first();

                if (is_null($cate)) continue;

                // 将原本的子分类移出来
                $this->db->table('metas')->where('parent', $cate['mid'])->update(['parent' => $cate['parent']]);
            }

            $count = $this->db->table('metas')->whereIn('mid', $ids)->delete(true);

            $this->db->table('relationships')->whereIn('mid', $ids)->update(['mid' => $default_category]);

            DB::commit();

            $this->request->session()->flash('success', '成功删除 ' . $count . '条分类');

            back();
        } catch (\Exception $e) {
            DB::rollback();

            with_error('删除分类时出现问题');

            back();
        }

        return true;
    }
}