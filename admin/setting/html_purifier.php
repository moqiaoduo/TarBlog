<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/21
 * Time: 18:06
 *
 * @var \Core\Http\Request $request
 * @var \Core\Options $options
 */

\Helper\Common::loadAdminSettingStyle(155);

$default_cache_path = str_replace(DIRECTORY_SEPARATOR, '/',
__ROOT_DIR__ . '/var/HTMLPurifier/HTMLPurifier/DefinitionCache/Serializer');

\Helper\Common::addJS(<<<JS
$('#cache-path').on('input', function () {
    if ($(this).val().length > 0)
        $('#cache-path-now').text($(this).val());
    else
        $('#cache-path-now').text('$default_cache_path'.replace(/\//g, '\\\'));
})
JS
);

$default_cache_path = str_replace('/', DIRECTORY_SEPARATOR, $default_cache_path);
?>
<style>
    .form-control {
        max-width: 600px;
    }
    code {
        word-wrap: break-word;
    }
</style>
<form method="post" action="do.php?a=Admin/Setting&p=html_purifier">
    <div class="collapse accordion">
        <div class="collapse-item">
            <div class="collapse-header active">
                <span>通用</span>
            </div>
            <div class="collapse-content active">
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label">自动删除空白</label>
                        <div class="form-inline-radio-group">
                            <input type="radio" name="html_purifier_auto_empty_clean" id="auto-empty-clean-no" class="form-radio"
                                <?php if(!$options->html_purifier_auto_empty_clean) echo 'checked'?> value="0">
                            <label for="auto-empty-clean-no">不启用</label>
                            <input type="radio" name="html_purifier_auto_empty_clean" id="auto-empty-clean-yes" class="form-radio"
                                <?php if($options->html_purifier_auto_empty_clean) echo 'checked'?> value="1">
                            <label for="auto-empty-clean-yes">启用</label>
                        </div>
                    </div>
                    <div class="form-description">无意义的空白将会被 HTML Purifier 删除.</div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label">启用缓存</label>
                        <div class="form-inline-radio-group">
                            <input type="radio" name="html_purifier_cache" id="cache-no" class="form-radio" value="0"
                                <?php if(!$options->html_purifier_cache) echo 'checked'?>>
                            <label for="cache-no">不启用</label>
                            <input type="radio" name="html_purifier_cache" id="cache-yes" class="form-radio" value="1"
                                <?php if($options->html_purifier_cache) echo 'checked'?>>
                            <label for="cache-yes">启用</label>
                        </div>
                    </div>
                    <div class="form-description">
                        为了获取最佳性能, 建议开启缓存, 需要确保
                        <code id="cache-path-now">
                            <?php echo $cache_path = $options->get('html_purifier_cache_path') ?: $default_cache_path ?>
                        </code>
                        有写入权限.
                        <?php if ($options->html_purifier_cache && substr(sprintf("%o", fileperms($cache_path)), -4) < 0755): ?>
                            <div class="alert alert-warning">
                                检测到缓存路径没有权限，请尽快设置权限.
                            </div>
                        <?php endif ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="cache-path">缓存路径</label>
                        <input type="text" class="form-control" name="html_purifier_cache_path" id="cache-path"
                               autocomplete="off" value="<?php $options->html_purifier_cache_path()?>">
                    </div>
                    <div class="form-description">
                        为空时，采用默认路径 <code><?php echo $default_cache_path ?></code> <br>
                        请务必填写绝对路径. 不熟悉的请不要设置.
                    </div>
                </div>
            </div>
        </div>
        <div class="collapse-item">
            <div class="collapse-header">
                <span>文章设定</span>
            </div>
            <div class="collapse-content">
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label">启用过滤</label>
                        <div class="form-inline-radio-group">
                            <input type="radio" name="html_purifier_article" id="article-no" class="form-radio" value="0"
                                <?php if(!$options->html_purifier_article) echo 'checked'?>>
                            <label for="article-no">不启用</label>
                            <input type="radio" name="html_purifier_article" id="article-yes" class="form-radio" value="1"
                                <?php if($options->html_purifier_article) echo 'checked'?>>
                            <label for="article-yes">启用</label>
                        </div>
                    </div>
                    <div class="form-description">保存文章时, 启用HTML Purifier过滤检查能够保护您的内容不被进行XSS攻击.
                        即便您的浏览器插件有可能进行注入行为, 我们也能在保存时进行过滤. 一般而言, 若您的文章内容比较简单, 建议您开启.</div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="allow-article-html">允许的HTML标签及属性</label>
                        <textarea id="allow-article-html" name="html_purifier_article_allow_html"
                                  class="form-control" rows="5"><?php $options->html_purifier_article_allow_html()?></textarea>
                    </div>
                    <div class="form-description">
                        请以半角逗号 "," 隔开各个标签, 例如 "div,p,a";
                        您还可以设置标签允许的属性, 每个属性请以竖线 "|" 隔开, 例如 "div,p[style],a[href|title]" .
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="allow-article-css">允许的CSS属性</label>
                        <textarea id="allow-article-css" name="html_purifier_article_allow_css"
                                  class="form-control" rows="5"><?php $options->html_purifier_article_allow_css()?></textarea>
                    </div>
                    <div class="form-description">
                        请以半角逗号 "," 隔开各个属性, 例如 "font,font-size,font-weight";
                        该设定用于过滤 style 属性中的内容.
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label">自动识别段落</label>
                        <div class="form-inline-radio-group">
                            <input type="radio" name="html_purifier_article_auto_para" id="auto-para-no" class="form-radio"
                                <?php if(!$options->html_purifier_article_auto_para) echo 'checked'?> value="0">
                            <label for="auto-para-no">不启用</label>
                            <input type="radio" name="html_purifier_article_auto_para" id="auto-para-yes" class="form-radio"
                                <?php if($options->html_purifier_article_auto_para) echo 'checked'?> value="1">
                            <label for="auto-para-yes">启用</label>
                        </div>
                    </div>
                    <div class="form-description">识别到段落时，将会给段落加上 p 标签. (不允许 p 标签时请不要启用! 否则会导致出错. )</div>
                </div>
            </div>
        </div>
        <div class="collapse-item">
            <div class="collapse-header">
                <span>评论设定</span>
            </div>
            <div class="collapse-content">
                <p><b>Tips: 评论强制开启过滤</b></p>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="allow-comment-html">允许的HTML标签及属性</label>
                        <textarea id="allow-comment-html" name="html_purifier_comment_allow_html"
                                  class="form-control" rows="5"><?php $options->html_purifier_comment_allow_html()?></textarea>
                    </div>
                    <div class="form-description">
                        请以半角逗号 "," 隔开各个标签, 例如 "div,p,a";
                        您还可以设置标签允许的属性, 每个属性请以竖线 "|" 隔开, 例如 "div,p[style],a[href|title]" .
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label" for="allow-post-html">允许的CSS属性</label>
                        <textarea id="allow-post-html" name="html_purifier_comment_allow_css"
                                  class="form-control" rows="5"><?php $options->html_purifier_comment_allow_css()?></textarea>
                    </div>
                    <div class="form-description">
                        请以半角逗号 "," 隔开各个属性, 例如 "font,font-size,font-weight";
                        该设定用于过滤 style 属性中的内容.
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-inline">
                        <label class="form-label">自动识别段落</label>
                        <div class="form-inline-radio-group">
                            <input type="radio" name="html_purifier_comment_auto_para" id="auto-comment-para-no" class="form-radio"
                                <?php if(!$options->html_purifier_comment_auto_para) echo 'checked'?> value="0">
                            <label for="auto-comment-para-no">不启用</label>
                            <input type="radio" name="html_purifier_comment_auto_para" id="auto-comment-para-yes" class="form-radio"
                                <?php if($options->html_purifier_comment_auto_para) echo 'checked'?> value="1">
                            <label for="auto-comment-para-yes">启用</label>
                        </div>
                    </div>
                    <div class="form-description">识别到段落时，将会给段落加上 p 标签. (不允许 p 标签时请不要启用! 否则会导致出错. )</div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">保存</button>
        <button type="reset" class="btn">重置</button>
    </div>
</form>