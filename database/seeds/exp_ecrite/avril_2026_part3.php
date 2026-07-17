<?php
declare(strict_types=1);

/** Combinaisons 14 à 19 — Avril 2026 */
return [
    [
        'combo' => 14,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez changer la décoration de votre appartement (meubles, peinture, objets, etc.). Vous écrivez un message à un(e) ami(e). Vous lui décrivez votre projet et vous lui demandez de vous aider.',
                'correction' => "Objet : Besoin de ton aide pour la déco de mon appart\n\nSalut Aly,\n\nJ'espère que tu vas bien ! Je suis en train de repenser la déco de mon appartement, et j'aurais besoin de ton aide. Je compte changer les meubles, repeindre les murs, et ajouter quelques objets déco pour rendre l'espace plus moderne et chaleureux. Tu as toujours eu un super goût pour ça, et j'adorerais avoir ton avis et ton aide pour choisir les couleurs et les éléments.\n\nSerais-tu dispo ce week-end pour venir m'aider à planifier tout ça ? Ça serait génial de le faire ensemble !\n\nMerci d'avance,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => '"École de musique ! Cours gratuits, concerts, jeux. Rendez-vous vendredi, à partir de 9 heures" Vous avez participé à cet évènement. Vous écrivez à vos amis pour raconter votre expérience et vous donnez votre opinion sur cette journée.',
                'correction' => "3 raisons d'assister à l'école de musique gratuite\n\nVendredi dernier, j'ai participé à l'événement de l'école de musique qui offrait des cours gratuits, des concerts, et des jeux. L'ambiance était conviviale et animée.\n\nJ'ai commencé la journée par un cours d'initiation à la guitare, où j'ai appris les bases de cet instrument. Ensuite, j'ai assisté à des concerts variés, du classique au jazz, qui étaient vraiment inspirants. Les jeux musicaux ont ajouté une touche de plaisir, rendant l'expérience encore plus interactive. J'ai particulièrement apprécié l'échange avec les musiciens et les autres participants, créant une vraie atmosphère communautaire.\n\nJe recommande vivement cette activité. C'est une excellente opportunité pour découvrir la musique, s'amuser, et rencontrer des passionnés. Ne ratez pas la prochaine édition !",
            ],
            [
                'task' => 3,
                'prompt' => 'Faut-il faire ses courses dans des petits magasins ou dans des supermarchés ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Le supermarché est très pratique ; on y trouve une grande variété de produits, tous à portée de main. Vous pouvez garer votre voiture dans le parking et faire le tour des rayons pour acheter tout ce dont vous avez besoin : fruits, légumes, fromages, viandes, boissons... De plus, les supermarchés offrent plusieurs marques pour un même produit, tout en proposant régulièrement des promotions et des remises.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "ASSOCIATION POUR LA SAUVEGARDE DES PETITS COMMERCES Le défi « Février sans supermarché » a été créé pour limiter la superpuissance des supermarchés et, par conséquent, permettre aux petits commerces de survivre et de réaliser des chiffres d'affaires plus conséquents. Ce défi consiste à boycotter les supermarchés pendant une durée d'un mois, en faisant toutes ses courses dans les épiceries de quartier. Le client aura tout à gagner : il bénéficiera non seulement de produits frais de meilleure qualité, mais aura également l'opportunité de papoter avec les voisins.",
                    ],
                ],
                'correction' => "Supermarchés ou petits commerces : où faire ses courses ?\n\nLe débat entre faire ses courses dans des supermarchés ou dans des petits magasins divise. Le Document 1 met en avant la commodité des supermarchés, avec leur large choix de produits et leurs promotions. Le Document 2 défend, quant à lui, les petits commerces, en soulignant l'importance de soutenir ces établissements de proximité.\n\nÀ mon avis, les deux approches ont leurs avantages. Les supermarchés offrent une grande diversité de produits à des prix compétitifs, ce qui est pratique pour les consommateurs pressés. Cependant, le Document 2 a raison de rappeler l'importance des petits commerces pour la vitalité des quartiers. En plus de proposer des produits souvent plus frais et de meilleure qualité, les petits commerces favorisent les relations sociales et le maintien d'un tissu économique local. Pour cette raison, il serait judicieux de trouver un équilibre entre les deux, en privilégiant les supermarchés pour les courses courantes, tout en soutenant les petits magasins pour des produits spécifiques et pour renforcer les liens communautaires.",
            ],
        ],
    ],
    [
        'combo' => 15,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je cherche un vélo en bon état et bon marché. Contactez- moi par courriel : mathieu@gmail.com Vous avez un vélo à vendre. Vous écrivez un courriel pour décrire votre vélo et proposer un prix. Vous lui donnez un rendez-vous pour essayer le vélo.',
                'correction' => "Bonjour Mathieu,\n\nJ'ai vu votre annonce et je pense que mon vélo pourrait vous intéresser. Il s'agit d'un VTT en bon état, avec un cadre solide et des pneus récemment changés. Il dispose également de 18 vitesses et de freins fonctionnels. Je le vends à 120 €, un prix raisonnable pour sa qualité.\n\nSi vous souhaitez l'essayer, nous pouvons convenir d'un rendez-vous ce samedi après-midi près du parc central. Faites-moi savoir si cela vous convient ou si une autre date vous arrange.\n\nDans l'attente de votre réponse,\n\nCordialement,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé une journée à la campagne avec vos amis. À votre retour, vous écrivez un message sur votre forum pour raconter à vos amis comment cette journée s\'est passée. Vous expliquez ce que vous avez aimé (activités, lieu, animaux, etc...).',
                'correction' => "Une Journée Magique à la Campagne : Détente et Découverte\n\nEnvie de changer d'air ? Une escapade à la campagne est parfaite pour s'éloigner du stress quotidien. Entre paysages apaisants et activités authentiques, la nature offre un cadre idéal pour se ressourcer.\n\nAvec mes amis, nous avons vécu une journée inoubliable. Nous avons commencé par une randonnée à travers des prairies fleuries, suivie d'une visite d'une petite ferme où nous avons nourri des animaux adorables comme des chèvres et des poules. Après cela, nous avons savouré un délicieux pique-nique composé de produits locaux, entourés d'un panorama époustouflant. L'air pur et la tranquillité nous ont fait un bien fou.\n\nJe recommande vivement cette expérience à tous ceux qui veulent se déconnecter et profiter des plaisirs simples de la vie. Une journée à la campagne, c'est une bouffée d'oxygène garantie !",
            ],
            [
                'task' => 3,
                'prompt' => 'La sévérité des parents envers les enfants',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Je vais bientôt avoir 22 ans et j'habite toujours chez mes parents. Mon père et ma mère restent autoritaires avec moi, même si je suis majeure. Quand j'étais mineure, je n'avais pas le droit de dormir dehors, ni même de dépasser 21h lorsque je sortais avec des amies. Maintenant, peu de choses ont changé ; certes, j'ai le droit de veiller plus tard la nuit, mais ma mère ne cesse de m'appeler sur mon téléphone portable jusqu'à ce que je sois de retour.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Les parents ont parfois peur d'être trop sévères avec leurs enfants. Ils craignent qu'à cause d'un excès d'autorité, leurs enfants ne s'épanouissent pas et manquent plus tard de personnalité. Même si les parents acceptent, par amour, tout ce que leurs enfants demandent, cela pourrait avoir des effets négatifs lorsqu'ils passent à l'âge adulte. En effet, pour vivre en communauté, il y a certaines règles à respecter.",
                    ],
                ],
                'correction' => "La Sévérité Parentale : Entrave ou Nécessité ?\n\nDans le débat sur l'autorité parentale, fermeté et indulgence s'opposent. Le Document 1 met en lumière l'expérience d'un enfant soumis à des règles strictes, même à l'âge adulte. En revanche, le Document 2 souligne les conséquences possibles d'un excès de permissivité sur l'épanouissement des enfants et leur adaptation sociale.\n\nÀ mon avis, un équilibre est nécessaire entre sévérité et bienveillance. Une autorité excessive, comme décrit dans le Document 1, peut limiter l'autonomie des enfants et créer des tensions familiales. Cependant, la permissivité absolue évoquée dans le Document 2 peut priver les enfants de repères essentiels à la vie en société. La solution réside dans une éducation basée sur le respect et la communication, permettant aux parents de guider leurs enfants tout en les préparant à devenir des adultes responsables et autonomes.",
            ],
        ],
    ],
    [
        'combo' => 16,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Rédigez un message pour inviter votre ami(e) à passer ses vacances dans votre ville en précisant les lieux et les endroits à visiter.',
                'correction' => "Salut Jean,\n\nJ'espère que tu vas bien ! J'aimerais t'inviter à passer tes vacances dans ma ville. C'est un endroit magnifique avec beaucoup à offrir : des plages splendides, des restaurants délicieux, et une vie nocturne animée. Je te ferai découvrir les meilleurs coins et les petites merveilles cachées. Ce serait génial de passer du temps ensemble et de te montrer pourquoi j'aime tant cet endroit. J'ai hâte de te voir ici !\n\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un concours pour gagner un séjour de deux semaines dans votre ville préférée. Le thème de ce concours est "Mon artiste préféré". Écrivez un article de blog pour parler de votre artiste préféré.',
                'correction' => "Mon Artiste Préféré : Vincent Van Gogh\n\nRécemment, j'ai participé à un concours pour gagner un séjour de deux semaines dans ma ville préférée. Le thème était \"Mon artiste préféré\". J'ai choisi Vincent Van Gogh, dont l'œuvre m'inspire profondément.\n\nVan Gogh est célèbre pour ses œuvres emblématiques comme « La Nuit étoilée » et « Les Tournesols ». Son utilisation des couleurs et ses techniques uniques captivent mon imagination. Dans mon article, j'ai décrit ma visite au Musée Van Gogh à Amsterdam, où j'ai eu la chance d'admirer ses œuvres de près. C'était une expérience inoubliable qui a renforcé mon admiration pour son art.\n\nJe recommande à tous les amateurs d'art de découvrir les œuvres de Van Gogh. Sa capacité à transmettre des émotions à travers ses peintures est extraordinaire et offre une perspective unique sur la beauté du monde.",
            ],
            [
                'task' => 3,
                'prompt' => 'La Chasse aux animaux : Pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "« Je suis de ceux qui n'arrivent pas à comprendre comment l'on peut prendre du plaisir à tuer des animaux. Je suis de ceux qui n'arrivent pas à comprendre comment on peut prétendre aimer la nature alors qu'on la détruit » Gala, 29 ans",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "« Les gens chassent pour différentes raisons : la subsistance, le commerce, la conservation et l'aménagement de la faune, la protection de la propriété, l'exercice, le loisir et le prestige. » David, journaliste de la FRM",
                    ],
                ],
                'correction' => "Chasse aux Animaux : Passion ou Destruction ?\n\nDans le débat sur la chasse, les opinions divergent. Le Document 2 exprime une opposition ferme, soulignant le paradoxe de prétendre aimer la nature tout en la détruisant. À l'inverse, le Document 2 met en lumière les diverses motivations derrière la pratique de la chasse.\n\nÀ mon sens, chaque point de vue présente des aspects importants. Les opposants à la chasse soulignent un respect profond pour la vie animale et la nature, refusant toute forme de violence. Cependant, le Document 1 défend la chasse comme une pratique multifacette, incluant la subsistance, la gestion de la faune et le loisir. Plutôt que de condamner ou d'approuver unilatéralement, il est crucial de considérer les contextes culturels et économiques spécifiques. La diversité des perspectives peut mener à des solutions équilibrées, respectant à la fois les besoins humains et la protection de la faune.",
            ],
        ],
    ],
    [
        'combo' => 17,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Salut, j\'ai appris que tu vas à une salle de sport et qu\'elle est magnifique. Peux-tu m\'en dire plus ? ». Écrivez un message pour répondre à votre ami concernant ce sujet.',
                'correction' => "Salut,\n\nC'est vrai, je vais à une salle de sport qui est vraiment top, et je te la recommande ! Elle est moderne, avec des équipements de qualité pour tous les types d'exercices : musculation, cardio, et même des cours collectifs comme le yoga ou le spinning.\n\nCe que j'aime particulièrement, c'est l'ambiance conviviale. Les coachs sont super sympas, toujours prêts à donner des conseils personnalisés, et les vestiaires sont impeccables. En plus, il y a un espace détente avec sauna, parfait pour se relaxer après l'effort.\n\nSi tu veux, je peux te donner plus d'infos ou t'accompagner pour une séance découverte. Je suis sûr que tu vas adorer !\n\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez lu sur un forum un débat concernant les formations en ligne. Écrivez un message en décrivant votre expérience (cours de langue, formation professionnelle, etc.). Donnez votre avis sur ce que vous avez aimé et ce que vous n\'avez pas.',
                'correction' => "Formations en Ligne : Une Option Pratique ?\n\nLes formations en ligne gagnent en popularité grâce à leur flexibilité. Ayant suivi un cours de langue en ligne, je veux partager mon expérience pour éclairer ceux qui hésitent encore.\n\nJ'ai particulièrement apprécié la liberté d'apprendre à mon rythme, avec des vidéos interactives et des exercices pratiques. La flexibilité des horaires était un véritable atout pour gérer mon emploi du temps chargé. Cependant, j'ai regretté le manque d'interaction humaine. Les échanges avec les formateurs étaient limités, ce qui compliquait parfois la compréhension des notions complexes.\n\nJe recommande les formations en ligne pour leur accessibilité, surtout pour ceux qui recherchent une méthode pratique et autonome. Cependant, elles nécessitent une grande discipline et ne conviennent pas à ceux qui préfèrent un encadrement direct. Une solution idéale pour apprendre à distance, mais avec quelques limites à considérer selon vos besoins.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Produits Faits Maison : Pour Ou Contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Fabriquer vos propres produits biologiques à la maison offre un contrôle total sur les ingrédients, garantissant des options plus saines et personnalisées. Cela contribue également à réduire les déchets plastiques grâce à des emballages réutilisables. De plus, vous économiserez de l'argent à long terme et développerez des compétences créatives, favorisant ainsi un mode de vie plus durable.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Cependant, la fabrication de produits biologiques à domicile comporte des risques. Les erreurs de formulation peuvent entraîner des produits inefficaces ou irritants. De plus, le temps et les efforts nécessaires pour trouver et préparer les ingrédients peuvent être contraignants. Il y a également un manque de garantie de sécurité et de stabilité des produits faits maison, avec un risque accru de contamination bactérienne si les produits ne sont pas correctement conservés. Enfin, le coût initial élevé pour l'achat d'ingrédients de qualité peut être dissuasif pour certains.",
                    ],
                ],
                'correction' => "Produits Faits Maison : Une Bonne Idée ou un Pari Risqué ?\n\nLe débat sur les produits faits maison oppose deux perspectives. Le Document 1 met en avant leurs avantages : contrôle des ingrédients, réduction des déchets plastiques, économies à long terme et encouragement d'un mode de vie durable. À l'inverse, le Document 2 souligne les contraintes, comme les risques de contamination, le temps requis et le coût initial élevé des ingrédients.\n\nÀ mon avis, fabriquer ses produits maison est une démarche louable, offrant des bénéfices pour la santé et l'environnement, comme le souligne le Document 1. Cependant, les limites évoquées dans le Document 2 ne doivent pas être ignorées. Pour que cette pratique soit viable, il est essentiel de s'informer sur les formulations sécurisées et de commencer par des recettes simples. Cette approche raisonnée permettrait de profiter des avantages tout en minimisant les risques liés à la sécurité et à la qualité des produits.",
            ],
        ],
    ],
    [
        'combo' => 18,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous allez déménager à Nice, en France. Vous écrivez un message sur le site d\'une agence immobilière. Vous donnez les informations nécessaires (superficie, budget, nombre de pièces, etc).',
                'correction' => "Bonjour,\n\nJe me permets de vous écrire à propos de l'annonce que vous avez publiée en ligne concernant la location de votre appartement. Je suis très intéressé(e) par ce logement, mais j'aimerais obtenir quelques informations supplémentaires. Pourriez-vous me préciser la superficie exacte, les équipements inclus (lave-linge, meubles, etc.) et le montant des charges ?\n\nPar ailleurs, pourriez-vous me donner quelques détails sur le quartier : commerces à proximité, accès aux transports en commun, ambiance générale ?\n\nJe suis disponible pour une visite à votre convenance. En vous remerciant par avance pour votre réponse, je vous souhaite une excellente journée.\n\nCordialement,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez commencé à prendre des cours dans une école de langues. Vous envoyez un message à vos amis pour leur raconter comment s\'est passée votre première semaine. Parlez de votre impression sur l\'école et sur vos cours.',
                'correction' => "Une école, une langue, une aventure\n\nApprendre une langue dans une école spécialisée, ce n'est pas juste suivre des cours. C'est vivre une expérience immersive, rencontrer des personnes du monde entier et progresser grâce à des méthodes efficaces.\n\nJ'ai suivi un stage intensif de français dans une école à Montréal pendant un mois. Le matin, on étudiait la grammaire et le vocabulaire. L'après-midi, on participait à des activités variées : théâtre, visites, discussions en groupe… L'ambiance était chaleureuse, les professeurs attentifs, et mes camarades très motivés. J'ai rapidement gagné en aisance à l'oral.\n\nSi vous rêvez d'apprendre une langue autrement, je vous recommande vivement ce type d'école. C'est une façon rapide et agréable de progresser, de gagner en confiance et de vivre une belle aventure humaine. Vous reviendrez avec des souvenirs, des progrès… et de nouvelles amitiés !",
            ],
            [
                'task' => 3,
                'prompt' => 'Caméras de surveillance : pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "De nos jours, il y a de plus en plus de caméras de surveillance dans les villes. La vidéosurveillance est appréciée par les Français car cela leur donne un sentiment de sécurité. D'après un sondage, 75 % d'entre eux sont pour le développement de la vidéosurveillance. Ils seraient d'accord pour être filmés, mais seulement dans l'espace public : la rue, les magasins ou encore les transports. Cependant, ils refusent d'être surveillés par leur employeur. En France, peu de caméras de vidéosurveillance sont installées sur le lieu de travail.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Certaines enquêtes montrent que la vidéosurveillance coûte cher et que ses résultats sont insuffisants. D'une part, la présence des caméras est facile à remarquer. Par conséquent, la vidéosurveillance est inutile pour éviter les vols, la consommation de drogue et la violence dans les villes. D'autre part, l'utilisation de la vidéosurveillance demande beaucoup de personnel. Une étude allemande montre que sept personnes par caméra sont nécessaires pour analyser les informations.",
                    ],
                ],
                'correction' => "La vidéosurveillance : sécurité renforcée ou liberté menacée ?\n\nDans le débat sur la vidéosurveillance, deux points de vue s'opposent. Le Document 1 met en avant le sentiment de sécurité qu'elle procure, largement approuvé par la population. En revanche, le Document 2 critique son coût élevé et son efficacité limitée face aux problèmes urbains.\n\nÀ mon avis, la vidéosurveillance peut être utile si elle est utilisée avec discernement. Elle rassure les citoyens, surtout dans les lieux publics où les risques sont plus élevés. Toutefois, comme le souligne le Document 2, sa présence ne suffit pas à elle seule pour réduire la criminalité. De plus, son coût en matériel et en ressources humaines pose question. La surveillance doit donc rester ciblée et limitée à certains espaces. Il est essentiel de préserver l'équilibre entre sécurité et respect des libertés individuelles.",
            ],
        ],
    ],
    [
        'combo' => 19,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami(e) veut découvrir la région dans laquelle vous habitez. Écrivez-lui un message pour lui proposer des sites à visiter.',
                'correction' => "Salut Noman,\n\nJ'espère que tu vas bien ! Je suis ravi que tu souhaites découvrir la région où j'habite. Voici quelques sites à visiter : le Vieux Port avec ses charmants cafés, la plage de la Pointe des Sables pour un après-midi de détente, le musée d'art contemporain pour les amateurs de culture, et une randonnée au Mont des Étoiles pour une vue imprenable.\n\nJ'ai hâte de partager ces moments avec toi. Fais-moi savoir quand tu seras disponible !\n\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un cours de sport dans une salle. Écrivez un article dans votre blog parlant de cette expérience et en exprimant également votre avis par rapport à cette salle.',
                'correction' => "Une Expérience Sportive Revigorante\n\nLa semaine dernière, j'ai participé à un cours de sport dans une salle de fitness moderne. Située en centre-ville, cette salle propose divers cours, du yoga au crossfit. L'atmosphère est conviviale et motivante, avec des coachs professionnels et attentifs.\n\nMon expérience a été extrêmement positive. J'ai choisi un cours de circuit training, combinant cardio et musculation. Dès mon arrivée, j'ai été accueilli chaleureusement par le personnel. Le coach m'a expliqué les exercices et corrigé ma posture, me permettant de progresser rapidement et en toute sécurité.\n\nJe recommande vivement cette salle de sport à ceux qui cherchent à améliorer leur condition physique dans un environnement dynamique et professionnel. Que vous soyez débutant ou confirmé, vous y trouverez des cours adaptés et des coachs à l'écoute pour vous accompagner dans vos objectifs.",
            ],
            [
                'task' => 3,
                'prompt' => 'Livraison Des Repas Au Bureau : Pour Ou Contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Grâce à la livraison en entreprise, les employés bénéficieront d'un gain de temps notable. Il n'est plus question d'aller sortir loin du lieu de travail pour trouver de quoi manger. En complément, l'argent et l'énergie économisés permettent d'être encore plus efficace au travail. Sans pour autant mettre fin à une session importante liée au travail, le repas sera déjà prêt et pourra attendre la fin d'une conférence, d'une réunion ou d'un rendez-vous. Il s'agit d'une véritable solution dédiée aux entreprises ayant une activité intense et qui requièrent la présence continue de leurs employés.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Cette pratique révèle souvent des inconvénients à cause de sa notoriété montante. Certains jours, il arrive que les responsables de livraisons peuvent être envahis par un grand nombre de livraisons à faire et cela risque de générer des perturbations liées au stress de l'attente. De même pour les employés, une trop longue heure de travail peut causer un état de fatigue si ce dernier ne quitte pas son bureau pour le repas. Dans tous les cas, il est recommandé de toujours marquer des temps de pause lors des durs labeurs.",
                    ],
                ],
                'correction' => "Repas au Bureau : Allier Productivité et Bien-être grâce à une Livraison Équilibrée\n\nDans le débat sur la livraison des repas au bureau, les avis divergent. Le Document 1 souligne les avantages en termes de gain de temps et d'efficacité pour les employés. En revanche, le Document 2 met en avant les inconvénients liés aux retards potentiels et au manque de pauses.\n\nÀ mon sens, chaque aspect de la livraison des repas au bureau présente des avantages et des inconvénients distincts. La livraison permet aux employés de rester concentrés et de gagner du temps, ce qui peut améliorer la productivité. Cependant, comme le souligne le Document 2, il est essentiel de ne pas négliger l'importance des pauses pour éviter la fatigue et le stress. Plutôt que de les opposer, je préconise une approche équilibrée, où la livraison des repas est utilisée de manière judicieuse, tout en encourageant les employés à prendre des pauses régulières. Cette méthode permettrait d'optimiser le bien-être et l'efficacité au travail, créant un environnement sain et productif.",
            ],
        ],
    ],
];
