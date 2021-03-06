<?php
/**
 * Created by TarBlog.
 * Date: 2020/8/18
 * Time: 2:37
 */

namespace App\Archive;

use App\Archive;
use Models\User;

class Author extends Archive
{
    protected $type = 'author';

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $user = $this->db->table('users')->where('id', $this->routeParams['id'])
            ->firstWithModel(User::class);

        if (is_null($user)) return false;

        $this->_archiveTitle = $user->name ?: $user->username;

        $this->paginator = $this->db->table('contents')->whereNull('deleted_at')
            ->where('type', 'post')->where('uid', $user->id)
            ->whereIn('status', ['publish', 'password'])->orderByDesc('created_at')
            ->paginate($this->request->get('page', 1), $this->options->get('pageSize', 10));

        $this->queue = $this->paginator->getData();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        if (file_exists($file = $this->_themeDir . DIRECTORY_SEPARATOR . 'author.php'))
            include $file;
        else
            parent::render();
    }
}