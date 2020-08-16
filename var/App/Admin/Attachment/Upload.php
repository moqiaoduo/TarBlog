<?php
/**
 * Created by tarblog.
 * Date: 2020/8/3
 * Time: 23:50
 */

namespace App\Admin\Attachment;

use App\NoRender;
use Utils\Auth;
use Utils\DB;

class Upload extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('post-base');

        $file = $this->request->file('file');

        if ($file->isValid()) {
            DB::beginTransaction();

            try {
                if (!in_array($file->getFileExt(), unserialize($this->options->get('allowFileExt', 'a:0:{}'))))
                    throw new \Exception('不支持的文件类型');

                $result = $this->plugin->trigger($plugged)->attachment_save(); // 插件修改路径

                if ($plugged) {
                    $file = $result[0];
                } else {
                    $file->move(date('Ymd'), md5(uniqid()) . '.' . $file->getFileExt());
                }

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
                    'uid' => Auth::id(), 'slug' => generate_unique_slug($slug, 'attachment'),
                        'content' => serialize($data)] + auto_fill_time());

                $id = $this->db->lastInsertId();

                // 插件修改url
                $result = $this->plugin->trigger($plugged)->attachment_url($file->getRelativePath());

                if ($plugged) {
                    $url = $result[0];
                } else {
                    $url = siteUrl('usr/upload/' . $file->getRelativePath());
                }

                DB::commit();

                json(['type' => $type, 'name' => $file->getOriginName(), 'url' => $url, 'id' => $id,
                    'size' => $file->getFormatSize(), 'success' => true]);
            } catch (\Exception $e) {
                DB::rollback();

                json(['success' => false, 'msg' => $e->getMessage()]);
            }
        }

        return true;
    }
}