<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Insertion des consignes TCF Canada</h1>";

try {
    $pdo->beginTransaction();
    
    // === COMPRÉHENSION ÉCRRITE ===
    $ceConsignes = <<<HTML
<h2>Consignes pour l'épreuve de Compréhension Écrite</h2>

<div class="consigne-section">
    <h3>📋 Informations générales</h3>
    <ul>
        <li><strong>Durée :</strong> 60 minutes</li>
        <li><strong>Nombre de questions :</strong> 39 questions à choix multiples</li>
        <li><strong>Support :</strong> Documents variés (articles, lettres, annonces, graphiques, etc.)</li>
        <li><strong>Objectif :</strong> Évaluer votre capacité à comprendre des documents écrits en français</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>📚 Structure de l'épreuve</h3>
    <p>L'épreuve se compose de plusieurs sections progressives :</p>
    <ul>
        <li><strong>Section 1 :</strong> Questions sur des documents de la vie quotidienne (annonces, lettres, emails)</li>
        <li><strong>Section 2 :</strong> Questions sur des documents de la vie professionnelle</li>
        <li><strong>Section 3 :</strong> Questions sur des documents d'information générale (articles de presse)</li>
        <li><strong>Section 4 :</strong> Questions sur des textes littéraires ou argumentatifs</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>💡 Conseils pour réussir</h3>
    <ul>
        <li>Lisez d'abord les questions avant de lire le texte</li>
        <li>Repérez les mots-clés dans les questions et cherchez-les dans le texte</li>
        <li>Gérez votre temps : ne passez pas trop de temps sur une question difficile</li>
        <li>Faites attention aux négations et aux expressions comme "sauf", "excepté", "ne... pas"</li>
        <li>Éliminez les réponses évidemment fausses pour augmenter vos chances</li>
        <li>Ne laissez aucune question sans réponse : il n'y a pas de point négatif</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>⏱️ Gestion du temps</h3>
    <p>Voici une répartition recommandée du temps :</p>
    <ul>
        <li>5 minutes : lecture des questions et repérage des mots-clés</li>
        <li>45 minutes : lecture des documents et réponse aux questions</li>
        <li>10 minutes : révision et vérification des réponses</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>🎯 Types de questions</h3>
    <ul>
        <li><strong>Questions à choix multiples :</strong> Une seule bonne réponse parmi 4 propositions</li>
        <li><strong>Questions d'association :</strong> Relier des éléments entre eux</li>
        <li><strong>Questions de classement :</strong> Mettre des éléments dans l'ordre chronologique</li>
        <li><strong>Questions de vrai/faux :</strong> Déterminer si une information est correcte ou non</li>
    </ul>
</div>
HTML;

    // === COMPRÉHENSION ORALE ===
    $coConsignes = <<<HTML
<h2>Consignes pour l'épreuve de Compréhension Orale</h2>

<div class="consigne-section">
    <h3>📋 Informations générales</h3>
    <ul>
        <li><strong>Durée :</strong> 35 minutes</li>
        <li><strong>Nombre de questions :</strong> 39 questions à choix multiples</li>
        <li><strong>Support :</strong> Enregistrements audio variés (conversations, interviews, annonces, etc.)</li>
        <li><strong>Objectif :</strong> Évaluer votre capacité à comprendre des documents oraux en français</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>📚 Structure de l'épreuve</h3>
    <p>L'épreuve se compose de plusieurs sections progressives :</p>
    <ul>
        <li><strong>Section 1 :</strong> Questions sur des enregistrements courts (annonces, messages)</li>
        <li><strong>Section 2 :</strong> Questions sur des interviews et conversations</li>
        <li><strong>Section 3 :</strong> Questions sur des exposés et documents longs</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>💡 Conseils pour réussir</h3>
    <ul>
        <li>Écoutez attentivement la première écoute pour comprendre le contexte général</li>
        <li>Lisez les questions avant l'écoute pour savoir quoi chercher</li>
        <li>Prenez des notes si nécessaire (mots-clés, chiffres, noms)</li>
        <li>Faites attention aux indices intonatifs et aux émotions dans la voix</li>
        <li>Ne vous découragez pas si vous ne comprenez pas tout : concentrez-vous sur les réponses</li>
        <li>Utilisez la deuxième écoute pour confirmer vos réponses</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>⏱️ Gestion du temps</h3>
    <p>Chaque enregistrement est écouté deux fois :</p>
    <ul>
        <li><strong>Première écoute :</strong> Compréhension globale et repérage des informations</li>
        <li><strong>Pause :</strong> Lecture des questions et choix des réponses</li>
        <li><strong>Deuxième écoute :</strong> Confirmation et ajustement des réponses</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>🎯 Types d'enregistrements</h3>
    <ul>
        <li><strong>Annonces publiques :</strong> Gares, aéroports, magasins</li>
        <li><strong>Messages téléphoniques :</strong> Répondeurs, boîtes vocales</li>
        <li><strong>Interviews :</strong> Radio, télévision, podcasts</li>
        <li><strong>Conversations :</strong> Dialogues entre plusieurs personnes</li>
        <li><strong>Exposés :</strong> Conférences, présentations</li>
    </ul>
</div>
HTML;

    // === EXPRESSION ÉCRITE ===
    $eeConsignes = <<<HTML
<h2>Consignes pour l'épreuve d'Expression Écrite</h2>

<div class="consigne-section">
    <h3>📋 Informations générales</h3>
    <ul>
        <li><strong>Durée :</strong> 60 minutes</li>
        <li><strong>Nombre de tâches :</strong> 3 tâches</li>
        <li><strong>Support :</strong> Documents écrits (lettres, emails, articles)</li>
        <li><strong>Objectif :</strong> Évaluer votre capacité à produire des écrits en français</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>📚 Structure de l'épreuve</h3>
    <p>L'épreuve se compose de trois tâches progressives :</p>
    
    <h4>Tâche 1 : Écrire un message (15 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Situation de la vie quotidienne ou professionnelle</li>
        <li><strong>Type :</strong> Lettre, email, message</li>
        <li><strong>Longueur :</strong> Environ 50 mots</li>
        <li><strong>Objectif :</strong> Transmettre une information simple</li>
    </ul>
    
    <h4>Tâche 2 : Rédiger un texte argumentatif (45 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Sujet d'actualité ou problème de société</li>
        <li><strong>Type :</strong> Article, essai, opinion</li>
        <li><strong>Longueur :</strong> Environ 180 mots</li>
        <li><strong>Objectif :</strong> Exprimer et défendre un point de vue</li>
    </ul>
    
    <h4>Tâche 3 : Synthèse de documents (60 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Deux ou trois documents sur un même thème</li>
        <li><strong>Type :</strong> Synthèse écrite</li>
        <li><strong>Longueur :</strong> Environ 250 mots</li>
        <li><strong>Objectif :</strong> Résumer et comparer les informations</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>💡 Conseils pour réussir</h3>
    <ul>
        <li>Lisez attentivement le sujet et repérez les mots-clés</li>
        <li>Respectez scrupuleusement le nombre de mots demandé</li>
        <li>Structurez votre texte : introduction, développement, conclusion</li>
        <li>Utilisez des connecteurs logiques (cependant, par conséquent, en effet)</li>
        <li>Variez le vocabulaire et les structures grammaticales</li>
        <li>Relisez votre texte pour corriger les fautes d'orthographe</li>
        <li>Adaptez votre ton au contexte (formel ou informel)</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>🎯 Critères d'évaluation</h3>
    <ul>
        <li><strong>Respect du sujet :</strong> Répondre à la question posée</li>
        <li><strong>Cohérence :</strong> Organisation logique du texte</li>
        <li><strong>Richesse lexicale :</strong> Vocabulaire varié et précis</li>
        <li><strong>Correction grammaticale :</strong> Syntaxe et conjugaison</li>
        <li><strong>Orthographe :</strong> Respect des règles d'écriture</li>
    </ul>
</div>
HTML;

    // === EXPRESSION ORALE ===
    $eoConsignes = <<<HTML
<h2>Consignes pour l'épreuve d'Expression Orale</h2>

<div class="consigne-section">
    <h3>📋 Informations générales</h3>
    <ul>
        <li><strong>Durée :</strong> 15 minutes</li>
        <li><strong>Nombre de tâches :</strong> 3 tâches</li>
        <li><strong>Support :</strong> Documents visuels ou thématiques</li>
        <li><strong>Objectif :</strong> Évaluer votre capacité à s'exprimer oralement en français</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>📚 Structure de l'épreuve</h3>
    <p>L'épreuve se compose de trois tâches progressives :</p>
    
    <h4>Tâche 1 : Entretien dirigé (2 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Questions personnelles sur votre vie</li>
        <li><strong>Thèmes :</strong> Études, travail, loisirs, projets</li>
        <li><strong>Objectif :</strong> Présenter brièvement votre situation</li>
    </ul>
    
    <h4>Tâche 2 : Exposé (5 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Document visuel (photo, graphique)</li>
        <li><strong>Préparation :</strong> 2 minutes de préparation</li>
        <li><strong>Objectif :</strong> Décrire et commenter un document</li>
    </ul>
    
    <h4>Tâche 3 : Débat (8 minutes)</h4>
    <ul>
        <li><strong>Contexte :</strong> Sujet d'actualité ou problème de société</li>
        <li><strong>Préparation :</strong> 2 minutes de préparation</li>
        <li><strong>Objectif :</strong> Exprimer et défendre un point de vue</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>💡 Conseils pour réussir</h3>
    <ul>
        <li>Parlez clairement et à un rythme modéré</li>
        <li>Structurez votre exposé : introduction, développement, conclusion</li>
        <li>Utilisez des connecteurs logiques (d'abord, ensuite, enfin)</li>
        <li>Donnez des exemples concrets pour illustrer vos propos</li>
        <li>N'hésitez pas à demander de répéter si vous n'avez pas compris</li>
        <li>Exprimez votre opinion personnellement (je pense, à mon avis)</li>
        <li>Gérez votre temps : ne dépassez pas le temps imparti</li>
    </ul>
</div>

<div class="consigne-section">
    <h3>🎯 Critères d'évaluation</h3>
    <ul>
        <li><strong>Fluence :</strong> Fluidité et aisance de l'expression</li>
        <li><strong>Richesse lexicale :</strong> Vocabulaire varié et précis</li>
        <li><strong>Correction grammaticale :</strong> Syntaxe et conjugaison</li>
        <li><strong>Prononciation :</strong> Intelligibilité de la parole</li>
        <li><strong>Cohérence :</strong> Organisation du discours</li>
        <li><strong>Interaction :</strong> Capacité à échanger avec l'examinateur</li>
    </ul>
</div>
HTML;

    // Mise à jour des consignes CE
    $stmt = $pdo->prepare("UPDATE tcf_ce_consignes SET body = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$ceConsignes]);
    echo "<p style='color:green'>✓ Consignes Compréhension Écrite mises à jour</p>";
    
    // Mise à jour des consignes CO
    $stmt = $pdo->prepare("UPDATE tcf_co_consignes SET body = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$coConsignes]);
    echo "<p style='color:green'>✓ Consignes Compréhension Orale mises à jour</p>";
    
    // Mise à jour des consignes EE (tache1)
    $stmt = $pdo->prepare("UPDATE tcf_ee_consignes SET body = ?, updated_at = NOW() WHERE task_key = 'tache1'");
    $stmt->execute([$eeConsignes]);
    echo "<p style='color:green'>✓ Consignes Expression Écrite mises à jour</p>";
    
    // Mise à jour des consignes EO (tache2)
    $stmt = $pdo->prepare("UPDATE tcf_eo_consignes SET body = ?, updated_at = NOW() WHERE task_key = 'tache2'");
    $stmt->execute([$eoConsignes]);
    echo "<p style='color:green'>✓ Consignes Expression Orale mises à jour</p>";
    
    $pdo->commit();
    echo "<p style='color:blue; font-weight:bold;'>✓ Toutes les consignes ont été insérées avec succès !</p>";
    
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
