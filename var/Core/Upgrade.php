<?php
/**
 * 升级程序
 *
 * Created by tarblog.
 * Date: 2020/6/8
 * Time: 11:50
 */

namespace Core;

use Core\Database\Manager as Database;
use Helper\HTMLPurifier;
use Utils\DB;

/**
 * 版本迁移规则
 * 1. 版本升级必须是一个连续的过程，绝不允许出现中间有断层（即v0_2_2_to_v0_2_3和v0_2_5_to_v0_3_0之间没有版本升级方法是不允许的）
 * 2. 版本升级方法必须按版本大小进行排序，例如（v0_2_2_to_v0_2_3 v0_2_2_to_v0_3_0 v0_2_3_to_v0_3_0）
 * 3. 如上所示，可以写跨版本升级的方法，但是请按版本来排序，不可以调换顺序（否则可能会导致升级出错）
 * 4. $newest_version必须是要能迁移到的版本，若没有迁移函数，不要去修改它。__VERSION__才是程序真正的版本号
 * 5. 这个迁移，主要只涉及数据库结构和内容的修改，只有极少数情况下会涉及文件修改
 */
class Upgrade
{
    /**
     * 从0.2.2升级到0.3.0
     *
     * @param Database $db
     * @param Options $options
     */
    public static function v0_2_2_to_v0_3_0(Database $db, Options $options)
    {
        $db->exec('ALTER TABLE `users` CHANGE COLUMN `remember_token` `auth_token` ' .
            'VARCHAR(100) NULL DEFAULT NULL AFTER `identity`'); // users表字段remember_token改为auth_token

        $routeTable = unserialize($options->get('routeTable', 'a:0:{}'));

        // 路由表改为各个设置
        $options->set('postUrl', $routeTable['post']['path']);
        $options->set('pageUrl', $routeTable['page']['path']);
        $options->set('categoryUrl', $routeTable['category']['path']);
        $options->set('articleListUrl', $routeTable['list']['path']);
        $options->set('showArticleList', $routeTable['list']['enable']);
        DB::table('options')->where('name', 'routeTable')->delete();

        // plugins弃用，改用ena_plugins，但是不会继承启动状态（避免插件未更新时带来的麻烦）,所以直接删了
        DB::table('options')->where('name', 'plugins')->delete();

        // 更新附件中type信息
        foreach (DB::table('contents')->where('type', 'attachment')
                     ->whereNull('deleted_at')->get() as $val) {
            $info = unserialize($val['content']);

            [$type] = explode('/', $info['type']);

            $info['type'] = $type;

            $info['size'] = format_size($info['size']);

            DB::table('contents')->where('cid', $val['cid'])->update(['content' => serialize($info)]);
        }
    }

    /**
     * 从0.3.0升级到0.3.2
     *
     * @param Database $db
     * @param Options $options
     */
    public static function v0_3_0_to_v0_3_2(Database $db, Options $options)
    {
        // 新增默认设置
        $options->set('html_purifier_auto_empty_clean', 1);
        $options->set('html_purifier_cache', 0);
        $options->set('html_purifier_article', 0);
        $options->set('html_purifier_article_allow_html',
            'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
        $options->set('html_purifier_article_allow_css',
            'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        $options->set('html_purifier_article_auto_para', 0);
        $options->set('html_purifier_comment_allow_html',
            'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
        $options->set('html_purifier_comment_allow_css',
            'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        $options->set('html_purifier_comment_auto_para', 1);

        HTMLPurifier::load();

        HTMLPurifier::config(['HTML.Allowed' => $options->get('html_purifier_comment_allow_html'),
            'CSS.AllowedProperties' => $options->get('html_purifier_comment_allow_css'),
            'AutoFormat.AutoParagraph' => $options->get('html_purifier_comment_auto_para') == 1]);

        // 过滤所有评论
        foreach ($db->table('comments')->get() as $comment) {
            $db->table('comments')->where('id', $comment['id'])
                ->update(['content' => HTMLPurifier::clean($comment['content'])]);
        }
    }

    public static $newest_version = 'v0.3.2';
}