<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 14:08
 */

namespace App\Admin\Theme;

use App\NoRender;
use Utils\Auth;

class Change extends NoRender
{
    public function execute(): bool
    {
        Auth::check('admin-level');

        if (!is_dir(__ROOT_DIR__ . __THEME_DIR__ . '/' . ($theme = $this->request->get('theme'))))
            back(with_error('主题不存在'));

        $this->options->set('theme', $theme);

        $this->request->session()->flash('success', '切换主题成功');

        back();

        return true;
    }
}