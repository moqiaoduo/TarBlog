<?php
/**
 * Created by TarBlog.
 * Date: 2018/8/23
 * Time: 23:18
 */

use Helper\Common;
use Utils\Auth;

require "init.php";

Common::setTitle('首页');
Common::setDescription('仪表盘');

include "header.php";

Auth::check('dashboard');
?>


<?php include "footer.php" ?>
