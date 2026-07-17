<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
$parts = [
    __DIR__ . '/mai_2026_part1.php',
    __DIR__ . '/mai_2026_part2.php',
    __DIR__ . '/mai_2026_part3.php',
    __DIR__ . '/mai_2026_part4.php',
    __DIR__ . '/mai_2026_part5.php',
    __DIR__ . '/mai_2026_part6.php',
    __DIR__ . '/mai_2026_part7.php',
    __DIR__ . '/mai_2026_part8.php',
    __DIR__ . '/mai_2026_part9.php',
    __DIR__ . '/mai_2026_part10.php',
    __DIR__ . '/mai_2026_part11.php',
    __DIR__ . '/mai_2026_part12.php',
    __DIR__ . '/mai_2026_part13.php',
];
$merged = [];
foreach ($parts as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Fichier données manquant: ' . $file);
    }
    $merged = array_merge($merged, require $file);
}
return $merged;
