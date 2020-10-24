<?php
/**
 * Created by tarblog.
 * Date: 2020/8/5
 * Time: 22:56
 */

namespace App\Admin\Page;

use App\NoRender;
use Core\Validate;
use Helper\Content;
use Helper\HTMLPurifier;
use Models\Page;
use Utils\Auth;

class Save extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('page');

        $options = $this->options;

        if ($options->html_purifier_article) {
            HTMLPurifier::load();

            HTMLPurifier::config(['HTML.Allowed' => $options->get('html_purifier_article_allow_html'),
                'CSS.AllowedProperties' => $options->get('html_purifier_article_allow_css'),
                'AutoFormat.AutoParagraph' => $options->get('html_purifier_article_auto_para') == 1]);

            $this->plugin->article_html_purifier();
        }

        $p = $this->request->post();

        $uid = $this->user->id();

        $validate = new Validate($p);

        [$result] = $validate->make([
            'type' => 'in:page,page_draft'
        ]);

        if (!$result) {
            back(function () {
                with_input();
            });
        }

        $title = empty($p['title']) ? '未命名页面' : $p['title'];

        $content = $options->html_purifier_article ? HTMLPurifier::clean($p['content']) : $p['content'];

        if (empty($p['created_at']))
            $created_at = dateX();
        else
            $created_at = dateX(0, $p['created_at']);

        $base_data = ['title' => $title, 'content' => $content, 'uid' => $uid,
            'created_at' => $created_at, 'updated_at' => dateX()];

        if (($cid = $p['id']) > 0) {
            $page = Content::getPageById($cid);

            if (is_null($page)) showErrorPage('未找到页面');

            if ($p['type'] == 'page_draft' && $page->type == 'page') {
                $exist = $this->db->table('contents')->where('parent', $cid)
                    ->where('type', 'page_draft')->exists();

                if ($exist) {
                    $this->db->table('contents')->where('type', 'page_draft')
                        ->where('parent', $cid)->update($base_data); // 更新草稿
                } else {
                    $this->db->table('contents')->insert(
                        ['parent' => $cid, 'type' => 'page_draft'] + $base_data); // 创建草稿
                }
            } elseif ($p['type'] == 'page' || $post->type == 'page_draft') {
                $this->db->table('contents')->where('parent', $cid)
                    ->where('type', 'page_draft')->delete();
                $page->type = $p['type'];
                $page->title = $title;
                $page->content = $content;
                $page->updated_at = dateX();
                $page->created_at = $created_at;
            }
        } else {
            //新增
            $page = new Page(['type' => $p['type']] + $base_data);
        }

        if ($p['type'] == 'page') // 只有发布的时候才能更改页面顺序
            $page->order = is_numeric($p['order']) ? $p['order'] : $this->getNewOrderNum($cid); // 非数字自动分配

        $page->template = $p['template'];
        $page->status = $p['visibility'];
        $page->password = $p['password'];
        $page->allowComment = (int)$this->request->has('allowComment');

        if ($cid == 0) Content::saveContent($page);

        $page->slug = Content::slug($p, 'page', $this->plugin, $page);

        Content::saveContent($page, true);

        if (!empty($p['attachment'])) Content::link2P($p['attachment'], $page->cid);

        redirect('write-page.php?edit=' . $page->cid, function () {
            $this->request->session()->flash('success', '页面已保存');
        });

        return true;
    }

    public function getNewOrderNum($cid)
    {
        return $this->db->table('contents')->where('type', 'page')
                ->when($cid, function ($query) use ($cid) {
                    $query->where('cid', '<>', $cid);
                })->max('order') + 1;
    }
}