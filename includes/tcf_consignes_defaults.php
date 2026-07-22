<?php

declare(strict_types=1);

/**
 * Corps HTML des consignes TCF Canada EE (une partition = une tâche).
 * Affiché dans les cartes .combinaison — HTML simple, editable admin.
 */
function tcf_consigne_ee_bodies(): array
{
    return [
        'tache1' => <<<'HTML'
<!-- tcf-ee-guide-v2 -->
<div class="ee-consigne-body">
    <h4>Format de la Tâche 1</h4>
    <p>C’est <strong>la plus simple</strong> des trois tâches (niveau A2 à B1). Vous devez rédiger un <strong>message court</strong> pour une situation de la vie quotidienne.</p>
    <p><strong>Types de messages demandés :</strong></p>
    <ul>
        <li>Message à un ami ou un collègue</li>
        <li>Courriel pour signaler quelque chose</li>
        <li>Note pour demander ou proposer quelque chose</li>
        <li>Invitation à un événement</li>
    </ul>
    <h4>Structure recommandée</h4>
    <ul>
        <li><strong>1. Ouverture :</strong> salutation adaptée (Cher/Chère, Bonjour, Salut)</li>
        <li><strong>2. Corps du message :</strong> objet, détails importants, demande ou proposition</li>
        <li><strong>3. Fermeture :</strong> formule de politesse (Amicalement, Cordialement)</li>
    </ul>
    <h4>Conseils pratiques</h4>
    <ul>
        <li><strong>Respectez le nombre de mots :</strong> visez environ 80 à 100 mots (dans la fourchette 60-120).</li>
        <li><strong>Adaptez le registre :</strong> informel avec un ami, plus formel en contexte professionnel.</li>
        <li><strong>Soyez clair et concis :</strong> allez droit au but, évitez les répétitions.</li>
        <li><strong>Relisez-vous :</strong> orthographe, accords et cohérence.</li>
    </ul>
</div>
HTML,
        'tache2' => <<<'HTML'
<!-- tcf-ee-guide-v2 -->
<div class="ee-consigne-body">
    <h4>Attention</h4>
    <p>Ce n’est <strong>pas</strong> une simple copie du document. Vous devez <strong>raconter / reformuler</strong>, puis <strong>donner votre réaction personnelle</strong>.</p>
    <h4>Format de la Tâche 2</h4>
    <p>Vous rédigez un <strong>article / récit</strong> (souvent de type blog) à partir d’un document fourni (lettre, article, message…).</p>
    <ul>
        <li>Longueur : <strong>120 à 150 mots</strong></li>
        <li>Durée conseillée : <strong>environ 20 minutes</strong></li>
        <li>Niveau : <strong>B1 avancé – B2</strong></li>
    </ul>
    <h4>Structure recommandée</h4>
    <ul>
        <li><strong>1. Introduction :</strong> présentez brièvement le sujet ou le document</li>
        <li><strong>2. Développement :</strong> reformulez les faits, ajoutez détails et réactions</li>
        <li><strong>3. Conclusion :</strong> donnez votre avis / ressenti et clôturez</li>
    </ul>
    <h4>Conseils pratiques</h4>
    <ul>
        <li><strong>Utilisez les temps du passé</strong> quand vous racontez une expérience.</li>
        <li><strong>Reformulez :</strong> ne recopiez pas le document.</li>
        <li><strong>Variez le lexique</strong> et utilisez des connecteurs (ensuite, cependant, en effet).</li>
        <li><strong>Gardez un fil logique :</strong> une idée claire par paragraphe.</li>
    </ul>
</div>
HTML,
        'tache3' => <<<'HTML'
<!-- tcf-ee-guide-v2 -->
<div class="ee-consigne-body">
    <h4>Format de la Tâche 3</h4>
    <p>C’est la tâche <strong>la plus exigeante</strong> : vous produisez un <strong>texte argumentatif</strong> à partir de documents / points de vue.</p>
    <p><strong>Vous recevez :</strong></p>
    <ul>
        <li>Une question / un sujet à traiter</li>
        <li>Deux documents présentant souvent des opinions différentes</li>
    </ul>
    <h4>Structure obligatoire en 2 parties avec titre</h4>
    <ul>
        <li><strong>Partie 1 (40-60 mots) :</strong> introduction, résumé des positions, connecteur d’opposition</li>
        <li><strong>Partie 2 (80-120 mots) :</strong> votre point de vue, arguments + exemples, conclusion</li>
    </ul>
    <h4>Connecteurs utiles</h4>
    <ul>
        <li><strong>Opposition :</strong> Cependant, néanmoins, toutefois, en revanche, au contraire, alors que</li>
        <li><strong>Opinion :</strong> À mon avis, selon moi, je pense que, il me semble que, personnellement</li>
    </ul>
    <h4>Conseils pratiques</h4>
    <ul>
        <li><strong>Lisez attentivement les 2 documents</strong> avant d’écrire.</li>
        <li><strong>N’oubliez pas le titre</strong> de votre texte.</li>
        <li><strong>Restez neutre en partie 1</strong> ; argumentez clairement en partie 2.</li>
    </ul>
</div>
HTML,
    ];
}

