<?php

namespace App;

use Slim\Http\Response;

class View
{
    protected string $templateDir;

    public function __construct(string $templateDir)
    {
        $this->templateDir = $templateDir;
    }

    public function render(Response $res, string $filename, array $data = [])
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $this->templateDir . '/' . $filename . '.php';
        $content = ob_get_clean();

        ob_start();
        require $this->templateDir . '/layout.php';

        return $res->write(ob_get_clean());
    }
}
