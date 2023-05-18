<?php

namespace Akrbdk\PhpMvcCore;

class View
{
    public string $title = '';

    public function renderView(string $view, array $params = [])
    {
        $viewContent = $this->renderOnlyView($view, $params);
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    private function renderOnlyView($view, array $params = [])
    {
        foreach ($params as $name => $value) {
            $$name = $value;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/$view.php";
        return ob_get_clean();
    }

    private function layoutContent()
    {
        $layout = Application::$app->layout;
        if (Application::$app->controller) {
            $layout = Application::$app->controller->layout;
        }
        ob_start();
        include_once Application::$ROOT_DIR . "/views/layouts/$layout.php";
        return ob_get_clean();
    }

    private function renderContent(string $content)
    {
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $content, $layoutContent);
    }
}
