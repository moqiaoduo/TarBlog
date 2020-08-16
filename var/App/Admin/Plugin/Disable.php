<?php
/**
 * Created by tarblog.
 * Date: 2020/8/11
 * Time: 22:35
 */

namespace App\Admin\Plugin;

use App\NoRender;
use Utils\Str;

class Disable extends NoRender
{
    public function execute(): bool
    {
        $plugin = $this->request->get('plugin');

        $class = Str::toCamel($plugin . '_plugin');

        if (!file_exists($file = __ROOT_DIR__ . __PLUGIN_DIR__ .
                DIRECTORY_SEPARATOR . $plugin . '/Plugin.php') || !class_exists($class))
            showErrorPage('未找到插件', 404);

        $ena_plugins = (array)unserialize($this->options->get('ena_plugins', 'a:0:{}'));

        if (!in_array($plugin, $ena_plugins)) back(with_error('插件已经为禁用状态，请勿重复操作'));

        $pluginObj = new $class($this->plugin); /* @var \Core\Plugin\Plugin $pluginObj */

        if (!$pluginObj->deactivating()) back(with_error('插件禁用失败'));

        $ena_plugins = array_filter($ena_plugins, function ($val) use ($plugin) {
            return $val != $plugin;
        });

        $this->options->set('ena_plugins', serialize($ena_plugins));

        $pluginObj->deactivated();

        $this->request->session()->flash('success', '插件成功禁用');

        back();

        return true;
    }
}