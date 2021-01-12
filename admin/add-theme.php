<?php
/**
 * Created by TarBlog.
 * Date: 2018/8/23
 * Time: 15:13
 *
 * @var \Core\Errors $errors
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 */

use Helper\Common;

require "init.php";

Common::setTitle('安装主题');
if (!$request->has('upload'))
    Common::setDescription('<a href="add-theme.php?upload" class="btn btn-sm btn-default">上传</a>');

include "header.php";

if ($request->has('upload')):
?>
<link rel="stylesheet" href="assets/css/upload_tmp.css">
    <div class="row">
        <div class="col-sm-12">
            <p>请点击下面的区域或将文件拖入该区域来上传主题</p>
            <p>安装完成后，将自动跳转到主题列表页面</p>
            <div id="upload">
                <div>点击该区域选择文件<br>或者拖放文件到这里上传<br>(仅支持zip格式)</div>
            </div>
        </div>
    </div>
    <input type="file" style="display: none;" id="file-select">
<input type="hidden" id="upload-url" value="do.php?a=Admin/Theme/Upload">
    <?php else: ?>
    <div class="row">
        <div class="col-sm-12">
            <p>暂不支持在线安装</p>
        </div>
    </div>
<?php endif;
Common::addJSFile('assets/js/upload_tmp.js');
$admin_url = __ADMIN_DIR__;
Common::addJS(<<<JS
var upload_success = function () {
    window.location.href = "{$options->siteUrl}{$admin_url}theme.php"
}
JS
);
include "footer.php";

