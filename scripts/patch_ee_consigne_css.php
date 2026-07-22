<?php
declare(strict_types=1);
$f = dirname(__DIR__) . '/Assets/css/style_Expresion_Ecrite.css';
$s = file_get_contents($f);
$start = strpos($s, '/* ========== Consignes EE');
$end = strpos($s, '/* EO garde');
if ($start === false || $end === false) {
    fwrite(STDERR, "markers not found\n");
    exit(1);
}
$new = <<<'CSS'
/* ========== Consignes EE : mêmes cartes combinaison ========== */
#ee-consignes-container {
    display: flex;
    flex-direction: column;
    gap: 0;
}

#ee-consignes-container .combinaison {
    margin-bottom: 1rem;
}

#ee-consignes-container .combinaison:hover {
    transform: none;
}

#ee-consignes-container .combinaison-header {
    padding: 0.7rem 1rem;
}

#ee-consignes-container .combinaison-header h2 {
    font-size: 0.95rem;
    font-weight: 700;
}

#ee-consignes-container .combinaison-header .ee-consigne-meta {
    display: block;
    margin-top: 0.2rem;
    font-size: 0.75rem;
    font-weight: 500;
    opacity: 0.92;
    color: #fff;
}

#ee-consignes-container .combinaison-content {
    padding: 1rem 1.15rem;
}

#ee-consignes-container .ee-consigne-body h4 {
    margin: 0.85rem 0 0.4rem;
    color: var(--main-color, #d30d0d);
    font-size: 0.95rem !important;
    font-weight: 700;
}

#ee-consignes-container .ee-consigne-body h4:first-child {
    margin-top: 0;
}

#ee-consignes-container .ee-consigne-body p,
#ee-consignes-container .ee-consigne-body li {
    font-size: 0.92rem !important;
    line-height: 1.55 !important;
    color: #3a4150;
}

#ee-consignes-container .ee-consigne-body ul {
    margin: 0.25rem 0 0.5rem;
    padding-left: 1.15rem;
}

#ee-consignes-container .ee-consigne-body p {
    margin: 0 0 0.45rem;
}

.tcf-consigne-empty {
    color: #64748b;
    margin: 0;
    font-size: 0.92rem;
}

CSS;
file_put_contents($f, substr($s, 0, $start) . $new . substr($s, $end));
echo "OK\n";
