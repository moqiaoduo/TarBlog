<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 12:09
 */

namespace App\Admin\Category;

use App\NoRender;
use Utils\DB;

class Sync extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        \Helper\Sync::metaCount($this->db->table('metas')->where('type', 'category')->pluck('mid'));

        $this->request->session()->flash('success', '同步成功');

        back();

        return true;
    }
}