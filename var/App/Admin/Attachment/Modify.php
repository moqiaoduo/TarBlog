<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/14
 * Time: 2:19
 */

namespace App\Admin\Attachment;

use App\NoRender;
use Helper\Content;
use Utils\Auth;
use Utils\DB;

class Modify extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('attachment');

        $attachment = Content::getAttachmentById($cid = $this->request->get('cid'));

        if (is_null($attachment)) showErrorPage('您欲编辑的附件不存在', 404);

        $slug = generate_unique_slug($this->request->post('slug'), 'attachment', $cid);

        $info = unserialize($attachment['content']);

        $info['description']=$this->request->post('description');

        DB::table('contents')->where('cid', $cid)->update([
            'title' => $this->request->post('title'),
            'slug' => $slug,
            'content' => serialize($info),
        ]);

        $this->request->session()->flash('success', '修改已保存');

        back();

        return true;
    }
}