/**
 * Bloc critères (carte combinaison dédiée).
 */
function tcf_consigne_ee_criteria_html(): string
{
    return <<<'HTML'
<div class="ee-consigne-body">
    <ul>
        <li><strong>Cohérence :</strong> organisation logique des idées, transitions fluides</li>
        <li><strong>Vocabulaire :</strong> richesse lexicale, précision, éviter les répétitions</li>
        <li><strong>Grammaire :</strong> correction, variété des structures, conjugaison</li>
        <li><strong>Respect de la consigne :</strong> nombre de mots, format, réponse complète</li>
    </ul>
</div>
HTML;
}

function tcf_consigne_eo_bodies(): array
{
    return [
        'tache1' => <<<'HTML'
<!-- tcf-eo-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Objectif</h4>
    <p>Répondre spontanément à des questions simples sur votre identité, votre vie quotidienne et vos habitudes. L’examinateur cherche à vous mettre à l’aise.</p>
    <h4>Stratégies gagnantes</h4>
    <ul>
        <li><strong>Soyez naturel :</strong> répondez de manière spontanée sans trop réfléchir</li>
        <li><strong>Développez légèrement :</strong> ne vous contentez pas de réponses monosyllabiques</li>
        <li><strong>Restez positif :</strong> adoptez un ton amical et ouvert</li>
        <li><strong>Écoutez bien :</strong> assurez-vous de comprendre la question avant de répondre</li>
    </ul>
    <h4>Point important</h4>
    <p>L’examinateur ne vous laisse pas toujours parler les 2 minutes complètes sans interruption. S’il remarque que vous récitez votre présentation par cœur, il peut intervenir pour vous poser une question sur ce que vous avez mentionné (ex. : « Pourquoi avez-vous choisi d’étudier la finance ? »). Il est donc essentiel de bien comprendre ce que vous dites et d’être prêt à développer naturellement chaque point abordé.</p>
    <h4>Type de présentation</h4>
    <p>Faites une présentation <strong>classique et équilibrée</strong>, et non une présentation détaillée comme pour un entretien d’embauche. Donnez un aperçu général de votre identité, parcours et projet. Évitez de trop détailler. Restez concis, clair et naturel.</p>
    <h4>Exemple de présentation</h4>
    <p>« Bonjour, je m’appelle Yazid Bahraoui. Je suis né le 14 janvier 1995 et je vis actuellement au Canada, à Toronto… »</p>
    <h4>Deux situations fréquentes</h4>
    <ul>
        <li><strong>Présentation sans interruption :</strong> vous présentez naturellement et l’examinateur écoute sans intervenir.</li>
        <li><strong>Présentation avec interruption :</strong> l’examinateur détecte une récitation et pose des questions pour vérifier votre compréhension.</li>
    </ul>
</div>
HTML,
        'tache2' => <<<'HTML'
<!-- tcf-eo-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Format de la Tâche 2</h4>
    <p>L’examinateur vous met dans une situation précise dans laquelle vous devez <strong>poser des questions</strong> afin d’obtenir des informations. Il s’agit d’un dialogue interactif entre vous et l’examinateur.</p>
    <p><strong>Déroulement :</strong></p>
    <ul>
        <li>L’examinateur présente la situation et vous donne le sujet</li>
        <li>Vous avez <strong>2 minutes de préparation</strong> pour préparer vos questions</li>
        <li>L’enregistrement commence et le dialogue dure <strong>3 minutes 30</strong></li>
        <li>Vous posez une question → l’examinateur répond → vous enchaînez avec une autre question</li>
    </ul>
    <h4>Point clé</h4>
    <p>Il n’y a pas un nombre précis de questions à poser. Pour obtenir une bonne note, posez des questions <strong>sans vous arrêter jusqu’à la fin du temps</strong>. Cela montre votre capacité à interagir, relancer le dialogue et obtenir un maximum d’informations.</p>
    <h4>Objectif</h4>
    <p>Simuler une situation de la vie quotidienne où vous devez obtenir des informations en posant des questions pertinentes. Vous montrez votre capacité à interagir et à maintenir un dialogue dynamique.</p>
    <h4>Stratégies pendant la préparation (2 minutes)</h4>
    <ul>
        <li><strong>Identifier le contexte :</strong> à qui je parle ? (ami = tutoiement ; professionnel = vouvoiement)</li>
        <li><strong>Préparer plusieurs axes :</strong> date, lieu, horaire, prix, paiement, conditions, équipements…</li>
        <li><strong>Noter 5-6 questions essentielles</strong> comme base du dialogue</li>
        <li><strong>Anticiper des questions de relance</strong> pour continuer jusqu’à la fin du temps</li>
        <li><strong>Formules de politesse :</strong> comment commencer et conclure selon le contexte</li>
    </ul>
    <h4>Stratégies pendant le dialogue (3 min 30)</h4>
    <ul>
        <li><strong>Saluez poliment :</strong> « Bonjour, je voudrais… » / « Excusez-moi, pourriez-vous m’aider ? »</li>
        <li><strong>Posez vos questions une par une</strong> et laissez l’examinateur répondre</li>
        <li><strong>Relancez :</strong> « Et concernant… », « Pouvez-vous me dire… »</li>
        <li><strong>Réagissez naturellement :</strong> « D’accord », « Je comprends »</li>
        <li><strong>Ne vous arrêtez pas</strong> jusqu’à la fin du temps</li>
        <li><strong>Concluez :</strong> « Merci beaucoup pour ces informations »</li>
    </ul>
</div>
HTML,
        'tache3' => <<<'HTML'
<!-- tcf-eo-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Information importante — idée fausse à corriger</h4>
    <p>Beaucoup de candidats pensent qu’ils doivent parler sans interruption pendant les 4 min 30 pour obtenir une bonne note. <strong>C’est faux.</strong></p>
    <p><strong>La réalité :</strong> vous pouvez développer l’essentiel en 2 à 2 min 30 et obtenir une excellente note. L’examinateur relance ensuite avec d’autres questions.</p>
    <p><strong>Ce qui compte :</strong> votre capacité à réagir aux relances, apporter de nouvelles informations et développer vos idées à chaque intervention.</p>
    <h4>Erreur courante</h4>
    <p>Approche trop directe : donner immédiatement son avis (seulement avantages OU inconvénients) puis conclure trop vite. Le discours s’arrête en 1-2 minutes.</p>
    <p><strong>Solution :</strong> abordez <strong>toujours</strong> les avantages <strong>et</strong> les inconvénients, même si vous avez une opinion tranchée.</p>
    <h4>Objectif et déroulement</h4>
    <p>L’examinateur choisit un sujet d’actualité et pose une question ouverte (ex. : « Peut-on vivre sans téléphone ? »). Sans temps de préparation, vous devez :</p>
    <ul>
        <li>Répondre clairement avec arguments et exemples</li>
        <li>Développer l’essentiel (souvent 2 à 2 min 30)</li>
        <li>Répondre aux questions de relance pour enrichir vos propos</li>
    </ul>
    <h4>Structure efficace : avantages / inconvénients</h4>
    <ul>
        <li><strong>1. Introduction (20-30 sec) :</strong> reformulez le sujet et annoncez avantages + inconvénients</li>
        <li><strong>2. Les avantages (45 sec – 1 min) :</strong> 2-3 avantages avec exemples</li>
        <li><strong>3. Les inconvénients (45 sec – 1 min) :</strong> 2-3 inconvénients avec exemples</li>
        <li><strong>4. Opinion personnelle (30-40 sec) :</strong> indiquez clairement votre position</li>
        <li><strong>5. Relances (temps restant) :</strong> développez avec de nouveaux exemples</li>
    </ul>
    <h4>Ce que l’examinateur évalue vraiment</h4>
    <p>Ce n’est pas un monologue de 4 min 30. C’est un <strong>dialogue interactif</strong> : réaction aux relances, nouvelles informations, développement des idées.</p>
</div>
HTML,
    ];
}

