<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/13
 * Time: 23:46
 */

namespace App\Admin;

use App\NoRender;

use Core\Upgrade as UpgradeList;
use ReflectionMethod;

class Upgrade extends NoRender
{
    public function execute(): bool
    {
        $db_version = $this->options->get('version', 'v0.2.2'); // 因为之前没有这个option，所以默认为v0.2.2

        $core_version = UpgradeList::$newest_version;

        if ($this->request->has('download')) {
            // 下载并更新，这个必须由管理员操作
        } elseif ($db_version < $core_version) {
            // 由于某些原因可能无法验证登录，因此我们允许直接更新
            $this->upgrade($db_version, $core_version);
        }

        $this->request->session()->flash('success', '升级完成');

        back();

        return true;
    }

    public function upgrade($old_version, $version)
    {
        $reflect = new \ReflectionClass(UpgradeList::class);

        $methods = $reflect->getMethods();

        $valid_version = str_replace('.', '_', $version);

        $last_check_version = $old_valid_version = str_replace('.', '_', $old_version);

        $upgrade_route = [];

        foreach ($methods as $method) {
            [$current_version, $target_version] = explode('_to_', $method->getName());

            if ($current_version == $old_valid_version && $target_version == $valid_version) {
                $upgrade_route = [$method->getName()];
                break;
            }

            if ($current_version == $last_check_version) {
                $upgrade_route[] = $method->getName();
                if ($target_version == $valid_version) break;
                $last_check_version = $target_version;
            }
        }

        foreach ($upgrade_route as $method) {
            UpgradeList::$method($this->db, $this->options);
        }

        $this->options->set('version', $version); // 最后执行版本号更新
    }
}