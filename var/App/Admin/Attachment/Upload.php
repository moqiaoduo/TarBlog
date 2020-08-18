<?php
/**
 * Created by tarblog.
 * Date: 2020/8/3
 * Time: 23:50
 */

namespace App\Admin\Attachment;

use App\NoRender;
use Core\File;
use Utils\Auth;
use Utils\DB;

class Upload extends NoRender
{
    /**
     * 分两种情况
     * 如果以原生方式上传，那得处理上传的文件；
     * 如果是第三方上传，那只要接收文件信息
     */
    public function execute(): bool
    {
        Auth::check('post-base');

        if ($this->request->post('skipUpload')) {
            $file = new File(false);

            $name = $this->request->post('filename');

            $file->setOptions([
                'name' => $name,
                'originName' => $name,
                'relativePath' => $this->request->post('path'),
                'mime' => $this->request->post('mime'),
                'size' => $this->request->post('size')
            ]);

            DB::beginTransaction();

            try {
                $this->saveShowFileInfo($file);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();

                json(['success' => false, 'msg' => $e->getMessage()]);
            }
        } else {
            $file = $this->request->file('file');

            if ($file->isValid()) {
                DB::beginTransaction();

                try {
                    if (!in_array($file->getFileExt(), unserialize($this->options->get('allowFileExt', 'a:0:{}'))))
                        throw new \Exception('不支持的文件类型');

                    $this->plugin->trigger($plugged)->attachment_save(); // 插件修改保存方式

                    if (!$plugged)
                        $file->move(date('Ymd'), md5(uniqid()) . '.' . $file->getFileExt());

                    $this->saveShowFileInfo($file);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();

                    json(['success' => false, 'msg' => $e->getMessage()]);
                }
            }
        }

        return true;
    }

    public function saveShowFileInfo(File $file)
    {
        // 插件修改slug
        $result = $this->plugin->trigger($plugged)->generate_slug_attachment($file->getOriginName());

        if ($plugged) {
            $slug = $result[0];
        } else {
            $slug = $file->getOriginName();
        }

        $type = explode('/', $file->getMime())[0];

        $data = ['name' => $file->getOriginName(), 'type' => $type,
            'ext' => $file->getFileExt(), 'size' => $file->getFormatSize(),
            'path' => str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePath())];

        $this->db->table('contents')->insert(['type' => 'attachment', 'title' => $file->getOriginName(),
                'uid' => $this->user->id(), 'slug' => generate_unique_slug($slug, 'attachment'),
                'content' => serialize($data)] + auto_fill_time());

        $id = $this->db->lastInsertId();

        // 插件修改url
        $result = $this->plugin->trigger($plugged)->attachment_url($file->getRelativePath());

        if ($plugged) {
            $url = $result[0];
        } else {
            $url = siteUrl('usr/upload/' . $file->getRelativePath());
        }

        json(['type' => $type, 'name' => $file->getOriginName(), 'url' => $url, 'id' => $id,
            'size' => $file->getFormatSize(), 'success' => true]);
    }
}