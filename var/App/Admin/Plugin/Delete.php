<?php
/**
 * Created by tarblog.
 * Date: 2020/8/12
 * Time: 0:16
 */

namespace App\Admin\Plugin;

use App\NoRender;
use Utils\Str;

class Delete extends NoRender
{
    public function execute(): bool
    {
        $plugin = $this->request->get('plugin');

        $class = Str::toCamel($plugin . '_plugin');

        if (!file_exists($file = __ROOT_DIR__ . __PLUGIN_DIR__ .
                DIRECTORY_SEPARATOR . $plugin . '/Plugin.php') || !class_exists($class))
            showErrorPage('未找到插件', 404);

        $ena_plugins = (array)unserialize($this->options->get('ena_plugins', 'a:0:{}'));

        if (in_array($plugin, $ena_plugins)) back(with_error('不允许删除正在使用的插件！'));

        del_dir(__ROOT_DIR__ . __PLUGIN_DIR__ . DIRECTORY_SEPARATOR . $plugin);

        $this->request->session()->flash('success', '删除插件成功');

        back();

        return true;
    }
}