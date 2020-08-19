<?php

namespace App;

class NotFound extends Base
{
    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        http_response_code(404); // 设置HttpCode=404
        // 调用404页面
        if (file_exists($err_page = $this->_themeDir . DIRECTORY_SEPARATOR . '404.php'))
            include $err_page;
        else
            showErrorPage("页面不存在");
    }
}