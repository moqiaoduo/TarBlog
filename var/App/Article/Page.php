<?php
/**
 * Created by tarblog.
 * Date: 2020/5/30
 * Time: 0:47
 */

namespace App\Article;

use App\Article;

class Page extends Article
{
    protected $type = 'page';

    /**
     * @inheritDoc
     */
    public function render()
    {
        $tpl = $this->_data->template;
        if (!empty($tpl) && file_exists($file = $this->_themeDir . DIRECTORY_SEPARATOR . 'page-' . $tpl. '.php')) {
            include $file;
        } else {
            include $this->_themeDir . DIRECTORY_SEPARATOR . 'page.php';
        }
    }

    public function _respondId()
    {
        return 'respond-page-' . $this->_data->cid;
    }
}