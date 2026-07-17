<?php
declare(strict_types=1);

/** Combinaisons 37 à 42 — Mai 2026 */
return [
    [
        'combo' => 37,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez déménagé dans une nouvelle ville il y a un mois. Rédigez un courriel à votre ami Léo pour lui donner de vos nouvelles. Dans votre message, décrivez votre appartement, le quartier, la ville, ainsi que les activités que vous avez découvertes.',
                'correction' => 'Objet : Mes nouvelles depuis mon déménagement

Salut Léo,

J’ai déménagé il y a un mois à Nantes et je m’y plais beaucoup. Mon appartement est petit mais très agréable : il y a une chambre lumineuse, un salon avec balcon et une cuisine moderne. Mon quartier est calme, avec une boulangerie, un marché et un parc à cinq minutes. La ville est vivante et jolie, surtout le centre historique et les bords de la Loire. J’ai déjà découvert plusieurs activités : je vais à la piscine le mardi soir, je fais du vélo le week-end et j’ai visité un musée samedi dernier. J’ai aussi testé un café-concert sympa près de chez moi. Et toi, comment vas-tu ?

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à une fête traditionnelle, dans votre pays ou à l’étranger. Rédigez un article pour votre blog dans lequel vous décrirez cette fête. Expliquez également pourquoi vous avez apprécié cet événement.',
                'correction' => '[Une soirée inoubliable à la fête des lanternes]

Bonjour à tous,

L’été dernier, pendant un voyage en Thaïlande, j’ai eu la chance d’assister à la célèbre fête des lanternes avec deux amis. L’événement a eu lieu le soir, près d’une rivière, dans une ambiance calme et magique. Dès notre arrivée, les rues étaient décorées de petites lumières, et il y avait de la musique traditionnelle, des stands de nourriture et beaucoup de familles en costume.

Le moment le plus beau a été quand tout le monde a allumé sa lanterne avant de la laisser s’envoler dans le ciel. J’ai ressenti une grande émotion en voyant des centaines de lumières briller au-dessus de nous. Nous avons aussi goûté des spécialités locales et regardé un spectacle de danse.

J’ai vraiment apprécié cette fête parce qu’elle était à la fois joyeuse, paisible et très symbolique. J’ai découvert une belle tradition et j’ai partagé un moment unique avec mes amis.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Parcs Zoologiques : Pour Ou Contre ?',
                'correction' => 'Les parcs zoologiques aujourd’hui

Le débat sur les zoos reste très actuel. Selon le premier document, ils protègent les espèces menacées, assurent de bons soins aux animaux et favorisent leur reproduction. En revanche, le second texte affirme que ces lieux enferment les animaux loin de leur habitat naturel et de leur liberté. (49 mots)

À mon avis, les zoos peuvent être utiles, mais seulement s’ils respectent des règles très strictes. D’abord, ils jouent un rôle important dans la protection de certaines espèces rares. Par exemple, des programmes d’élevage ont permis de sauver plusieurs animaux menacés. Ensuite, ils peuvent aussi sensibiliser le public à la protection de la nature, surtout les enfants. De plus, certains zoos modernes créent des espaces plus adaptés et financent des actions de conservation. Enfin, je pense qu’un zoo ne doit pas être un simple lieu de divertissement. Si les animaux vivent dans de mauvaises conditions, il faut fermer ces établissements. La priorité doit toujours être le bien-être animal. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les zoos présentent de nombreux avantages, y compris pour les espèces en voie de disparition. Les animaux y sont bien soignés. Les études démontrent que les taux de reproduction ont augmenté grâce aux programmes menés dans les zoos, contribuant ainsi à la sauvegarde de certaines espèces.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les animaux sauvages ne devraient pas être confinés dans des zoos, car cela ne correspond pas à leur habitat naturel. Il décrit les zoos comme des prisons pour les animaux, citant l’exemple d’ours polaires maintenus dans des environnements où la température atteint 15 degrés. Enfin, les animaux nécessitent un environnement naturel et la liberté.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 38,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
                'correction' => 'Objet : Projet de week-end le mois prochain

Salut les amis,

Je vous écris pour vous proposer un week-end ensemble le mois prochain. J’aimerais partir à Annecy du samedi 15 au dimanche 16 juin. On pourrait y aller en train, départ vers 8 h, pour arriver en fin de matinée. Sur place, je pense réserver un petit hôtel près du lac, pas trop cher et bien situé. Le samedi, on pourrait faire une balade au bord du lac, louer des vélos et dîner dans un restaurant du centre-ville. Le dimanche, je propose une visite de la vieille ville puis un pique-nique avant le retour. Dites-moi vite si vous êtes disponibles et si cette idée vous plaît !

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : “voyage.internaute.fr”. Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
                'correction' => '[Mon année de voyage : une excellente idée]

Bonjour à tous,

Pour moi, tout quitter pendant un an a été une très bonne idée. En 2022, j’ai laissé mon appartement à Lyon et j’ai pris un billet pour l’Asie avec un ami d’enfance. Au début, j’avais peur de perdre mes habitudes et mon travail, mais j’avais aussi très envie de découvrir autre chose.

Nous avons commencé par la Thaïlande. À Chiang Mai, l’ambiance était calme, les marchés étaient colorés et la nourriture sentait les épices partout. Ensuite, nous sommes allés au Vietnam et au Japon. Chaque semaine, nous visitions un nouveau lieu, nous goûtions des plats différents et nous rencontrions des voyageurs du monde entier.

Ce voyage m’a appris à être plus indépendant et plus ouvert. Bien sûr, il y a eu des moments difficiles : j’ai raté un train à Kyoto et je me suis senti très stressé. Mais, dans l’ensemble, cette année a changé ma vie.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail : Favorable ou Défavorable ?',
                'correction' => 'La place du travail dans la vie moderne

Le travail occupe une place essentielle dans notre société. Le premier document souligne qu’il apporte souvent fatigue et manque de temps pour les proches; travailler moins permettrait donc de mieux vivre. Cependant, le second texte affirme que l’emploi construit aussi l’identité, les relations sociales et le sentiment d’utilité. (44 mots)

À mon avis, le travail est nécessaire, mais il ne doit pas envahir toute la vie. D’abord, il permet de gagner sa vie, d’être autonome et de participer à la société. Ensuite, il peut donner confiance en soi quand on apprend, progresse et se sent utile. De plus, le travail favorise les rencontres et crée parfois de vraies amitiés. Par exemple, beaucoup de jeunes trouvent leur premier réseau professionnel grâce à un stage. Enfin, je pense qu’un bon équilibre est indispensable. Quand les horaires sont trop lourds, la santé et la vie familiale souffrent rapidement. Il faut donc défendre un travail plus humain, avec du temps pour se reposer, vivre et rester proche de ses proches. (116 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Le travail est au centre de notre vie. Dès l’enfance, on entend souvent la question : « Qu’est-ce que tu veux faire quand tu seras grand ? ». Le travail devrait être synonyme de réussite et de satisfaction, mais il est trop souvent synonyme de fatigue et d’emprisonnement. Aujourd’hui, beaucoup pensent que l’on ne passe pas assez de temps avec sa famille, ses amis. Il est urgent de revoir la place occupée par le travail dans notre société. Certains pensent que travailler moins permettrait d’avoir plus de temps libre pour mieux vivre.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Certaines personnes ont décidé d’arrêter de travailler pour changer de mode de vie. Pourtant, aujourd’hui, travailler, c’est exister. La question : « Qu’est-ce que tu fais dans la vie ? » revient souvent lors d’une première rencontre. Elle prouve que l’emploi fait partie de notre identité. D’après le spécialiste Jean-Daniel Remond, la vie en entreprise est très importante. Les contacts quotidiens, les réseaux, les amitiés, l’impression d’être utile, mais aussi les difficultés, tout cela contribue à construire notre personnalité et notre identité.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 39,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez assister à un festival de cinéma dans votre ville. Vous écrivez un message à votre ami(e) pour lui proposer de venir avec vous. Vous lui donnez toutes les informations nécessaires sur l’événement (films, dates et horaires, tarifs, etc.).',
                'correction' => 'Objet : Festival de cinéma ce week-end

Salut Lina,

J’aimerais te proposer de venir avec moi au festival de cinéma de notre ville. Il aura lieu du 14 au 16 juin au cinéma Lumière, en centre-ville. Le programme est très intéressant : vendredi à 19 h, il y a un film canadien; samedi à 16 h, une comédie française; et dimanche à 18 h, un documentaire suivi d’une discussion avec le réalisateur. Le billet coûte 8 euros par séance, mais le pass pour les trois jours est à 20 euros. On peut aussi acheter les places en ligne. Si tu es d’accord, on pourrait y aller samedi après-midi et dîner ensemble après le film.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez accueilli un(e) étudiant(e) étranger(e) pendant une semaine chez vous. Sur votre blog, vous écrivez un article pour raconter cette semaine. Vous expliquez pourquoi vous avez aimé cette expérience.',
                'correction' => 'Une semaine inoubliable avec un étudiant étranger

Bonjour à tous,

La semaine dernière, j’ai accueilli chez moi Lucas, un étudiant espagnol venu dans ma ville pour un échange universitaire. Au début, j’étais un peu stressé, car je ne savais pas si nous allions bien nous entendre. Finalement, tout s’est très bien passé. Dès le premier soir, nous avons discuté longtemps de nos habitudes, de nos études et de nos cultures. L’ambiance était chaleureuse et naturelle.

Pendant la semaine, je lui ai montré le centre-ville, le marché local et un petit musée près de chez moi. Nous avons aussi préparé un dîner ensemble, et il m’a appris à cuisiner une omelette espagnole. Le soir, nous regardions des films et nous riions beaucoup.

J’ai vraiment aimé cette expérience, parce qu’elle m’a permis de découvrir une autre culture sans voyager. En plus, Lucas était gentil, curieux et respectueux. Cette semaine a été simple, mais très enrichissante pour nous deux.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La Restauration Rapide.',
                'correction' => 'La restauration rapide aujourd’hui

La restauration rapide occupe une place importante dans la vie moderne. Le premier document souligne la diversité des plats, le respect de l’hygiène et la liberté laissée au client pour composer un menu équilibré. Cependant, le second texte insiste sur les risques pour la santé et sur la pollution liée aux emballages plastiques. (49 mots)

À mon avis, la restauration rapide peut être utile, mais elle ne doit pas devenir une habitude. D’abord, elle permet de gagner du temps, surtout pour les étudiants et les salariés pressés. Ensuite, certains établissements proposent aujourd’hui des salades, des fruits ou des sandwichs plus légers. De plus, chacun reste responsable de ses choix alimentaires, car il est possible d’éviter les boissons trop sucrées ou les portions excessives. Par exemple, une personne qui choisit une salade, de l’eau et un yaourt fera un repas plus raisonnable. Enfin, si l’on mange trop souvent dans ces restaurants, on risque de nuire à sa santé et d’augmenter les déchets. Il faut donc consommer ces repas avec modération. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les restaurants rapides se distinguent par leur engagement à proposer une variété de plats équilibrés, respectant strictement les normes d’hygiène. En laissant aux clients la liberté de composer leur propre menu, ces établissements les responsabilisent dans leurs choix alimentaires, tout en satisfaisant leurs préférences gustatives.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les spécialistes affirment que manger régulièrement dans des restaurants de fast-food, qui proposent de la restauration rapide, est dangereux pour la santé. La nourriture servie est souvent la même : frites, hamburgers et boissons sucrées. Ces aliments contiennent une grande quantité de calories, bien trop pour un seul repas. De plus, la plupart des produits dans ces restaurants sont emballés dans du plastique. Par conséquent, manger dans un fast-food augmente la production de déchets plastiques, ce qui est nuisible pour l’environnement.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 40,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami(e) va fêter son anniversaire. Écrivez un message à vos amis pour lui acheter un cadeau commun.',
                'correction' => 'Objet : Cadeau commun pour l’anniversaire de Sarah

Salut les amis,

Comme vous le savez, Sarah va fêter son anniversaire samedi prochain, le 18 mai. Je vous propose d’acheter un cadeau commun pour lui faire plaisir. Comme elle aime beaucoup la musique et les sorties, on pourrait lui offrir un casque audio de bonne qualité ou bien un bon pour un concert à Montréal le mois prochain. Ce serait un cadeau utile et original. Si vous êtes d’accord, chacun peut participer avec 10 ou 15 euros. Merci de me dire avant jeudi soir si vous voulez participer, pour que je puisse acheter le cadeau vendredi. N’hésitez pas à proposer d’autres idées aussi !

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'La direction d’une école de musique cherche un endroit où organiser une fête pour 100 personnes. Écrivez un courriel pour les informer que vous avez trouvé un local (lieu, tarifs, service, etc…).',
                'correction' => 'Bonjour à tous,

Le week-end dernier, j’ai visité un local qui pourrait convenir parfaitement pour la fête de notre école de musique. Il se trouve au centre-ville, près de la gare, donc l’accès est très pratique pour tout le monde. J’y suis allé avec un ami qui avait déjà organisé un anniversaire dans cette salle, et j’ai été très impressionné.

Le lieu était spacieux, lumineux et bien décoré. Il pouvait accueillir environ 100 personnes sans problème. Le responsable m’a expliqué que le tarif était de 850 euros pour la soirée, avec les tables, les chaises et le nettoyage inclus. Il proposait aussi un service de boissons et quelques amuse-bouches pour un prix raisonnable.

J’ai beaucoup aimé l’ambiance chaleureuse et l’espace réservé pour la musique et la danse. Franchement, j’ai eu une très bonne impression, et je pense que ce local serait idéal pour notre événement.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La sévérité des enfants',
                'correction' => 'Autorité parentale et liberté des jeunes

La question de la sévérité des parents envers leurs enfants suscite des avis différents. Dans le premier document, une jeune femme de 22 ans dénonce des règles encore très strictes imposées par sa mère. En revanche, le second document rappelle qu\'une éducation trop permissive peut nuire à l\'apprentissage des règles sociales. (46 mots)

À mon avis, les parents doivent fixer des limites, mais sans contrôler excessivement leurs enfants. D\'abord, des règles claires protègent les jeunes et leur apprennent le respect des autres. Ensuite, une trop grande sévérité peut créer de la peur, du mensonge et des conflits dans la famille. De plus, quand un enfant grandit, il a besoin de confiance pour devenir autonome. Par exemple, autoriser un adolescent à sortir, tout en demandant une heure de retour raisonnable, me paraît plus utile que l\'appeler sans arrêt. Enfin, le dialogue est essentiel, car il permet d\'expliquer les règles et d\'aider l\'enfant à les accepter plus facilement. Ainsi, l\'équilibre entre autorité et liberté reste la meilleure solution. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je vais bientôt avoir 22 ans et j\'habite chez mes parents. Malgré ma majorité, mes parents restent autoritaires avec moi. Lorsque j\'étais mineure et que je sortais avec des amies, je n\'avais pas le droit de dormir dehors ni même de dépasser 21 h. Aujourd\'hui, peu de choses ont changé. Certes, j\'ai le droit de veiller plus tard, mais ma mère ne cesse de m\'appeler sur mon téléphone portable jusqu\'à ce que je sois de retour chez nous.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les parents ont parfois peur d\'être trop sévères avec leurs enfants. Ils craignent qu\'à cause d\'un excès d\'autorité, leurs enfants ne s\'épanouissent pas et manquent de personnalité plus tard. Même si, par amour, les parents acceptent tout ce que leurs enfants demandent, cela pourrait avoir des effets négatifs lorsqu\'ils passent à l\'âge adulte. En effet, pour vivre en communauté, il est nécessaire de respecter certaines règles.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 41,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous êtes locataire d’un appartement trop grand pour vous. Écrivez une annonce dans un journal pour chercher un colocataire (superficie de l’appartement, caractère du colocataire, prix, etc…).',
                'correction' => 'Objet : Recherche d’un colocataire pour grand appartement

Bonjour,

Je cherche un colocataire pour partager mon appartement, devenu trop grand pour moi seul. Il se trouve à Lyon, près du métro Jean Macé, dans un quartier calme et pratique. L’appartement fait 78 m², avec deux chambres, un grand salon, une cuisine équipée, une salle de bain et un balcon. La chambre à louer est lumineuse et meublée. Le loyer est de 450 euros par mois, charges comprises. Je cherche une personne sérieuse, propre, calme et respectueuse, de préférence non-fumeuse. Si vous êtes étudiant ou jeune salarié, c’est parfait. L’appartement sera disponible à partir du 1er juin.

Cordialement,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous venez de commencer une nouvelle activité de loisir (sport, danse, etc.). Écrivez un article sur votre blog pour parler de cette expérience.',
                'correction' => 'Ma première expérience de danse latine

Bonjour à tous,

La semaine dernière, j’ai commencé un nouveau loisir : la salsa. J’ai suivi mon premier cours dans une école de danse près de chez moi, vendredi soir, avec une amie qui voulait essayer aussi. Au début, j’étais un peu stressé, car je ne connaissais personne et je pensais que ce serait trop difficile. La salle était grande, lumineuse, et l’ambiance était très chaleureuse. Le professeur expliquait les pas lentement, puis nous avons répété en musique. J’ai d’abord fait plusieurs erreurs, mais je me suis rapidement senti plus à l’aise. Nous avons changé de partenaire plusieurs fois, ce qui m’a permis de parler avec d’autres élèves. Tout le monde souriait et encourageait les débutants. À la fin du cours, j’étais fatigué, mais vraiment content. Cette expérience m’a donné envie de continuer, parce qu’elle était à la fois sportive, amusante et très motivante.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La Gratuité Des Musées : Pour Ou Contre ?',
                'correction' => 'La gratuité des musées en débat

La question de la gratuité des musées divise l’opinion publique. Le premier document souligne les risques de surfréquentation et la baisse des ressources financières, en proposant des tarifs réduits. En revanche, le second texte défend cette mesure, car elle favorise l’accès de tous à la culture et à l’éducation. (46 mots)

À mon avis, la gratuité des musées est une mesure positive, mais elle doit être bien organisée. D’abord, elle permet aux familles modestes, aux étudiants et aux jeunes de découvrir l’art plus facilement. Ensuite, elle encourage la curiosité et l’apprentissage, ce qui renforce la culture générale du public. De plus, visiter un musée sans payer peut donner envie d’y retourner plus souvent. Par exemple, lors des journées gratuites, beaucoup de personnes visitent des lieux qu’elles ne connaissaient pas. Enfin, pour éviter les problèmes financiers et la foule, l’État peut compenser les pertes et limiter l’accès à certains horaires. Ainsi, la gratuité reste, selon moi, un véritable outil culturel et éducatif. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La gratuité des musées peut entraîner une surfréquentation, ce qui nuit à la qualité de l’expérience des visiteurs. De plus, cela peut réduire les ressources financières des musées, affectant ainsi leur entretien et la préservation des œuvres. Un tarif réduit serait une meilleure solution pour rendre la culture accessible tout en soutenant les musées.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La gratuité des musées est une excellente initiative. Cela rend la culture accessible à tous, indépendamment de leur situation financière. J’ai pu visiter plusieurs musées gratuitement, ce qui m’a permis d’enrichir mes connaissances sans me soucier du coût. Pour moi, la gratuité des musées contribue à l’éducation du public et au partage du patrimoine culturel.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 42,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Je vais bientôt vivre dans ton quartier. Je cherche un endroit sympathique pour faire mes courses. Est-ce que tu connais un marché intéressant ? Merci d’avance et à bientôt ! Bernard Vous répondez à votre ami Bernard. Dans votre message, vous décrivez un marché de votre quartier que vous aimez bien (lieu, horaires, produits, etc.).',
                'correction' => 'Objet : Un marché très sympa dans mon quartier

Salut Bernard,

Oui, je connais un marché très agréable dans mon quartier : le marché Saint-Martin, en plein centre-ville, près de la mairie. J’y vais presque chaque semaine parce qu’il est animé, propre et les commerçants sont vraiment sympathiques. Il a lieu le mercredi et le samedi matin, de 8 h à 13 h. On y trouve des fruits et légumes frais, du fromage, du pain, du poisson, des fleurs et même des produits bio. Les prix sont corrects et la qualité est bonne. Il y a aussi un petit café juste à côté, pratique après les courses. Je pense que ce marché va beaucoup te plaire.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous faites partie d’une association de quartier qui propose des activités aux enfants (aide aux devoirs, sorties, jeux, etc.). Sur votre site internet, vous racontez votre expérience et vous expliquez pourquoi ce type d’association est utile.',
                'correction' => '[Une journée inoubliable avec les enfants du quartier]

Bonjour à tous,

L’an dernier, au printemps, j’ai participé à une journée organisée par notre association de quartier dans la salle municipale et au parc voisin. Nous étions six bénévoles avec une quinzaine d’enfants de 7 à 12 ans. Le matin, nous avons commencé par l’aide aux devoirs dans une ambiance calme et chaleureuse. Certains enfants avaient des difficultés, mais ils ont vite repris confiance grâce aux explications et aux encouragements. Ensuite, nous avons partagé un pique-nique au parc. Il faisait beau, les enfants riaient, jouaient au ballon et couraient partout.

L’après-midi, nous avons proposé des jeux collectifs et un atelier de dessin. J’ai été très touché de voir des enfants timides parler davantage et participer avec joie. Cette expérience m’a vraiment marqué, car notre association est utile: elle aide les enfants à apprendre, à se socialiser et à passer du temps dans un cadre sûr. Elle soutient aussi les familles du quartier.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Le Livre Papier ou Le Livre Numérique ?',
                'correction' => 'Le livre papier et le livre numérique

Aujourd’hui, les lecteurs hésitent entre le livre papier et le livre numérique. Le premier document souligne surtout le prix plus bas du format numérique, son intérêt écologique et son accessibilité pour les personnes malvoyantes. Cependant, le second insiste sur le charme du livre papier, plus émotionnel, et sur les difficultés techniques du numérique. (50 mots)

À mon avis, les deux formats ont leur place, mais le livre papier reste préférable dans la vie quotidienne. D’abord, il offre un vrai confort de lecture, sans écran ni batterie. Ensuite, il crée un lien affectif plus fort, car on peut le conserver, l’annoter ou l’offrir. De plus, il convient à tous, même aux personnes peu à l’aise avec la technologie. Par exemple, mes grands-parents lisent beaucoup, mais ils trouvent les liseuses compliquées et fatigantes. Enfin, le livre numérique reste très utile pour voyager ou transporter plusieurs ouvrages facilement. Je pense donc qu’il faut défendre le papier sans refuser les avantages pratiques du numérique. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Depuis plusieurs années maintenant, de nombreux lecteurs ont décidé de remplacer la bibliothèque traditionnelle par des livres numériques. Selon eux, l’avantage est avant tout économique. D’une part, le livre numérique permet d’économiser du papier, d’autre part la version numérique d’un livre est généralement moins chère que la version papier. Les livres numériques ont un autre avantage : ils permettent une ouverture sur le monde pour les personnes en situation de handicap. Certaines options, comme la possibilité d’augmenter la taille des lettres, facilitent la lecture pour les personnes malvoyantes.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Le livre numérique remplacera-t-il le livre papier ? « Non », répondront la plupart des lecteurs. Le livre papier est un beau support. Quel plaisir de le prêter aux gens qu’on aime ou de l’offrir en glissant un petit mot dedans ! Le livre papier a une histoire, l’odeur du neuf ou de l’ancien… Il transmet beaucoup d’émotions alors que le livre numérique a un côté un peu impersonnel. De plus, les livres numériques demandent de posséder un minimum de connaissances en informatique, ce qui peut être une difficulté pour certaines personnes.',
                    ],
                ],
            ],
        ],
    ],
];
