<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:13
 *
 * @var \Core\Options $options
 * @var \Core\Http\Request $request
 */
$extension = implode(',',unserialize($options->get('allowFileExt','a:0:{}')));
\Helper\Common::loadAdminSettingStyle();

$probablyUrl = $request->getProtocol() . $_SERVER['HTTP_HOST'];
?>
<form method="post" class="form-container" action="do.php?a=Admin/Setting&p=general">
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="siteName">站点名称</label>
            <input type="text" id="siteName" name="siteName" required value="<?php $options->title()?>"
                   autocomplete="off" class="form-control">
        </div>
        <div class="form-description">站点的名称将显示在网页的标题处</div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="siteUrl">站点地址</label>
            <input type="text" id="siteUrl" name="siteUrl" required value="<?php echo $options->siteUrl?>"
                   autocomplete="off" class="form-control">
        </div>
        <div class="form-description">
            站点地址主要用于生成内容的永久链接
            <?php if ($probablyUrl != $options->siteUrl): ?>
                <div class="alert alert-warning">
                    当前地址 <?php echo $probablyUrl ?> 与上述设定值不一致
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="description">站点描述</label>
            <input type="text" id="description" name="description" value="<?php $options->description()?>"
                   autocomplete="off" class="form-control">
        </div>
        <div class="form-description">站点描述将显示在网页代码的头部</div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="keyword">关键词</label>
            <input type="text" id="keyword" name="keyword" value="<?php $options->keyword()?>"
                   autocomplete="off" class="form-control">
        </div>
        <div class="form-description">请以半角逗号 "," 分割多个关键字</div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label">是否允许注册</label>
            <div class="form-inline-radio-group">
                <input type="radio" name="register" id="register-no" class="form-radio" value="0"
                    <?php if(!$options->register) echo 'checked'?>>
                <label for="register-no">不允许</label>
                <input type="radio" name="register" id="register-yes" class="form-radio" value="1"
                    <?php if($options->register) echo 'checked'?>>
                <label for="register-yes">允许</label>
            </div>
        </div>
        <div class="form-description">允许访问者注册到你的网站, 默认的注册用户不享有任何写入权限</div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="timezone">时区</label>
            <input type="text" id="timezone" name="timezone" value="<?php $options->timezone()?>"
                   autocomplete="off" class="form-control" placeholder="Asia/Shanghai">
        </div>
        <div class="form-description">若不填写，则默认使用Asia/Shanghai</div>
    </div>
    <div class="form-group">
        <div class="form-inline">
            <label class="form-label" for="allowFileExt">允许上传的文件格式</label>
            <textarea id="allowFileExt" name="allowFileExt"
                      class="form-control" rows="5"><?php echo $extension?></textarea>
        </div>
        <div class="form-description">请以半角逗号 "," 分割多个后缀名，若不填写则无法上传任何附件</div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">保存</button>
        <button type="reset" class="btn">重置</button>
    </div>
</form>