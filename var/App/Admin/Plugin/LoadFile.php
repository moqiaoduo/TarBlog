<?php
/**
 * Created by tarblog.
 * Date: 2020/8/10
 * Time: 23:45
 */

namespace App\Admin\Plugin;

use App\NoRender;
use Core\File;
use Helper\CodeMirror;
use Utils\PHPComment;

class LoadFile extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $path = $this->request->get('path');

        $plugin = $this->request->get('theme');

        if (empty($plugin)) {
            ob_clean();
            http_response_code(403);
            die('插件名称为空');
        }

        $dir = __ROOT_DIR__ . __PLUGIN_DIR__ . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR;

        $file = new File(false, $dir . DIRECTORY_SEPARATOR . $path);

        if (!in_array($ext = $file->getFileExt(), CodeMirror::ALLOW_EXT)) {
            ob_clean();
            http_response_code(403);
            die('该文件无法作为代码编辑');
        }

        if (file_exists($file->getPath())) {
            $content = file_get_contents($file->getPath());
        } else {
            ob_clean();
            http_response_code(404);
            die('该文件不存在');
        }

        $info = $this->plugin->getPluginInfo($plugin);

        if (!isset($info['package'])) {
            ob_clean();
            http_response_code(403);
            die('插件不合规范，无法编辑');
        }

        $name = CodeMirror::getTitle($file);

        switch ($ext) {
            case 'php':
                $mode = "application/x-httpd-php";
                break;
            case 'css':
                $mode = "text/css";
                break;
            case 'js':
                $mode = "text/javascript";
                break;
            default:
                $mode = '';
        }

        json(['content' => $content, 'title' => ($info['display'] ?? $info['package']) . ': ' . $name, 'mode' => $mode]);

        return true;
    }
}