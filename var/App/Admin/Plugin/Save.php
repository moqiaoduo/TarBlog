<?php
/**
 * Created by tarblog.
 * Date: 2020/8/11
 * Time: 1:51
 */

namespace App\Admin\Plugin;

use App\NoRender;

class Save extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $path = $this->request->post('path');

        $plugin = $this->request->post('theme');

        if (empty($plugin)) {
            ob_clean();
            http_response_code(403);
            die('插件名称为空');
        }

        $fullPath = __ROOT_DIR__ . __PLUGIN_DIR__ . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($fullPath)) {
            ob_clean();
            http_response_code(404);
            die('该文件不存在');
        }

        if (file_put_contents($fullPath, $this->request->post('code')))
            echo basename($fullPath) . ' 保存成功';
        else
            echo basename($fullPath) . ' 保存失败';

        return true;
    }
}