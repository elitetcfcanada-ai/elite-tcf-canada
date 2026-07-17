<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 32,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez modifier la décoration de votre appartement (meubles, couleurs, accessoires, etc.). Vous rédigez un message à un(e) ami(e) pour lui présenter votre projet et lui demander son aide.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => '« École de musique ! Activités gratuites, concerts et jeux. Rendez-vous vendredi dès 9 heures. »Vous avez pris part à cet événement. Vous adressez un message à vos amis pour décrire ce que vous avez vécu et exprimer votre avis sur cette journée.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Faire ses courses : entre grandes surfaces et commerces de proximité',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Faire ses courses au supermarché est avantageux, puisqu\'on y trouve une grande diversité de produits au même endroit. Vous avez la possibilité de garer votre voiture et de passer d\'un rayon à l\'autre pour acheter ce dont vous avez besoin : fruits, légumes, fromages, viandes, boissons. De plus, plusieurs marques sont proposées pour chaque produit, avec des offres promotionnelles régulières.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'ASSOCIATION POUR LA DÉFENSE DES COMMERCES DE PROXIMITÉ L\'initiative « Février sans supermarché » vise à limiter le pouvoir des supermarchés et à soutenir la survie des petits commerces en améliorant leurs ventes. Le principe est simple : ne pas faire ses courses dans les grandes surfaces durant un mois et privilégier les épiceries de quartier. Les clients en retirent des avantages, comme des produits plus frais et de qualité, ainsi que des moments d\'échange avec leurs voisins.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 33,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à vos amis pour les inviter à un anniversaire surprise de votre meilleur(e) ami(e). (Lieu, date, horaire, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une brocante (achat / vente de produits d\'occasion) dans votre ville. Sur votre blog personnel, racontez pourquoi vous avez aimé cette activité.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Vols à Bas Prix',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je fais souvent mes voyages avec des compagnies aériennes à bas prix. Les compagnies low-cost mettent à disposition des prix inférieurs à ceux proposés par les compagnies aériennes régulières. Cela me coûte des fois moins cher que de voyager en voiture ou en train. Avec ces tarifs-là, vous vous doutez qu\'il y a un hic : en effet, vous n\'aurez droit à aucun service à bord (ni aliments, ni boissons). Je dirais donc que le low-cost n\'est surtout pas fait pour les vols long-courriers.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Récemment, j\'ai pris la décision de ne plus voyager avec les compagnies aériennes à bas prix. En effet, j\'ai longuement réfléchi afin de prendre ma décision, mais ce choix était évident : des sièges inconfortables, des conditions de travail pénibles et surtout des avions vétustes qui remettent en cause la sécurité ! Dès lors, pour certains voyages, je vais opter pour la voiture ou même le train, ce dernier permettant même de découvrir de jolis paysages. Quant aux longs trajets, mieux vaut prendre un vol en compagnie régulière.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 34,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'C\'est bientôt l\'anniversaire de votre meilleur(e) ami(e). Vous écrivez un message à votre groupe d\'amis pour leur proposer de faire un cadeau en commun. Vous décrivez le cadeau que vous aimeriez faire et vous leur expliquez comment ils peuvent participer.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => '« Cher collègue,Nous cherchons un local pour organiser la fête de fin d\'année de notre école de musique. Nous cherchons un lieu pour accueillir 100 personnes. Merci de nous faire une proposition.La direction de l\'école de musique de Louviers »Vous avez trouvé un local adapté. Vous êtes allé(e) le visiter. Vous écrivez à la direction. Vous racontez votre visite et vous justifiez votre proposition (tarifs, services, lieu, etc.).',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Vivre chez ses parents, pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les avantages : penser à l\'avenir, avoir les vêtements propres, être en confort surtout que l\'argent n\'est pas toujours suffisant, vivre avec ses parents pendant la période des études permet aux jeunes d\'économiser les frais de logement, des plats faits maison et une certaine stabilité psychique. Les adolescents qui vivent avec leurs parents peuvent économiser leur argent pour des projets de vie (ils ne payent ni le loyer ni la nourriture).',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les inconvénients : manque de liberté, vivre seul permet d\'être indépendant. Les adolescents qui vivent avec leurs parents s\'ennuient car leurs parents décident à leur place et restent dépendants. Témoignage d\'un jeune de 25 ans qui a perdu son emploi et a dû revenir chez ses parents : il a perdu son espace d\'intimité. Pour certains, revenir chez ses parents, c\'est revenir en arrière.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 35,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous allez déménager. Des amis ont accepté de vous aider. Vous leur écrivez un message collectif pour leur expliquer comment le déménagement va se passer (lieux, horaires, durée, trajet, tâches à faire, etc.)',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Participez à notre concours pour gagner un séjour pour deux personnes dans la ville de votre choix. Rédigez un article sur le thème : « La vie de mon artiste préféré(e) ». Vous participez à ce concours. Vous expliquez pourquoi vous avez choisi cet(te) artiste et vous racontez sa vie.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les objets connectés',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les objets connectés facilitent notre vie quotidienne. Ce sont des objets ou des équipements pilotés à distance à l\'aide d\'un téléphone portable ou par Internet, comme le système de chauffage ou la fermeture des portes. De plus, avec une montre ou un bracelet connecté, toutes nos activités peuvent être analysées. Un logiciel va mesurer le nombre de nos pas pour nous inciter à faire davantage d\'exercice. Un domaine où l\'on comprend mieux leur utilité est celui de la surveillance de la santé. Certains objets agissent comme un carnet de santé, toujours prêts à vous rappeler un rendez-vous chez le médecin ou des médicaments à prendre.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Il y aurait 50 milliards d\'objets connectés dans le monde : l\'alarme, le téléviseur, la caméra de surveillance, les volets, le détecteur de fumée, etc. Face à cette évolution, la question de la sécurité se pose. En effet, un pirate informatique peut prendre le contrôle d\'un objet connecté en quelques minutes. Ou encore, un cambrioleur pourrait vérifier grâce aux caméras de surveillance connectées d\'une habitation si les occupants sont absents. Il est aussi possible d\'entrer dans le système connecté d\'une voiture et d\'en prendre les commandes à distance.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 36,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Je suis votre amie Anna et je compte passer un week-end dans ta ville. Donnez-moi des informations sur les moyens de transport pour explorer la ville. Répondez à Anna dans un message.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à une fête de voisins du quartier, écrivez un blog pour montrer pourquoi vous avez aimé cette fête.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les distributeurs de boissons dans les lycées : Pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je suis en faveur des distributeurs de boissons dans les lycées. Premièrement, ils offrent une commodité supplémentaire pour les élèves, notamment pour ceux qui n\'ont pas le temps de passer à la cafétéria pendant les pauses. Deuxièmement, s\'ils sont bien gérés, ces distributeurs peuvent offrir une gamme de boissons saines, comme de l\'eau, du jus de fruits pur et des boissons aux fruits sans sucre ajouté. Ces distributeurs peuvent être une source de revenus supplémentaire pour l\'école, qui peut être réinvestie dans l\'amélioration des infrastructures ou des programmes scolaires.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Je suis contre l\'installation de distributeurs de boissons dans les lycées. Ma principale préoccupation est liée à la santé des élèves. Malheureusement, beaucoup de ces distributeurs sont remplis de boissons sucrées et de sodas qui contribuent à l\'obésité infantile et à d\'autres problèmes de santé comme le diabète. Même les jus de fruits, qui peuvent sembler sains, contiennent souvent beaucoup de sucre. Les écoles devraient être des lieux qui encouragent des habitudes alimentaires saines et je crains que la présence de ces distributeurs n\'encourage une consommation excessive de boissons sucrées.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 37,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Rédiger un e-mail au service client pour signaler la réception d\'un objet endommagé après une commande en ligne, en décrivant le problème et en précisant la solution souhaitée.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Rédiger un article sur la visite d\'une exposition dédiée à un artiste préféré, en partageant les impressions et en décrivant les œuvres découvertes.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les travaux scolaires à domicile',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Selon des associations des parents d\'élèves, les devoirs à la maison sont utiles, car ils permettent aux élèves d\'apprendre à organiser leur temps de manière autonome. Pour les parents, les devoirs sont lien quotidien avec l\'école. Même s\'il est parfois difficile de suivre les devoirs après une journée de travail fatigant, ils apprécient ce moment partagé avec leurs enfants parce que ceux-ci sont contents que leurs parents s\'intéressent à eux. C\'est valorisant !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Nous protestons depuis longtemps contre les devoirs à la maison pour plusieurs raisons. Personne ne jamais prouve leur utilité pour améliorer les résultats des élèves. Beaucoup de parents ont peu de temps pour encadrer les devoirs de leurs enfants et certains parents ne savent pas le faire. Quant aux élèves, ceux qui ont réussi les exercices en classe perdent leurs temps à faire à la maison. Ceux qui ne sont pas aidés à la maison ne réussissent toujours pas, ils sont défavorisés. C\'est pourquoi nous pensons qu\'il faut supprimer les devoirs à la maison.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 38,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'France Télévisions réalise un reportage sur le sport amateur et invite les passionnés à partager leur expérience en tant que sportifs sur francetélévision.fr.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Rédiger un message à des amis pour raconter un séjour dans une belle région du pays, en décrivant l\'expérience vécue et en expliquant les raisons de son appréciation.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Faut-il choisir une école privée ou publique ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les écoles privées connaissent une augmentation du nombre d\'inscriptions en France lors de chaque rentrée scolaire. Leur attractivité ne repose pas seulement sur la réussite académique, mais aussi sur leur renommée. Beaucoup de parents estiment que leurs enfants y sont mieux encadrés et bénéficient d\'un suivi plus rigoureux. Ces établissements, accessibles moyennant des frais de scolarité, regroupent principalement des élèves de milieux favorisés, ce qui représente un critère important pour certaines familles.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Dans les établissements privés, la présence d\'élèves issus de milieux défavorisés est limitée. En raison des coûts de scolarité, ces écoles ne sont pas accessibles à toutes les familles, ce qui accentue la séparation avec les écoles publiques. Cette situation empêche la mixité sociale et prive les élèves de rencontres enrichissantes avec des jeunes d\'horizons divers. Ce modèle éducatif perpétue ainsi les inégalités et renforce le sentiment d\'exclusion.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 39,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut, Je sais que tu vas dans une salle de sport. Qu\'est-ce que tu penses de cette salle ? Peux-tu m\'en dire un peu plus ? Je voudrais savoir si elle est bien ! Merci d\'avance ! Joanna — Vous répondez à votre amie Joanna. Dans votre message, vous décrivez la salle de sport que vous fréquentez (activités, horaires, prix, etc.)',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Rédiger un message décrivant une expérience avec les formations en ligne, en mettant en avant les points positifs et les éventuels défis rencontrés.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les produits faits maison : pour ou contre ?',
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
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 40,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez assister à un festival de cinéma dans votre ville. Vous écrivez un message à votre ami(e) pour lui proposer de venir avec vous. Vous lui donnez toutes les informations nécessaires sur l\'événement (films, dates et horaires, tarifs, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez récemment assisté à un festival de cinéma et vous avez vu un film qui vous a particulièrement marqué. Écrivez un message à vos amis pour leur raconter votre expérience lors de ce festival et leur parler de votre film préféré.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les maisons de retraite, pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les maisons de retraite représentent une solution adaptée pour les personnes âgées. Elles y bénéficient de soins appropriés et d\'un encadrement professionnel. En plus, elles ont la possibilité de participer à diverses activités qui leur permettent de garder une vie sociale et d\'éviter la solitude. Dans certains cas, c\'est mieux que de rester seules chez elles. (Sophie, infirmière)',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les maisons de retraite peuvent priver les personnes âgées de la présence de leur famille et donner l\'impression d\'être abandonnées. De plus, les conditions de vie ne sont pas toujours satisfaisantes et certains établissements manquent parfois de personnel compétent. Selon moi, l\'aide à domicile est une meilleure solution pour vieillir auprès de ses proches. (Jean, retraité)',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 41,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Parc de loisirs : « J\'ai hâte de passer la journée avec toi demain. S\'il te plaît, dis-moi, quelle activité nous pourrons faire ? » — Répondez à votre ami(e) pour lui décrire la sortie (horaires, transport, activités, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un message à vos amis pour leur raconter votre expérience lors d\'un salon du livre (conférence, expositions et rencontre avec les auteurs…).',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Faire ses courses alimentaires chez les producteurs locaux ou au supermarché',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Jean qui vit à la campagne favorise l\'achat des produits alimentaires directement à la ferme pour la bonne qualité, quitte à payer un peu plus cher.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Camille qui vit dans la ville préfère faire ses courses au supermarché pour la variété qu\'il propose et pour les prix abordables.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 42,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Rédigez un message pour inviter votre ami(e) à passer ses vacances dans votre ville en précisant les lieux et les endroits à visiter.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un concours pour gagner un séjour de deux semaines dans votre ville préférée. Le thème de ce concours est « Mon artiste préféré ». Écrivez un article de blog pour parler de votre artiste préféré.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'La chasse aux animaux : pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => '« Je suis de ceux qui n\'arrivent pas à comprendre comment l\'on peut prendre du plaisir à tuer des animaux. Je suis de ceux qui n\'arrivent pas à comprendre comment on peut prétendre aimer la nature alors qu\'on la détruit. » Gala, 29 ans',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => '« Les gens chassent pour différentes raisons : la subsistance, le commerce, la conservation et l\'aménagement de la faune, la protection de la propriété, l\'exercice, le loisir et le prestige. » David, journaliste de la FRM',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 43,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'La bibliothèque de votre quartier propose une rencontre avec un(e) auteur(e). Vous souhaitez y aller avec un(e) ami(e). Vous lui écrivez un message. Vous décrivez l\'événement et vous lui proposez de venir avec vous.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Dans le cadre de la Semaine du goût, vous avez participé à une activité pour découvrir les cuisines du monde. Vous écrivez un article pour le site Internet de votre ville. Dans cet article, vous racontez votre expérience et vous expliquez ce que vous avez aimé.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'L\'art urbain : Pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Dans les grandes villes, il y a de plus en plus de peintures et de dessins sur les murs des maisons, des écoles et même des églises. Cet art urbain permet à la culture d\'avoir une place dans l\'espace public. Selon certains maires, il est important que les villes aident les artistes en leur proposant des espaces où ils sont autorisés à peindre. De plus, ces graffitis attirent les touristes qui peuvent participer à des visites thématiques sur cet art. Ils peuvent ainsi découvrir des lieux moins connus du grand public.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Certaines villes dépensent chaque année une somme d\'argent importante pour lutter contre les peintures de rue. Ces dessins sur les murs, sur les bancs ou dans les stations de métro sont difficiles à enlever. Il est parfois nécessaire de faire appel à des équipes de nettoyage spécialisées. Pour certains habitants, ces peintures donnent une mauvaise image de leurs quartiers. Elles provoquent aussi la colère des propriétaires des murs qui sont parfois obligés de les nettoyer eux-mêmes. Cependant, malgré les interdictions des mairies et les amendes, les graffitis continuent d\'apparaître sur les murs des villes.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 44,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous venez de vous installer dans un nouveau logement. Vous recherchez un colocataire. Vous écrivez une annonce avec toutes les informations utiles sur le logement (lieu, taille, loyer, etc.) et vous décrivez la personne avec laquelle vous pourriez vivre (caractère, mode de vie, etc.)',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez commencé une nouvelle activité de loisir (danse, peinture, sport, etc.). Vous écrivez un message à vos amis pour raconter votre expérience et pour donner votre opinion sur cette activité.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'La gratuité des musées : Pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La gratuité est l\'une des valeurs fondatrices du musée, elle fait référence à l\'idéal de partage de la culture. Tous les professionnels de la culture sont d\'accord pour aller vers une démocratisation culturelle et donc réduire les obstacles à la visite d\'un musée. À la différence du théâtre ou de l\'opéra, il y a une tradition de gratuité depuis la création des musées : un dimanche par mois, l\'entrée dans les musées nationaux est gratuite pour tout le monde. Cette mesure a pour objectif d\'attirer de nouveaux visiteurs. En effet, le musée est avant tout un lieu d\'éducation qui doit accueillir le plus grand nombre. (D\'après https://www.ouest-france.fr)',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La gratuité totale dans les musées est une fausse bonne idée car elle fait croire au public que la culture est gratuite. Or les musées ont besoin d\'un financement pour entretenir et développer leurs collections. Par contre, la mise en place de prix réduits pour des publics choisis est une solution plus adaptée. Pour que ce dispositif soit efficace, il faut accompagner les catégories de visiteurs auxquelles s\'adressent les réductions, comme par exemple organiser des visites pour les adolescents ou les personnes à mobilité réduite. Ouvrir les portes du musée ne suffit pas : il faut créer une relation active entre le musée et ses publics grâce à des activités spécifiques. (D\'après https://www.lesechos.fr)',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 45,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Bonjour, bonne nouvelle ! J\'ai enfin obtenu mon visa pour le Canada. Mon arrivée est prévue pour le 3 mars. Pourrais-tu m\'aider à trouver un hôtel pour ma première semaine sur place ?Merci beaucoup pour ton aide ! »Carole. Vous avez réservé un hôtel pour Carole et lui envoyez un courriel contenant une description détaillée de l\'établissement ainsi que toutes les informations essentielles, telles que son emplacement, le prix et les services proposés.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Dans votre blog, racontez votre expérience de l\'apprentissage d\'une langue étrangère (vous écrivez sur un forum internet en racontant votre expérience en apprenant une langue étrangère).',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les jeux vidéo : Pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Des études ont montré que certaines zones du cerveau de l\'adulte peuvent être développées en jouant aux jeux vidéo. Ainsi, il peut être intéressant de jouer aux jeux vidéo, car ils permettent, par exemple, d\'améliorer la capacité d\'analyse, la capacité à faire des choix et la rapidité de réaction. C\'est une bonne nouvelle, car la répartition des joueurs par âge montre que 83 % des joueurs sont des adultes. Cependant, il convient de rester prudent, car certains jeux vidéo ne favorisent pas ce type d\'amélioration sur le cerveau.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Pendant trois ans, des enfants âgés de 8 à 17 ans ont participé à une étude sur les jeux vidéo. Les résultats de cette étude montrent que les enfants qui jouent beaucoup aux jeux vidéo sont plus violents, plus nerveux et plus stressés que ceux qui ne jouent pas ou peu. Ceux qui jouent beaucoup ont également moins de bons résultats à l\'école. Il est donc conseillé aux parents d\'être vigilants et de limiter l\'usage de ces jeux par leurs enfants.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 46,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut ! Je sais que tu fais du sport depuis le mois dernier. Ça m\'intéresse beaucoup et j\'aimerais bien venir avec toi. Tu peux m\'en dire plus ? À bientôt ! Camille — Vous répondez à votre ami Camille. Dans votre message, vous décrivez votre activité sportive et vous donnez des informations utiles (lieu, durée, prix, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Tout quitter pour changer de vie ? Il y a deux ans, nous avons décidé de changer de vie. Paul a quitté son poste de banquier à Paris et nous avons ouvert une boulangerie à Calgary ! Que pensez-vous de cette décision ? Avez-vous déjà vécu un grand changement, professionnel ou personnel ? Paul et Naïma — Vous avez lu ce message sur un forum internet. Vous répondez à Paul et Naïma. Dans votre message, vous donnez votre opinion sur le choix de Paul et de Naïma et vous racontez comment vous feriez si vous étiez à leur place.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Le bien-être au travail',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Nous avons installé, dans votre entreprise, des bureaux réglables qui montent et descendent, ceci pour permettre aux employés de choisir la position qui leur convient le mieux pour travailler. Les employés sont satisfaits et disent qu\'ils sont plus productifs et plus efficaces lors des négociations au téléphone par exemple. Même le climat social est plus convivial et la communication verbale entre collègues s\'est renforcée. « Lorie, responsable ».',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'De nos jours, les entreprises veulent toujours plus de productivité et d\'efficacité, et les employés passent plus de temps sur les lieux de travail. Les entreprises cherchent toujours des moyens ou astuces pour convaincre leurs employés de travailler plus et qu\'elles pensent à leur santé. Moi, ce qui m\'importe, c\'est de faire mon job sur mon temps de travail réel sans être obligé de faire des rallonges en heures supplémentaires. « Florian, ingénieur ».',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
    [
        'combo' => 47,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez décidé d\'offrir un voyage à votre ami pour son anniversaire. Écrivez un message pour lui expliquer ce que vous avez préparé : la destination, les dates et les autres détails du voyage.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Un internaute a publié le message suivant : « Je vais partir étudier un an à l\'étranger et j\'ai peur ». Rédigez une réponse pour partager votre expérience personnelle. Parlez des défis que vous avez rencontrés, des solutions que vous avez trouvées, et des bénéfices que vous avez tirés de cette expérience.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'La réduction du temps de travail',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La réduction du temps de travail permet aux employés de mieux équilibrer leur vie professionnelle et personnelle. En travaillant moins, ils peuvent consacrer plus de temps à leur famille, à leurs loisirs et à leur santé. Cela peut également augmenter leur satisfaction au travail et réduire le stress, améliorant ainsi leur productivité globale.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La réduction du temps de travail peut bénéficier aux entreprises en diminuant l\'absentéisme et en améliorant la rétention des employés. Avec des horaires plus flexibles, les employés sont souvent plus motivés et engagés. Cependant, cela nécessite une bonne organisation et une adaptation des processus pour maintenir l\'efficacité et répondre aux besoins de l\'entreprise.',
                    ],
                ],
                'correction' => '',
            ],
        ],
    ],
];
