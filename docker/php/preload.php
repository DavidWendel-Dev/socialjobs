<?php

/*
|--------------------------------------------------------------------------
| Opcache Preload — SocialJobs
|--------------------------------------------------------------------------
| Carrega o autoloader do Composer para que classes frequentemente usadas
| sejam armazenadas em memória compartilhada pelo Opcache no boot do FPM.
*/

declare(strict_types=1);

$autoload = __DIR__ . '/vendor/autoload.php';

if (! is_file($autoload)) {
    return;
}

require $autoload;

// Preload seletivo — evita ler arquivos com traits/atributos complexos que
// possam quebrar o preloader. O autoload já resolve o restante em runtime.
$paths = [
    __DIR__ . '/vendor/laravel/framework/src/Illuminate/Foundation',
    __DIR__ . '/vendor/laravel/framework/src/Illuminate/Http',
    __DIR__ . '/vendor/laravel/framework/src/Illuminate/Routing',
    __DIR__ . '/vendor/laravel/framework/src/Illuminate/Container',
    __DIR__ . '/vendor/laravel/framework/src/Illuminate/Support',
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        continue;
    }
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($rii as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') {
            continue;
        }
        @opcache_compile_file($file->getPathname());
    }
}
