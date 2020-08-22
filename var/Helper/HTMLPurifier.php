<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/21
 * Time: 17:16
 */

namespace Helper;

use HTMLPurifier_Config;

class HTMLPurifier
{
    /**
     * @var HTMLPurifier_Config
     */
    private static $config;

    /**
     * 加载HTML Purifier并做一些默认设置
     */
    public static function load()
    {
        require_once __ROOT_DIR__ . '/var/HTMLPurifier/HTMLPurifier.auto.php';

        self::$config = HTMLPurifier_Config::createDefault();

        self::$config->set('HTML.Doctype', 'HTML 4.01 Transitional');

        self::$config->set('AutoFormat.RemoveEmpty', true);

        self::$config->set('Cache.DefinitionImpl', null); // 默认禁用缓存
    }

    /**
     * 获取或设置配置
     *
     * @param array|mixed $key
     * @param mixed $default
     * @return HTMLPurifier_Config
     */
    public static function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return self::$config;
        } elseif (is_array($key)) {
            foreach ($key as $k => $v) {
                self::$config->set($k, $v);
            }
        } else {
            return self::$config->get($key) ?? $default;
        }

        return self::$config;
    }

    public static function clean($dirty_html)
    {
        return (new \HTMLPurifier(self::$config))->purify($dirty_html);
    }
}