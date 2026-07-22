<?php
declare(strict_types=1);

/** Combinaisons 31 à 36 — Mai 2026 */
return [
    [
        'combo' => 31,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous envisagez de déménager et avez trouvé un appartement. Vous souhaitez en informer un ami en lui fournissant les détails concernant le bien en question : nombre de pièces, emplacement et prix.',
                'correction' => 'Objet : Mon futur appartement

Salut Karim,

J’espère que tu vas bien. Je voulais t’informer que j’envisage de déménager bientôt, car j’ai trouvé un appartement qui me plaît beaucoup. Il se trouve à Montréal, dans le quartier de Rosemont, près du métro et de plusieurs commerces. C’est un logement de trois pièces : un salon lumineux, une chambre confortable et une petite cuisine bien équipée. Il y a aussi un balcon, ce qui est vraiment agréable. Le quartier est calme et pratique pour aller au travail. Le loyer est de 1 250 dollars par mois, ce qui reste raisonnable pour l’emplacement. Si tout se passe bien, je signerai le bail la semaine prochaine.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'Une annonce de festival de musique gratuit a été publiée dans votre ville, et vous avez profité de cette occasion pour y assister avec votre ami. Écrivez un article de blog pour raconter l’expérience que vous avez vécue lors de cet événement musical.',
                'correction' => '[Un festival gratuit inoubliable]

Salut à tous,

Le week-end dernier, dans le parc central de ma ville, j’ai assisté à un festival de musique gratuit avec mon ami Karim. Dès notre arrivée, il y avait déjà beaucoup de monde, et l’ambiance était très joyeuse. Le soleil se couchait doucement, les lumières de la scène brillaient, et on entendait de la musique partout.

Nous avons d’abord écouté un groupe de rock local qui a mis le public en énergie. Ensuite, une chanteuse de musique pop est montée sur scène, et j’ai vraiment adoré sa voix. Entre deux concerts, nous avons mangé des frites et bu des boissons fraîches près des stands. Il y avait aussi des familles, des jeunes et des touristes, ce qui rendait l’atmosphère encore plus vivante.

J’ai beaucoup aimé cette soirée parce qu’elle était simple, festive et bien organisée. Je me suis senti libre, détendu et heureux de partager ce moment avec mon ami.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'L\'emploi des jeunes : diplôme ou expérience ?',
                'correction' => 'Diplôme et expérience chez les jeunes

L’accès des jeunes à l’emploi pose aujourd’hui un vrai problème. Le premier document souligne que beaucoup de diplômés restent au chômage, faute d’expérience et malgré leurs études. En revanche, le second affirme que l’université peut freiner la créativité et que l’expérience directe serait plus utile pour réussir. (47 mots)

À mon avis, diplôme et expérience sont complémentaires, mais le diplôme reste une base essentielle. D’abord, il permet d’acquérir des connaissances solides et reconnues, surtout dans des métiers techniques comme la santé, le droit ou l’ingénierie. Ensuite, l’expérience aide à appliquer ces savoirs dans des situations concrètes et à développer l’autonomie. De plus, les employeurs recherchent souvent des jeunes capables de travailler rapidement en équipe. Un stage ou un emploi étudiant peut donc faire la différence. Enfin, opposer totalement études et expérience me semble excessif. Par exemple, un jeune infirmier a besoin d’un diplôme pour exercer, mais aussi de pratique pour être vraiment efficace auprès des patients. Il faut donc mieux relier formation et terrain. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'De nombreux jeunes diplômés de l\'université se retrouvent au chômage. La reconnaissance des formations universitaires sur le marché du travail soulève des questions. En effet, les jeunes rencontrent souvent des difficultés pour trouver un emploi : les recruteurs leur reprochent d\'avoir trop de diplômes et pas assez d\'expérience, ou bien leur jeunesse constitue un obstacle. Plus de 60 % des jeunes diplômés n\'ont toujours pas trouvé de travail un an après la fin de leurs études. Dans ce contexte, il semble urgent de revaloriser les diplômes et de favoriser l\'emploi et la croissance.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'On entend souvent parler de célèbres chefs d’entreprise qui ont réussi sans aller à l’université. L’exemple de ces personnalités riches et admirées pourrait laisser croire qu’il suffit d’être brillant pour diriger une entreprise. Certains dirigeants estiment même que les études universitaires peuvent être un frein. Selon eux, l’université enseigne le conformisme et limite la créativité en incitant les étudiants à suivre des parcours classiques. Pour les futurs créateurs d’entreprise, rien ne vaudrait l’autoformation et l’expérience, sans l’aide d’aucune structure académique.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 32,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Envoyez un courriel à votre ami francophone afin de lui demander de l\'aide pour la recherche d\'un logement, en lui fournissant toutes les informations nécessaires (type de logement, budget, date).',
                'correction' => 'Objet : Besoin de ton aide pour trouver un logement

Salut Thomas,

J’espère que tu vas bien. Je t’écris parce que je vais bientôt m’installer à Montréal pour mes études et j’aurais besoin de ton aide pour trouver un logement. Je cherche de préférence un studio ou une petite chambre dans une colocation, dans un quartier calme et bien desservi par les transports. Mon budget est de 700 à 850 dollars par mois, charges comprises si possible. J’aimerais emménager à partir du 1er septembre. Si tu connais quelqu’un qui loue un appartement ou si tu peux regarder des annonces pour moi, ce serait vraiment gentil. Merci beaucoup pour ton aide.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'Votre école vous a chargé d\'organiser une journée spéciale pour accueillir les nouveaux étudiants francophones. Vous rédigez un courriel destiné à ces étudiants dans lequel vous donnez tous les détails pour le bon déroulement de cette journée.',
                'correction' => 'Bonjour à tous,

La semaine dernière, notre école a organisé une journée spéciale pour accueillir les nouveaux étudiants francophones, et j’ai eu le plaisir de préparer cet événement avec quelques camarades et des professeurs. Nous nous sommes retrouvés dès 8 heures dans le hall principal, qui était décoré avec des affiches colorées et une ambiance très chaleureuse. D’abord, nous avons présenté l’école, les salles de classe, la bibliothèque et la cafétéria pendant une visite guidée. Ensuite, nous avons partagé un petit-déjeuner convivial, ce qui a permis à tout le monde de faire connaissance plus facilement.

L’après-midi, nous avons organisé des jeux en équipe et un atelier de conversation en français. Au début, certains étudiants étaient un peu timides, mais ils ont rapidement souri et participé avec enthousiasme. J’ai trouvé cette journée très réussie, car l’atmosphère était détendue, amicale et rassurante. J’ai vraiment été heureux de voir les nouveaux étudiants se sentir déjà un peu chez eux.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'La vie à la campagne ou en ville ?',
                'correction' => 'Ville et campagne : deux cadres de vie

Le choix entre la ville et la campagne suscite souvent des avis différents. Le premier document valorise la ville pour ses loisirs variés, sa proximité et son offre culturelle. En revanche, le second défend la campagne pour son calme, son contact avec la nature et des logements plus spacieux et moins chers. (48 mots)

Pour ma part, je pense que la campagne offre une meilleure qualité de vie à long terme. D\'abord, elle permet de vivre dans un environnement plus calme, ce qui réduit le stress et favorise le repos. Ensuite, l\'air y est souvent plus pur, ce qui est important pour la santé. De plus, les logements sont généralement plus grands et plus abordables. Par exemple, une famille peut y louer une maison avec jardin pour un prix raisonnable, alors qu\'en ville elle doit souvent se contenter d\'un petit appartement. Enfin, même si la ville propose plus d\'activités, les transports et Internet permettent aujourd\'hui de garder un bon accès aux services essentiels. (116 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'À mon avis, le fait de vivre en ville, cela vous donne la possibilité de vous divertir de plusieurs manières ; comme aller au cinéma, déjeuner dans un restaurant, faire du shopping... Tout est à côté, vous n’avez pas besoin de parcourir plusieurs kilomètres pour prendre un taxi (si le trajet est un peu long) et vous aurez à disposition tout ce dont vous désirez. Même les amoureux des évènements culturels sont servis ; musée, théâtre, opéra... tout y est !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Dernièrement, j’ai décidé de quitter la ville pour vivre à la campagne, car j’avais vraiment besoin de me rapprocher de la nature et de profiter du calme. Désormais, au lieu de partir quotidiennement au bar du coin ou au restaurant, j’invite des amis à boire un verre sur la terrasse de ma maison ou j’organise de temps en temps un barbecue dans mon jardin. Un autre détail d’importance m’a encouragé à prendre cette décision ; c’est la disponibilité des logements avec des prix largement inférieurs à ceux proposés en ville. Avec mon budget actuel, j’ai une grande maison avec terrasse et jardin privatif, alors qu’en ville, j’avais droit à un petit appartement au 5e étage d’un immeuble.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 33,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous habitez dans un grand appartement et vous recherchez un colocataire. Décrivez le type de colocation que vous proposez ainsi que les caractéristiques de votre appartement',
                'correction' => 'Objet : Recherche d’un colocataire pour grand appartement à Montréal

Bonjour Julien,

Je cherche un colocataire sérieux et sympathique pour partager mon grand appartement situé à Montréal, près du métro Berri-UQAM. Je propose une colocation calme, propre et respectueuse, idéale pour un étudiant ou un jeune salarié. Chacun garde son espace, mais on peut aussi partager quelques repas ou discuter de temps en temps.

L’appartement est très spacieux : il comprend trois chambres, un grand salon lumineux, une cuisine équipée, une salle de bain moderne et un balcon. Il y a aussi le Wi-Fi, une machine à laver et beaucoup de rangements. Le quartier est agréable, avec des commerces, un parc et des transports à proximité.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'Une troupe de théâtre s\'est installée dans votre ville, et vous avez assisté à l\'un de ses spectacles. Rédigez un article de blog pour décrire cette expérience.',
                'correction' => '**Une soirée théâtrale inoubliable**

Bonjour à tous,

La semaine dernière, une troupe de théâtre s’est installée dans ma ville, près de la place centrale, et j’ai eu la chance d’assister à son spectacle avec deux amis. La représentation a eu lieu samedi soir dans une petite salle municipale. Dès notre arrivée, l’ambiance était chaleureuse : il y avait beaucoup de monde, des lumières douces et une musique agréable.

La pièce racontait une histoire drôle et touchante sur une famille pleine de secrets. Les acteurs ont joué avec beaucoup d’énergie, et leurs costumes étaient magnifiques. À plusieurs moments, toute la salle a ri, puis un grand silence s’est installé pendant les scènes plus émouvantes. J’ai vraiment été impressionné par le talent de la troupe.

Après le spectacle, nous sommes restés quelques minutes pour discuter avec les comédiens. J’étais très content d’avoir vécu cette expérience, parce qu’elle était originale, vivante et pleine d’émotion.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'L\'influence de la publicité sur les enfants : pour ou contre ?',
                'correction' => 'La publicité et les enfants

La place de la publicité dans la vie des enfants suscite un débat important. Le premier document affirme qu’ils sont fortement exposés à des annonces, surtout pour des produits peu sains, ce qui influence leurs envies. Cependant, le second estime que leur consommation dépend davantage de l’éducation familiale et du contexte social. (48 mots)

À mon avis, l’influence de la publicité sur les enfants est réelle et ne doit pas être sous-estimée. D’abord, les enfants n’ont pas toujours le recul nécessaire pour distinguer information et stratégie commerciale. Ensuite, la répétition des messages crée des envies fortes, surtout pour les sucreries, les jouets et les jeux vidéo. De plus, même si les parents jouent un rôle essentiel, ils doivent souvent résister à des demandes fréquentes provoquées par les annonces. Par exemple, un enfant qui voit plusieurs fois la publicité d’une céréale colorée peut la réclamer au supermarché sans connaître sa valeur nutritionnelle. Enfin, il faut mieux encadrer la publicité destinée aux plus jeunes afin de protéger leur esprit critique. (119 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Une étude a révélé que les enfants sont constamment exposés à de nombreuses publicités, que ce soit à la télévision, dans les magazines ou sur Internet, et ce, dans des endroits vulnérables à l’influence publicitaire. Les enfants sont particulièrement ciblés par des publicités pour des produits alimentaires peu sains, des jouets, des jeux vidéo et autres produits, ce qui peut influencer leurs choix de consommation et leurs demandes envers leurs parents.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Un article de recherche publié dans une revue scientifique souligne que les enfants ont des capacités cognitives limitées pour comprendre et interpréter les messages publicitaires, et qu’ils ne sont généralement pas conscients du caractère persuasif de la publicité. Toutefois, d’autres facteurs tels que l’éducation parentale, les influences sociales et culturelles ont un rôle plus important dans les choix de consommation des enfants que la publicité elle-même.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 34,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à vos amis pour les inviter à un anniversaire surprise de votre meilleur ami. (Lieu, date, heure, etc.).',
                'correction' => 'Objet : Invitation à l’anniversaire surprise de Karim

Salut les amis,

J’espère que vous allez bien. Je vous écris pour vous inviter à l’anniversaire surprise de notre meilleur ami, Karim. La fête aura lieu le samedi 18 mai à 19 h 30, chez moi, au 25 rue Victor-Hugo, à Lyon. Merci d’arriver vers 19 h pour que tout soit prêt avant son arrivée à 19 h 45. Nous avons prévu un dîner, de la musique, des jeux et bien sûr un grand gâteau. Si vous voulez, vous pouvez aussi apporter une petite boisson ou un dessert. Surtout, gardez le secret pour que la surprise soit réussie ! Merci de me confirmer votre présence avant le 15 mai.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à une fête de famille. Envoyez un message à vos amis pour leur raconter cette fête et expliquez ce que vous avez le plus apprécié.',
                'correction' => 'Salut à tous,

Le week-end dernier, j’ai assisté à une grande fête de famille chez mes grands-parents, à la campagne. Nous étions presque trente personnes : mes oncles, mes tantes, mes cousins et même des voisins proches. Il faisait beau et le jardin était décoré avec des guirlandes colorées. Dès mon arrivée, j’ai senti une ambiance très chaleureuse.

Nous avons commencé par un grand déjeuner tous ensemble. Ensuite, les enfants ont joué dehors pendant que les adultes discutaient autour de la table. Plus tard, nous avons mis de la musique et tout le monde a dansé. Ma grand-mère a préparé plusieurs plats délicieux, mais j’ai surtout adoré son gâteau au chocolat, que nous avons mangé au moment du dessert.

Ce que j’ai le plus apprécié, c’est le temps passé avec toute la famille. J’étais vraiment heureux de voir autant de sourires, d’entendre des souvenirs amusants et de partager un moment aussi simple que joyeux.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'La restauration rapide',
                'correction' => 'La restauration rapide dans la vie quotidienne

La restauration rapide occupe une place importante dans notre société. Selon le premier document, elle présente des risques pour la santé à cause d\'aliments trop gras, trop sucrés et d’emballages polluants. Cependant, le second document souligne sa rapidité, sa praticité et le respect des règles d’hygiène. (45 mots)

À mon avis, la restauration rapide peut être utile, mais elle ne doit pas devenir une habitude. D’abord, ces repas sont souvent trop riches en sel, en sucre et en matières grasses, ce qui peut nuire à la santé à long terme. Ensuite, les emballages jetables posent un vrai problème écologique. De plus, manger trop vite empêche parfois d’adopter une alimentation équilibrée. Par exemple, un étudiant pressé peut choisir un hamburger chaque midi, alors qu’un sandwich maison serait plus sain et moins coûteux. Enfin, je pense qu’il faut réserver le fast-food aux situations exceptionnelles, comme un voyage ou une journée très chargée, et privilégier une cuisine simple au quotidien. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les spécialistes affirment que manger régulièrement dans des restaurants de fast-food, qui proposent de la restauration rapide, est dangereux pour la santé. La nourriture servie est souvent la même : frites, hamburgers et boissons sucrées. Ces aliments contiennent une grande quantité de calories, bien trop pour un seul repas. De plus, la plupart des produits dans ces restaurants sont emballés dans du plastique. Par conséquent, manger dans un fast-food augmente la production de déchets plastiques, ce qui est nuisible pour l\'environnement.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Au lieu de critiquer systématiquement la restauration rapide, nous devrions considérer son utilité. Dans un restaurant fast-food, il est possible d\'obtenir un repas chaud et de le savourer sur place en seulement quelques minutes, ce qui représente un avantage indéniable. Lorsque l\'on est pressé, il n\'est pas toujours possible de privilégier une cuisine traditionnelle. Ces restaurants, de par leur omniprésence que ce soit sur les autoroutes ou dans les centres-villes, offrent une solution pratique. De surcroît, ces établissements respectent généralement des normes d\'hygiène strictes, assurant la sécurité des consommateurs.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 35,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous allez fêter votre anniversaire. Vos amis vous demandent ce que vous souhaitez comme cadeaux. Vous voulez des vêtements, vous leur écrivez un message pour leur décrire les vêtements que vous aimeriez recevoir.',
                'correction' => 'Objet : Idées de cadeaux pour mon anniversaire

Salut les amis,

Merci de me demander ce qui me ferait plaisir pour mon anniversaire ! J’aimerais beaucoup recevoir des vêtements, surtout pour le printemps. Par exemple, j’aime les chemises simples, de couleur blanche, bleue ou beige, en taille M. J’aimerais aussi un jean confortable, plutôt bleu foncé, en coupe droite. Si vous préférez offrir un pull, je voudrais un modèle léger, pas trop large, en noir ou gris. J’aime aussi les baskets blanches, pointure 42, avec un style simple. En général, je préfère des vêtements classiques et pratiques, pas trop voyants. Merci beaucoup pour votre gentillesse !

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'www.manger-international.com Ce mois-ci, nous nous intéressons aux habitudes alimentaires dans le monde. Racontez-nous comment mangent les habitants de votre pays ! Les 10 premiers témoignages seront publiés sur notre site ! Vous avez lu cette annonce, vous écrivez un article pour les lecteurs du site “www.manger-international.com”. Vous expliquez comment mangent les habitants de votre pays et vous indiquez quelles habitudes vous plaisent ou vous déplaisent, et pourquoi.',
                'correction' => 'Les repas dans mon pays

Bonjour à tous,

Dans mon pays, j’ai toujours trouvé que les repas occupaient une place très importante dans la vie quotidienne. Le mois dernier, pendant un séjour dans ma ville natale, j’ai encore observé ces habitudes avec ma famille et mes voisins. Le matin, nous prenions souvent un petit-déjeuner simple, avec du pain, du thé et parfois des olives ou du fromage. À midi, l’ambiance était plus animée, surtout le week-end, car tout le monde se réunissait autour d’un grand plat chaud.

J’ai beaucoup aimé ce moment de partage, parce que les repas étaient conviviaux et généreux. Les habitants parlaient longtemps à table, riaient et prenaient leur temps. En revanche, ce qui m’a un peu déplu, c’est que certaines personnes mangeaient trop vite le soir ou consommaient beaucoup de pain et de boissons sucrées. À mon avis, ces habitudes ne sont pas très bonnes pour la santé.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Jeux Vidéo Ont-Ils Un Impact Sur Notre Comportement ?',
                'correction' => 'Les effets des jeux vidéo sur le comportement

Les jeux vidéo occupent une place importante dans la vie moderne. Le premier document affirme qu’ils améliorent l’orientation, la mémoire, la concentration et la rapidité, donc certaines capacités intellectuelles. Cependant, le second souligne un risque réel de dépendance, surtout chez les adolescents, avec des conséquences possibles sur les études et la vie sociale. (49 mots)

À mon avis, les jeux vidéo n’ont pas un effet unique sur le comportement; tout dépend du temps passé à jouer et du type de jeu choisi. D’abord, ils peuvent développer des qualités utiles, comme la réflexion, la coordination et la patience. Ensuite, ils offrent aussi un moyen de se détendre après le travail ou les cours. Par exemple, certains jeux de stratégie apprennent à prendre des décisions rapides. De plus, jouer en ligne peut renforcer l’esprit d’équipe quand les échanges restent respectueux. Enfin, le danger apparaît quand le joueur perd le contrôle de son temps. Dans ce cas, le jeu devient un problème. Il faut donc un usage modéré et encadré. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Beaucoup de gens pensent encore que les jeux vidéo nous rendent bêtes. Pourtant, de nombreuses études prouvent le contraire. Ainsi, selon une de ces études, les utilisateurs de jeux de course développeraient leur sens de l’orientation, leur mémoire et la précision de leurs mouvements. D’autres études montrent clairement une amélioration de la rapidité et de la concentration de tous les joueurs. Et c’est sans parler des jeux d’entraînement destinés aux personnes âgées pour consolider la mémoire et la vivacité d’esprit… Bref, c’est scientifique, le jeu vidéo rend plus intelligent !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les jeux vidéo, sur console ou sur ordinateur, chez soi ou en salle, seul, avec des amis ou en ligne avec des inconnus, peuvent provoquer une dépendance chez certaines personnes. On parle d’addiction quand le jeu vidéo devient le principal (ou l’unique) centre d’intérêt, à la place d’autres activités (relationnelles, professionnelles, scolaires, loisirs, sport…). Cette dépendance est particulièrement préoccupante lors de l’adolescence, période importante où les jeux vidéo peuvent avoir un impact négatif sur les résultats scolaires.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 36,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous venez de vous installer dans une nouvelle ville. Vous écrivez un message à un(e) ami(e) pour décrire votre nouvel environnement (quartier, voisins, magasins, etc.).',
                'correction' => 'Objet : Ma nouvelle vie à Québec

Salut Samir,

Je viens de m’installer à Québec, dans le quartier de Limoilou, et je m’y plais beaucoup. C’est un quartier calme, propre et très agréable à vivre. Dans ma rue, il y a plusieurs arbres et un petit parc où les familles se promènent le soir. Mes voisins sont sympathiques et m’ont déjà aidé à trouver une boulangerie et une pharmacie. À cinq minutes de chez moi, il y a un supermarché, un café, une librairie et même une petite salle de sport. Le centre-ville est à seulement quinze minutes en bus, donc c’est très pratique. J’aime surtout l’ambiance conviviale et le fait que tout soit proche.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une action pour la « Journée mondiale du nettoyage de notre planète ». Vous avez ramassé des déchets dans un lieu public (plage, forêt, rue, etc.) avec d’autres personnes. Vous racontez cette expérience à vos amis, vous expliquez pourquoi il est important de participer à ce type d’action.',
                'correction' => 'Une matinée utile pour la planète

Salut à tous,

Samedi dernier, j’ai participé à la Journée mondiale du nettoyage de notre planète avec des voisins et quelques amis. Nous nous sommes retrouvés tôt le matin près d’une petite plage au bord du lac. Il faisait beau, l’air était frais et l’ambiance était très motivante. Au début, j’étais surpris de voir autant de déchets : bouteilles en plastique, canettes, sacs et même de vieux jouets.

Pendant deux heures, nous avons ramassé les déchets, trié le verre et le plastique, puis rempli plusieurs grands sacs. Des passants nous ont regardés et certains nous ont même aidés quelques minutes. J’ai ressenti à la fois de la tristesse devant cette pollution et de la fierté en voyant la plage redevenir propre.

Cette expérience m’a vraiment marqué. Je pense qu’il est important de participer à ce type d’action, parce que chaque geste compte pour protéger la nature et donner un bon exemple aux autres.

À bientôt,

elite tcf canada',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Publicités, Pour Ou Contre ?',
                'correction' => 'La place de la publicité dans la société

La publicité suscite des avis opposés. Pour Quentin, elle est utile aux entreprises, informe sur les nouveautés, permet de profiter des promotions et finance même des jeux gratuits. En revanche, Estelle la juge envahissante, agaçante à la télévision et nuisible à l’environnement à cause des prospectus papier. (44 mots)

À mon avis, la publicité est utile, mais elle doit rester raisonnable. D’abord, elle aide les consommateurs à connaître les produits, les services et les réductions disponibles. Ensuite, elle soutient de nombreuses entreprises, surtout les petits commerces qui ont besoin de se faire connaître. De plus, certains contenus gratuits sur Internet existent grâce à elle, comme des applications ou des vidéos. Par exemple, j’ai découvert un magasin local grâce à une annonce en ligne, et j’ai pu acheter moins cher pendant une promotion. Enfin, je pense qu’il faut limiter les formes de publicité trop agressives, comme les coupures trop fréquentes à la télévision ou les papiers inutiles, afin de mieux respecter le public et l’environnement. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Certaines personnes trouvent la publicité ennuyeuse, mais, à mon avis, elle est indispensable pour le commerce et les entreprises. Grâce à la publicité, on fait connaître un produit ou un service. Et puis, parfois, elles sont drôles ! J’aime bien les écouter quand je suis en voiture. Cela me permet aussi d’être informé des nouveautés et des promotions. Personnellement, j’adore comparer les articles : je peux ainsi faire beaucoup d’économies sur mes achats. Enfin, beaucoup de personnes jouent à des jeux gratuits sur leur téléphone : sans publicité, ces jeux seraient tous payants. Quentin, 28 ans.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La publicité est présente partout dans notre vie de tous les jours : journaux, radios, télévision, téléphone, Internet… Par exemple, sur certaines chaînes de télévision, les émissions sont coupées par la publicité et c’est agaçant. Puis, recevoir des dizaines de kilos de papier de publicité par an dans la boîte aux lettres, ce n’est pas très respectueux de l’environnement ! Je pense qu’il est nécessaire de faire voter une loi pour réduire les publicités par courrier et à la télévision. Si la publicité était plus discrète, elle serait plus appréciée. Estelle, 35 ans.',
                    ],
                ],
            ],
        ],
    ],
];
