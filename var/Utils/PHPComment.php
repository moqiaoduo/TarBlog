<?php
/**
 * Created by tarblog.
 * Date: 2020/8/5
 * Time: 0:14
 */

namespace Utils;

class PHPComment
{
    /**
     * 解析注释
     *
     * @param $content
     * @return mixed
     */
    public static function parse($content)
    {
        preg_match('/\/\*{2}(.*)\*\//is', $content, $comment);

        if (empty($comment = $comment[1] ?? null)) return false;

        preg_match("/\*\s([^@].*)/", $comment, $description);

        $info['description'] = $description[1] ?? null;

        preg_match_all('/\*\s@(.*)/', $comment, $items);

        foreach ($items[1] ?? [] as $val) {
            preg_match_all('/(.*?)\s(.*)/', $val, $rs);
            if (!isset($rs[1][0]) || !isset($rs[2][0]))
                continue;

            $info[trim($rs[1][0])] = trim($rs[2][0]);
        }

        return $info;
    }

    /**
     * 从文件解析注解（第二行开始）
     *
     * @param $filePath
     * @return bool|mixed
     */
    public static function parseFromFile($filePath)
    {
        if (!file_exists($filePath)) return false;

        return self::parse(file_get_contents($filePath));
    }
}
