<?php
/**
 * Created by tarblog.
 * Date: 2020/8/9
 * Time: 16:48
 */

namespace Helper;

use Core\File;
use Utils\PHPComment;

class CodeMirror
{
    const ALLOW_EXT = ['php', 'js', 'css'];

    const NAMES = [
        'index.php' => '首页模板',
        'style.css' => '样式表',
        'style.min.css' => '样式表',
        'functions.php' => '模板函数',
        '404.php' => '404模板',
        'comments.php' => '评论模板',
        'footer.php' => '主题页脚',
        'header.php' => '主题页眉',
        'page.php' => '独立页面',
        'post.php' => '文章页面'
    ];

    const PLUGIN_NAMES = ['Plugin.php' => '插件主文件'];

    public static function loadHead()
    {
        ?>
        <link rel="stylesheet" href="assets/plugins/codemirror/lib/codemirror.css">
        <link rel="stylesheet" href="assets/plugins/ztree/zTreeStyle/zTreeStyle.css">
        <style>
            .row {
                display: flex;
                justify-content: space-between;
            }

            .column-left {
                position: relative;
                flex: 1;
            }

            .column-right {
                width: 300px;
                margin-left: 20px;
            }

            @media screen and (max-width: 768px) {
                .row {
                    display: initial;
                }

                .column-right {
                    width: 100%;
                    margin-left: 0;
                }
            }

            .outline {
                background-color: #f7f7f7;
                border: 1px solid #ddd;
                overflow-y: scroll;
                overflow-x: auto;
                min-height: 60vh;
                height: calc(100vh - 320px);
            }

            .form-group {
                position: relative;
            }

            .CodeMirror {
                border: 1px solid #eee;
                min-height: 60vh;
                height: calc(100vh - 370px);
            }
            #loading {
                position: absolute;
                top: 40px;
                left: 0;
                right: 0;
                height: calc(100% - 40px);
                z-index: 999;
            }
        </style>
        <?php
    }

    public static function js($theme, $type = 'theme')
    {
        Common::addJSFile('assets/plugins/codemirror/lib/codemirror.js',
            'assets/plugins/codemirror/addon/edit/matchbrackets.js',
            'assets/plugins/codemirror/mode/htmlmixed.js',
            'assets/plugins/codemirror/mode/xml.js',
            'assets/plugins/codemirror/mode/javascript.js',
            'assets/plugins/codemirror/mode/css.js',
            'assets/plugins/codemirror/mode/clike.js',
            'assets/plugins/codemirror/mode/php.js',
            'assets/plugins/ztree/jquery.ztree.core.min.js');

        $actions = ['loadFile' => 'Admin/Theme/LoadFile',
            'loadTree' => 'Admin/Theme/LoadTree', 'save' => 'Admin/Theme/Save'];
        $defaultSelect = 'index.php';

        if ($type == 'plugin') {
            $actions = ['loadFile' => 'Admin/Plugin/LoadFile',
                'loadTree' => 'Admin/Plugin/LoadTree', 'save' => 'Admin/Plugin/Save'];
            $defaultSelect = 'Plugin.php';
        }

        Common::addJS(<<<JS
$(function() {
    var myTextarea = document.getElementById('editor');
    var first = true;
    var theme = '$theme';
    var selectPath = '';
    var originForm;
    
    var CodeMirrorEditor = CodeMirror.fromTextArea(myTextarea, {
        lineNumbers: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true,
        lineWrapping: true,
        extraKeys: {"Ctrl-Alt-Space": "autocomplete"}
    });
    
    function reloadCodeMirror(mode, content) {
        CodeMirrorEditor.set_option('mode', mode)
        CodeMirrorEditor.getDoc().setValue(content)
    }
    
    function reloadTree() {
        var zTree = $.fn.zTree.getZTreeObj("fileTree");
        zTree.reAsyncChildNodes(null, "refresh");
    }
    
    function selectIndexPHP() {
        var zTree = $.fn.zTree.getZTreeObj("fileTree");
        var node = zTree.getNodeByParam("relativePath", '$defaultSelect');
        zTree.cancelSelectedNode();//先取消所有的选中状态
        zTree.selectNode(node,true);//将指定ID的节点选中
        loadFile('$defaultSelect')
        first = false;
    }
    
    function limitEditorSize() {
        $('.CodeMirror').css('width', $('#title').width() + 'px')
    }
    
    function loadFile(path) {
        $('#editor').val(CodeMirrorEditor.getDoc().getValue())
        
        if (!first && $('#editor-form').serialize() !== originForm && !confirm('还没有保存代码，确认切换文件吗？')) {
            return;
        }
        $('#loading').show();
        $.ajax({
            url: 'do.php',
            type: 'get',
            data: {
                theme: theme,
                path: path,
                a: '{$actions['loadFile']}'
            },
            dataType: 'json',
            success: function (data) { 
                $('#title').html(data.title);
                $('#editor').val(data.content);
                reloadCodeMirror(data.mode, data.content);
                selectPath = path;
                originForm = $('#editor-form').serialize();
            },
            error: function (jqXHR, textStatus, errorThrown) { 
                alert(jqXHR.responseText)
            },
            complete: function (jqXHR) {
                $('#loading').hide();
            }
        })
    }
    
    $.fn.zTree.init($("#fileTree"), {
        async: {
            type: 'get',
            url: 'do.php',
            enable: true,
            autoParam: ['relativePath'],
            otherParam: function () {
                return {
                    a: '{$actions['loadTree']}',
                    theme: theme
                }
            }
        },
        callback: {
            onAsyncSuccess: function () {
                if (first) selectIndexPHP()
            },
            onClick: function (event, treeId, treeNode, clickFlag) {
                console.log(treeNode)
                if (!treeNode.isParent) {
                    loadFile(treeNode.relativePath)
                }
            }
        }
    }, []);
    
    $('#theme').on('change', function () {
        theme = $(this).val();
        
        reloadTree()
    });
    
    $(window).on('resize', function () {
        limitEditorSize()
    });
    
    $(window).on('beforeunload', function (event) {
        $('#editor').val(CodeMirrorEditor.getDoc().getValue())
        
        if ($('#editor-form').serialize() !== originForm) {
            event.returnValue = '还没有保存代码，确认离开页面吗？';
            return '还没有保存代码，确认离开页面吗？';
        }
    });
    
    $('#editor-form').on('submit', function (event) {
        event.preventDefault();
        $('#loading').show();
        $.ajax({
            url: 'do.php',
            type: 'post',
            data: {
                theme: theme,
                path: selectPath,
                code: $('#editor').val(),
                a: '{$actions['save']}'
            },
            complete: function (jqXHR) {
                alert(jqXHR.responseText)
                $('#loading').hide();
            },
            success: function () {
                originForm = $('#editor-form').serialize()
            }
        })
    })
    
    limitEditorSize();
});
JS
        );
    }

    /**
     * @param File $file
     * @return string
     */
    public static function getTitle($file)
    {
        $page_info = PHPComment::parseFromFile($file->getPath());

        $filename = $file->getName();
        if (substr($filename, 0, 5) === 'page-' && isset($page_info['template'])) {
            $name = "独立页面模板: " . $page_info['template'] . " ($filename)";
        } elseif (isset(self::NAMES[$filename])) {
            $name = self::NAMES[$filename] . " ($filename)";
        } else {
            $name = $filename;
        }

        return $name;
    }
}