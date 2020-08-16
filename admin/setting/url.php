<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:15
 *
 * @var \Core\Options $options
 * @var $post_url
 * @var $page_url
 * @var $category_url
 */

\Helper\Common::loadAdminSettingStyle(140);
$default_post_choice = [
    '/archives/{cid}' => '默认风格',
    '/archives/{slug}.html' => 'wordpress风格',
    '/archives/{year}/{month}/{day}/{slug}.html' => '按日期归档',
    '/{category}/{slug}.html' => '按分类归档',
    'custom' => '个性化定义'
];
?>
<style>
    .radio-label {
        cursor: pointer;
    }
</style>
<div class="alert alert-info">
    TarBlog提醒您：设置千万条，此页第一条。填写不规范，网站两行泪。<br>
    若非特殊需求，尽量不要选择参数 {directory} 多级分类，因为在所有规则中，只有这一项因需要遍历路径而耗时最长，虽然可用但体验不好。
</div>
<form method="post" class="form-container" action="do.php?a=Admin/Setting&p=url">
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label">是否使用地址重写功能</label>
            <div class="form-inline-radio-group">
                <input type="radio" name="rewrite" value="0" class="form-radio" id="rewrite-no"
                    <?php if (!$options->rewrite) echo "checked" ?>>
                <label for="rewrite-no">不启用</label>
                <input type="radio" name="rewrite" value="1" class="form-radio" id="rewrite-yes"
                    <?php if ($options->rewrite) echo "checked" ?>>
                <label for="rewrite-yes">启用</label>
            </div>
        </div>
        <div class="form-description">
            地址重写即 rewrite 功能是某些服务器软件提供的优化内部连接的功能.<br>
            启用该功能可以让你的链接看上去完全是静态地址.<br>
            启用该功能时，会在程序根目录下自动生成.htaccess（for Apache）<br>
            如果您的Web服务器不支持.htaccess规则，请手动建立重写规则<br>
            <a href="javascript:alert('暂无');">点击这里查看各个Web服务器适用的重写规则</a>
        </div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label">自定义文章路径</label>
            <div>
                <?php foreach ($default_post_choice as $key => $value): ?>
                    <div class="form-inline-radio-group">
                        <label class="radio-label">
                            <input type="radio" name="postPattern" value="<?php echo $key ?>" class="form-radio"
                                <?php if ($post_url === $key) {
                                    echo "checked";
                                    $hasCheckPost = true;
                                }
                                if (!isset($hasCheckPost) && $key === 'custom') echo "checked" ?>>
                            <?php echo $value; if ($key === 'custom') { ?>
                                <input type="text" name="customPattern" class="form-control"
                                       style="height: 26px;vertical-align: baseline;padding: 0 5px;"
                                       value="<?php if (!isset($hasCheckPost)) echo $post_url ?>">
                            <?php } else echo ' ' . $key ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-description">
            可用参数: {cid} 日志 ID, {slug} 日志缩略名, {category} 分类, {directory} 多级分类, {year} 年, {month} 月, {day} 日<br>
            选择一种合适的文章静态路径风格, 使得你的网站链接更加友好.<br>
            一旦你选择了某种链接风格请不要轻易修改它.
        </div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="pagePattern">独立页面路径</label>
            <input type="text" id="pagePattern" name="pagePattern" required class="form-control"
                   value="<?php echo $page_url ?>" autocomplete="off">
        </div>
        <div class="form-description">
            可用参数: {cid} 页面 ID, {slug} 页面缩略名<br>
            请在路径中至少包含上述的一项参数.
        </div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="categoryPattern">分类路径</label>
            <input type="text" id="categoryPattern" name="categoryPattern" required class="form-control"
                   value="<?php echo $category_url ?>" autocomplete="off">
        </div>
        <div class="form-description">
            可用参数: {mid} 分类 ID, {slug} 分类缩略名, {directory} 多级分类<br>
            请在路径中至少包含上述的一项参数.
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">保存</button>
        <button type="reset" class="btn" id="reset">重置</button>
    </div>
</form>
