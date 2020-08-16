<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 12:10
 */

namespace App\Admin\Tag;

use App\NoRender;
use Utils\DB;

class Sync extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        \Helper\Sync::metaCount($this->db->table('metas')->where('type', 'tag')->pluck('mid'));

        $this->request->session()->flash('success', '同步成功');

        back();

        return true;
    }
}