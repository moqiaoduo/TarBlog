<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 11:44
 */

namespace App\Admin\Attachment;

use App\NoRender;
use Utils\DB;

class Clean extends NoRender
{
    public function execute(): bool
    {
        Auth::check('attachment');

        $count = 0;
        foreach ($this->db->table('contents')->where('type', 'attachment')->get() as $val) {
            if (empty($val['parent']) || !$this->db->table('contents')->where('cid', $val['parent'])->exists()) {
                $this->db->table('contents')->where('cid', $val['cid'])->delete();
                $count++;
            }
        }

        $this->request->session()->flash('success', '已清理 ' . $count . '个附件');

        back();

        return true;
    }
}