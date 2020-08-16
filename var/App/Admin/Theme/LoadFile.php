<?php
/**
 * Created by tarblog.
 * Date: 2020/8/10
 * Time: 23:45
 */

namespace App\Admin\Theme;

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

        $theme = $this->request->get('theme');

        if (empty($theme)) $theme = $this->options->get('theme', 'default');

        $dir = __ROOT_DIR__ . __THEME_DIR__ . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;

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

        $info = PHPComment::parseFromFile($dir . '/index.php');

        if (!isset($info['package'])) $info['package'] = $theme;

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

        json(['content' => $content, 'title' => $info['package'] . ': ' . $name, 'mode' => $mode]);

        return true;
    }
}