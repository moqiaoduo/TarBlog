<?php
/**
 * Created by tarblog.
 * Date: 2020/8/8
 * Time: 10:51
 */

namespace App\Admin\Category;

use App\NoRender;
use Core\Validate;
use Models\Category;
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
            'name' => 'required|max:255',
            'parent' => 'required'
        ]);

        if (!$result) {
            back(function () use ($message) {
                with_input();
                with_error($message);
            });
        }

        if (($mid = $p['id']) > 0) {
            $category = $this->db->table('metas')->where('mid', $mid)->where('type', 'category')
                ->firstWithModel(Category::class);

            if (is_null($category)) showErrorPage('未找到您想要编辑的分类', 404);
        } else {
            $category = new Category();
        }

        $category->name = $p['name'];

        $category->description = $p['description'];

        if ($p['parent'] != 0 && !$this->db->table('metas')->where('type', 'category')
            ->where('mid', $p['parent'])->exists()) showErrorPage('所选父级分类不存在', 404);

        $category->parent = $p['parent'];

        if (!($slug = $p['slug'])) {
            $result = $this->plugin->trigger($plugged)->generate_slug_category($category);

            if ($plugged) {
                $slug = $result[0];
            } else {
                $slug = $category->name;
            }
        }

        if ($mid == 0) DB::saveWithModel('metas', $category, 'mid', false);

        $category->slug = generate_unique_slug($slug, 'category', $category->mid);

        DB::saveWithModel('metas', $category, 'mid', true);

        $this->request->session()->flash('success', '保存成功');

        back();

        return true;
    }
}