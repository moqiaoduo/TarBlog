<?php
/**
 * Created by tarblog.
 * Date: 2020/8/12
 * Time: 15:33
 */

namespace App\Admin\User;

use App\NoRender;
use Core\Validate;
use Utils\Auth;

class Create extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        Auth::check('admin-level');

        $post = $this->request->post();

        [$result, $field, $message] = (new Validate($this->request->post()))
            ->make([
                'username|用户名' => 'required|unique:users|max:255',
                'email|电子邮箱地址' => 'required|email|unique:users|max:255',
                'name|用户昵称' => 'nullable|max:255',
                'password|用户密码' => 'required|confirm|between:6,255',
                'url|个人主页地址' => 'nullable|url|max:255',
                'identity|用户组' => 'required|in:reader,poster,writer,editor,admin'
            ]);

        if (!$result) {
            back(function () use ($field, $message) {
                with_error([$field => $message]);
                with_input();
            });
        }

        if (!Auth::register($post['username'], $post['password'], $post['email'],
            ['name' => $post['name'], 'url' => $post['url'], 'identity' => $post['identity']]))
            back(with_error('注册信息未成功写入'));

        $this->request->session()->flash('success', '添加用户成功');

        redirect(siteUrl(__ADMIN_DIR__ . 'user.php'));

        return true;
    }
}