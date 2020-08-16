<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:13
 *
 * @var \Core\Options $options
 */
?>
<style>
    .form-control, select.form-control {
        height: 26px;
        vertical-align: baseline;
        padding: 0 2px;
    }
</style>
    <form method="post" class="form-container" action="do.php?a=Admin/Setting&p=comment">
        <div class="form-group">
            <h3>评论显示</h3>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsShow[]" value="commentsShowUrl" class="form-checkbox"
                    <?php if ($options->commentsShowUrl) echo " checked" ?> id="commentsShowUrl">
                <label for="commentsShowUrl">评论者名称显示时自动加上其个人主页链接</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsShow[]" value="commentsUrlNofollow" class="form-checkbox"
                    <?php if ($options->commentsUrlNofollow) echo " checked" ?> id="commentsUrlNofollow">
                <label for="commentsUrlNofollow">对评论者个人主页链接使用 nofollow 属性</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsShow[]" value="commentsAvatar" class="form-checkbox"
                    <?php if ($options->commentsAvatar) echo " checked" ?> id="commentsAvatar">
                <label for="commentsAvatar">
                    启用 Gravatar 头像服务, 最高显示评级为
                    <select name="commentsAvatarRating" class="form-control">
                        <option value="G"
                            <?php if ($options->commentsAvatarRating == 'G') echo " selected" ?>>G - 普通
                        </option>
                        <option value="PG"
                            <?php if ($options->commentsAvatarRating == 'PG') echo " selected" ?>>PG - 13岁以上
                        </option>
                        <option value="R"
                            <?php if ($options->commentsAvatarRating == 'R') echo " selected" ?>>R - 17岁以上成人
                        </option>
                        <option value="X"
                            <?php if ($options->commentsAvatarRating == 'X') echo " selected" ?>>X - 限制级
                        </option>
                    </select> 的头像
                </label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsShow[]" value="commentsPageBreak" class="form-checkbox"
                    <?php if ($options->commentsPageBreak) echo " checked" ?> id="commentsPageBreak">
                <label for="commentsPageBreak">
                    启用分页, 并且每页显示
                    <input type="text" value="<?php echo $options->commentsPageSize ?>" name="commentsPageSize" size="2"
                           style="text-align:center;" class="form-control">
                    篇评论, 在列出时将
                    <select name="commentsPageDisplay" class="form-control">
                        <option value="first"
                            <?php if ($options->commentsPageDisplay == 'first') echo " selected" ?>>第一页
                        </option>
                        <option value="last"
                            <?php if ($options->commentsPageDisplay == 'last') echo " selected" ?>>最后一页
                        </option>
                    </select> 作为默认显示
                </label>

            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsShow[]" value="commentsThreaded" class="form-checkbox"
                    <?php if ($options->commentsThreaded) echo " checked" ?> id="commentsThreaded">
                <label for="commentsThreaded">启用评论回复</label>
            </div>
            <div class="form-inline-checkbox-group">
                <label>将 <select name="commentsOrder" class="form-control">
                    <option value="DESC"
                        <?php if ($options->commentsOrder == 'DESC') echo " selected" ?>>较新的
                    </option>
                    <option value="ASC"
                        <?php if ($options->commentsOrder == 'ASC') echo " selected" ?>>较旧的
                    </option>
                </select> 的评论显示在前面</label>
            </div>
        </div>
        <div class="form-group">
            <h3>评论提交</h3>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsRequireModeration" class="form-checkbox"
                    <?php if ($options->commentsRequireModeration) echo " checked" ?> id="commentsRequireModeration">
                <label for="commentsRequireModeration">所有评论必须经过审核</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsWhitelist" class="form-checkbox"
                    <?php if ($options->commentsWhitelist) echo " checked" ?> id="commentsWhitelist">
                <label for="commentsWhitelist">评论者之前须有评论通过了审核</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsRequireMail" class="form-checkbox"
                    <?php if ($options->commentsRequireMail) echo " checked" ?> id="commentsRequireMail">
                <label for="commentsRequireMail">必须填写邮箱</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsRequireURL" class="form-checkbox"
                    <?php if ($options->commentsRequireURL) echo " checked" ?> id="commentsRequireURL">
                <label for="commentsRequireURL">必须填写网址</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsCheckReferer" class="form-checkbox"
                    <?php if ($options->commentsCheckReferer) echo " checked" ?> id="commentsCheckReferer">
                <label for="commentsCheckReferer">检查评论来源页 URL 是否与文章链接一致</label>
            </div>
            <div class="form-inline-checkbox-group">
                <input type="checkbox" name="commentsPost[]" value="commentsPostIntervalEnable"
                       class="form-checkbox" id="commentsPostIntervalEnable"
                    <?php if ($options->commentsPostIntervalEnable) echo " checked" ?>>
                <label for="commentsPostIntervalEnable">
                    同一 IP 发布评论的时间间隔限制为
                    <input type="text" name="commentsPostInterval" style="text-align:center;" size="1"
                           value="<?php echo $options->commentsPostInterval ?>" class="form-control">
                    分钟
                </label>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">保存</button>
            <button type="reset" class="btn">重置</button>
        </div>
    </form>
    <div id="temp"></div>
<?php
\Helper\Common::addJS(<<<JS
$(function() {
    $('input[name="frontPage"]').on('change', function () {
        if ($(this).val()==='page') $('#archivePattern').show();
        else $('#archivePattern').hide()
    })
    
})
JS
);
