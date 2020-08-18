<?php
/**
 * Created by tarblog.
 * Date: 2020/7/31
 * Time: 14:20
 */

namespace Helper;

use Core\Plugin\Manager as PluginManager;

class Common
{
    private static $title;

    private static $description;

    private static $js;

    /**
     * @param $title
     */
    public static function setTitle($title)
    {
        self::$title = $title;
    }

    /**
     * @return mixed
     */
    public static function getTitle()
    {
        return self::$title;
    }

    public static function title()
    {
        echo self::$title;
    }

    /**
     * @param mixed $description
     */
    public static function setDescription($description)
    {
        self::$description = $description;
    }

    /**
     * @return mixed
     */
    public static function getDescription()
    {
        return self::$description;
    }

    public static function description()
    {
        echo self::$description;
    }

    public static function js()
    {
        echo self::$js;
    }

    public static function addJS($content, $withScriptTag = true)
    {
        if ($withScriptTag) {
            self::$js .= <<<HTML
<script>
$content
</script>
HTML;
        } else {
            self::$js .= $content;
        }
    }

    public static function addJSFile($file)
    {
        if (is_array($file)) {
            foreach ($file as $f)
                self::$js .= '<script src="' . $f . '"></script>';
        } elseif (func_num_args() > 1) {
            foreach (func_get_args() as $f)
                self::$js .= '<script src="' . $f . '"></script>';
        } else {
            self::$js .= '<script src="' . $file . '"></script>';
        }
    }

    public static function slugInput($value = '')
    {
        return <<<HTML
<div style="position: relative; display: inline-block;margin-right: 0.5em;">
<input type="text" id="slug" name="slug" autocomplete="off" value="{$value}" class="mono" 
style="left: 0; top: 0; min-width: 5px; position: absolute; width: 100%;">
<pre id="slug-hide" class="mono" style="display: block; visibility: hidden; height: 15px; padding: 0 2px; margin: 0;overflow-y:hidden;">{$value}</pre></div>
HTML;
    }

    public static function tinyMCEJS()
    {
        self::addJSFile('assets/plugins/tinymce/tinymce.min.js');

        self::addJS(<<<JS
tinymce.init({
 selector:'textarea' ,
 plugins: 'code codesample lists advlist anchor wordcount quickbars image media table help autolink autosave charmap emoticons fullscreen hr insertdatetime link paste preview searchreplace toc',
 toolbar: 'code codesample | undo redo | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | forecolor backcolor | link restoredraft preview fullscreen',
 menubar: 'edit insert view format table tools help',
 language: "zh_CN",
 image_advtab: true,
 height: 500,
 quickbars_insert_toolbar: 'quicktable',
 codesample_languages: [
    {text: 'HTML/XML', value: 'markup'},
    {text: 'JavaScript', value: 'javascript'},
    {text: 'CSS', value: 'css'},
    {text: 'PHP', value: 'php'},
    {text: 'Ruby', value: 'ruby'},
    {text: 'Python', value: 'python'},
    {text: 'Java', value: 'java'},
    {text: 'C', value: 'c'},
    {text: 'C#', value: 'csharp'},
    {text: 'C++', value: 'cpp'}
  ],
  content_css: [
    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
  ]
});
function addLinkToEditor(title, url) {
  tinymce.activeEditor.execCommand('mceInsertContent', false, '<p><a href="'+url+'">'+title+'</a></p>');
}
function addPicToEditor(name, url, width, height) {
  tinymce.activeEditor.execCommand('mceInsertContent', false, '<p><img src="'+url+'" width="'+
  width+'" height="'+height+'" alt="'+name+'"></p>');
}
JS
        );
    }

    public static function loadArticleCss()
    {
        echo '<link rel="stylesheet" href="assets/css/article.css">' .
            '<link rel="stylesheet" href="assets/plugins/datetimepicker/jquery.datetimepicker.min.css"/>';
    }

    public static function loadArticleJS()
    {
        $plugin = app('plugin'); /* @var PluginManager $plugin */

        $plugin->trigger($plugged)->new_editor_js();

        if (!$plugged) Common::tinyMCEJS();

        ob_start();

        $plugin->upload();

        $upload_code = ob_get_clean();

        Common::addJSFile('assets/plugins/datetimepicker/jquery.datetimepicker.full.min.js', 'assets/js/article.js');

        Common::addJS($upload_code, false);
    }

    public static function selectAllJS()
    {
        Common::addJS(<<<'JS'
$(function(){ 
    var item_checkbox = $('.item-checkbox');
    var select_all = $('#select-all');
    if (window.ActiveXObject || "ActiveXObject" in window) { 
        $('input:checkbox').on('click', function () { 
            this.blur(); 
            this.focus(); 
        });
    }
    
    function is_select_all() {
        var select_all = true;
        item_checkbox.each(function() {
            if (!$(this).is(':checked')) {
                select_all = false;
                return false;
            }
        });
        return select_all;
    }
    
    item_checkbox.on('change', function() {
        select_all.prop("checked", is_select_all());
    });
    
    select_all.on('change', function() {
        item_checkbox.prop("checked", $(this).is(':checked'));
    });
});

JS
        );
    }

    public static function buttonPostJS($action, $button_id, $input_id = 'list-action', $form_id = 'list-form')
    {
        Common::addJS(<<<JS
$(function() {
    $('#$button_id').on('click', function () {
        $('#$input_id').val('$action');
        $('#$form_id').trigger('submit');
     })
})
JS
        );
    }

    public static function loadSuccessAlert($success)
    {
        if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span><?php echo $success ?></span>
            </div>
        <?php endif;
    }

    public static function loadErrorAlert($err)
    {
        if ($err): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span><?php echo $err ?></span>
            </div>
        <?php endif;
    }

    public static function loadAdminSettingStyle($label_width = 130)
    {
        ?>
        <style>
            @media screen and (min-width: 768px) {
                .form-inline .form-label {
                    width: <?php echo $label_width ?>px;
                }

                .form-description {
                    margin-left: <?php echo $label_width + 10 ?>px;
                }
            }

            .form-container {
                margin: 0;
                max-width: 600px;
            }
        </style>
        <?php
    }

    public static function buildSelect($options, $settings = [])
    {
        ?>
        <select<?php if (isset($settings['class'])) echo ' class="' . $settings['class'] . '"' ?>
            <?php if (isset($settings['name'])) echo ' name="' . $settings['name'] . '"' ?>
            <?php if (isset($settings['id'])) echo 'id="' . $settings['id'] . '"' ?>>
            <?php foreach ($options as $key => $val):dump($key); ?>
                <option value="<?php echo $key ?>"
                    <?php if (isset($settings['value']) && $settings['value'] == $key) echo 'selected' ?>>
                    <?php echo $val ?>
                </option>
            <?php endforeach ?>
        </select>
        <?php
    }

    public static function loadToolCSS()
    {
        ?>
        <style>
            .tool-item > td {
                padding: 15px !important;
            }

            .tool-item > .tool-name {
                max-width: 250px;
            }

            .tool-item > .tool-desc {
                max-width: 500px;
            }

            .table {
                margin-bottom: 0;
            }

            .tool-item:last-of-type {
                border-bottom: 1px solid #f4f4f4;
            }
        </style>
        <?php
    }
}