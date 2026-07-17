<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
$part1 = __DIR__ . '/janvier_2026_part1.php';
$part2 = __DIR__ . '/janvier_2026_part2.php';
$part3 = __DIR__ . '/janvier_2026_part3.php';
foreach ([$part1, $part2, $part3] as $file) {
    if (!is_file($file)) {
        throw new RuntimeException('Fichier données manquant: ' . $file);
    }
}

/** @var list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> $merged */
$merged = array_merge(
    require $part1,
    require $part2,
    require $part3
);

return $merged;
