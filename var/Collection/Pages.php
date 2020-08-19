<?php
/**
 * Created by tarblog.
 * Date: 2020/6/1
 * Time: 16:32
 */

namespace Collection;

use Core\DataContainer;
use Utils\DB;
use Utils\Route;

class Pages extends DataContainer
{
    public function __construct()
    {
        $this->setQueue(DB::table('contents')->whereIn('status', ['publish', 'password'])
            ->whereNull('deleted_at')->where('type', 'page')->orderBy('order')
            ->orderByDesc('created_at')->get());
    }

    public function id($return = false)
    {
        if ($return)
            return $this->row['cid'];

        echo $this->row['cid'];
    }

    public function link()
    {
        echo route('page', Route::fillPageParams($this->row));
    }

    public function title()
    {
        echo $this->row['title'];
    }
}