<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
$parts = [
    __DIR__ . '/avril_2026_part1.php',
    __DIR__ . '/avril_2026_part2.php',
    __DIR__ . '/avril_2026_part3.php',
];
$merged = [];
foreach ($parts as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Fichier données manquant: ' . $file);
    }
    $merged = array_merge($merged, require $file);
}
return $merged;
