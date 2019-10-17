<?php declare(strict_types=1);

namespace SilerExt\Config;

use Siler\Container;
use Siler\Twig;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_Extensions_Extension_Text;
use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;

function config(string $path) {
    $config = Container\get('config');

    $result = $config;
    foreach (explode('.', $path) as $part) {
        $result = $result[$part] ?? null;
    }

    return $result;
}

function initConfig(string $filename) {
    Container\set('config', require_once $filename);
}

function initTwig() {
    $twig_config = config('twig');

    $debug = !isset($twig_config['cache_path']);
    $twig = Twig\init(
        $twig_config['template_path'],
        $twig_config['cache_path'] ?? false,
        $debug
    );
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    $twig->addFilter(new TwigFilter('firstParagraph', function($data) {
        return explode("\n", $data)[0];
    }));

    // add dump functions on debug
    if ($debug) {
        $twig->addFunction(new TwigFunction('dump', function($data) {
            if (function_exists('dump')) {
                dump($data);
            } else {
                var_dump($data);
            }
        }));
    }
}

function initWhoops(string $editor = 'atom') {
    $whoops = new WhoopsRun();
    $whoopsHandler = new WhoopsPrettyPageHandler();
    $whoopsHandler->setEditor($editor);
    $whoops->prependHandler($whoopsHandler);
    $whoops->register();
}
