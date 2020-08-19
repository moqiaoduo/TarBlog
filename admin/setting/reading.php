<?php
/**
 * Created by TarBlog.
 * Date: 2019/2/20
 * Time: 12:13
 *
 * @var \Core\Options $options
 */

$page=new \Collection\Pages();
\Helper\Common::loadAdminSettingStyle(70);
?>
    <form method="post" class="form-container" action="do.php?a=Admin/Setting&p=reading">
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label">站点首页</label>
                <div>
                    <div class="form-inline-radio-group">
                        <input type="radio" name="frontPage" value="recent" id="frontPage-list" class="form-radio"
                            <?php if(!$options->indexPage) echo "checked"?>>
                        <label for="frontPage-list">显示最新发布的文章</label>
                    </div>
                    <div class="form-inline-radio-group">
                        <input type="radio" name="frontPage" value="page" id="frontPage-page" class="form-radio"
                            <?php if($options->indexPage) echo 'checked'?>>
                        <label for="frontPage-page">使用
                            <select name="frontPagePage" class="form-control"
                                    style="height: 26px;vertical-align: baseline;padding: 0 5px;">
                                <?php while ($page->next()):?>
                                    <option value="<?php $page->id()?>"
                                        <?php if($options->indexPage==$page->id) echo " selected"?>>
                                        <?php $page->title()?>
                                    </option>
                                <?php endwhile;?>
                            </select> 作为首页</label>
                    </div>
                    <div id="archivePattern" class="form-inline-radio-group"
                         style="<?php if(!$options->indexPage) echo "display:none;"?>padding-left:20px;">
                        <input type="checkbox" name="frontArchive" class="form-checkbox" id="frontArchive"
                            <?php if($options->showArticleList) echo "checked"?>>
                        <label for="frontArchive">同时将文章列表页路径更改为
                            <input name="archivePattern" style="height: 26px;vertical-align: baseline;padding: 0 5px;"
                                   type="text" value="<?php $options->articleListUrl() ?>" class="form-control">
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="form-inline">
                <label class="form-label" for="pageSize">每页文章数</label>
                <input type="text" id="pageSize" name="pageSize" required value="<?php echo $options->pageSize ?>"
                       autocomplete="off" class="form-control">
            </div>
            <div class="form-description">站点的名称将显示在网页的标题处</div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">保存</button>
            <button type="reset" class="btn" id="reset">重置</button>
        </div>
    </form>

<?php
$click_action = $options->indexPage >0 ? "$('#archivePattern').show()" : "$('#archivePattern').hide()";
\Helper\Common::addJS(<<<JS
$(function() {
    $("#reset").on('click',function() {
        $click_action
    });
    
    $('input[name="frontPage"]').on('change', function () {
        if ($(this).val()==='page') $('#archivePattern').show();
        else $('#archivePattern').hide()
    })
    
})
JS
);