/**
 * Critères + conseils jour J (carte combinaison EO).
 */
function tcf_consigne_eo_criteria_html(): string
{
    return <<<'HTML'
<div class="ee-consigne-body">
    <h4>Critères d’évaluation</h4>
    <ul>
        <li><strong>Lexique :</strong> richesse et précision du vocabulaire</li>
        <li><strong>Morphosyntaxe :</strong> correction grammaticale et structure des phrases</li>
        <li><strong>Phonologie :</strong> prononciation et accentuation</li>
        <li><strong>Pragmatique :</strong> adaptation au contexte et efficacité communicative</li>
    </ul>
    <h4>Conseils pour le jour J</h4>
    <ul>
        <li><strong>Restez calme et confiant :</strong> l’examinateur évalue votre niveau, il ne cherche pas à vous piéger.</li>
        <li><strong>Demandez des clarifications si nécessaire :</strong> « Pourriez-vous répéter, s’il vous plaît ? »</li>
        <li><strong>Ne vous arrêtez pas sur une erreur :</strong> continuez ; la fluidité compte beaucoup.</li>
        <li><strong>Entraînez-vous régulièrement</strong> avec les sujets et le simulateur.</li>
    </ul>
</div>
HTML;
}

/**
 * Corps HTML des consignes CE (sections accordion).
 */
