<?php
/**
 * Created by tarblog.
 * Date: 2020/8/10
 * Time: 23:45
 */

namespace App\Admin\Theme;

use App\NoRender;
use Helper\CodeMirror;
use Utils\Dir;
use Utils\PHPComment;

class LoadTree extends NoRender
{
    public function execute(): bool
    {
        $path = $this->request->get('relativePath');

        $theme = $this->request->get('theme');

        if (empty($theme)) $theme = $this->options->get('theme', 'default');

        $dir = __THEME_DIR__ . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $path;

        $nodes = [];

        foreach (Dir::getAllDirsAndFiles($dir) as $file) {
            $filePath = __ROOT_DIR__ . $dir . DIRECTORY_SEPARATOR . $file;

            if (isset(CodeMirror::NAMES[$file])) {
                $name = CodeMirror::NAMES[$file] . '(' . $file . ')';
            } elseif (substr($file, 0, 5) === 'page-') {
                $info = PHPComment::parseFromFile($filePath);
                if (isset($info['template'])) $name = '独立页面模板: ' . $info['template'] . '(' . $file . ')';
                else $name = $file;
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