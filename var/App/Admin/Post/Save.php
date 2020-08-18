<?php
/**
 * Created by tarblog.
 * Date: 2020/8/5
 * Time: 22:56
 */

namespace App\Admin\Post;

use App\NoRender;
use Core\Validate;
use Helper\Content;
use Helper\Sync;
use Models\Post;
use Utils\Auth;

class Save extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('post-base');

        $p = $this->request->post();

        $uid = $this->user->id();

        $validate = new Validate($p);

        [$result] = $validate->make([
            'type' => 'in:post,post_draft'
        ]);

        if (!$result) {
            back(function () {
                with_input();
            });
        }

        $title = empty($p['title']) ? '未命名文章' : $p['title'];

        if (empty($p['created_at']))
            $created_at = dateX();
        else
            $created_at = dateX(0, $p['created_at']);

        $base_data = ['title' => $title, 'content' => $p['content'], 'uid' => $uid,
            'created_at' => $created_at, 'updated_at' => dateX()];

        if (($cid = $p['id']) > 0) {
            $post = Content::getPostById($cid);

            if (is_null($post)) showErrorPage('未找到文章');

            if ($post->uid !== $this->user->id() && !Auth::check('post-premium', false))
                showErrorPage('您没有权限编辑这篇文章', 403);

            if ($p['type'] == 'post_draft' && $post->type == 'post') {
                $exist = $this->db->table('contents')->where('parent', $cid)
                    ->where('type', 'post_draft')->exists();

                if ($exist) {
                    $this->db->table('contents')->where('type', 'post_draft')
                        ->where('parent', $cid)->update($base_data); // 更新草稿
                } else {
                    $this->db->table('contents')->insert(
                        ['parent' => $cid, 'type' => 'post_draft'] + $base_data); // 创建草稿
                }
            } elseif ($p['type'] == 'post') {
                $this->db->table('contents')->where('parent', $cid)
                    ->where('type', 'post_draft')->delete();
                $post->type = 'post';
                $post->title = $title;
                $post->content = $p['content'];
                $post->updated_at = dateX();
                $post->created_at = $created_at;
            }
        } else {
            //新增
            $post = new Post(['type' => $p['type']] + $base_data);
        }

        // 权限不足的清空下，这些设置都是不可见的，也就是说都按默认来，或者更高权限的人设置的值
        if (Auth::check('post-base-manager', false)) {
            $post->status = $p['visibility'];
            $post->password = $p['password'];
            $post->allowComment = (int)$this->request->has('allowComment');
        }

        // 甭管啥，只要你权限不够，新建修改都得审核
        if (!Auth::check('post-base-manager', false))
            $post->status = 'waiting';

        if ($cid == 0) Content::saveContent($post);

        $post->slug = Content::slug($p, 'page', $this->plugin, $post);

        Content::saveContent($post, true);

        if (!empty($p['attachment'])) Content::link2P($p['attachment'], $post->cid);

        if (empty($p['category'])) $p['category'][] = $this->options->get('defaultCategory', 1);

        Sync::meta($post->cid, array_merge($p['category'], Content::getTagsId($post->cid, $p['tag'])));

        redirect('write-post.php?edit=' . $post->cid, function () {
            $this->request->session()->flash('success', '文章已保存');
        });

        return false;
    }
}