function tcf_consigne_ce_bodies(): array
{
    return [
        'structure' => <<<'HTML'
<!-- tcf-ce-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Format officiel</h4>
    <p>L’épreuve de compréhension écrite TCF Canada se compose de <strong>39 questions</strong> réparties sur les niveaux de A1 à C2. Chaque question a un poids différent selon sa difficulté. La durée totale est de <strong>60 min</strong>.</p>
    <h4>Liberté de l’ordre des questions</h4>
    <p>Contrairement à d’autres examens, vous n’êtes pas obligé de répondre dans l’ordre. Vous pouvez commencer par la question de votre choix et naviguer librement entre les questions.</p>
    <h4>Stratégie recommandée : commencez par la fin</h4>
    <p>Les dernières questions valent plus de points. Voici le barème complet :</p>
    <ul>
        <li><strong>Questions 36-39 (C2) :</strong> 33 points chacune</li>
        <li><strong>Questions 30-35 (C1) :</strong> 26 points chacune</li>
        <li><strong>Questions 20-29 (B2) :</strong> 21 points chacune</li>
        <li><strong>Questions 11-19 (B1) :</strong> 15 points chacune</li>
        <li><strong>Questions 5-10 (A2) :</strong> 9 points chacune</li>
        <li><strong>Questions 1-4 (A1) :</strong> 3 points chacune</li>
    </ul>
    <p><strong>Stratégie optimale :</strong> commencez par les questions 36-39 (33 points chacune), puis progressez vers les questions moins difficiles. Mieux vaut répondre correctement à 20 questions difficiles qu’à 30 questions faciles.</p>
    <h4>Points clés à retenir</h4>
    <ul>
        <li>Total : <strong>39 questions = 699 points</strong></li>
        <li>Vous pouvez sauter et revenir aux questions</li>
        <li>Priorisez les questions à haut scoring</li>
        <li>Durée : <strong>60 min</strong></li>
        <li>Mieux vaut réussir les questions difficiles que multiplier les questions faciles</li>
    </ul>
</div>
HTML,
        'techniques' => <<<'HTML'
<!-- tcf-ce-guide-v1 -->
<div class="ee-consigne-body">
    <h4>1. Lecture active et sélective</h4>
    <p>Ne lisez pas le texte en entier. Scannez d’abord les questions pour identifier ce que vous cherchez, puis lisez le texte en ciblant les informations pertinentes.</p>
    <ul>
        <li>Lisez les questions avant le texte</li>
        <li>Cherchez les mots-clés</li>
        <li>Utilisez le contexte pour comprendre les mots inconnus</li>
    </ul>
    <h4>2. Gestion du temps</h4>
    <p>Vous avez 60 minutes pour 39 questions. Allouez environ <strong>1,5 minute</strong> par question. Ne vous attardez pas sur une question difficile.</p>
    <ul>
        <li>Priorisez les questions à haut scoring, puis revenez aux autres</li>
        <li>Revenez aux questions difficiles si le temps le permet</li>
        <li>Conservez du temps pour vérifier vos réponses</li>
    </ul>
    <h4>3. Reconnaissance des structures textuelles</h4>
    <p>Identifiez la structure du texte (introduction, développement, conclusion). Cela vous aide à naviguer rapidement et à trouver les informations essentielles.</p>
    <ul>
        <li>Recherchez les marqueurs de transition (cependant, finalement, etc.)</li>
        <li>Identifiez les paragraphes clés</li>
        <li>Faites attention aux titres et sous-titres</li>
    </ul>
    <h4>4. Inférence et contexte</h4>
    <p>Vous ne pouvez pas toujours trouver les réponses textuellement. Apprenez à faire des inférences et à utiliser le contexte pour comprendre le sens implicite.</p>
    <ul>
        <li>Utilisez les indices contextuels</li>
        <li>Comprenez les synonymes et paraphrases</li>
        <li>Analysez les relations entre les idées</li>
    </ul>
    <h4>5. Élimination des distracteurs</h4>
    <p>Utilisez le processus d’élimination. Si vous n’êtes pas certain, éliminez les réponses clairement fausses ou contraires au texte.</p>
    <ul>
        <li>Cherchez les pièges courants (informations hors contexte)</li>
        <li>Vérifiez la pertinence de chaque réponse</li>
        <li>Méfiez-vous des réponses partiellement correctes</li>
    </ul>
</div>
HTML,
        'erreurs' => <<<'HTML'
<!-- tcf-ce-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Lire trop lentement</h4>
    <p>Vous n’avez pas le temps de lire chaque mot. Apprenez à scanner et à extraire les informations essentielles rapidement.</p>
    <h4>Se fier au premier sens</h4>
    <p>Les questions testent souvent la compréhension nuancée. Allez au-delà du sens littéral et cherchez le contexte implicite.</p>
    <h4>Oublier les détails</h4>
    <p>Les petits détails comptent beaucoup. Une seule lettre mal lue peut changer le sens. Lisez avec attention.</p>
    <h4>Manquer de temps</h4>
    <p>Si vous manquez de temps, vous risquez de répondre sans réfléchir. Apprenez à gérer votre rythme de travail.</p>
    <h4>Ignorer le contexte culturel</h4>
    <p>Certains textes font référence à la culture francophone. Familiarisez-vous avec ces références pour mieux comprendre.</p>
    <h4>Changer vos réponses à la fin</h4>
    <p>Votre premier instinct est généralement correct. Évitez de changer vos réponses sans une bonne raison.</p>
</div>
HTML,
    ];
}

