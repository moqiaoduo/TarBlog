<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/18
 * Time: 19:17
 *
 * @var \Core\Plugin\Manager $plugin
 * @var \Core\Errors $errors
 * @var \Core\Http\Session $session
 */

use Helper\Common;
use Utils\Auth;

require "init.php";

Common::setTitle('已安装的插件');

$title = '已安装的插件';

Auth::check('admin-level');

include "header.php";
Common::loadSuccessAlert($session->get('success'));
Common::loadErrorAlert($errors->first());
?>
<div class="table-responsive">
    <table class="table table-hover">
        <colgroup>
            <col width="25%">
            <col width="45%">
            <col width="8%">
            <col width="10%">
            <col width="">
        </colgroup>
        <thead>
        <tr>
            <th>名称</th>
            <th>描述</th>
            <th>版本</th>
            <th>作者</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($plugin->scanAllPlugins() as $pg => $info):
            if (empty($info['package'])) continue;
            $enable = $plugin->isEnable($info['package']) ?>
            <tr>
                <td><?php echo $info['display'] ?? $info['package'] ?></td>
                <td><?php echo $info['description'] ?></td>
                <td><?php echo $info['version'] ?></td>
                <td><?php $author = $info['author'] ?? '';
                    if (isset($info['link'])):?>
                        <a target="_blank" href="<?php echo $info['link'] ?>"><?php echo $author ?></a>
                    <?php else: echo $author;endif ?>
                </td>
                <td>
                    <?php if ($enable): ?>
                        <a href="do.php?a=Admin/Plugin/Disable&plugin=<?php echo $pg ?>">禁用</a>
                        <?php $setting_page = $plugin->trigger($plugged)->{$info['package'] . '_setting'}();
                        if ($plugged): ?>
                            <a href="setting.php?p=<?php echo $setting_page[0] ?>">设置</a>
                        <?php endif;
                    else: ?>
                        <a href="do.php?a=Admin/Plugin/Enable&plugin=<?php echo $pg ?>">启用</a>
                    <?php endif ?>
                    <a href="plugin-editor.php?plugin=<?php echo $pg ?>">编辑</a>
                    <?php if (!$enable): ?>
                        <a href="do.php?a=delete&plugin=<?php echo $pg ?>" onclick="return confirm('确定要删除吗？')">删除</a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include "footer.php" ?>
