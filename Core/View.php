<?php

namespace Core;

use App\Config;

/**
 * View — rendu des templates Twig
 */
class View
{
    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array  $args      Associative array of data to display in the view
     *
     * @return void
     */
    public static function renderTemplate(string $template, array $args = []): void
    {
        static $twig = null;

        if ($twig === null) {
            $isDev = Config::isDev();

            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/App/Views');

            $twig = new \Twig\Environment($loader, [
                'debug' => $isDev,
                'cache' => $isDev ? false : dirname(__DIR__) . '/cache/twig',
            ]);

            if ($isDev) {
                $twig->addExtension(new \Twig\Extension\DebugExtension());
            }
        }

        echo $twig->render($template, self::setDefaultVariables($args));
    }

    /**
     * Ajoute les variables disponibles dans toutes les vues
     *
     * @param array $args
     * @return array
     */
    private static function setDefaultVariables(array $args = []): array
    {
        $args['user'] = $_SESSION['user'] ?? null;

        return $args;
    }
}