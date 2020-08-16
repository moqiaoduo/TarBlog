<?php
/**
 * Created by tarblog.
 * Date: 2020/8/10
 * Time: 23:45
 */

namespace App\Admin\Plugin;

use App\NoRender;
use Helper\CodeMirror;
use Utils\Dir;
use Utils\PHPComment;

class LoadTree extends NoRender
{
    public function execute(): bool
    {
        $path = $this->request->get('relativePath');

        $plugin = $this->request->get('theme');

        if (empty($plugin)) {
            ob_clean();
            http_response_code(403);
            die('插件名称为空');
        }

        $dir = __PLUGIN_DIR__ . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $path;

        $nodes = [];

        foreach (Dir::getAllDirsAndFiles($dir) as $file) {
            $filePath = __ROOT_DIR__ . $dir . DIRECTORY_SEPARATOR . $file;

            if (isset(CodeMirror::NAMES[$file])) {
                $name = CodeMirror::NAMES[$file] . '(' . $file . ')';
            } else {
                $name = $file;
            }

            $node = ['name' => $name];

            if (is_dir($filePath)) $node['isParent'] = true;

            $node['relativePath'] = $path . (empty($path) ? '' : DIRECTORY_SEPARATOR) . $file;

            $nodes[] = $node;
        }

        json($nodes);

        return true;
    }
}