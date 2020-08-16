<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 14:30
 */

namespace App\Admin\Theme;

use App\NoRender;

class Delete extends NoRender
{
    public function execute(): bool
    {
        if ($this->options->get('theme', 'default') == $theme = $this->request->get('theme'))
            back(with_error('不允许删除正在使用的主题！'));

        del_dir(__ROOT_DIR__ . __THEME_DIR__ . DIRECTORY_SEPARATOR . $theme);

        $this->request->session()->flash('success', '删除主题成功');

        back();

        return true;
    }
}