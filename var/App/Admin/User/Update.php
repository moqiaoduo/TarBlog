<?php
/**
 * Created by tarblog.
 * Date: 2020/8/12
 * Time: 17:01
 */

namespace App\Admin\User;

use App\NoRender;
use Core\Validate;
use Models\User;
use Utils\Auth;
use Utils\DB;

class Update extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $uid = $this->request->post('id');

        if ($uid <= 0) back();

        if ($uid != $this->user->id()) Auth::check('admin-level');

        $user = $this->db->table('users')->where('id', $uid)->firstWithModel(User::class);

        if (is_null($user)) showErrorPage('用户不存在', 404);

        $post = $this->request->post();

        [$result, $field, $message] = (new Validate($this->request->post()))
            ->make([
                'email|电子邮箱地址' => 'required|email|max:255',
                'name|用户昵称' => 'nullable|max:255',
                'password|用户密码' => 'nullable|confirm|between:6,255',
                'url|个人主页地址' => 'nullable|url|max:255',
                'identity|用户组' => 'nullable|in:reader,poster,writer,editor,admin'
            ]);

        if (!$result) {
            back(function () use ($field, $message) {
                with_error([$field => $message]);
                with_input();
            });
        }

        if ($this->db->table('users')->where('email', $post['email'])
            ->where('id', '<>', $uid)->exists())
            back(with_error(['email' => '邮箱已被占用']));

        $user->email = $post['email'];
        $user->name = $post['name'];
        if (!empty($post['password']))
            $user->password = password_hash($post['password'], PASSWORD_DEFAULT);
        $user->url = $post['url'];
        if (!empty($post['identity']))
            $user->identity = $post['identity'];

        DB::saveWithModel('users', $user, 'id', true);

        $this->request->session()->flash('success', '用户个人资料修改成功');

        back();

        return true;
    }
}