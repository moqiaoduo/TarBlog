<?php
/**
 * Created by tarblog.
 * Date: 2020/6/2
 * Time: 0:45
 */

namespace Collection;

use Core\DataContainer;
use Models\Category;
use Utils\DB;
use Utils\Route;

class Categories extends DataContainer
{
    public function __construct($data = [])
    {
        if (empty($data))
            $this->setQueue(DB::table('metas')->where('type', 'category')->get());
        else
            $this->setQueue($data);
    }

    public function link()
    {
        echo route('category', Route::fillCategoryParams(new Category($this->row)));
    }

    public function name()
    {
        echo $this->row['name'];
    }
}