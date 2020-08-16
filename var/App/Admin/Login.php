<?php
/**
 * Created by tarblog.
 * Date: 2020/7/30
 * Time: 16:50
 */

namespace App\Admin;

use App\NoRender;
use Core\Validate;
use Utils\Auth;

class Login extends NoRender
{
    public function execute(): bool
    {
        [$result, $field, $message] = (new Validate($this->request->post()))
            ->make([
                'username|用户名' => 'required',
                'password|密码' => 'required',
            ]);

        if (!$result) {
            back(function () use ($field, $message) {
                with_input();
                with_error([$field => $message]);
            });
        }

        if (!Auth::attempt($this->request->post('username'), $this->request->post('password'),
            $this->request->has('remember'))) {
            back(function () {
                with_input();
                with_error('用户名或密码错误');
            });
        }

        redirect(siteUrl(__ADMIN_DIR__));

        return true;
    }
}