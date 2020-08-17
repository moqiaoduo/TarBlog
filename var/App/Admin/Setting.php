<?php
/**
 * Created by tarblog.
 * Date: 2020/8/10
 * Time: 0:58
 */

namespace App\Admin;

use App\NoRender;

class Setting extends NoRender
{
    public function execute(): bool
    {
        $request = $this->request;

        switch ($request->get('p')) {
            case 'general':
                $options = [
                    'siteName' => $request->post('siteName'),
                    'siteUrl' => $request->post('siteUrl'),
                    'description' => $request->post('description'),
                    'keyword' => $request->post('keyword'),
                    'register' => $request->post('register'),
                    'timezone' => $request->post('timezone', 'Asia/Shanghai'),
                    'allowFileExt' => serialize(explode(',', $request->post('allowFileExt')))
                ];
                break;
            case 'reading':
                if ($request->post('frontPage', 'recent') == 'page')
                    $index = $request->post('frontPagePage', 0);
                else $index = 0;

                $options = [
                    'indexPage' => $index,
                    'showArticleList' => $request->has('frontArchive'),
                    'articleListUrl' => $request->post('archivePattern', '/article'),
                    'pageSize' => $request->post('pageSize', 10)
                ];
                break;
            case 'comment':
                $options = [
                    'commentsAvatarRating' => $request->post('commentsAvatarRating'),
                    'commentsPageSize' => $request->post('commentsPageSize'),
                    'commentsPageDisplay' => $request->post('commentsPageDisplay'),
                    'commentsOrder' => $request->post('commentsOrder'),
                    'commentsPostInterval' => $request->post('commentsPostInterval'),
                    'commentsShowUrl' => 0,
                    'commentsUrlNofollow' => 0,
                    'commentsAvatar' => 0,
                    'commentsPageBreak' => 0,
                    'commentsThreaded' => 0,
                    'commentsRequireModeration' => 0,
                    'commentsWhitelist' => 0,
                    'commentsRequireMail' => 0,
                    'commentsRequireURL' => 0,
                    'commentsCheckReferer' => 0,
                    'commentsAutoClose' => 0,
                    'commentsPostIntervalEnable' => 0
                ];

                foreach ($request->post('commentsShow', []) as $value) {
                    if (isset($options[$value])) $options[$value] = 1;
                }
                foreach ($request->post('commentsPost', []) as $value) {
                    if (isset($options[$value])) $options[$value] = 1;
                }
                break;
            case 'url':
                $rewrite = $request->post('rewrite', 0);
                $htaccess = __ROOT_DIR__ . '/.htaccess';
                if ($rewrite && !file_exists($htaccess)) {
                    file_put_contents($htaccess, <<<EOF
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]
EOF
);
                }

                if (($postUrl = $request->post('postPattern', 'custom')) == 'custom')
                    $postUrl = $request->post('customPattern', '/archives/{cid}');

                $options = [
                    'postUrl' => $postUrl,
                    'pageUrl' => $request->post('pagePattern', '/page/{cid}'),
                    'categoryUrl' => $request->post('categoryPattern', '/category/{slug}'),
                    'rewrite' => $rewrite
                ];
                break;
            default:
                $options = [];
        }

        foreach ($options as $key => $val) {
            $this->options->set($key, $val);
        }

        $this->request->session()->flash('success', '设置保存成功');

        back();

        return true;
    }
}