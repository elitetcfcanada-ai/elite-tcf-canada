<?php
declare(strict_types=1);

/**
 * Génère database/seeds/ce_exam_3.json — Compréhension écrite n°3
 * Usage : php scripts/build_ce_exam_3_json.php
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
        '<p>Chers Amis,</p>'
        . '<p>Nous sommes heureux de vous annoncer l\'arrivée d\'un nouveau membre dans notre famille. Il se nomme Adrien et a vu le jour le 1<sup>er</sup> septembre au matin.</p>'
        . '<p>Bien à vous.</p>',
        'De quel évènement s\'agit-il&nbsp;?',
        3,
        ['D\'un anniversaire', 'D\'un décès', 'D\'un mariage', 'D\'une naissance'],
        3
    ),
    q(
        2,
        '<p>Désolée, je ne viens pas aux cours de français et je ne peux pas travailler à la bibliothèque avec toi aujourd\'hui. Je suis malade et je reste à la maison.</p>'
        . '<p>À bientôt,</p>'
        . '<p><strong>Sandra</strong></p>',
        'Qu\'est-ce que Sandra fait&nbsp;?',
        3,
        ['Elle étudie son français', 'Elle invite son ami Rémi', 'Elle reste chez elle', 'Elle va à la bibliothèque'],
        2
    ),
    q(
        3,
        '<p>J\'ai une réunion jusqu\'à 19&nbsp;h&nbsp;30. Tu peux réserver nos places au cinéma pour un film après 20&nbsp;h&nbsp;?</p>'
        . '<p>Merci</p>'
        . '<p><strong>Lucie</strong></p>',
        'Que veut faire Lucie après 19&nbsp;h&nbsp;30&nbsp;?',
        3,
        ['Aller voir un film', 'Dîner avec Fred', 'Passer chez un ami', 'Rester au travail'],
        0
    ),
    q(
        4,
        '<p><strong>SAMEDI</strong></p>'
        . '<ul>'
        . '<li><strong>Marthe</strong>&nbsp;: 12&nbsp;h—13&nbsp;h&nbsp;: Cours de natation</li>'
        . '<li><strong>Djamel</strong>&nbsp;: 11&nbsp;h—13&nbsp;h&nbsp;: Révision avec Claire</li>'
        . '</ul>'
        . '<p><strong>DIMANCHE</strong></p>'
        . '<ul>'
        . '<li><strong>Marthe</strong>&nbsp;: 8&nbsp;h—9&nbsp;h&nbsp;: Course avec Claire</li>'
        . '<li><strong>Djamel</strong>&nbsp;: 14&nbsp;h—18&nbsp;h&nbsp;: Répétition</li>'
        . '</ul>',
        'Que fait Marthe le samedi à 12 heures&nbsp;?',
        3,
        ['Elle chante', 'Elle court', 'Elle étudie', 'Elle nage'],
        3
    ),
    q(
        5,
        '<p>Une société de production <strong>RECHERCHE</strong> pour faire de la <strong>FIGURATION</strong> dans un film sur le thème parents/enfants, de jeunes mères confrontées à l\'éducation de leurs enfants.</p>',
        'Que recherche la société de production&nbsp;?',
        9,
        ['De jeunes enfants', 'Des femmes et des enfants', 'De jeunes mamans', 'Des parents avec leurs enfants'],
        2
    ),
    q(
        6,
        '<p><strong>À Héloïse</strong></p>'
        . '<p>Héloïse, quand tu viendras chez moi, pense à prendre des vêtements chauds. À la montagne, en hiver, il fait très froid&nbsp;! Je t\'ai envoyé les horaires du bus par SMS. Appelle-moi ce soir quand tu rentres du travail. Vivement les vacances&nbsp;!</p>'
        . '<p>Bises</p>'
        . '<p><strong>Sam</strong></p>',
        'Que doit faire Héloïse&nbsp;?',
        9,
        ['Bien choisir ses vêtements', 'Chercher les horaires du bus', 'Envoyer un courriel à Sam', 'Trouver une location de vacances'],
        0
    ),
    q(
        7,
        '<p>Salut Samira,</p>'
        . '<p>La fête du cinéma commence samedi prochain, ça t\'intéresse&nbsp;? Viens chez moi après le travail vendredi, on choisira ce qu\'on ira voir samedi ou dimanche.</p>'
        . '<p>Réponds-moi vite&nbsp;!</p>'
        . '<p><strong>Lali</strong></p>',
        'Que veut faire Lali&nbsp;?',
        9,
        ['Aller à un anniversaire', 'Dîner avec son amie', 'Partir en week-end', 'Regarder des films'],
        3
    ),
    q(
        8,
        '<p><strong>ÉCOLE FERRANDI</strong></p>'
        . '<p>Les parents peuvent venir chercher leurs enfants après les cours du matin et du soir sauf si les enfants mangent à l\'école ou s\'ils font des activités après la classe. Tous les enfants doivent avoir quitté l\'école à 18&nbsp;h.</p>'
        . '<p><strong>P. Guignoux</strong></p>',
        'De quoi parle le directeur&nbsp;?',
        9,
        ['De l\'inscription des élèves', 'Des horaires de sortie', 'Des repas à la cantine', 'Du temps de repos'],
        1
    ),
    q(
        9,
        '<p>Que puis-je dire de ce documentaire, moi qui pleure depuis tant d\'années sur le manque de saveur de ce que je mange&nbsp;? Je me rappelle du bon goût des pêches et des abricots&nbsp;! Je comprends mieux la majorité des agriculteurs&nbsp;: ils ne mangeraient pas ce qu\'ils vendent, c\'est fou&nbsp;! J\'ai apprécié d\'avoir le point de vue des différentes personnes concernées&nbsp;: agriculteurs, consommateurs, enseignants, cuisiniers.</p>',
        'Quel est le thème du documentaire&nbsp;?',
        9,
        ['L\'alimentation', 'L\'éducation', 'La cuisine', 'Le commerce'],
        0
    ),
    q(
        10,
        '<p>Vous mangez italien, dansez le flamenco ou apprenez le suédois&nbsp;?</p>'
        . '<p>Partagez vos prises de vue (portraits, paysages, etc.) qui montrent que l\'Europe fait partie de votre vie.</p>'
        . '<p>Participez au concours «&nbsp;Europe en images&nbsp;».</p>'
        . '<p><strong>À GAGNER</strong>&nbsp;: un séjour à Bruxelles ou un atelier de cuisine.</p>',
        'Que propose ce concours&nbsp;?',
        9,
        ['D\'envoyer des photos', 'D\'inventer une recette.', 'De créer un spectacle.', 'De découvrir une langue'],
        0
    ),
    q(
        11,
        '<p>L\'école Jean Monnet organise sa «&nbsp;Journée Portes Ouvertes&nbsp;» samedi prochain, de 9&nbsp;h&nbsp;00 à 16&nbsp;h&nbsp;00. Tous les parents d\'élèves sont invités. Ce sera l\'occasion de rencontrer les professeurs et l\'équipe administrative qui se tiennent prêts à répondre à vos questions.</p>'
        . '<p><strong>La direction de l\'école J. Monnet</strong></p>',
        'Que pourront faire les parents pendant cette journée&nbsp;?',
        15,
        ['Dialoguer avec le personnel enseignant.', 'Écouter une présentation du directeur.', 'Participer à une conférence sur l\'éducation.', 'Répondre à un questionnaire sur les cours.'],
        0
    ),
    q(
        12,
        '<p>On savait déjà que le chocolat était bon pour la tension. Les scientifiques affirment maintenant «&nbsp;qu\'une consommation de chocolat pourrait être une stratégie efficace de protection contre les rayons du soleil&nbsp;». Pour cela, il n\'est pas nécessaire de recouvrir son corps de cacao&nbsp;: d\'après les chercheurs, il suffirait de consommer trois carrés par jour pour bénéficier des bienfaits du chocolat. Cela n\'empêche pas de se protéger avec de la crème solaire et d\'avoir une bonne alimentation.</p>',
        'Que conseillent ces chercheurs pour se protéger du soleil&nbsp;?',
        15,
        ['D\'utiliser des crèmes solaires à base de chocolat', 'De diminuer sa consommation de chocolat', 'De manger quotidiennement du chocolat', 'De suivre un traitement de pilules au chocolat'],
        2
    ),
    q(
        13,
        '<p>Adoptées séparément à la naissance, des jumelles indonésiennes se sont retrouvées 29 ans plus tard en Suède. Elles habitaient à 40&nbsp;km l\'une de l\'autre mais ne le savaient pas. Leurs parents adoptifs avaient été informés de leur lien de parenté mais avaient arrêté leurs recherches. C\'est un message posté sur Facebook par l\'une des jumelles qui a permis leur rencontre. Elles sont l\'une et l\'autre enseignantes&nbsp;!</p>',
        'Qu\'est-ce que ces jumelles ont en commun&nbsp;?',
        15,
        ['Elles adorent utiliser Facebook', 'Elles enseignent dans la même école', 'Elles habitent dans le même pays', 'Elles ont la même famille adoptive'],
        2
    ),
    q(
        14,
        '<p>«&nbsp;Avec un Master Commerce en poche, je ne pensais pas qu\'il serait à ce point difficile de trouver mon premier emploi. Et pourtant… quel chemin semé d\'embûches&nbsp;! Tout le monde me disait «&nbsp;Ne t\'inquiète pas, dans deux ou trois mois tu auras trouvé&nbsp;!&nbsp;» Sept mois plus tard, toujours rien en vue. À 26 ans, je me retrouve sans emploi, toujours chez mes parents. L\'avenir&nbsp;? Je le vois ailleurs, je prépare activement mon départ pour l\'Angleterre.&nbsp;»</p>'
        . '<p>Amandine n\'est pas une exception chez les jeunes. Les diplômes ne suffisent plus à assurer une situation stable en France et beaucoup de jeunes décident de partir à l\'étranger pour obtenir un emploi.</p>',
        'De quoi est-il question dans cet article&nbsp;?',
        15,
        ['La difficulté de trouver un stage à l\'étranger', 'La nécessité de s\'exiler pour pouvoir travailler', 'Le manque de formation des jeunes diplômés', 'Le niveau trop faible des salaires proposés'],
        1
    ),
    q(
        15,
        '<p><strong>Pour ou contre les devoirs&nbsp;?</strong></p>'
        . '<p>Une école donne sa réponse en accueillant chaque lundi, de 17 à 18 heures, les élèves qui peuvent revenir en classe faire leurs devoirs avec leur enseignant… et leurs parents. Ainsi, un dialogue s\'instaure entre enseignants, parents et élèves. Le directeur relève un autre avantage&nbsp;: les devoirs sont bien faits. Mais les devoirs alourdissent des journées déjà longues. Avec six heures de cours, quatre jours par semaine, l\'écolier français décroche la première place sur le podium de la fatigue.</p>',
        'Que propose cette école&nbsp;?',
        15,
        ['D\'alléger les emplois du temps le lundi pour les professeurs', 'De permettre aux enfants de faire leur travail du soir en classe', 'De supprimer totalement le travail à la maison pour les élèves', 'D\'inviter les parents à des visites régulières de l\'établissement'],
        1
    ),
    q(
        16,
        '<p>Pour permettre à tous un accès égal au sport, la maison départementale des personnes handicapées offre d\'accompagner ceux qui sont en situation de handicap dans leurs projets de loisirs sportifs. Ce service propose une aide dans le choix d\'une activité, la recherche de clubs capables de les accueillir et si besoin, un accompagnement dans le premier contact avec l\'association. Il s\'agit de partir des besoins, des envies et des possibilités de la personne, pour construire avec elle son projet sportif adapté.</p>',
        'Que propose la maison départementale&nbsp;?',
        15,
        ['De faciliter les rencontres avec des sportifs handicapés', 'De trouver des entraîneurs spécialisés en handisport', 'D\'encourager les personnes handicapées à faire du sport', 'D\'organiser les matchs avec des sportifs handicapés'],
        2
    ),
    q(
        17,
        '<p><strong>COMMENT APPRENDRE UNE LANGUE ÉTRANGÈRE</strong></p>'
        . '<p>Existe-t-il un âge critique au-delà duquel on ne peut plus jamais atteindre le niveau d\'un locuteur natif&nbsp;? Ce sujet fait l\'objet de nombreux débats. Plus on est jeune, plus on a d\'appétit pour découvrir le monde. L\'apprentissage d\'une langue, quelle qu\'elle soit, est donc plus simple, mais il y a également une composante sociale. On se comporte avec l\'enfant d\'une manière bien particulière, que l\'on ne peut reproduire avec un adolescent ou un adulte.</p>',
        'D\'après cet extrait, qu\'est-ce qui favorise chez les enfants l\'apprentissage d\'une langue étrangère&nbsp;?',
        15,
        ['Leur curiosité', 'Leur imagination', 'Leur mémoire', 'Leur obéissance'],
        0
    ),
    q(
        18,
        '<p>La question de l\'orientation est fondamentale et se pose inévitablement au moment de commencer des études universitaires. Comment faire le bon choix&nbsp;? Sur quelles bases&nbsp;? Quel parcours sera le mieux adapté à la personnalité scolaire et psychologique, aux capacités de l\'étudiant&nbsp;? Un livre, édité par le magazine <em>l\'Étudiant</em>, propose une démarche progressive qui aide à définir un projet en fonction des goûts et d\'un objectif professionnel.</p>',
        'Que peut-on trouver dans ce livre&nbsp;?',
        15,
        ['Des idées de stages en entreprise.', 'Un guide des démarches d\'inscription', 'Une méthode pour choisir une formation', 'Une sélection des meilleures universités'],
        2
    ),
    q(
        19,
        '<p>Madame Mansion,</p>'
        . '<p>Suite à notre conversation téléphonique pour le travail de secrétaire, nous aimerions vous rencontrer jeudi prochain à 10&nbsp;h&nbsp;00 dans nos bureaux. Il faudra apporter une photocopie de votre carte d\'identité et de vos diplômes. Merci de nous rappeler pour confirmer.</p>'
        . '<p>Cordialement,</p>'
        . '<p><strong>Mathieu Leroux</strong><br>Directeur du service des ressources humaines</p>',
        'Que doit faire Mme Mansion&nbsp;?',
        15,
        ['Annuler un rendez-vous', 'Chercher des documents', 'Envoyer un curriculum vitae', 'Téléphoner à l\'entreprise'],
        3
    ),
    q(
        20,
        '<p>Chers Valérie et Serge,</p>'
        . '<p>Que vos projets immobiliers voient enfin le jour avec cette année qui débute&nbsp;! Qu\'elle vous apporte la joie de déménager à Nancy comme vous le souhaitez, mais aussi, à tous les niveaux, réussite et beaucoup de bonheur&nbsp;!</p>'
        . '<p>J\'espère que nous aurons le temps de nous voir plus souvent et de partir à nouveau en vacances ensemble&nbsp;: notre séjour en Corse reste un merveilleux souvenir pour moi.</p>'
        . '<p>Je vous embrasse,</p>'
        . '<p><strong>Elisa</strong></p>',
        'Quel est le but de cette lettre&nbsp;?',
        21,
        ['Annoncer l\'achat d\'un appartement', 'Donner des nouvelles à des amis', 'Envoyer des vœux de nouvel an', 'Organiser un voyage en Corse'],
        2
    ),
    q(
        21,
        '<p>Les bons vieux bancs verts sur les trottoirs parisiens ont de la concurrence. Depuis le mois de décembre, sur certains boulevards, un nouveau mobilier urbain a fait son apparition, avec 12 modèles de banquettes, chaises et tabourets. Pour la première fois, la ville de Paris recueille l\'avis des usagers et des riverains. Le traditionnel banc en fonte et bois a-t-il encore de l\'avenir au milieu de ce mobilier futuriste&nbsp;?</p>',
        'Quelle est l\'originalité de ce projet&nbsp;?',
        21,
        ['La priorité donnée à l\'aspect écologique.', 'La prise en compte de l\'opinion publique.', 'Le caractère provisoire de l\'installation', 'Le choix des matériaux de fabrication'],
        1
    ),
    q(
        22,
        '<p>Le smartphone participe à l\'entretien d\'un lien pathologique de plus en plus répandu, le désir de mainmise des parents sur les adolescents. La crise d\'adolescence confronte les parents au désir d\'autonomie de l\'adolescent et les oblige à faire le deuil de leur position de parent. Cela suppose qu\'ils lâchent prise et fassent confiance à leur enfant. Mais de plus en plus de parents souhaitent que leur ado reste à la maison. Le smartphone, qui permet de rester en lien avec ses copains, arrange donc les parents.</p>',
        'Quel est le constat fait par l\'auteur à propos des smartphones&nbsp;?',
        21,
        ['Ils éloignent les jeunes du monde des adultes.', 'Ils entraînent des comportements de type addictif.', 'Ils ont tendance à couper les adolescents du réel', 'Ils peuvent être utilisés comme outils de contrôle'],
        3
    ),
    q(
        23,
        '<p><strong>Les Arts Premiers au Musée</strong></p>'
        . '<p>Si cette reconnaissance sonne comme un cri de victoire, c\'est qu\'elle marque l\'issue d\'un combat idéaliste qui n\'a fait d\'autres victimes que l\'arrogance et les préjugés. Le signal en fut donné par Guillaume Apollinaire en 1909&nbsp;: «&nbsp;le Louvre devrait recueillir certains chefs-d\'œuvre exotiques dont l\'aspect n\'est pas moins émouvant que celui des beaux spécimens de la statuaire occidentale.&nbsp;» Près d\'un siècle plus tard, le vœu du poète est enfin comblé&nbsp;: plus d\'une centaine de sculptures primitives d\'Afrique, d\'Asie, d\'Océanie et des Amériques, jusqu\'alors confinées dans des réserves ethnographiques, prennent place dans le temps de l\'Art, jusqu\'alors réservé à l\'Europe et à ses sources.</p>',
        'Cet événement est présenté comme…',
        21,
        [
            'Le résultat d\'une lutte menée par des gens orgueilleux et intolérants.',
            'Le triomphe d\'une opinion émise par Apollinaire mais autrefois combattue.',
            'Un miracle dû à la persévérance des archéologues sur plusieurs continents.',
            'La consécration des œuvres européennes jusqu\'alors remisées dans les musées.',
        ],
        1
    ),
    q(
        24,
        '<p>Quelles sont les forces dont disposait l\'Homme pour conquérir l\'hégémonie sur la planète&nbsp;?</p>'
        . '<p>L\'Homme est dépourvu de moyens physiques, il n\'a ni crocs, ni griffes, ni armure&nbsp;; il est chétif, fragile et vulnérable. Mais, d\'une part, il prime tous ses autres compagnons de vie par la puissance de son cerveau&nbsp;; d\'autre part, il est attiré par ses semblables, il tend à faire groupe avec les autres individus de son espèce, et ce sont ces tendances sociales qui, multipliant l\'Homme par lui-même, lui ont donné le moyen d\'atteindre à de si prodigieux résultats dans le domaine du savoir comme dans celui du pouvoir.</p>',
        'Qu\'est-ce qui a permis à l\'espèce humaine de conquérir le monde, à part son intelligence&nbsp;?',
        21,
        ['La fréquentation de ses congénères.', 'L\'amélioration de son habitat naturel.', 'Les mutations de son organisme.', 'L\'extermination des autres espèces.'],
        0
    ),
    q(
        25,
        '<p>Le témoignage de Maud est très représentatif de la vie étudiante actuelle&nbsp;:</p>'
        . '<p>«&nbsp;J\'ai cherché un logement près de la fac. J\'ai trouvé un studio à 600&nbsp;$ par mois. Mon père me donne 500 par mois. Je dois travailler comme surveillante dans un lycée. Les fins de mois sont difficiles, mais mon dossier d\'allocation logement vient d\'être accepté. Finalement&nbsp;? J\'ai de la chance&nbsp;!&nbsp;»</p>',
        'Pourquoi Maud pense-t-elle avoir de la chance&nbsp;?',
        21,
        ['Elle aura un meilleur pouvoir d\'achat sous peu.', 'Elle se débrouille sans l\'aide de personne.', 'Elle trouvera facilement un emploi après l\'université.', 'Elle va habiter près de chez ses parents.'],
        0
    ),
    q(
        26,
        '<p>Le rapport de l\'Agence internationale de l\'énergie se concentre sur les données de la dernière conférence climatique. Il contient une information intéressante&nbsp;: d\'après l\'agence, les réserves mondiales de gaz sont plus importantes que prévues. Le marché mondial serait même sous la menace d\'un excédent massif, en raison de l\'essor de la production américaine et d\'une chute de la demande liée à la crise.</p>',
        'Qu\'a révélé le rapport de l\'agence internationale de l\'énergie&nbsp;?',
        21,
        [
            'La consommation de gaz dans le monde augmente.',
            'La demande croissante en gaz a des effets sur le climat.',
            'Les besoins en gaz des Américains sont supérieurs à l\'offre.',
            'Les ressources en gaz à l\'échelle mondiale s\'accroissent.',
        ],
        3
    ),
    q(
        27,
        '<p>Une étude récente révèle que la concentration de déchets plastiques flottant à la surface du Pacifique nord a été multipliée par cent en quarante ans. Ce constat, émis par les pêcheurs en haute mer, est alarmant car cette pollution a des conséquences écologiques. La gigantesque plaque de déchets flottant sur l\'océan Pacifique, épaisse par endroits de plusieurs dizaines de mètres, constitue un milieu favorable à la reproduction d\'une espèce d\'araignée d\'eau. Cet insecte est en train de se multiplier dans le Pacifique nord. Si la densité des plastiques continue à augmenter, certaines espèces pourraient continuer à se multiplier, risquant de déséquilibrer l\'écosystème du Pacifique.</p>',
        'Quel danger représentent ces déchets&nbsp;?',
        21,
        [
            'Un obstacle pour les bateaux de pêche.',
            'Un réchauffement dramatique des eaux.',
            'Un risque d\'intoxication des populations du Pacifique.',
            'Une diminution de la diversité de la faune océanique.',
        ],
        3
    ),
    q(
        28,
        '<p>Avez-vous déjà observé des familles en train de remplir des sachets de bonbons sur un stand de confiserie&nbsp;? Un chercheur américain a montré qu\'elles en achètent plus quand les bonbons sont de couleurs différentes. De nombreuses observations similaires prouvent que les entreprises agroalimentaires connaissent nos comportements et la manière dont nous prenons certaines de nos décisions. Par exemple, elles exploitent notre préférence innée pour le sucre, à tous les âges. Elles savent que l\'éducation nutritionnelle des enfants et des jeunes est défaillante dans certaines familles et en profitent.</p>',
        'Que dénonce l\'auteur de cet article&nbsp;?',
        21,
        [
            'La manipulation des clients par les industriels.',
            'L\'attitude imprévisible des consommateurs.',
            'L\'uniformisation des saveurs et des goûts',
            'L\'utilisation abusive des produits de synthèse.',
        ],
        0
    ),
    q(
        29,
        '<p>L\'étendue et la variété des invités reçus par le Parlement Européen ne cessent d\'étonner. Prix Nobel, stars de série télé, grand maître des échecs… Cette diversité n\'a d\'égale que l\'éclectisme des sujets préparés et débattus au sein de l\'hémicycle&nbsp;: la situation dans des pays en guerre, les relations entre pays, les changements climatiques… Bien évidemment, ces personnalités connues et reconnues ne sont pas choisies pour les paillettes et le strass qu\'elles peuvent, parfois, véhiculer, mais parce qu\'elles ont une expertise reconnue ou un message à délivrer.</p>',
        'À quoi réfère le terme «&nbsp;diversité&nbsp;» dans le texte&nbsp;?',
        21,
        ['Aux hôtes du parlement.', 'Aux idées débattues.', 'Aux pays représentés.', 'Aux projets de loi votés.'],
        0
    ),
    q(
        30,
        '<p>Parcourir les routes d\'Europe l\'été, beaucoup de touristes le font sans forcément connaître les règles en vigueur dans chacun des pays. Ce qu\'il faut savoir, c\'est qu\'il existe des différences qui peuvent être notables selon les législations. Alors que la vitesse maximale sur autoroute est de 130&nbsp;km/h en France, elle n\'est que de 120&nbsp;km/h en Belgique, en Espagne ou au Portugal. Quant au taux d\'alcoolémie toléré, il varie de 0,8&nbsp;g/l en Irlande à zéro en République tchèque.</p>',
        'Que fait l\'auteur de cet article&nbsp;?',
        26,
        ['Il commente une loi.', 'Il donne un conseil.', 'Il formule une plainte.', 'Il raconte une anecdote.'],
        1
    ),
    q(
        31,
        '<p>On s\'est habitué depuis belle lurette à ne plus rencontrer de poinçonneurs dans le métro ni de pompistes dans les stations-service. Mais la liste des professions en voie de disparition ne cesse de s\'allonger. Caissières dans les supermarchés, guichetiers dans les banques, hôtesses dans les cinémas… Tous remplacés par des machines&nbsp;? Pas si simple. Comme l\'analyse Thibaut Carpentier, directeur du cabinet de conseil Obstand, la tentation industrielle de réduire les frais de personnel est grande mais cette logique se heurte à celle des consommateurs qui ont du mal à se passer d\'une personne humaine. D\'ailleurs, toutes les entreprises concernées l\'ont bien compris&nbsp;: pas de suppression de postes mais des redéploiements avec élargissement des compétences.</p>',
        'Quel obstacle rencontre l\'automatisation des services&nbsp;?',
        26,
        [
            'Les coûts élevés des investissements.',
            'Les faibles qualifications des employés.',
            'Les réticences émises par la clientèle.',
            'Les spécificités de certains métiers.',
        ],
        2
    ),
];

$out = [
    'title' => 'Compréhension Écrite 3',
    'subtitle' => 'Épreuve de compréhension écrite — 31 questions',
    'duration_seconds' => 3600,
    'visibility' => 'gratuit',
    'questions' => $questions,
];

$path = dirname(__DIR__) . '/database/seeds/ce_exam_3.json';
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
