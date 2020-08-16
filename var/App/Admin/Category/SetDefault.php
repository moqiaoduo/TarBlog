<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 10:47
 */

namespace App\Admin\Category;

use App\NoRender;
use Utils\Auth;

class SetDefault extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('category');

        set_option('defaultCategory', $this->request->get('default'));

        $this->request->session()->flash('success', '保存成功');

        back();

        return true;
    }
}