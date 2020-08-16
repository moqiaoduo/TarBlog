<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/13
 * Time: 17:26
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 * @var \Core\Http\Session $session
 * @var \Core\Errors $errors
 * @var \Core\Plugin\Manager $plugin
 */

use Utils\Auth;

require "init.php";

if (Auth::hasLogin()) to_homepage();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php $options->title() ?> | 登录</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Load App CSS -->
    <link rel="stylesheet" href="assets/css/app.css" />

    <style>
        body {
            background-color: #66cccc;
        }
        .form-container {
            display: none;
            max-width: 400px;
            position: absolute;
            background-color: #fff;
            padding: 40px;
        }
        h1 {
            margin-block-start: 0;
            text-align: center;
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<form id="login-form" action="do.php?a=Admin/Login" class="form-container" method="post">
    <h1>登录</h1>
    <?php if($error = $errors->first()): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <span><b>登录失败!</b> <?php echo $error ?></span>
        </div>
    <?php endif ?>
    <div class="form-group">
        <input type="text" name="username" placeholder="用户名或电子邮箱" required class="form-control input-block"
               value="<?php old('username') ?>" />
    </div>
    <div class="form-group">
        <input type="password" name="password" class="form-control input-block" placeholder="密码" required />
    </div>
    <div class="form-group">
        <input type="checkbox" name="remember" id="remember" class="form-checkbox">
        <label for="remember">记住我</label>
    </div>
    <div class="form-group form-submit-group">
        <button type="submit" class="btn btn-primary">登录</button>
        <?php if($options->register): ?>
        <a href="register.php" class="btn">注册</a>
        <?php endif ?>
    </div>
</form>

<!-- jQuery 3 -->
<script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
<!-- App Javascript -->
<script src="assets/js/app.js"></script>
<!-- jQuery Validate Plugin -->
<script src="assets/plugins/jquery-validate/jquery.validate.min.js"></script>
<script src="assets/plugins/jquery-validate/localization/messages_zh.min.js"></script>
<script>
    $(function () {
        $(window).on('resize', function () {
            var login = $("#login-form");
            login.css({
                display: 'block',
                left: ($(window).width() - login.outerWidth()) / 2,
                top: ($(window).height() - login.outerHeight()) / 2,
            });
        });

        $(window).trigger('resize');

        $("#login-form").validate({
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
                    required: true
                },
                password: {
                    required: true
                },
            },
        });
    });
</script>
</body>
</html>