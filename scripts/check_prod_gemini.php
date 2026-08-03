<?php
declare(strict_types=1);

$ch = curl_init('https://elitetcfcanada.online/scripts/diag_gemini.php?key=REPAIR_TCF_2026');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 50,
    CURLOPT_CONNECTTIMEOUT => 20,
]);
$r = curl_exec($ch);
$c = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$e = curl_error($ch);
curl_close($ch);
if ($r === false) {
    echo "diag_fail={$e}\n";
    exit(1);
}
echo "diag_http={$c}\n{$r}";
