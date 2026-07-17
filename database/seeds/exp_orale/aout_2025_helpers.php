<?php
declare(strict_types=1);

function eo_aout_part(string $taskKey, int $partNum, array $subjects): array
{
    return [
        'task_key' => $taskKey,
        'part_number' => $partNum,
        'part_title' => 'Partie ' . $partNum,
        'subjects' => $subjects,
    ];
}

function eo_aout_sub(string $title, string $prompt, string $correction, string $icon = 'bx bx-message-detail'): array
{
    return [
        'title' => $title,
        'prompt' => $prompt,
        'correction' => trim($correction),
        'icon_class' => $icon,
    ];
}
