<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:32
 */

namespace Collection;

use Core\DataContainer;
use Core\Dynamic;
use Utils\DB;
use Utils\Route;

class Pages extends DataContainer
{
    use Dynamic;

    public function __construct()
    {
        $this->setQueue(DB::table('contents')->whereIn('status', ['publish', 'password'])
            ->whereNull('deleted_at')->where('type', 'page')->orderBy('order')
            ->orderByDesc('created_at')->get());
    }

    public function _id()
    {
        return $this->row['cid'];
    }

    public function _link()
    {
        return route('page', Route::fillPageParams($this->row));
    }

    public function _title()
    {
        return $this->row['title'];
    }
}