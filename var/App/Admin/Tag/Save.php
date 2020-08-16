<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 11:53
 */

namespace App\Admin\Tag;

use App\NoRender;
use Core\Validate;
use Models\Tag;
use Utils\Auth;
use Utils\DB;

class Save extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('category');

        $p = $this->request->post();

        $validate = new Validate($p);

        [$result, , $message] = $validate->make([
            'name' => 'required|max:255'
        ]);

        if (!$result) {
            back(function () use ($message) {
                with_input();
                with_error($message);
            });
        }

        if (($mid = $p['id']) > 0) {
            $tag = $this->db->table('metas')->where('mid', $mid)->where('type', 'tag')
                ->firstWithModel(Tag::class);

            if (is_null($tag)) showErrorPage('未找到您想要编辑的标签', 404);
        } else {
            $tag = new Tag();
        }

        $tag->name = $p['name'];

        $tag->description = $p['description'];

        if (!($slug = $p['slug'])) {
            $result = $this->plugin->trigger($plugged)->generate_slug_category($tag);

            if ($plugged) {
                $slug = $result[0];
            } else {
                $slug = $tag->name;
            }
        }

        if ($mid == 0) DB::saveWithModel('metas', $tag, 'mid', false);

        $tag->slug = generate_unique_slug($slug, 'tag', $tag->mid);

        DB::saveWithModel('metas', $tag, 'mid', true);

        $this->request->session()->flash('success', '保存成功');

        back();

        return true;
    }
}