/**
 * Corps HTML des consignes CO (sections accordion).
 */
function tcf_consigne_co_bodies(): array
{
    return [
        'structure' => <<<'HTML'
<!-- tcf-co-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Format officiel</h4>
    <p>L’épreuve de compréhension orale TCF Canada se compose de <strong>39 questions</strong> réparties sur les niveaux de A1 à C2. Chaque question a un poids différent selon sa difficulté. La durée totale est de <strong>35 min</strong>.</p>
    <h4>Écoute unique</h4>
    <p>Contrairement à certains examens, chaque enregistrement n’est joué <strong>qu’une seule fois</strong>. Il est donc crucial de rester concentré et de prendre des notes efficacement dès la première écoute.</p>
    <h4>Stratégie recommandée : anticipez et notez</h4>
    <p>Les dernières questions valent plus de points. Voici le barème complet :</p>
    <ul>
        <li><strong>Questions 36-39 (C2) :</strong> 33 points chacune</li>
        <li><strong>Questions 30-35 (C1) :</strong> 26 points chacune</li>
        <li><strong>Questions 20-29 (B2) :</strong> 21 points chacune</li>
        <li><strong>Questions 11-19 (B1) :</strong> 15 points chacune</li>
        <li><strong>Questions 5-10 (A2) :</strong> 9 points chacune</li>
        <li><strong>Questions 1-4 (A1) :</strong> 3 points chacune</li>
    </ul>
    <p><strong>Stratégie optimale :</strong> restez particulièrement concentré pour les questions 30-39 qui rapportent le plus de points. Ne vous découragez pas si les premières questions semblent faciles.</p>
    <h4>Points clés à retenir</h4>
    <ul>
        <li>Total : <strong>39 questions = 699 points</strong></li>
        <li>Chaque enregistrement n’est joué qu’une fois</li>
        <li>Prenez des notes courtes et efficaces</li>
        <li>Durée : <strong>35 min</strong></li>
        <li>Mieux vaut réussir les questions difficiles que multiplier les questions faciles</li>
    </ul>
</div>
HTML,
        'techniques' => <<<'HTML'
<!-- tcf-co-guide-v1 -->
<div class="ee-consigne-body">
    <h4>1. Écoute active et anticipation</h4>
    <p>Avant l’écoute, lisez attentivement les questions et les choix de réponses. Cela vous permettra d’anticiper le type d’information à chercher pendant l’écoute.</p>
    <ul>
        <li>Lisez les questions avant l’enregistrement</li>
        <li>Identifiez les mots-clés à écouter</li>
        <li>Anticipez le contexte de la conversation</li>
    </ul>
    <h4>2. Prise de notes efficace</h4>
    <p>Développez un système de notes rapide. Utilisez des abréviations, des symboles et des mots-clés plutôt que des phrases complètes.</p>
    <ul>
        <li>Notez les chiffres, dates et noms propres</li>
        <li>Utilisez des symboles (→, +, -, ?)</li>
        <li>Concentrez-vous sur l’essentiel, pas sur chaque mot</li>
    </ul>
    <h4>3. Reconnaissance des indices sonores</h4>
    <p>Apprenez à reconnaître les indices qui signalent des informations importantes : changements de ton, répétitions, expressions comme « le plus important », « en conclusion », etc.</p>
    <ul>
        <li>Écoutez les changements d’intonation</li>
        <li>Repérez les mots de liaison</li>
        <li>Identifiez les reformulations et répétitions</li>
    </ul>
    <h4>4. Gestion des accents et débits variés</h4>
    <p>L’examen inclut différents accents francophones. Entraînez-vous régulièrement à écouter des locuteurs de France, Canada, Belgique, Suisse et Afrique francophone.</p>
    <ul>
        <li>Écoutez des podcasts francophones variés</li>
        <li>Regardez des films et séries en français</li>
        <li>Habituez-vous à différentes vitesses d’élocution</li>
    </ul>
    <h4>5. Élimination des distracteurs</h4>
    <p>Si vous n’êtes pas certain de la réponse, procédez par élimination. Identifiez les réponses clairement fausses ou qui ne correspondent pas à ce que vous avez entendu.</p>
    <ul>
        <li>Méfiez-vous des réponses qui utilisent les mêmes mots que l’audio</li>
        <li>Cherchez les contradictions avec ce que vous avez entendu</li>
        <li>Faites confiance à votre première impression</li>
    </ul>
</div>
HTML,
        'erreurs' => <<<'HTML'
<!-- tcf-co-guide-v1 -->
<div class="ee-consigne-body">
    <h4>Paniquer si vous manquez un mot</h4>
    <p>Ne vous arrêtez pas sur un mot incompris. Continuez à écouter et utilisez le contexte pour comprendre le sens général.</p>
    <h4>Écrire des notes trop longues</h4>
    <p>Des notes trop détaillées vous feront manquer des informations importantes. Utilisez des abréviations et des mots-clés.</p>
    <h4>Se fier aux mots identiques</h4>
    <p>Une réponse qui reprend exactement les mêmes mots que l’audio n’est pas forcément correcte. Cherchez le sens, pas les mots.</p>
    <h4>Négliger la préparation</h4>
    <p>Utilisez le temps avant chaque écoute pour lire les questions. Cette préparation est cruciale pour savoir quoi chercher.</p>
    <h4>Attendre la fin pour répondre</h4>
    <p>Répondez dès que vous êtes sûr de la réponse. Attendre peut vous faire oublier des informations importantes.</p>
    <h4>S’entraîner sans chronométrage</h4>
    <p>Pratiquez toujours dans les conditions réelles de l’examen pour vous habituer au rythme et à la pression du temps.</p>
</div>
HTML,
    ];
}

