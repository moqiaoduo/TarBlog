<?php
/**
 * Created by tarblog.
 * Date: 2020/8/4
 * Time: 16:20
 */

namespace App\Admin\Attachment;

use App\NoRender;
use Helper\Content;
use Utils\Auth;
use Utils\DB;

class Delete extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('post-base');

        if ($this->request->post('batch')) {
            if (empty($ids = $this->request->post('ids'))) back();

            foreach ($ids as $id) {
                $result = $this->deleteById($id);
                if ($result != 'ok')
                    with_error($result);
            }

            $this->request->session()->flash('success', '附件删除成功');

            back();
        } else {
            echo $this->deleteById($this->request->post('id'));
        }

        return true;
    }

    public function deleteById($id)
    {
        $data = Content::getAttachmentById($id);

        if (is_null($data)) {
            return '删除失败';
        }

        if (!$this->user->isAdmin() && $data['uid'] != Auth::id()) {
            return '你没有权限删除这个文件!';
        }

        $info = unserialize($data['content']);

        $result = $this->plugin->trigger($plugged)->delete_attachment($info);

        if ($plugged) {
            $result = $result[0];
        } else {
            $path = __ROOT_DIR__ . '/usr/upload/' . $info['path'];
            if (file_exists($path)) unlink($path);
            $result = 'ok';
        }

        if ($result == 'ok') {
            $this->db->table('contents')->where('cid', $data['cid'])->delete();
        }

        return $result;
    }
}