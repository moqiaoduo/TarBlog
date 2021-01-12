<?php
/**
 * Created by TarBlog.
 * Date: 2021/1/12
 * Time: 18:57
 */

namespace App\Admin\Theme;

use Utils\Auth;
use ZipArchive;

class Upload extends \App\NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('admin-level');

        if (!class_exists(ZipArchive::class))
            return json(['success' => false, 'msg' => '服务器不支持解压zip文件，请检查PHP相关扩展是否开启']) || false;

        $file = $this->request->file('file');

        if ($file->isValid()) {
            if ($file->getFileExt() !== 'zip')
                return json(['success' => false, 'msg' => '不支持的文件类型']) || false;

            // 暂存，待会要删除的
            $file->move(date('Ymd'), md5(uniqid()) . '.' . $file->getFileExt());

            del_dir($tmp_dir = __ROOT_DIR__ . '/usr/upload/tmp');
            mkdir($tmp_dir); // 临时创建文件夹

            $zip = new ZipArchive();

            $result = $zip->open($file->getPath());

            if ($result !== true)
                return json(['success' => false, 'msg' => '打开Zip失败：' . $result]) || false;

            $zip->extractTo($tmp_dir);

            $zip->close();

            $dirs = glob($tmp_dir . DIRECTORY_SEPARATOR . '*');

            if (count($dirs) > 1 || !is_dir($dirs[0])) {
                json(['success' => false, 'msg' => '目录结构不正确，安装中止']);
            } else {
                $dir = $dirs[0];

                if (file_exists($dist = __ROOT_DIR__ . __THEME_DIR__ . DIRECTORY_SEPARATOR . basename($dir))) {
                    json(['success' => false, 'msg' => '已有相同名字的目录，安装中止']);
                } else {
                    mkdir($dist);

                    copy_dir($dir, $dist);

                    json(['success' => true]);
                }
            }

            unlink($file->getPath());

            del_dir($tmp_dir);
        }

        return true;
    }
}