function tcf_consigne_body_needs_refresh(string $body, string $skill): bool
{
    $plain = trim(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($plain === '') {
        return true;
    }
    if ($skill === 'ee' && stripos($body, 'tcf-ee-guide-v2') === false) {
        return true;
    }
    if ($skill === 'ee' && stripos($body, 'tcf-ee-guide-body') !== false) {
        return true;
    }
    if ($skill === 'eo' && stripos($body, 'tcf-eo-guide-v1') === false) {
        return true;
    }
    if ($skill === 'ce' && stripos($body, 'tcf-ce-guide-v1') === false) {
        return true;
    }
    if ($skill === 'co' && stripos($body, 'tcf-co-guide-v1') === false) {
        return true;
    }
    if (mb_strlen($plain) < 180) {
        return true;
    }
    if ($skill !== 'ee' && $skill !== 'eo' && $skill !== 'ce' && $skill !== 'co' && stripos($body, 'consigne-section') === false) {
        return true;
    }
    if (stripos($body, "Consignes pour l'épreuve") !== false || stripos($body, 'Consignes pour l’épreuve') !== false) {
        return true;
    }
    if (
        preg_match('/Tâche\s*1\s*:/u', $plain)
        && preg_match('/Tâche\s*2\s*:/u', $plain)
        && preg_match('/Tâche\s*3\s*:/u', $plain)
    ) {
        return true;
    }
    if ($skill === 'ee' && (stripos($plain, 'Entretien dirigé') !== false || stripos($plain, 'Exercice en interaction') !== false)) {
        return true;
    }
    return false;
}
