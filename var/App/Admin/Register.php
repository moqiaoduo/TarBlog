<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 9:51
 */

namespace App\Admin;

use App\NoRender;
use Core\Validate;
use Utils\Auth;

class Register extends NoRender
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $post = $this->request->post();

        [$result, $field, $message] = (new Validate($this->request->post()))
            ->make([
                'username|用户名' => 'required|unique:users|max:255',
                'password|密码' => 'required|confirm|between:6,255',
                'email|电子邮箱' => 'required|email|unique:users|max:255'
            ]);

        if (!$result) {
            back(function () use ($field, $message) {
                with_error([$field => $message]);
                with_input();
            });
        }

        if (!Auth::register($post['username'], $post['password'], $post['email']))
            back(with_error('注册信息未成功写入'));

        redirect(siteUrl(__ADMIN_DIR__ . 'login.php'));

        return true;
    }
}