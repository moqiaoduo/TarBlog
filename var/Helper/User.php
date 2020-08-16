<?php
/**
 * Created by tarblog.
 * Date: 2020/8/12
 * Time: 16:44
 */

namespace Helper;


class User
{
    public static function loadCSS()
    {
        ?>
        <style>
            @media screen and (min-width: 768px) {
                .form-inline+.form-error {
                    padding-left: 110px;
                }
            }
        </style>
<?php
    }

    public static function loadJS($update = false)
    {
        Common::addJSFile('assets/plugins/jquery-validate/jquery.validate.min.js',
            'assets/plugins/jquery-validate/localization/messages_zh.min.js');

        $password_required = $update ? '' : 'required: true,';

        Common::addJS(<<<JS
        $(".form-container").validate({
            errorElement: "div",
            errorClass: "form-error",
            errorPlacement: function (error, element) {
                var parnet = $(element).parent();
                if (parnet.hasClass('form-inline'))
                    $(element).parent().after(error);
                else
                    $(element).after(error);
            },
            rules: {
                username: {
                    required: true,
                    minlength: 5
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    $password_required
                    minlength: 6
                },
                confirm_password: {
                    $password_required
                    minlength: 5,
                    equalTo: 'input[name="password"]'
                }
            },
            messages: {
                username: {
                    required: "请输入用户名",
                    minlength: "用户名不能小于 5 个字符"
                },
                password: {
                    minlength: "密码长度不能小于 6 个字符"
                },
                confirm_password: {
                    minlength: "密码长度不能小于 6 个字符",
                    equalTo: "两次密码输入不一致"
                },
                email: "请输入一个正确的邮箱"
            }
        });
JS
        );
    }
}