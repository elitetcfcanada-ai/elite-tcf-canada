<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 17,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez trouvé un appartement intéressant et comptez déménager. Informez un ami par écrit en lui donnant tous les détails essentiels (type de logement, localisation et prix).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté avec un ami à un festival de musique gratuit organisé dans votre ville. Écrivez un article de blog pour partager votre expérience et vos impressions.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Diplôme ou expérience pour l\'emploi des jeunes ?',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Beaucoup de jeunes sortant de l\'université se retrouvent sans emploi. La valeur des diplômes sur le marché du travail pose des questions. Souvent, les recruteurs leur reprochent d\'avoir trop étudié et trop peu d\'expérience, ou considèrent leur jeunesse comme un frein. Plus de 60 % des diplômés restent sans travail un an après leurs études. Il paraît donc urgent de mieux reconnaître les diplômes et de faciliter l\'insertion professionnelle.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'On parle souvent de dirigeants célèbres qui ont réussi sans suivre de cursus universitaire. Ces exemples peuvent laisser penser qu\'il suffit d\'être talentueux pour diriger une entreprise. Certains chefs d\'entreprise considèrent même que l\'université peut freiner la créativité. Selon eux, elle pousse les étudiants à suivre des parcours standards et limite l\'innovation. Pour les futurs entrepreneurs, rien ne remplacerait l\'expérience pratique et l\'autoformation, sans dépendre d\'une institution académique.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 18,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Rédigez un courriel à votre ami francophone afin de solliciter son soutien pour trouver un logement. Pensez à préciser les informations essentielles (genre de logement, budget, date).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Votre établissement vous a confié la mission d\'organiser une journée d\'accueil pour les nouveaux étudiants francophones. Écrivez un courriel à leur intention en précisant toutes les informations utiles pour le bon déroulement de cette rencontre.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'La vie à la campagne ou en ville',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Selon moi, vivre en ville offre beaucoup d\'occasions de se divertir : aller au cinéma, manger dans un restaurant, faire les magasins… Tout est accessible sans devoir parcourir de longues distances ni chercher un taxi pour un trajet. On trouve facilement tout ce dont on a besoin. De plus, les amateurs de culture y trouvent aussi leur bonheur : musées, théâtres, opéras… tout est présent !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Récemment, j\'ai choisi de quitter la vie urbaine pour m\'installer à la campagne, car je ressentais le besoin de me rapprocher de la nature et de profiter de la tranquillité. Aujourd\'hui, au lieu de sortir chaque jour dans un café ou un restaurant, je préfère inviter mes amis sur ma terrasse ou organiser parfois un barbecue dans mon jardin. Une autre raison qui m\'a poussé à faire ce choix, c\'est le prix des logements : à la campagne, les maisons sont beaucoup plus abordables. Avec mon budget actuel, je profite d\'une grande maison avec terrasse et jardin, alors qu\'en ville je n\'avais qu\'un petit appartement au cinquième étage.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 19,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous occupez un grand appartement et recherchez un colocataire. Racontez la manière dont vous imaginez la colocation et présentez les particularités de votre logement.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Une compagnie de théâtre est venue dans votre ville et vous avez assisté à l\'une de ses représentations. Écrivez un article de blog pour raconter cette expérience.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Influence de la publicité sur les enfants : pour ou contre ?',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'D\'après une enquête, les enfants sont en permanence confrontés à de nombreuses publicités diffusées à la télévision, dans les journaux ou sur Internet, souvent dans des espaces où ils sont vulnérables. Celles-ci ciblent principalement les jouets, les jeux vidéo et les aliments peu sains, ce qui influence leurs décisions et leurs demandes familiales.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Un travail de recherche paru dans une revue scientifique montre que les enfants comprennent difficilement les messages publicitaires, même s\'ils savent généralement que leur but est de persuader. Pourtant, d\'autres facteurs comme l\'éducation reçue des parents, la société et la culture ont un poids plus fort sur leurs choix de consommation que la publicité.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 20,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je vais bientôt vivre dans ton quartier. Je cherche un endroit sympathique pour faire mes courses. Est-ce que tu connais un marché intéressant ?Merci d\'avance et à bientôt !Bernard. »

