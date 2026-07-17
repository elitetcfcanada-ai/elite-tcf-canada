<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 1,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez invité votre ami Cédric à votre mariage au Château de Chombony et il vous a répondu qu\'il ne connaît pas ce château. Décrivez à votre ami (lieu, localisation, transports, etc.).',
                'correction' => "Salut Cédric,\n\nJe comprends que tu ne connaisses pas le Château de Chombony. Il se situe à environ 30 minutes du centre-ville, dans un cadre magnifique entouré de verdure et de jardins. C'est un lieu très élégant, parfait pour célébrer un mariage. Le château se trouve près de l'autoroute principale et il est facilement accessible en voiture. Il y a un grand parking gratuit pour les invités. Si tu préfères les transports en commun, une gare se trouve à proximité et nous organiserons des navettes jusqu'au château. Tu verras, l'endroit est vraiment charmant et idéal pour une belle fête.\n\nA bientôt",
            ],
            [
                'task' => 2,
                'prompt' => 'Dans votre blog, racontez votre expérience de l\'apprentissage d\'une langue étrangère (vous écrivez sur un forum internet en racontant votre expérience en apprenant une langue étrangère).',
                'correction' => "Chers lecteurs,\nApprendre une langue étrangère a été pour moi un véritable défi, mais aussi une aventure passionnante. Au début, j'étais motivé(e), mais je me sentais un peu perdu(e) face à la grammaire et à la prononciation. J'avais surtout peur de faire des erreurs en parlant. Pour progresser, j'ai adopté une méthode simple : pratiquer chaque jour. J'ai commencé par regarder des vidéos et écouter des podcasts adaptés à mon niveau. Ensuite, j'ai rejoint un groupe de conversation en ligne. Même si c'était difficile au début, cela m'a permis de gagner en confiance. Avec le temps, j'ai constaté des progrès importants. Aujourd'hui, je comprends mieux et je peux m'exprimer plus facilement.\nJe recommande vivement de rester régulier, de ne pas avoir peur des erreurs et de pratiquer le plus possible avec des locuteurs natifs. La clé du succès est la persévérance.",
            ],
            [
                'task' => 3,
                'prompt' => 'Cuisinier Amateur Ou Cuisinier Professionnel ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les amateurs ont fait des recettes réussies, mais ils manquent toujours de compétences et de technique, c\'est pourquoi la formation et l\'expérience sont nécessaires pour être un vrai cuisinier.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Il parle des cuisiniers qui ont appris le métier de cuisinier sur internet et qui ont le buzz sur les réseaux sociaux. il raconte aussi l\'histoire d\'une amatrice qui est devenue professionnelle et qui a rédigé plusieurs livres sur la cuisine pour les amateurs de cuisine à la maison.',
                    ],
                ],
                'correction' => "Cuisinier amateur ou cuisinier professionnel : quelle est la différence ?\n\nLa cuisine attire aujourd'hui de nombreuses personnes, qu'elles soient amateurs ou professionnelles. Selon le document 1, même si les amateurs peuvent réussir certaines recettes, ils manquent souvent de compétences techniques et d'une formation approfondie. Le document 2 montre toutefois que certains passionnés, formés grâce à Internet, ont réussi à devenir populaires et même professionnels.\nÀ mon avis, la passion est un élément fondamental, mais elle ne suffit pas toujours pour garantir une carrière stable. Un cuisinier professionnel possède une formation solide, maîtrise les techniques culinaires et respecte des normes strictes d'hygiène et d'organisation. Il sait gérer le stress et travailler en équipe dans un environnement exigeant. Cependant, les amateurs peuvent aussi développer un grand talent grâce à la pratique et aux ressources en ligne. Selon moi, l'idéal est de combiner passion, formation et expérience pour réussir durablement dans ce domaine.",
            ],
        ],
    ],
    [
        'combo' => 2,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami Mehdi vient d\'emménager dans votre ville et cherche des renseignements sur les moyens de transport. Écrivez un message en lui donnant les informations nécessaires (types de transport, abonnement, tarif, etc.)',
                'correction' => "Salut Mehdi,\nBienvenue dans ma ville ! Pour te déplacer facilement, tu as plusieurs options. Il y a des bus et un métro qui couvrent presque tous les quartiers. Les bus passent régulièrement, surtout aux heures de pointe. Le métro est rapide et pratique pour traverser la ville. Tu peux acheter un ticket à l'unité, mais je te conseille de prendre un abonnement mensuel si tu utilises souvent les transports. C'est plus économique. Il existe aussi une carte rechargeable qui permet de payer moins cher chaque trajet. Enfin, il y a des vélos en libre-service et des applications de covoiturage si tu préfères. N'hésite pas si tu as d'autres questions !",
            ],
            [
                'task' => 2,
                'prompt' => 'Exprimez votre admiration pour une personnalité, célèbre ou non, en vous appuyant sur ses actions spécifiques. Rédigez un article de blog en détaillant les actions remarquables de cette personne et expliquez pourquoi vous l\'aimez.',
                'correction' => "Une source d'inspiration au quotidien\nAujourd'hui, j'aimerais partager mon admiration pour une personne qui m'inspire\nprofondément : ma mère. Elle n'est pas célèbre, mais ses actions parlent d'elles-mêmes. Depuis toujours, elle travaille avec détermination pour assurer le bien-être de notre famille. Malgré les difficultés, elle n'a jamais abandonné ses objectifs et a toujours trouvé des solutions avec calme et courage.\nElle s'implique aussi dans des actions solidaires dans notre quartier, notamment en aidant des familles dans le besoin et en participant à des collectes de dons. Sa générosité et son sens du partage sont remarquables. Ce que j'admire le plus chez elle, c'est sa capacité à rester positive et à encourager les autres, même dans les moments difficiles. Elle m'a appris la persévérance, la patience et l'importance\nde croire en soi. Pour toutes ces raisons, elle reste mon plus grand modèle.",
            ],
            [
                'task' => 3,
                'prompt' => 'Vivre En Colocation',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La vie en colocation offre de nombreux avantages. Partager un logement avec d\'autres personnes permet de réduire les dépenses, que ce soit le loyer, les factures ou les frais généraux. De plus, cela favorise les interactions sociales et les échanges culturels. Vivre avec des colocataires permet de rencontrer des individus de différents horizons, de nouer des amitiés et de partager des expériences enrichissantes.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La colocation peut cependant présenter des défis. Les différences de personnalité et de mode de vie entre les colocataires peuvent entraîner des tensions. La gestion des responsabilités et des tâches ménagères peut également être source de conflits. De plus, la colocation peut limiter l\'intimité et l\'espace personnel. Il est important d\'établir une communication ouverte et respectueuse, ainsi que des règles de vie commune, pour favoriser une cohabitation harmonieuse.',
                    ],
                ],
                'correction' => "Vivre en colocation : une bonne solution ?\nLa colocation est une option de logement de plus en plus populaire, notamment chez les étudiants et les jeunes travailleurs. Selon le document 1, elle permet de réduire les dépenses et favorise les échanges sociaux et culturels. Le document 2 souligne toutefois que des différences de personnalité et un manque d'intimité peuvent créer des tensions. À mon avis, la colocation présente plus d'avantages que d'inconvénients, surtout lorsqu'on a un budget limité. Partager le loyer et les factures permet de faire des économies importantes. De plus, vivre avec d'autres personnes peut être enrichissant sur le plan humain. On apprend à respecter les différences, à communiquer et à collaborer. Cependant, pour éviter les conflits, il\nest essentiel d'établir des règles claires dès le départ, notamment concernant le ménage et les responsabilités. Une bonne communication est la clé d'une cohabitation réussie.",
            ],
        ],
    ],
    [
        'combo' => 3,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
                'correction' => "Salut les amis,\nJe vous propose qu'on parte en week-end le mois prochain à Québec. C'est une ville magnifique avec beaucoup de choses à découvrir. On pourrait y aller en voiture, cela prendra environ trois heures et ce sera plus pratique pour se déplacer sur place. Sur place, on pourra visiter le Vieux-Québec, se promener près du fleuve et découvrir les petits cafés typiques. J'ai aussi vu qu'il y a des activités comme des visites guidées et des balades en bateau. Dites-moi si vous êtes disponibles et si l'idée vous plaît afin qu'on puisse réserver l'hébergement rapidement !",
            ],
            [
                'task' => 2,
                'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : "voyage.internaute.fr". Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
                'correction' => "Tout quitter pour voyager un an : une décision courageuse\nÀ mon avis, tout quitter pour partir en voyage pendant un an peut être une excellente idée, à condition d'être bien préparé. J'ai moi-même pris une pause dans mes études pour voyager plusieurs mois à l'étranger. Au début, j'avais peur de perdre du temps, mais cette expérience m'a énormément apporté.\nVoyager m'a permis de découvrir de nouvelles cultures, d'améliorer mes compétences linguistiques et de gagner en autonomie. J'ai appris à m'adapter à des situations imprévues et à sortir de ma zone de confort. Cette année m'a aidé à mieux comprendre mes objectifs professionnels. Cependant, il est important de planifier son budget et de réfléchir aux conséquences sur sa carrière. Selon moi, si le projet est réfléchi et organisé, partir un an peut être une expérience enrichissante et formatrice.",
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail : favorable ou défavorable ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Le travail est au centre de notre vie. Dès l\'enfance, on entend souvent la question : « Qu\'est-ce que tu veux faire quand tu seras grand ? » Le travail devrait être synonyme de réussite et de satisfaction, mais il est trop souvent synonyme de fatigue et d\'emprisonnement. Aujourd\'hui, beaucoup pensent que l\'on ne passe pas assez de temps avec sa famille, ses amis. Il est urgent de revoir la place occupée par le travail dans notre société. Certains pensent que travailler moins permettrait d\'avoir plus de temps libre pour mieux vivre.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Certaines personnes ont décidé d\'arrêter de travailler pour changer de mode de vie. Pourtant, aujourd\'hui, travailler, c\'est exister. La question : « Qu\'est-ce que tu fais dans la vie ? » revient souvent lors d\'une première rencontre. Elle prouve que l\'emploi fait partie de notre identité. D\'après le spécialiste Jean-Daniel Remond, la vie en entreprise est très importante. Les contacts quotidiens, les réseaux, les amitiés, l\'impression d\'être utile, mais aussi les difficultés, tout cela contribue à construire notre personnalité et notre identité.',
                    ],
                ],
                'correction' => "Le travail : favorable ou défavorable ?\nLe travail occupe une place centrale dans notre société. Selon le document 1, il est parfois perçu comme une source de fatigue et un obstacle à la vie familiale, au point que certains souhaitent travailler moins pour mieux vivre. En revanche, le document 2 affirme que le travail est essentiel, car il contribue à construire notre identité et notre place dans la société. À mon avis, le travail est indispensable, mais il doit rester équilibré. Il permet d'assurer une stabilité financière, de développer des compétences et de se sentir utile. Comme l'explique le\ndocument 2, le travail participe à la construction de notre identité, car il favorise les relations sociales et le sentiment d'appartenance. Toutefois, je comprends aussi les inquiétudes mentionnées dans le document 1. Un excès de travail peut entraîner du stress et réduire le temps consacré à la famille. Selon moi, l'idéal est d'adopter un rythme raisonnable pour concilier réussite professionnelle et épanouissement personnel.",
            ],
        ],
    ],
    [
        'combo' => 4,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '"Bonjour, ça y est, j\'ai obtenu mon visa pour le Canada. Je vais arriver le 3 mars. Est-ce que tu pourras m\'aider à trouver un hôtel pour la première semaine ? Merci d\'avance pour ton aide." Matthias. Vous avez trouvé un hôtel pour Matthias. Vous lui écrivez un courriel. Dans ce message vous décrivez l\'hôtel et vous lui donnez toutes les informations utiles (situation, tarif…).',
                'correction' => "Salut Matthias,\nFélicitations pour ton visa ! J'ai trouvé un hôtel qui devrait te convenir pour ta première semaine. Il s'appelle \"Hôtel Central Montréal\" et il est situé près du centre-ville, à quelques minutes du métro. Tu pourras facilement te déplacer vers les principaux quartiers et les services administratifs. La chambre coûte environ 110 dollars par nuit, petit-déjeuner inclus. L'hôtel propose le Wi-Fi gratuit, une réception ouverte 24h/24 et un service de bagagerie. Les avis sont très positifs, surtout pour la propreté et l'accueil.\nSi tu es d'accord, je peux faire la réservation rapidement pour les dates du 3 au 10 mars. Dis moi ce que tu en penses !",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous êtes allés voir un spectacle (film, pièce de théâtre, concert, etc.) avec des amis. Vous l\'avez aimé. Sur votre blog, vous racontez votre soirée et vous expliquez pourquoi vous avez aimé le spectacle.',
                'correction' => "Une soirée inoubliable au théâtre\nChers lecteurs,\nHier soir, je suis allé(e) voir une pièce de théâtre avec des amis, et je dois dire que j'ai passé un moment exceptionnel. La salle était pleine et l'ambiance très chaleureuse dès notre arrivée. La pièce racontait une histoire à la fois touchante et drôle, ce qui a captivé le public du début à la fin. J'ai particulièrement aimé le jeu des acteurs. Ils étaient très expressifs et naturels, ce qui rendait l'histoire encore plus réaliste. Les décors et les lumières étaient également bien réalisés et contribuaient à créer une atmosphère immersive.Ce spectacle m'a plu parce qu'il m'a fait rire, réfléchir et ressentir de vraies émotions. Partager\ncette expérience avec mes amis a rendu la soirée encore plus agréable. C'est une sortie que je recommande vivement !",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Relations Amicales au Travail',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les amitiés entre collègues au travail peuvent être extrêmement bénéfiques. Elles favorisent un climat de travail agréable et une ambiance positive au sein de l\'équipe. Avoir des amis parmi ses collègues permet de renforcer les liens professionnels et de créer un sentiment de camaraderie. Cela peut contribuer à une meilleure communication, une collaboration plus étroite et une résolution plus efficace des problèmes. De plus, partager des moments de convivialité en dehors du travail, comme des déjeuners ou des activités après le bureau, peut renforcer les liens et créer une dynamique de groupe solide.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Il est important de trouver un équilibre entre amitié et professionnalisme au travail. Les amitiés excessivement proches peuvent parfois créer des tensions ou des conflits lorsque des décisions professionnelles doivent être prises. De plus, les amitiés exclusives entre certains collègues peuvent exclure les autres membres de l\'équipe, ce qui peut nuire à la cohésion et à la collaboration. Il est essentiel de maintenir des limites claires et de veiller à ce que les amitiés ne compromettent pas le professionnalisme, la hiérarchie ou la productivité au sein de l\'organisation.',
                    ],
                ],
                'correction' => "Les relations amicales au travail : un atout ou un risque ?\nLes relations amicales au travail suscitent souvent des débats. Selon le document 1, l'amitié entre collègues favorise une ambiance positive, une meilleure communication et une collaboration plus efficace. En revanche, le document 2 souligne que des liens trop proches peuvent créer des tensions ou nuire au professionnalisme. À mon avis, les relations amicales au travail sont bénéfiques si elles restent équilibrées. Passer de nombreuses heures au bureau rend naturel le fait de tisser des liens. Avoir des collègues avec qui l'on s'entend bien rend l'environnement plus agréable et réduit le stress. Cela peut également faciliter le travail en équipe et la résolution des problèmes. Cependant, il est essentiel\nde garder une certaine distance professionnelle. Les décisions importantes doivent rester objectives et justes. Selon moi, l'idéal est de cultiver des relations respectueuses et positives, tout en maintenant des limites claires pour préserver la cohésion et la productivité.",
            ],
        ],
    ],
    [
        'combo' => 5,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez passé un week-end à la campagne. Écrivez un message à votre ami(e) pour lui décrire ce qui s\'est passé. (60 mots minimum/120 mots maximum)',
                'correction' => "Salut,\nJ'espère que tu vas bien. Je voulais te raconter mon week-end à la campagne. C'était vraiment reposant\net très agréable. Nous avons passé du temps dans un petit village entouré de nature. Nous avons fait une\nlongue promenade, puis un pique-nique près d'un champ. J'ai aussi vu des animaux comme des vaches\net des chevaux, ce qui m'a beaucoup plu. L'air était frais et l'ambiance très calme, loin du bruit de la\nville. Le soir, nous avons mangé un bon repas et discuté longtemps. Ce week-end m'a fait beaucoup de\nbien et j'aimerais y retourner bientôt.\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Votre direction est à la recherche d\'une salle pour la fête de fin d\'année, capable d\'accueillir 100 invités. Rédigez un message à la direction pour leur dire que vous avez trouvé un local idéal. (lieu, tarifs, services, etc.). (120 mots minimum/150 mots maximum)',
                'correction' => "Bonjour,\nJ'espère que vous allez bien. Je vous écris pour vous informer que j'ai trouvé une salle idéale pour\norganiser la fête de fin d'année. Il s'agit de la salle « Le Grand Salon », située en centre-ville, facilement\naccessible en transport en commun et disposant d'un parking à proximité. Elle peut accueillir jusqu'à\n120 personnes, ce qui correspond parfaitement à notre besoin de 100 invités. Le tarif de location est de\n950 \$ pour la soirée, incluant les tables, les chaises, le service de nettoyage et un espace pour la musique.\nLa salle propose également un service traiteur en option, ainsi qu'un équipement audio et un\nvidéoprojecteur.\nSi vous le souhaitez, je peux organiser une visite cette semaine afin de confirmer la réservation.\nCordialement,\nAyoub",
            ],
            [
                'task' => 3,
                'prompt' => 'Utilisation des nouvelles technologies',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Jean : Je suis fermement convaincu que l\'intégration des nouvelles technologies dans les écoles est cruciale pour préparer les élèves à un avenir numérique. Je pense que l\'usage des tablettes et des ordinateurs stimule non seulement l\'engagement des élèves mais enrichit également leur expérience éducative en leur offrant un accès facile à une variété de ressources, encourageant ainsi leur créativité et autonomie.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Sara : Je suis sceptique quant à l\'usage intensif des technologies dans l\'enseignement. Je crois que cela peut réduire les interactions humaines essentielles et favoriser une dépendance préoccupante aux écrans. À mon avis, les méthodes d\'enseignement traditionnelles et le contact direct entre enseignants et élèves restent indispensables pour un développement équilibré et complet des compétences des jeunes.',
                    ],
                ],
                'correction' => "Utilisation des nouvelles technologies à l'école : pour ou contre ?\nL'intégration des nouvelles technologies à l'école est un sujet qui divise. Pour certains, elles\nsont indispensables pour préparer les élèves à un avenir numérique et pour faciliter l'accès à\ndes ressources variées, tout en développant la créativité et l'autonomie. Cependant, d'autres\npensent qu'un usage trop important des écrans peut réduire les échanges humains et créer une\ndépendance.\nÀ mon avis, les nouvelles technologies sont utiles à l'école, mais elles doivent être utilisées\navec équilibre. Elles peuvent rendre les cours plus intéressants et aider les élèves à apprendre\nplus facilement. Par exemple, une tablette permet de faire des recherches rapides, de regarder\ndes vidéos éducatives ou de travailler sur des exercices interactifs. Cela peut aussi développer\nl'autonomie des élèves et les préparer au monde professionnel. Cependant, un usage excessif\npeut réduire la concentration et limiter les échanges entre les élèves et les enseignants. De\nplus, certains jeunes deviennent dépendants des écrans. C'est pourquoi je pense qu'il faut\nencadrer leur utilisation, fixer des règles claires et conserver des méthodes traditionnelles.\nL'idéal est de combiner les deux pour un apprentissage efficace.",
            ],
        ],
    ],
    [
        'combo' => 6,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez commandé un objet sur Internet et après réception du colis, vous constatez que l\'objet est cassé. Rédigez un e-mail au service clientèle pour signaler le problème, décrivez le dommage de l\'objet et précisez ce que vous attendez comme solution. (60 mots minimum/120 mots maximum)',
                'correction' => "Objet : Article reçu cassé – demande de remplacement ou remboursement\nBonjour,\nJe vous contacte concernant une commande reçue aujourd'hui. Après ouverture du colis, j'ai constaté\nque l'objet était cassé. En effet, l'article présente une fissure importante et une partie est complètement\ndétachée, ce qui le rend inutilisable. Le carton semblait intact, mais l'objet était mal protégé à l'intérieur.\nJe vous remercie de bien vouloir me proposer une solution rapidement. Je souhaite soit un remplacement\ndu produit, soit un remboursement complet. Je peux vous envoyer des photos du dommage si nécessaire.\nCordialement,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez visité une exposition de votre artiste préféré. Rédigez un article exprimant votre expérience lors de la visite. Décrivez ce que vous avez vu et vos impressions. (120 mots minimum/150 mots maximum)',
                'correction' => "Une exposition inoubliable de mon artiste préféré\nJ'ai récemment visité une exposition consacrée à mon artiste préféré, et cette expérience m'a vraiment\nmarqué. Dès l'entrée, j'ai été impressionné par l'organisation et l'ambiance calme du lieu. Les œuvres\nétaient bien présentées, avec des explications claires qui permettaient de mieux comprendre le style et\nl'histoire de chaque création.\nJ'ai surtout apprécié les tableaux, car les couleurs étaient très expressives et les détails étaient\nincroyables. Certaines œuvres représentaient des scènes de la vie quotidienne, tandis que d'autres étaient\nplus modernes et abstraites. Ce mélange rendait la visite très intéressante. J'ai aussi été touché par\nl'émotion que l'artiste réussissait à transmettre à travers ses œuvres.\nEn sortant, je me sentais inspiré et heureux d'avoir découvert cette exposition.\nJe recommande vivement cette visite à tous les amateurs d'art.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Devoirs à la Maison.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Selon des associations de parents d\'élèves, les devoirs à la maison sont utiles, car ils permettent aux élèves d\'apprendre à organiser leur temps de manière autonome. Pour les parents, les devoirs sont un lien quotidien avec l\'école. Même s\'il est parfois difficile de suivre les devoirs après une journée de travail fatigante, ils apprécient ce moment partagé avec leurs enfants parce que ceux-ci sont contents que leurs parents s\'intéressent à eux. C\'est valorisant !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Nous protestons depuis longtemps contre les devoirs à la maison pour plusieurs raisons. Personne n\'a jamais prouvé leur utilité pour améliorer les résultats des élèves. Beaucoup de parents ont peu de temps pour encadrer les devoirs de leurs enfants et certains parents ne savent pas le faire. Quant aux élèves, ceux qui ont réussi les exercices en classe perdent leur temps à les faire à la maison. Ceux qui ne sont pas aidés à la maison ne réussissent toujours pas, ils sont défavorisés. C\'est pourquoi nous pensons qu\'il faut supprimer les devoirs à la maison.',
                    ],
                ],
                'correction' => "Les devoirs à la maison : utiles ou inutiles ?\nLes devoirs à la maison sont souvent considérés comme utiles, car ils permettent aux élèves\nd'apprendre à organiser leur temps et de travailler de manière autonome. Pour certains\nparents, ils représentent aussi un lien quotidien avec l'école et un moment de partage avec\nleurs enfants. Cependant, d'autres estiment que leur utilité n'est pas prouvée et qu'ils créent\ndes inégalités entre les élèves selon l'aide disponible à la maison.\nÀ mon avis, les devoirs à la maison peuvent être utiles, mais seulement s'ils sont limités et\nbien adaptés. Ils permettent aux élèves de réviser les leçons, de s'entraîner et de développer\nleur autonomie. Par exemple, relire un texte ou faire quelques exercices de mathématiques\npeut aider à mieux comprendre et à mémoriser. Cependant, donner trop de devoirs peut\nfatiguer les enfants après une journée d'école et créer du stress. De plus, tous les élèves ne\nsont pas dans les mêmes conditions à la maison : certains parents n'ont pas le temps ou ne\nsavent pas aider. Cela peut donc accentuer les inégalités. Selon moi, il vaut mieux donner peu\nde devoirs, mais réguliers, et renforcer l'accompagnement en classe.",
            ],
        ],
    ],
    [
        'combo' => 7,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'France Télévision prépare un reportage sur le sport amateur. Et vous, quel sportif êtes-vous ? Envoyez-nous vos témoignages sur francetelevision.fr. (60 mots minimum / 120 mots maximum)',
                'correction' => "Je pratique un sport amateur depuis plusieurs années, principalement pour rester en forme et réduire le\nstress du quotidien. Je fais surtout du jogging et un peu de fitness, deux à trois fois par semaine. Ce que\nj'aime dans le sport amateur, c'est qu'il permet de se dépasser sans pression, à son rythme. Par exemple,\ncourir en plein air me donne une sensation de liberté et m'aide à garder une bonne énergie.\nJe pense que le sport amateur est essentiel, car il améliore la santé, le moral et la confiance en soi. Même\navec un emploi du temps chargé, il est possible de trouver du temps pour bouger régulièrement.",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé des vacances dans une belle région de votre pays. Vous écrivez un message à vos amis dans lequel vous décrivez votre expérience, vous expliquez pourquoi vous avez beaucoup aimé ce séjour. (120 mots minimum / 150 mots maximum)',
                'correction' => "Salut les amis,\nJ'espère que vous allez bien. Je voulais vous raconter mes dernières vacances dans une très\nbelle région de mon pays. J'ai passé quelques jours dans un endroit calme, entouré de nature,\navec des paysages magnifiques. Il y avait des montagnes, des petits villages traditionnels et\nune ambiance très chaleureuse. Chaque jour, je faisais des promenades, je prenais des photos\net je profitais de l'air frais.\nCe que j'ai le plus aimé, c'est la tranquillité et la beauté du lieu. J'ai aussi découvert des\nspécialités locales délicieuses et rencontré des habitants très accueillants. Ce séjour m'a\nvraiment permis de me reposer, de changer d'air et d'oublier le stress. Franchement, c'était\nune expérience incroyable que je recommande à tout le monde.\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Écoles Privées Ou Publiques ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'À la rentrée, le nombre d\'élèves inscrits dans des écoles privées a augmenté en France. Le succès des établissements privés n\'est pas directement lié aux bons résultats scolaires de leurs élèves. C\'est la réputation de ces lieux qui explique un tel enthousiasme. Aux yeux de nombreux parents, les élèves y sont mieux encadrés, mieux surveillés et les professeurs sont plus présents. Les parents sont aussi rassurés parce que les classes sont homogènes : elles accueillent généralement des élèves de milieux sociaux favorisés. En effet, les études dans ces établissements sont payantes, ce qui n\'est pas le cas dans les écoles publiques.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Dans les collèges privés, il y a peu d\'élèves de milieux sociaux défavorisés. Comme les études dans ces établissements sont payantes, certaines catégories de population en sont exclues. Ce système qui oppose écoles publiques et écoles privées ne facilite pas la mixité sociale. Ainsi, les élèves du privé ont rarement l\'occasion de rencontrer d\'autres jeunes issus de milieux sociaux moins favorisés qu\'eux et inversement. Ce modèle scolaire reproduit les inégalités sociales et renforce le sentiment d\'exclusion de certains jeunes.',
                    ],
                ],
                'correction' => "Écoles privées ou publiques : quel choix pour les familles ?\nLe nombre d'élèves inscrits dans les écoles privées augmente en France. Pour beaucoup de\nparents, ces établissements ont une meilleure réputation, avec un encadrement plus strict, des\nprofesseurs plus présents et des classes composées d'élèves de milieux favorisés. Cependant,\nd'autres dénoncent un système payant qui limite la mixité sociale et renforce les inégalités\nentre les élèves.\nÀ mon avis, les écoles publiques doivent rester la priorité, car elles représentent un modèle\nplus égalitaire. Elles permettent aux enfants de différents milieux de se rencontrer et\nd'apprendre ensemble. Par exemple, la mixité sociale aide les élèves à mieux comprendre la\nréalité de la société et à développer le respect des différences. Même si certaines écoles\nprivées offrent un meilleur encadrement, elles ne sont pas accessibles à tout le monde. Selon\nmoi, il faudrait surtout améliorer les écoles publiques en renforçant les moyens, la discipline\net la qualité de l'enseignement, afin que tous les élèves aient les mêmes chances de réussir.",
            ],
        ],
    ],
    [
        'combo' => 8,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut, Je sais que tu vas dans une salle de sport. Qu\'est-ce que tu penses de cette salle ? Peux-tu m\'en dire un peu plus ? Je voudrais savoir si elle est bien ! Merci d\'avance ! Joanna Vous répondez à votre amie Joanna. Dans votre message, vous décrivez la salle de sport que vous fréquentez (activités, horaires, prix, etc.) (60 mots minimum / 120 mots maximum).',
                'correction' => "Salut Joanna,\nJ'espère que tu vas bien. Oui, je vais dans une salle de sport près de chez moi et je la trouve\nvraiment très bien. Elle est propre, moderne et l'ambiance est agréable. On y trouve une salle\nde musculation, des machines de cardio et plusieurs cours collectifs comme le fitness, le yoga\net la zumba.\nLa salle est ouverte tous les jours, de 6 h à 22 h, ce qui est pratique. Le prix est raisonnable :\nenviron 40 \$ par mois, avec possibilité d'abonnement sans engagement. Il y a aussi des\ncoachs disponibles pour aider les débutants. Si tu veux, on peut y aller ensemble pour une\nséance d'essai.\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez suivi une formation (cours de langue, informatique, etc.). Sur un site Internet, vous écrivez un message pour raconter votre expérience (cours, participants, professeurs, etc.). Vous expliquez ce que vous avez aimé ou pas aimé pendant cette formation. (120 mots minimum / 150 mots maximum)',
                'correction' => "J'ai récemment suivi une formation de langue française et je souhaite partager mon expérience. La\nformation a duré quatre semaines, avec des cours quatre fois par semaine. Les participants venaient de\nplusieurs pays, ce qui rendait les échanges très intéressants. L'ambiance en classe était agréable et tout\nle monde était motivé.\nLes professeurs étaient compétents, patients et très pédagogues. Ils expliquaient clairement et nous\nfaisaient pratiquer beaucoup à l'oral, ce que j'ai particulièrement apprécié. Les activités étaient variées\n: exercices, jeux de rôle, compréhension orale et production écrite.\nCependant, j'ai moins aimé le rythme parfois trop rapide, surtout pour les débutants. Malgré cela, cette\nformation m'a permis de progresser et de gagner en confiance. Je la recommande à ceux qui veulent\naméliorer leur niveau dans un bon cadre.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les produits Fait maison : Pour ou contre?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Il est aujourd\'hui possible de fabriquer soi-même tout type de produits à la maison : des shampooings, des savons, des crèmes, du maquillage mais aussi des produits d\'entretien pour faire le ménage. C\'est formidable car on peut ainsi contrôler leur composition. Il est préférable de sélectionner des ingrédients et des parfums naturels qui ne contiennent pas d\'éléments chimiques. Par ailleurs, la fabrication maison permet de réduire les emballages et donc les déchets. N\'hésitez plus : fabriquez vos propres produits !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Vous préférez fabriquer vous-même votre savon au lieu de l\'acheter ? Attention, il y a des risques pour la santé si vous ne choisissez pas les bons ingrédients ou si les règles d\'hygiène ne sont pas respectées. Ainsi, avant de vous lancer, il est nécessaire de bien se renseigner et de suivre les règles strictes de fabrication et de conservation. De plus, même si ces produits coûtent moins cher et représentent une économie d\'argent au quotidien, la fabrication « maison » prend souvent beaucoup de temps.',
                    ],
                ],
                'correction' => "Fabriquer ses produits à la maison : une bonne idée ?\nAujourd'hui, de plus en plus de personnes fabriquent elles-mêmes des produits à la maison,\ncomme des savons, des crèmes ou des produits ménagers. Cette pratique permet de contrôler\nla composition, d'utiliser des ingrédients naturels et de réduire les emballages. Cependant,\ncertains rappellent qu'il existe des risques pour la santé si les règles d'hygiène ne sont pas\nrespectées et que cela demande beaucoup de temps\nÀ mon avis, fabriquer ses produits à la maison peut être une très bonne idée, mais\nseulement si l'on fait attention. Cette pratique permet de choisir des ingrédients\nnaturels, de limiter les produits chimiques et de réduire les emballages. Par exemple,\npréparer un savon simple ou un produit ménager peut être économique et plus\nrespectueux de l'environnement. Cependant, il ne faut pas se lancer sans information.\nSi les ingrédients sont mal choisis ou si l'hygiène n'est pas respectée, cela peut\nprovoker des allergies ou des irritations. De plus, la fabrication maison demande du\ntemps et de l'organisation, ce qui n'est pas toujours facile au quotidien. Selon moi, c'est\nune solution intéressante, mais il faut suivre des recettes fiables et rester prudent.",
            ],
        ],
    ],
];
