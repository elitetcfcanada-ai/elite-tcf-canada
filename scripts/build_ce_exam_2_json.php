<?php
declare(strict_types=1);

/**
 * Génère database/seeds/ce_exam_2.json — Compréhension écrite n°2
 * Usage : php scripts/build_ce_exam_2_json.php
 */

function q(int $id, string $situation, string $text, int $points, array $answers, int $correctIndex): array
{
    $out = [];
    foreach (['a', 'b', 'c', 'd'] as $i => $key) {
        $out[] = [
            'id' => $key,
            'text' => $answers[$i],
            'correct' => $i === $correctIndex,
        ];
    }

    return [
        'id' => $id,
        'situation' => $situation,
        'text' => $text,
        'points' => $points,
        'answers' => $out,
    ];
}

$questions = [
    q(
        1,
        '<p>Vous voulez découvrir d\'autres cultures&nbsp;?</p>'
        . '<p>Vous voulez parler anglais, français, espagnol&nbsp;?</p>'
        . '<p>Leçons à la maison ou au centre Bernardisilmo.</p>'
        . '<p>Informations au 01 03 02 06 65.</p>',
        'Qu\'est-ce que propose cette publicité&nbsp;?',
        3,
        ['Des cours', 'Des emplois', 'Des livres', 'Des voyages'],
        0
    ),
    q(
        2,
        '<p>Faites confiance à l\'entreprise pour tous vos envois urgents.</p>'
        . '<p>Nous prenons vos paquets à domicile ou dans les bureaux de poste et nous les envoyons dans le monde entier.</p>',
        'Que propose cette entreprise&nbsp;?',
        3,
        ['De stocker des marchandises', 'De transporter des colis', 'De vendre des cartons', 'De voyager à l\'étranger'],
        1
    ),
    q(
        3,
        '<p>Je suis bien arrivé chez moi. Je t\'appelle demain pour aller voir le film.</p>'
        . '<p>Je t\'embrasse.</p>'
        . '<p><strong>Cristian.</strong></p>',
        'Où est Cristian&nbsp;?',
        3,
        ['À la gare.', 'À la maison.', 'Au cinéma.', 'Au travail.'],
        1
    ),
    q(
        4,
        '<p>Pierre, n\'oubliez pas notre déjeuner au restaurant.</p>'
        . '<p>Je vous attends devant l\'ascenseur.</p>'
        . '<p><strong>Alice.</strong></p>',
        'Pourquoi est-ce qu\'Alice attend Pierre&nbsp;?',
        3,
        ['Pour des courses', 'Pour un repas', 'Pour un travail', 'Pour une réunion'],
        1
    ),
    q(
        5,
        '<p>François,</p>'
        . '<p>Pouvons-nous nous rencontrer dans ton bureau demain matin&nbsp;?</p>'
        . '<p><strong>Ariane.</strong></p>',
        'Pourquoi Ariane écrit-elle ce message&nbsp;?',
        3,
        ['Elle doit partir une journée', 'Elle propose un déjeuner', 'Elle souhaite parler à François', 'Elle veut visiter une entreprise'],
        2
    ),
    q(
        6,
        '<p>Le forum de Paris s\'adresse aux jeunes de moins de trente ans, avec ou sans diplôme. Des milliers d\'offres sont disponibles dans près de 300 métiers. Chaque année, un grand nombre de jeunes signent un contrat de travail lors du salon.</p>',
        'Pourquoi le forum de Paris est-il utile aux jeunes&nbsp;?',
        3,
        ['Il les renseigne sur des informations', 'Il leur permet de trouver un emploi', 'Il leur présente des stages en entreprise', 'Il leur propose des logements étudiants'],
        1
    ),
    q(
        7,
        '<p><strong>Jardin de la Fontaine</strong></p>'
        . '<p><em>Ouverture-Fermeture</em></p>'
        . '<ul>'
        . '<li>Du 1er septembre au 31 mars&nbsp;: 7h30-18h30</li>'
        . '<li>Du 1er avril au 30 juin&nbsp;: 7h30-20h30</li>'
        . '<li>Du 1er juillet au 31 août&nbsp;: 7h00-22h30</li>'
        . '</ul>',
        'Quand peut-on se promener dans le parc après 18h30&nbsp;?',
        9,
        ['En décembre', 'En février', 'En mai', 'En mars'],
        2
    ),
    q(
        8,
        '<ul>'
        . '<li>1&nbsp;kg de pommes</li>'
        . '<li>Salade</li>'
        . '<li>Mouchoirs en papier</li>'
        . '</ul>',
        'Où trouve-t-on ces produits&nbsp;?',
        9,
        ['Dans une boucherie', 'Dans une boulangerie', 'Dans une épicerie', 'Dans une poissonnerie'],
        2
    ),
    q(
        9,
        '<p>Bonjour Ahmed,</p>'
        . '<p>Ce message est pour te rappeler la réunion de lundi. Nous commanderons des paniers-repas pour le midi, car la séance peut durer longtemps&nbsp;: les dossiers à traiter sont nombreux. Peux-tu en informer les personnes des services concernés&nbsp;? Merci d\'avance.</p>'
        . '<p>À lundi.</p>'
        . '<p><strong>elite tcf canada</strong></p>',
        'Que demande elite tcf canada à Ahmed&nbsp;?',
        9,
        ['D\'apporter le déjeuner', 'D\'avancer le rendez-vous', 'De consulter les documents', 'De prévenir les collègues.'],
        3
    ),
    q(
        10,
        '<p>Madame, Monsieur,</p>'
        . '<p>En raison de travaux rue Paul Vaillant, l\'accès à la piscine municipale se fera côté boulevard Camélinat, du 15 au 25 mars inclus. Veuillez nous excuser pour la gêne occasionnée.</p>'
        . '<p><strong>Le service municipal.</strong></p>',
        'Quelle information est donnée au sujet de la piscine&nbsp;?',
        9,
        ['Elle sera fermée au mois de mars.', 'Il y aura des travaux à l\'intérieur.', 'L\'entrée se fera par une autre porte.', 'Le service municipal réparera les lieux.'],
        2
    ),
    q(
        11,
        '<p>Chers amis,</p>'
        . '<p>Nous aimerions vous avoir avec nous à notre soirée pour fêter la nouvelle année. Soyez là pour 21&nbsp;h&nbsp;00 en tenue décontractée. Nous sommes entre amis.</p>'
        . '<p><strong>Céline et Redah</strong></p>',
        'Pourquoi Céline et Redah invitent-ils des amis&nbsp;?',
        15,
        ['Pour un anniversaire', 'Pour un mariage', 'Pour un réveillon', 'Pour un spectacle'],
        2
    ),
    q(
        12,
        '<p>Clément et Pierre vont participer au tour automobile 4L Trophy au Maroc. Ils vont apporter 40&nbsp;kg de matériel et 10&nbsp;kg de nourriture à une école. Les deux amis recherchent des solutions pour payer leur voyage.</p>'
        . '<p>Vous voulez les aider&nbsp;?</p>'
        . '<p><a href="mailto:clp@4Ltrophy.fr">clp@4Ltrophy.fr</a></p>',
        'De quoi ont besoin Clément et Pierre&nbsp;?',
        15,
        ['D\'argent pour la course', 'De livres pour les enfants', 'De produits pour les repas', 'D\'une voiture pour le transport'],
        0
    ),
    q(
        13,
        '<p>Chers collègues,</p>'
        . '<p>Veuillez rendre au plus tard vendredi midi votre feuille de demande de congés. Nous vous rappelons qu\'il est essentiel pour le bon fonctionnement de notre société pendant la période estivale et pour satisfaire toutes vos demandes que ces feuilles soient dûment complétées et remises le plus tôt possible à votre responsable. Nous vous remercions de votre collaboration et nous vous souhaitons une bonne semaine.</p>'
        . '<p><strong>La direction des ressources humaines.</strong></p>',
        'Dans ce message électronique, que demande-t-on aux employés&nbsp;?',
        15,
        ['De participer à la vie de l\'entreprise', 'De poser leurs jours de vacances', 'De remplir un formulaire en ligne', 'De respecter les règles de sécurité'],
        1
    ),
    q(
        14,
        '<p><strong>«&nbsp;L\'internaute.fr&nbsp;»</strong> vous informe sur la protection de l\'environnement.</p>'
        . '<p>Chaque année, c\'est environ un million de tonnes de courriers non adressés qui arrivent dans les boîtes aux lettres des Français. Ces courriers sont des publicités de supermarchés ou des journaux gratuits. Or, il vaudrait mieux limiter la production de papier inutile.</p>'
        . '<p>La meilleure méthode consiste à poser un autocollant «&nbsp;Stop Pub&nbsp;», disponible gratuitement sur le site du ministère de l\'Écologie. Lorsqu\'il s\'agit de courrier adressé à votre nom, vous pouvez solliciter la liste Robinson pour demander de ne plus recevoir de courrier envoyé par les listes de marketing.</p>',
        'Quel est l\'objectif de l\'article de «&nbsp;L\'internaute.fr&nbsp;»&nbsp;?',
        15,
        ['Alerter les clients sur les fausses publicités des magasins', 'Donner des conseils pour arrêter de recevoir des publicités', 'Présenter le résumé d\'une étude sur les effets de la publicité', 'Vendre l\'autocollant « Stop Pub » du ministère de l\'Écologie'],
        1
    ),
    q(
        15,
        '<p>Des ingénieurs travaillent très sérieusement à la réalisation d\'un bus, semblable à un tramway tunnel qui circulera au-dessus des files de voitures pour dégager le trafic dans les zones urbaines. L\'engin sera entièrement électrique et pourra transporter 1&nbsp;200 personnes à une vitesse de 60&nbsp;km/heure. Pour que les automobilistes «&nbsp;au-dessous&nbsp;» évoluent sans danger, des feux placés dans la partie inférieure du bus signaleront ses changements de direction.</p>',
        'Quel sera l\'atout de ce moyen de transport&nbsp;?',
        15,
        ['Il modifiera son parcours à la demande des usagers', 'Il permettra d\'éviter les embouteillages en ville', 'Il sera équipé d\'un nouveau système de sécurité', 'Il sera très facile d\'entretien et peu coûteux'],
        1
    ),
    q(
        16,
        '<p>Pas facile de trouver le juste équilibre entre respect et liberté. Le principal du lycée Charles-de-Gaulle a décidé de demander à ses élèves de ne plus porter de vêtements de sport en cours. Le principal estime que c\'est une question de respect et de propreté. Un survêtement, ce n\'est pas assez habillé pour venir au lycée. Les élèves qui n\'étaient pas du même avis n\'ont pas été acceptés en cours.</p>',
        'Pourquoi la direction de cet établissement interdit-elle la tenue de sport en classe&nbsp;?',
        15,
        ['Pour aider les élèves à ne pas s\'enrhumer', 'Pour enseigner le droit à la tolérance', 'Pour respecter des normes d\'hygiène', 'Pour supprimer les différences sociales'],
        2
    ),
    q(
        17,
        '<p>Courir les 42,195&nbsp;km du marathon de Paris ne s\'improvise pas. C\'est une course qui nécessite de s\'entraîner régulièrement et d\'être capable de surmonter le «&nbsp;mur de douleur&nbsp;» qui survient chez tous les marathoniens entre le 32<sup>e</sup> et le 37<sup>e</sup> kilomètre. «&nbsp;Pour espérer terminer l\'épreuve, il faut courir depuis au moins un an et, dans les dernières semaines, faire trois à quatre heures d\'entraînement hebdomadaires réparties en autant de séances&nbsp;», insiste un organisateur de stages de préparation.</p>',
        'Quel est le conseil de cet organisateur avant de participer au marathon&nbsp;?',
        15,
        ['Avoir comme seul objectif de terminer l\'épreuve', 'Pratiquer la course à pied plusieurs fois par semaine', 'Prendre régulièrement des médicaments anti-douleur', 'Se donner de longues périodes de récupération'],
        1
    ),
    q(
        18,
        '<p>Pour trouver des personnalités sortant du lot à fort potentiel d\'évolution, un cabinet de recrutement organise des entretiens individuels sans consulter au préalable le dossier des candidats. Confrontés à une étude de cas, une discussion d\'idées ou un questionnaire croisé, ils peuvent se démarquer grâce à leur savoir-être ou à un talent particulier.</p>'
        . '<p>Les entretiens n\'ont pas pour enjeu de poste précis, ils visent à constituer une réserve de talents, dans laquelle il sera possible de puiser.</p>',
        'Quelle est la méthode de recrutement retenue par le cabinet&nbsp;?',
        15,
        ['Auditionner plusieurs candidats en même temps.', 'Faire mener l\'entrevue des candidats par un futur collègue.', 'Laisser les candidats s\'exprimer sans les interrompre', 'Rencontrer les candidats sans rien savoir d\'eux'],
        3
    ),
    q(
        19,
        '<p><strong>Avis de Sonia sur www.village.vacances.fr</strong></p>'
        . '<p>C\'était notre première expérience en Villages Vacances. Nous avons découvert un produit complet&nbsp;: hébergement, restauration, activités et services. Des activités sont prévues durant la journée pour les parents et les enfants. Notre petite Mathilde a fait gratuitement de la natation et du cheval&nbsp;!</p>',
        'Que proposent les Villages Vacances aux enfants&nbsp;?',
        15,
        ['De découvrir la région', 'De goûter des spécialités', 'De pratiquer un sport', 'D\'organiser un spectacle'],
        2
    ),
    q(
        20,
        '<p>Le piment d\'Espelette, qui célèbre actuellement sa fête, est probablement aujourd\'hui le produit le plus emblématique du Pays basque. Il a pourtant failli devenir un produit banal, c\'est-à-dire le contraire absolu de ce qu\'il est vraiment. Au début des années 90, on trouvait en effet et on produisait du piment, dit d\'Espelette, en Afrique du Nord, en Corse, en Espagne… bref partout où on aime ce goût puissant et peu piquant qui fait sa légitime réputation. Or, les Basques n\'aiment pas se faire voler leurs traditions. Ils ont donc obtenu en juin 2000 l\'appellation d\'origine contrôlée (AOC) interdisant tout plagiat. Aujourd\'hui, le diamant rouge d\'Espelette est particulièrement apprécié de certains grands chefs qui l\'utilisent pour faire «&nbsp;exploser&nbsp;» les saveurs.</p>',
        'L\'appellation d\'origine contrôlée accordée au piment d\'Espelette permet aux Basques…',
        21,
        ['De lutter contre les imitations.', 'D\'en accroître la production.', 'De développer sa saveur.', 'De le faire davantage connaître.'],
        0
    ),
    q(
        21,
        '<p><strong>Théo, la poupée «&nbsp;inter-affective&nbsp;».</strong></p>'
        . '<p>Théo a deux ans. Il mesure 52&nbsp;cm et communique spontanément avec sa maman.</p>'
        . '<p>Lancé par Berchet, ce robot est bourré d\'électronique et de capteurs pour interagir avec l\'enfant. Ainsi, Théo dort lorsqu\'il fait nuit, se réveille au lever du jour, réclame à manger ou à boire et même à aller au pot. Sa voix numérisée est d\'un naturel confondant. Surtout, il s\'adapte aux capacités de jeux de chaque enfant&nbsp;: plus il manifestera son désir de jouer, plus le jouet sera réactif.</p>',
        'La voix numérisée de Théo est d\'un «&nbsp;naturel confondant&nbsp;», ce qui signifie qu\'elle…',
        21,
        ['A un timbre qui n\'existe pas naturellement', 'Se confond avec la voix de l\'enfant qui joue', 'Enregistre et reproduit les voix entendues', 'Imite à la perfection la voix d\'un enfant.'],
        3
    ),
    q(
        22,
        '<p><strong>Erasmus</strong></p>'
        . '<p>Le programme d\'échange d\'étudiants entre les universités européennes a du plomb dans l\'aile. Au point qu\'en France près de quatre mille bourses n\'ont pas trouvé preneur. Ce phénomène n\'est pas spécifique à la France puisqu\'une baisse de dix pour cent aurait été enregistrée dans une dizaine de pays.</p>'
        . '<p>Toutes les études traduisent une absence de désir de mobilité chez les jeunes. Parmi les explications avancées, le coût, mais surtout une préférence marquée pour les stages en entreprise.</p>'
        . '<p>Voilà le grand concurrent d\'Erasmus depuis quelques années&nbsp;: l\'attention portée à l\'insertion professionnelle.</p>',
        'Qu\'explique le texte au sujet du programme Erasmus&nbsp;?',
        21,
        ['Il est financé par des universités publiques', 'Il est remplacé par un autre programme', 'Il facilite l\'accès au monde de l\'entreprise', 'Son succès auprès des étudiants se dégrade'],
        3
    ),
    q(
        23,
        '<p>Savoir opter pour la bonne route n\'est pas tout. L\'ultime clé pour gagner la course de la Route du Rhum, c\'est la connaissance de soi et tout particulièrement de ses rythmes de sommeil. C\'est un des aspects les plus méconnus de la voile de compétition, mais c\'est sur la faculté de récupération que peut se jouer le podium. Ainsi, Alain Gautier reconnaît avoir perdu la dernière Route du Rhum parce qu\'il n\'a pas su, dès les premiers jours de la course, gérer correctement ses phases de repos. «&nbsp;À la fin, j\'ai manqué de lucidité et j\'ai commis des erreurs&nbsp;», avoue-t-il après sa défaite.</p>',
        'Quel conseil donne le journaliste pour remporter la course&nbsp;?',
        21,
        ['Connaître ses adversaires', 'Cultiver sa concentration', 'Dormir efficacement', 'Ménager ses efforts'],
        2
    ),
    q(
        24,
        '<p>Le château de la Bussière est une demeure du 17<sup>e</sup> siècle située dans le Loiret. Surnommé «&nbsp;le château des pêcheurs&nbsp;», il possède des collections d\'objets rares sur la pêche. Sans oublier un superbe parc. Aujourd\'hui, ce monument est confronté à un fléau&nbsp;: un champignon ronge ses édifices. Les propriétaires ont déjà lancé une campagne de restauration, mais le champignon s\'attaque aussi à d\'autres parties du domaine.</p>'
        . '<p>Les maîtres des lieux souhaitent que le bâtiment retrouve son éclat en sollicitant les internautes. À partir de 15&nbsp;€, les contributeurs pourront recevoir des entrées gratuites, l\'inscription de leur nom sur une plaque dans le château ou bien encore un dîner avec les propriétaires.</p>',
        'D\'après l\'article, à quoi les internautes sont-ils encouragés&nbsp;?',
        21,
        ['À aider à la rénovation en travaillant au château.', 'À entrer en contact avec les occupants du château.', 'À financer les travaux du château par un don.', 'À visiter le château, ses jardins et expositions.'],
        2
    ),
    q(
        25,
        '<p>Sandrine Mercier et Michel Fonivicn ont le voyage dans la peau. Les deux journalistes ont longtemps exploré le monde avant d\'écrire un livre ensemble. <em>Ils sont partis vivre ailleurs</em> est constitué des histoires d\'une trentaine d\'expatriés et d\'autant de destins et de trajectoires.</p>'
        . '<p>Les auteurs sont allés chercher des personnages tous très différents, mais toujours touchants et sincères en racontant leurs parcours lointains&nbsp;: rappel du large, les premiers pas, les déceptions, le rapport à la France, la découverte des autres… c\'est certainement ce qui unit tous ces Français du bout du monde&nbsp;; par-delà leurs différences et leurs expériences&nbsp;: une ouverture d\'esprit, une volonté de partager et au final une richesse et une joie de vivre communicatives.</p>',
        'D\'après l\'auteur de cet article, quelle est la particularité de cet ouvrage&nbsp;?',
        21,
        ['L\'originalité des pays visités.', 'La description des habitudes quotidiennes.', 'La diversité des portraits présentés.', 'Les qualités littéraires des écrivains.'],
        2
    ),
    q(
        26,
        '<p>Longtemps délaissées, les algues semblent peu à peu séduire les consommateurs.</p>'
        . '<p>En raison de leur confusion avec les algues vertes qui polluent le littoral, ces légumes de mer sont parfois mal considérés. Pourtant, si les algues ne font pas encore partie de notre univers culturel alimentaire, leur consommation est en progression. «&nbsp;Les Occidentaux sont-ils prêts à en faire un produit courant&nbsp;?&nbsp;» questionne le docteur Arnaud Cacoul. Pas sûr. «&nbsp;Malgré leurs bienfaits nutritionnels, leur consommation restera sans doute limitée&nbsp;», estime ce nutritionniste.</p>',
        'Quelle place occupent les algues en France&nbsp;?',
        21,
        ['Elles font partie de la gastronomie régionale.', 'Les gens en mangent plus que par le passé.', 'Les médecins conseillent leur usage en cuisine', 'Leur culture augmente rapidement sur les côtes.'],
        1
    ),
    q(
        27,
        '<p>Depuis plusieurs années, Thierry Marc, un grand chef français, intervient pour donner des cours de cuisine en prison. Faire découvrir à ce public, contraint de vivre en milieu fermé, qu\'une profession, quelle qu\'elle soit, peut être épanouissante, est une tâche qui le passionne et nécessite très naturellement une grande énergie. Le désir de rallumer cette lueur d\'espoir, de montrer «&nbsp;qu\'après&nbsp;», il peut y avoir une réinsertion réussie et que chacun la porte en lui, est un moteur pour relever ce défi.</p>',
        'Pourquoi Thierry Marc intervient-il dans les prisons&nbsp;?',
        21,
        ['Pour améliorer les méthodes de travail de la cantine', 'Pour encourager la création de formations professionnelles', 'Pour faire naître l\'envie de s\'investir dans un métier', 'Pour sensibiliser le grand public à la réalité de la vie carcérale'],
        2
    ),
    q(
        28,
        '<p>L\'institution scolaire devra sans cesse être défendue, ne serait-ce que parce que la «&nbsp;société civile&nbsp;» n\'aura de cesse de vouloir assujettir l\'école à ses demandes particulières. Il faut sans cesse rappeler, par exemple, que les règles de l\'école ne sont pas celles de la maison. Car l\'enfant n\'y est plus seulement un enfant, il y est un élève. À l\'école, on ne se préoccupe plus du confort et de l\'affection pour l\'enfant. On s\'adresse à l\'intelligence de l\'élève.</p>'
        . '<p>Le rapport maître/élève n\'est d\'ailleurs pas un rapport affectif&nbsp;: on ne demande pas à un professeur d\'être sympathique. On lui demande d\'être exigeant et juste.</p>',
        'Quelle est l\'opinion de l\'auteur&nbsp;?',
        21,
        ['L\'ambiance de l\'école doit être calquée sur le modèle familial', 'L\'autorité du professeur doit être compensée par sa gentillesse', 'L\'épanouissement de l\'enfant doit primer sur sa réussite', 'L\'impartialité doit être l\'une des qualités principales d\'un maître'],
        3
    ),
    q(
        29,
        '<p>Durant les investigations journalistiques, le recours à la caméra cachée est de plus en plus fréquent. Mais souvent, son utilisation n\'est aussi qu\'un cache-misère, un moyen de compenser l\'absence d\'informations dans les reportages, de remplir le vide par des scènes un peu spectaculaires. C\'est une manière pour certains aussi de faire des économies.</p>'
        . '<p>Par ailleurs, cette pratique pose une autre question&nbsp;: celle du statut de la personne filmée à son insu. La filmer en caméra cachée, n\'est-ce pas supposer qu\'elle a quelque chose à se reprocher avant d\'avoir commencé à parler&nbsp;?</p>',
        'D\'après ce texte, pourquoi les journalistes utilisent-ils des caméras cachées&nbsp;?',
        21,
        ['Pour améliorer la qualité des documentaires.', 'Pour dissimuler la pauvreté des contenus.', 'Pour moderniser leurs méthodes de travail.', 'Pour saper les gens visés par les enquêtes'],
        1
    ),
    q(
        30,
        '<p>Trouver un logement dans une ville étudiante est un parcours du combattant pour un grand nombre d\'inscrits dans l\'enseignement supérieur. La barrière est parfois infranchissable. Non seulement les loyers sont élevés, mais la sélection des candidats par les propriétaires est très dure, souvent basée sur le niveau de revenus des garants. Pour lever ce frein, la caution locative étudiante (CLE) vient d\'être généralisée à l\'ensemble du territoire national après avoir été expérimentée dans quatre régions pendant un an. Avec ce dispositif, c\'est l\'État qui se porte garant du versement des loyers des étudiants en cas de non-paiement.</p>',
        'Quel est le principal obstacle à l\'accès au logement des étudiants selon cette analyse&nbsp;?',
        26,
        ['La méfiance des propriétaires', 'La rareté des offres de logement.', 'L\'absence d\'engagement de l\'État', 'Le montant de la caution exigée'],
        0
    ),
    q(
        31,
        '<p>Après avoir été fermée au public pendant douze ans, la grotte préhistorique d\'Atlantica, située en Espagne, doit rouvrir ses portes, mais de manière expérimentale. Il s\'agit d\'une expérience réservée à un groupe de cinq personnes tirées au hasard parmi les visiteurs du musée du site, qui vont pouvoir contempler les peintures rupestres originales. Cette expérience se déroulera une fois par semaine pendant huit mois afin de permettre à une équipe de scientifiques d\'évaluer l\'impact des visites sur l\'ensemble du site et de donner son avis sur une réouverture complète.</p>',
        'Qu\'évoque-t-on dans cet article&nbsp;?',
        26,
        ['L\'inauguration d\'une exposition temporaire', 'L\'inscription d\'une œuvre au patrimoine mondial', 'L\'ouverture au public d\'une zone dangereuse', 'L\'accès réglementé à un emplacement protégé'],
        3
    ),
    q(
        32,
        '<p>Pas de clash ni de portes qui claquent. À l\'issue de la deuxième conférence sociale, le gouvernement peut au moins se réjouir de ce résultat. Les représentants du patronat et des syndicats n\'ont pas applaudi à tout rompre l\'intervention du premier ministre, mais il n\'y avait pas non plus eu de fâcherie comme celle qui avait marqué la première conférence. Si certains se sont dits déçus par la minceur des annonces que le premier ministre a dévoilées ou par les zones de flou qui règnent sur plusieurs dossiers brûlants — les retraites en tête —, personne n\'a non plus eu vent de colères. Il faut dire que pendant deux jours, ministres et conseillers se sont échinés à repousser les points qui fâchent à plus tard.</p>',
        'Quel a été l\'aboutissement de la conférence&nbsp;?',
        26,
        ['L\'affrontement entre partenaires sociaux', 'L\'avancée majeure en termes de fiscalité', 'La proclamation de réformes importantes', 'L\'absence de prise de décisions effectives'],
        3
    ),
    q(
        33,
        '<p>«&nbsp;Des taxes vertes&nbsp;» ou taxes carbone sont envisagées pour faire face aux financements nécessaires d\'énergies non polluantes. Les mécanismes prévus dans le protocole de Kyoto concernent notamment un crédit carbone qui représente un volume d\'émission de gaz à effet de serre (GES) évité et des permis d\'émission négociables. Le crédit carbone est doté d\'une valeur marchande et s\'échange entre pays industrialisés. Un pays n\'arrivant pas à atteindre son objectif de réduction des GES pourrait acheter des crédits carbone à un autre qui aurait dépassé son objectif. Ce système a été parfois qualifié de permis à polluer, car un pays riche pourrait acheter le droit de polluer à un autre ayant réellement réduit ses émissions.</p>',
        'Quel est le paradoxe de la taxe carbone&nbsp;?',
        26,
        ['Elle concerne uniquement les économies fortes', 'Elle freine l\'utilisation de ressources renouvelables', 'Elle permet le dépassement des normes édictées', 'Elle ralentit le progrès dans les états émergents'],
        2
    ),
    q(
        34,
        '<p>Quel visage Paris pourrait-il prendre en 2100&nbsp;? C\'est ce que le collectif d\'architectes «&nbsp;Et alors&nbsp;» a imaginé au travers de vingt cartes postales géantes, exposées à Paris. Ce qui donne un résultat surprenant, parfois utopique, parfois réaliste.</p>'
        . '<p>Ainsi l\'idée d\'une centrale hydrothermique pour chauffer et refroidir tout un quartier, projet de la compagnie parisienne du chauffage urbain. Des potagers «&nbsp;partagés&nbsp;» au pied des immeubles en plein centre-ville, cela ressemble plus à un clin d\'œil malicieux… Le vélo a également la part belle&nbsp;:</p>'
        . '<p>Les architectes imaginent des voies rapides sur les toits de Paris, au milieu de toitures végétalisées et de jardins.</p>',
        'Quel projet est présenté comme réaliste&nbsp;?',
        26,
        ['L\'extension d\'un système de climatisation à tout un quartier', 'L\'installation des parcs sur les toits des bâtiments parisiens', 'La construction de pistes cyclables au sommet des immeubles', 'La création de jardins communs pour planter des légumes'],
        0
    ),
    q(
        35,
        '<p>Il faut se rendre à l\'évidence&nbsp;: le «&nbsp;jour&nbsp;» n\'est plus l\'unité de temps d\'un journal. Un quotidien papier ne peut pas rivaliser avec la vitesse à laquelle les sites, les blogs, les réseaux sociaux, les journaux en ligne, les radios et télés, les «&nbsp;news&nbsp;» des grands serveurs diffusent les nouvelles du jour. Aussi, au moment où il est en kiosques, se condamne-t-il à apparaître comme «&nbsp;le journal de la veille&nbsp;», perdant progressivement ses lecteurs qui, bombardés de scoops tous azimuts, sont déçus de n\'y trouver que ce qu\'ils savent déjà.</p>',
        'Quelle est la position de l\'auteur de l\'article&nbsp;?',
        26,
        ['Il conteste la fiabilité des sources d\'internet.', 'Il critique la surabondance de l\'information.', 'Il dénonce la médiocrité de la presse écrite.', 'Il doute de l\'intérêt des médias traditionnels.'],
        3
    ),
    q(
        36,
        '<p>Un vingt-sixième album de Françoise Hardy. <em>La pluie sans parapluie</em> est forcément un événement. On sait que le public qui achète encore des disques passe volontairement à la caisse, pour montrer sa fidélité, ou simplement son envie de vibrer avec cette voix unique et si familière avec laquelle elle semble d\'ailleurs plus en confiance que jamais. Elle module, appuie, interprète, joue avec souplesse de son phrasé, alors que les aficionados se contenteraient juste de ce grain unique et révéré. C\'est un album pour ceux qui ont le temps, qui ne consomment pas la musique en tant qu\'application sonore de la vie actuelle. Il faut se retrancher pour en saisir la saveur et la goûter. Un disque comme autrefois, avec un son soigné et de l\'émotion. Mais surtout une élégance comme on n\'en fait plus&nbsp;!</p>',
        'Selon le journaliste, quel type de public pourrait s\'intéresser au dernier album de F. Hardy&nbsp;?',
        33,
        ['Les acheteurs opposés au téléchargement illégal', 'Les amateurs de subtilité et de raffinement musical', 'Les admirateurs inconditionnels de l\'interprète', 'Les curieux en mal de musique expérimentale'],
        1
    ),
    q(
        37,
        '<p>Dans leur nouvelle collection, les éditions Nil invitent des auteurs à se livrer à un exercice épistolaire intime en écrivant une lettre pour dire l\'indicible. Le modèle est celle que Kafka rédigea à l\'intention de son père et qu\'il préféra ranger dans un tiroir tant l\'accusation portée était peu amène. Avec <em>L\'autre fille</em>, missive adressée à sa sœur aînée, morte avant sa naissance, Annie Ernaux inaugure brillamment le concept, fidèle à son écriture tranchante et analytique. Rejetant la théorie de l\'autofiction, l\'écrivain se dit fascinée par la réalité, partant à la recherche d\'une vérité sur une mort que ses parents lui ont toujours cachée. En demandant à des auteurs de se libérer d\'un vieux tourment, la collection souhaite réhabiliter un genre littéraire oublié.</p>',
        'À quel résultat la participation à cette collection conduit-elle Annie Ernaux&nbsp;?',
        33,
        ['À s\'acquitter d\'un engagement moral', 'À s\'affranchir d\'une histoire ancienne', 'À se défaire d\'un sentiment de culpabilité', 'À se justifier d\'une action peu glorieuse'],
        1
    ),
    q(
        38,
        '<p>Les méfiances à l\'égard de la gratuité des transports collectifs demeurent fortes. Sans surprise, l\'Union des transports publics affiche son hostilité. Trop onéreuse, ne facilitant pas le report de la voiture vers les transports collectifs, menaçant la qualité de service, la gratuité pour tous ne répondrait pas aux objectifs de développement d\'un réseau de transport.</p>'
        . '<p>Selon l\'UTP, la gratuité est une «&nbsp;fausse bonne idée&nbsp;» qui «&nbsp;induit des déplacements inutiles, encourage l\'étalement urbain et prive de ressources le système de transport au moment où la clientèle augmente et où les recettes fiscales des collectivités diminuent&nbsp;». Elle lui préfère le système de tarification spéciale pour les jeunes, les sans-emplois ou les familles nombreuses.</p>',
        'Pourquoi la gratuité des transports publics est-elle critiquée&nbsp;?',
        33,
        ['Ce choix creuse les inégalités sociales', 'Ce projet omet des questions de sécurité', 'Cette démarche déresponsabilise les usagers', 'Cette initiative coûte beaucoup trop cher'],
        3
    ),
    q(
        39,
        '<p>Depuis l\'adolescence, je fréquente assidûment les dictionnaires.</p>'
        . '<p>Chaque fois que je bute sur un mot, que je suis dans le flou culturel, ils m\'apportent la réponse et ouvrent de nouveaux horizons à ma curiosité en me renvoyant souvent à d\'autres ouvrages. Je possède une centaine et chacun est une bibliothèque à lui seul. Tous sont autant de béquilles de ma culture&nbsp;: par exemple, quand vous cherchez le mot juste, rien de tel qu\'un dictionnaire des synonymes&nbsp;! On ouvre un dictionnaire pour se renseigner sur une question précise comme on brasse la notice d\'un produit pharmaceutique destiné à soigner ceci ou cela et de self-service de la pensée.</p>',
        'Pourquoi Jean-Claude aime-t-il les dictionnaires&nbsp;?',
        33,
        ['Pour découvrir de nouvelles références', 'Pour posséder une bibliothèque fournie', 'Pour s\'évader vers des horizons lointains', 'Pour trouver des remèdes à ses maux'],
        0
    ),
];

$out = [
    'title' => 'Compréhension Écrite 2',
    'subtitle' => 'Épreuve de compréhension écrite — 39 questions',
    'duration_seconds' => 3600,
    'visibility' => 'gratuit',
    'questions' => $questions,
];

$path = dirname(__DIR__) . '/database/seeds/ce_exam_2.json';
$json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
if (file_put_contents($path, $json . "\n") === false) {
    fwrite(STDERR, "Cannot write $path\n");
    exit(1);
}

echo 'OK — ' . count($questions) . " questions → $path\n";