Vous répondez à votre ami Bernard. Dans votre message, vous décrivez un marché de votre quartier que vous aimez bien (lieu, horaires, produits, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous faites partie d\'une association de quartier qui propose des activités aux enfants (aide aux devoirs, sorties, jeux, etc.). Sur votre site internet, vous racontez votre expérience et vous expliquez pourquoi ce type d\'association est utile.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Le livre papier ou le livre numérique ?',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Depuis plusieurs années maintenant, de nombreux lecteurs ont décidé de remplacer la bibliothèque traditionnelle par des livres numériques. Selon eux, l\'avantage est avant tout économique. D\'une part, le livre numérique permet d\'économiser du papier, d\'autre part la version numérique d\'un livre est généralement moins chère que la version papier. Les livres numériques ont un autre avantage : ils permettent une ouverture sur le monde pour les personnes en situation de handicap. Certaines options, comme la possibilité d\'augmenter la taille des lettres, facilitent la lecture pour les personnes malvoyantes.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Le livre numérique remplacera-t-il le livre papier ? « Non », répondront la plupart des lecteurs. Le livre papier est un beau support. Quel plaisir de le prêter aux gens qu\'on aime ou de l\'offrir en glissant un petit mot dedans ! Le livre papier a une histoire, l\'odeur du neuf ou de l\'ancien… Il transmet beaucoup d\'émotions alors que le livre numérique à un côté un peu impersonnel. De plus, les livres numériques demandent de posséder un minimum de connaissances en informatique, ce qui peut être une difficulté pour certaines personnes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 21,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Bonjour,

Je viens bientôt en vacances dans ta région. Est-ce que tu peux me conseiller une visite à faire pendant mon voyage ?Merci beaucoup.Mathieu »

Vous répondez à votre ami Mathieu. Vous décrivez un lieu à visiter dans votre région.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => '« Bonjour,Vous avez pris des cours dans notre salle de sport.Donnez-nous votre avis sur notre site Internet !www.masalledesport.org »

Sur le site Internet de la salle de sport, vous répondez à ce message. Vous racontez cette expérience et vous donnez votre avis.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Livraison de repas',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Céline, 25 ans : La livraison de repas permet de gagner du temps. Par exemple, quand on travaille, on peut se faire livrer un repas au bureau sans se déplacer. On perd moins de temps pendant la pause déjeuner, on rentre donc plus tôt chez soi. Généralement, cela offre plus de choix : si un de nos collègues veut commander une pizza mais que nous souhaitons manger des sushis, plus de disputes, il suffit de commander des plats dans différents restaurants. L\'autre avantage de ce nouveau mode de consommation est qu\'il est disponible à toute heure. En effet, on trouve toujours des restaurants qui restent ouverts 24 heures sur 24.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Ahmed, 40 ans : La livraison de repas plaît de plus en plus aux consommateurs qui n\'ont pas le temps ou l\'envie de cuisiner. Toutefois, ce service présente des inconvénients écologiques. Les repas sont souvent livrés en scooter ou en voiture, qui sont des modes de transports polluants. Cette habitude a aussi des conséquences négatives sur la vie sociale. Quand on mange des repas livrés au bureau ou à domicile, on a tendance à rester enfermé et à voir les mêmes personnes. Pourtant, il est important de sortir prendre l\'air et, pourquoi pas, de rencontrer d\'autres personnes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 22,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je cherche un endroit pour déjeuner en plein air ce week-end. Qu\'est-ce que tu me proposes ?À bientôt,Barbara. »

Vous répondez à votre amie Barbara en décrivant le lieu (parc, jardin, terrasse, etc.).',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez visité un nouveau pays pendant vos vacances. Sur un site internes, vous racontez votre expérience et vous donnez votre opinion sur ce pays.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'L\'Utilisation du Plastique, Pour ou Contre',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Franck soutient l\'utilisation limitée du plastique, soulignant son importance dans de nombreux secteurs, notamment les secteurs médical et alimentaire. Pour lui, le plastique est vital pour la conservation des aliments et la stérilisation des équipements médicaux. Il prône une utilisation responsable et le recyclage, mais reconnaît que certains usages du plastique sont indispensables pour la société moderne.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Amicha s\'oppose fermement à l\'utilisation du plastique, mettant en avant son impact environnemental dévastateur. Elle soutient que les déchets plastiques polluent les océans et les écosystèmes, causant des dommages irréparables. Amicha milite pour des alternatives écologiques et durables, insistant sur l\'urgence de renoncer au plastique pour protéger l\'environnement et la santé publique.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 23,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Dans le cadre d\'un dossier consacré aux habitants, le journal « Bienvenue » vous demande d\'écrire un article. Installé(e) récemment dans la ville, vous devez d\'abord vous présenter, puis décrire vos lieux favoris.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Après avoir participé au concert de votre chanteur préféré, vous rédigez un article sur votre blog afin de raconter ce moment et de donner envie à vos proches et aux lecteurs de venir à son prochain concert.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les jeux vidéo : entre risques et bienfaits',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'De l\'enfance jusqu\'à l\'adolescence, les enfants jouent souvent aux jeux vidéo et, avec le temps, cela peut provoquer des idées négatives et des comportements violents. Une étude récente réalisée auprès de jeunes de 9 à 18 ans qui jouent régulièrement montre que les jeux vidéo violents augmentent fortement l\'agressivité. D\'après Diego Gentil, ce phénomène serait inévitable, même avec un contrôle parental.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'On parle souvent des aspects négatifs des jeux vidéo, pourtant ils peuvent aussi avoir des effets positifs sur le cerveau et la santé en général. Par exemple, ils permettent de développer certaines capacités mentales comme l\'attention, l\'imagination et l\'analyse. Cela s\'explique par le fait que, lorsque vous jouez, vous devez réfléchir en permanence et résoudre des problèmes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 24,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez vu une annonce en ligne offrant de l\'aide aux personnes qui veulent apprendre le français, en les mettant en contact avec des partenaires pour pratiquer et améliorer leur niveau. Vous rédigez un courriel pour répondre, en vous présentant et en disant pourquoi vous voulez pratiquer le français.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à une fête de famille. Vous envoyez un message à vos amis pour leur parler de cette fête et expliquer ce que vous avez préféré.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Villes sans voitures : avantages et précautions',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'En raison de la pollution importante observée dans de nombreuses régions, certaines villes ont choisi d\'interdire les voitures dans les zones urbaines. La ville d\'Oslo, en Norvège, a appliqué cette mesure récemment et constate des effets positifs pour tous. Avec le temps, les accidents diminuent, la dépendance au pétrole est réduite et l\'air devient plus sain.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Beaucoup de villes lancent des projets visant à interdire la circulation automobile en centre-ville sans préparer les infrastructures indispensables. Certes, diminuer le trafic permettrait de réduire les bouchons, le stress et la pollution de l\'air. Mais il est également important de prévoir de grands parkings, de renforcer les transports publics, et de donner des autorisations particulières à certains professionnels, comme les services d\'urgence, la police ou les livreurs.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 25,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je cherche un vélo en bon état et bon marché. Contactez-moi par courriel : mathieu@gmail.com »

Vous avez un vélo à vendre. Vous écrivez un courriel pour décrire votre vélo et proposer un prix. Vous lui donnez un rendez-vous pour essayer le vélo.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un message à vos amis pour partager votre expérience de travail temporaire effectué durant les vacances d\'été.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Transports en commun : bénéfices et contraintes',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les transports en commun gratuits, c\'est une très bonne idée. Cela permet de diminuer le nombre de voitures en ville et d\'éviter les bouchons. C\'est aussi une bonne mesure pour réduire la pollution et donc les risques de maladies respiratoires. Dans ma ville, il y a des transports en commun gratuits. On observe que les gens utilisent plus souvent les transports publics depuis que la gratuité est en place. Cette mesure a aussi profité aux commerçants : les gens reviennent faire leurs achats dans le centre-ville et vont moins dans les hypermarchés.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'L\'idée n\'est pas bonne. D\'une part, cela coûterait cher aux villes. Or, il me semble que la ville de Toulouse a d\'autres problèmes à régler en priorité. Par exemple, il faudrait d\'abord réaménager les espaces verts, cela profiterait à tout le monde. D\'autre part, il vaudrait mieux réorganiser les transports publics au lieu de les rendre gratuits. Aujourd\'hui, les gens préfèrent prendre leur voiture parce que la ville manque de transports publics dans certains quartiers éloignés. Enfin, selon moi, il faut garder les transports publics payants. Les gens respecteront davantage les équipements s\'ils participent à leur financement en payant un titre de transport.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 26,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez convier vos amis à visiter un lieu touristique que vous appréciez beaucoup. Dans un message chaleureux, vous présentez ce lieu, dites ce que vous aimez et proposez une date, un horaire et un hébergement pour cette sortie.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un festival (musique, cinéma, gastronomie, etc.), mais vous avez été déçu par cette expérience. Vous rédigez un article sur votre blog pour dire ce qui ne vous a pas satisfait.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Ville ou campagne : deux modes de vie',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'À mon avis, vivre en ville permet de profiter de nombreuses activités de loisirs. Aller au cinéma, dîner au restaurant ou faire les magasins est très facile. Tout est à proximité, donc on n\'a pas besoin de se déplacer longtemps pour se divertir. On peut marcher un peu ou prendre un taxi si nécessaire. La ville offre également une grande variété d\'activités culturelles comme les musées, le théâtre ou l\'opéra, pour que chacun trouve son bonheur.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Il y a peu de temps, j\'ai choisi de m\'installer à la campagne après avoir quitté la ville, car je voulais vivre dans un environnement plus naturel et tranquille. Maintenant, plutôt que de sortir au restaurant ou dans les bars, j\'invite mes amis à la maison pour partager un verre sur la terrasse ou organiser un barbecue. Le prix des logements a également été un élément important de mon choix. À la campagne, les maisons coûtent beaucoup moins cher qu\'en ville. Avec le même budget, j\'ai pu acheter une grande maison avec un jardin, ce qui n\'aurait pas été possible en zone urbaine.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 27,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez fêter votre anniversaire dans un restaurant. Vous invitez vos amis. Vous leur écrivez un courriel pour leur donner toutes les informations nécessaires (lieu, date, menu, prix, etc.) et vous leur demandez une réponse.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez quitté la ville afin de vous installer à la campagne. Sur votre blog, vous expliquez pourquoi vous avez fait ce choix et vous présentez les avantages de votre nouvelle vie.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Caméras De Surveillance À L\'école : Pour Ou Contre ?',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Dans l\'école où j\'enseigne à Montréal, les caméras de surveillance sont omniprésentes. Leur présence permet de dissuader les élèves de commettre des actes de violence. Les enseignants, les parents et la majorité des élèves les acceptent volontiers, car ils se sentent rassurés quant à la sécurité des enfants et que cela permet aux enseignants de travailler dans de bonnes conditions. Toutefois, certains élèves considèrent que cela porte atteinte à leur vie privée.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Je suis opposé à l\'installation des caméras de surveillance dans nos écoles à Montréal. Les résultats obtenus dans les pays où ce système est utilisé ne sont pas convaincants. Les personnes mal intentionnées peuvent facilement contourner les caméras, qui sont très visibles. Les problèmes de discipline dans une école peuvent être résolus en améliorant la communication entre les enseignants, l\'administration et les élèves. Il est également nécessaire de faire respecter et expliquer les règles de l\'école par tous, plutôt que de recourir aux caméras.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 28,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à votre ami pour l\'inviter à vous accompagner au concert de votre musicien préféré en lui précisant tous les détails (heure, tarif, lieu…)',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Les professeurs de votre quartier veulent organiser une rencontre avec leurs élèves pour les orienter dans le choix de leurs futures professions, vous leur écrivez un mail pour leur donner les avantages de votre profession.',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Location Courte Durée',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La location courte durée offre flexibilité et commodité pour les voyageurs. Elle permet de louer un logement pour une période de temps limitée, que ce soit pour des vacances ou un déplacement professionnel. Cela permet aux voyageurs de profiter d\'un hébergement confortable avec des équipements adaptés à leurs besoins, tout en évitant les engagements à long terme.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La location courte durée peut parfois être coûteuse, en particulier dans les zones touristiques. De plus, la disponibilité peut être limitée, surtout pendant les périodes de forte demande. Il est donc important de planifier et de réserver à l\'avance pour obtenir les meilleurs résultats.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 29,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Salut, J\'aimerais bien prendre des cours de français. Est-ce que tu peux me donner des informations sur l\'école où tu es inscrit ? Merci beaucoup et à bientôt ! Anna »

Vous répondez à votre amie Anna. Dans votre message, vous décrivez l\'école où vous êtes inscrit(e) et que vous connaissez bien. Vous donnez les informations utiles (localisation, type de cours, prix, etc.)',
                'correction' => 'Salut Anna,

Je voulais te donner quelques informations sur l\'école où je suis inscrit(e) pour t\'aider à faire ton choix. L\'école est très bien située, près du centre-ville et facilement accessible en transports en commun. Les prix sont raisonnables et il existe des réductions pour les étudiants. Plusieurs types de cours sont proposés : cours intensifs, cours du soir et cours le week-end, selon ton emploi du temps. Les groupes sont petits, ce qui permet de bien progresser, et les professeurs sont qualifiés et à l\'écoute. Franchement, c\'est une très bonne option si tu veux améliorer ton français rapidement.

À très vite,Nabil',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous travaillez dans une association qui aidents les personnes âgées. Rédigez un un article de blog pour raconter vos expériences et convaincre d\'autres personnes de rejoindre l\'association.',
                'correction' => '5 raisons de s\'engager auprès des personnes âgées aujourd\'hui

S\'engager dans une association qui soutient les personnes âgées est une activité humaine et solidaire, de plus en plus importante dans notre société. Ces actions visent à rompre l\'isolement, à offrir de l\'écoute et à améliorer le quotidien de personnes souvent seules ou fragilisées.

Pour ma part, je fais partie de cette association depuis plusieurs mois. J\'ai participé à des visites à domicile, à des moments de discussion et à des activités simples comme la lecture ou les promenades. Ces échanges m\'ont beaucoup apporté sur le plan humain. J\'ai appris à écouter, à être patient et à mieux comprendre les réalités du vieillissement. Les sourires et la reconnaissance des personnes âgées donnent un vrai sens à cet engagement.

Je recommande vivement cette expérience à toute personne souhaitant se rendre utile. Quelques heures suffisent pour faire une vraie différence. S\'engager, c\'est donner un peu de son temps, mais recevoir énormément en retour.',
            ],
            [
                'task' => 3,
                'prompt' => 'Les animaux de compagnie pour les enfants, pour ou contre ?',
                'correction' => 'Animaux de compagnie pour enfants : cadeau éducatif ou responsabilité lourde ?

Dans le débat sur les animaux de compagnie pour enfants, les avis sont partagés. Le Document 1 met en avant les bienfaits affectifs et éducatifs pour l\'enfant, comme la confiance et l\'autonomie. À l\'inverse, le Document 2 insiste sur les responsabilités, les coûts et l\'engagement à long terme que cela implique.

À mon sens, offrir un animal de compagnie à un enfant peut être une expérience très positive, à condition d\'être bien encadrée. Comme le souligne le Document 1, l\'animal aide l\'enfant à lutter contre la solitude et à développer le sens des responsabilités, tout en lui apprenant le respect du vivant. Cependant, le Document 2 rappelle à juste titre qu\'un animal n\'est pas un simple jouet. Il demande du temps, de l\'argent et une implication quotidienne des parents. Avant d\'adopter, il est donc essentiel de réfléchir sérieusement et de s\'assurer que toute la famille est prête à s\'engager sur le long terme.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Offrir un animal de compagnie à un enfant présente de nombreux avantages, comme le soulignent beaucoup de psychologues. Pour des enfants qui n\'ont pas de frères et/ou de soeurs, l\'animal est un compagnon qui leur évitera la solitude. Grâce à lui, un enfant prendra confiance en lui et il apprendra vite qu\'un animal est un être vivant qui a besoin d\'attention et de respect. En sa présence, l\'enfant se sentira en sécurité et pourra agir de manière autonome, sans l\'aide de ses parents.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Beaucoup d\'enfants demandent, un jour ou l\'autre, un animal à leurs parents, le plus souvent un chien ou un chat. Mais même si vous avez envie de faire plaisir à votre enfant, il vaut mieux réfléchir sérieusement avant d\'acheter un animal domestique. L\'animal devient un nouveau membre de la famille et représente un engagement sur de nombreuses années. Or, avoir un animal coûte souvent très cher, et c\'est une grande responsabilité. On ne peut pas le traiter comme un jouet que l\'on met à la poubelle quand l\'enfant s\'en désintéresse.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 30,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Répondez au courriel de votre ami Lucas pour lui donner des informations sur les nouveaux locaux de votre entreprise (lieu, disposition des pièces, équipements, etc.)',
                'correction' => 'Salut Lucas,

Je voulais te tenir au courant de nos nouveaux locaux. Nous sommes désormais situés au 123 Rue de la République. Les bureaux sont modernes et spacieux, avec des espaces ouverts et lumineux. Nous avons une grande salle de réunion équipée des dernières technologies, un coin détente avec café et snacks, et même une salle de sport pour le personnel. Tout est pensé pour favoriser la productivité et le bien-être.

J\'ai hâte de te les faire visiter !

À bientôt,
Nabil',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à un événement intitulé « Une semaine sans voiture ». Racontez votre expérience et donnez votre impression sur cette initiative. Décrivez le déroulement de l\'événement (dates, lieu, activités proposées).',
                'correction' => 'Une Semaine Sans Voiture : Une Expérience Écoresponsable

La semaine dernière, j\'ai participé à l\'événement « Une semaine sans voiture » du 20 au 26 juillet dans notre ville. L\'initiative visait à encourager les habitants à laisser leurs voitures à la maison et à explorer des modes de transport plus écologiques. Des ateliers sur le vélo, des randonnées urbaines et des conférences sur la mobilité durable étaient au programme.

Personnellement, j\'ai adoré cette expérience. J\'ai redécouvert ma ville à pied et à vélo, ce qui m\'a permis de voir des endroits que je n\'avais jamais remarqués. Les ateliers de réparation de vélos étaient particulièrement utiles, et les discussions sur l\'impact environnemental des voitures ont été enrichissantes.

Je recommande vivement cette initiative à tous. C\'est une excellente manière de réduire notre empreinte carbone tout en découvrant des alternatives de transport plus saines et économiques.',
            ],
            [
                'task' => 3,
                'prompt' => 'Les vêtements de grande marque',
                'correction' => 'Vêtements de Marque : Entre Expression et Praticité

Dans le débat sur les vêtements de marque, deux perspectives s\'opposent. Le Document 1 valorise l\'importance des marques pour les enfants et adolescents comme moyen d\'expression et d\'appartenance sociale. En revanche, le Document 2 met en avant la rapidité avec laquelle les enfants grandissent et usent leurs vêtements.

À mon avis, chaque point de vue présente des arguments valides. Les vêtements de marque offrent aux jeunes une manière de s\'exprimer et de se sentir intégrés à un groupe. Toutefois, comme le souligne le Document 2, la croissance rapide des enfants et leur tendance à user leurs vêtements rapidement remettent en question la durabilité de ces achats. Plutôt que d\'opposer ces deux points de vue, je suggère une approche équilibrée : acheter des vêtements de marque pour des occasions spéciales tout en privilégiant des options plus économiques pour un usage quotidien. Cette solution permet aux enfants de profiter de l\'expression personnelle offerte par les marques tout en restant pragmatique face à la nature éphémère de leurs besoins vestimentaires.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les vêtements de marque sont très importants pour les enfants et les adolescents. C\'est un moyen de s\'exprimer et de se rattacher à un groupe social. Cette attirance pour les marques est très présente chez les adolescents qui se cherchent et montrent leur personnalité. Les enfants aiment également porter des vêtements de marque avec des images de dessins animés qu\'ils regardent ou des logos qu\'ils apprécient.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les enfants grandissent très vite et les vêtements sont portés pendant une courte période. Ainsi, les vêtements deviennent rapidement trop petits. Mais il y a aussi le fait que les enfants usent assez rapidement les vêtements en jouant à l\'extérieur avec les copains, en s\'amusant dans l\'herbe ou à l\'aire de jeux. Les habits sont très vite sales ou troués.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 31,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je cherche un vélo en bon état et bon marché. Contactez-moi par courriel : mathieu@gmail.com

Vous avez un vélo à vendre. Vous écrivez un courriel pour décrire votre vélo et proposer un prix. Vous lui Donnez un RDV pour essayer le vélo.',
                'correction' => '',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé une journée à la campagne avec vos amis. À votre retour, vous écrivez un message sur votre forum pour raconter à vos amis comment cette journée s\'est passée. Vous expliquez ce que vous avez aimé (activités, lieu, animaux, etc…).',
                'correction' => '',
            ],
            [
                'task' => 3,
                'prompt' => 'Les sévérité des parents envers les enfants',
                'correction' => '',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je vais bientôt avoir 22 ans et j\'habite toujours chez mes parents. Mon père et ma mère restent autoritaires avec moi, même si je suis majeure. Quand j\'étais mineure, je n\'avais pas le droit de dormir dehors, ni même de dépasser 21h lorsque je sortais avec des amies. Maintenant, peu de choses ont changé ; certes, j\'ai le droit de veiller plus tard la nuit, mais ma mère ne cesse de m\'appeler sur mon téléphone portable jusqu\'à ce que je sois de retour.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les parents ont parfois peur d\'être trop sévères avec leurs enfants. Ils craignent, qu\'à cause d\'un excès d\'autorité, leurs enfants ne s\'épanouissent pas et manquent plus tard de personnalité. Même si les parents acceptent, par amour, tout ce dont leurs enfants demandent, cela pourrait avoir des effets négatifs lorsqu\'ils passent à l\'âge adulte. En effet, pour vivre en communauté, il y a certaines règles à respecter.',
                    ],
                ],
            ],
        ],
    ],];
