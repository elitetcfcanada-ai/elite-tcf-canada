<?php
declare(strict_types=1);

/** Combinaisons 61 à 67 — Mai 2026 */
return [
    [
        'combo' => 61,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'France Télévision prépare un reportage sur le sport amateur. Et vous, quel sportif êtes-vous ? Envoyez-nous vos témoignages sur francetélévision.fr.',
                'correction' => 'Objet : Mon témoignage sur le sport amateur

Bonjour,

Je souhaite partager mon expérience de sportif amateur. Depuis quatre ans, je pratique la course à pied à Lyon, surtout dans le parc de la Tête d’Or et le long des quais du Rhône. Je m’entraîne trois fois par semaine, le mardi et le jeudi à 19 h, puis le dimanche matin avec un petit groupe de mon quartier. Pour moi, le sport est très important : il me permet de rester en forme, de réduire le stress et de rencontrer des personnes motivées. Je participe aussi à des courses locales de 10 km, notamment au printemps. Même sans être professionnel, je ressens beaucoup de plaisir et de fierté.

Cordialement,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé des vacances dans une belle région de votre pays. Vous écrivez un message à vos amis dans lequel vous décrivez votre expérience, vous expliquez pourquoi vous avez beaucoup aimé ce séjour.',
                'correction' => 'Mes super vacances dans le sud

Salut à tous,

Le mois dernier, j’ai passé une semaine dans une très belle région du sud de mon pays, avec mes cousins. Nous avons loué une petite maison près des montagnes et d’un grand lac. Dès le premier jour, j’ai été impressionné par le calme, l’air pur et les paysages magnifiques. Le matin, nous faisions de longues promenades dans les villages. Les rues étaient fleuries, les habitants étaient accueillants et l’ambiance était vraiment paisible.

L’après-midi, nous avons nagé dans le lac, goûté des plats traditionnels et visité un marché local. Un soir, nous avons regardé le coucher du soleil depuis une colline: c’était un moment inoubliable. J’ai beaucoup aimé ce séjour parce que j’ai pu me reposer, rire avec ma famille et découvrir une région authentique. Je me suis senti libre, heureux et complètement dépaysé. Franchement, ces vacances m’ont fait beaucoup de bien.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Écoles Privées ou Publiques ?',
                'correction' => 'Écoles privées et écoles publiques : quels enjeux ?

Le choix entre école privée et école publique suscite un débat important. Le premier document explique que le privé attire surtout grâce à sa réputation, à un meilleur encadrement et à un public socialement favorisé. Cependant, le second souligne que ce modèle limite la mixité sociale et renforce les inégalités entre élèves. (47 mots)

À mon avis, l’école publique reste le meilleur choix pour construire une société plus juste. D’abord, elle accueille des élèves d’origines différentes, ce qui favorise la tolérance et l’ouverture d’esprit. Ensuite, elle permet à tous d’accéder à l’éducation, sans sélection par l’argent. De plus, la réussite scolaire ne dépend pas seulement du type d’établissement, mais surtout du travail de l’élève, du soutien familial et de la qualité des enseignants. Par exemple, beaucoup d’élèves issus du public obtiennent d’excellents résultats et réussissent ensuite dans des études supérieures. Enfin, même si le privé peut rassurer certains parents, il ne doit pas devenir un modèle qui sépare les jeunes selon leur milieu social. (115 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'À la rentrée, le nombre d’élèves inscrits dans des écoles privées a augmenté en France. Le succès des établissements privés n’est pas directement lié aux bons résultats scolaires de leurs élèves. C’est la réputation de ces lieux qui explique un tel enthousiasme. Aux yeux de nombreux parents, les élèves y sont mieux encadrés, mieux surveillés et les professeurs sont plus présents. Les parents sont aussi rassurés parce que les classes sont homogènes : elles accueillent généralement des élèves de milieux sociaux favorisés. En effet, les études dans ces établissements sont payantes, ce qui n’est pas le cas dans les écoles publiques.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Dans les collèges privés, il y a peu d’élèves de milieux sociaux défavorisés. Comme les études dans ces établissements sont payantes, certaines catégories de population en sont exclues. Ce système qui oppose écoles publiques et écoles privées ne facilite pas la mixité sociale. Ainsi, les élèves du privé ont rarement l’occasion de rencontrer d’autres jeunes issus de milieux sociaux moins favorisés qu’eux et inversement. Ce modèle scolaire reproduit les inégalités sociales et renforce le sentiment d’exclusion de certains jeunes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 62,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut, Je sais que tu vas dans une salle de sport. Qu’est-ce que tu penses de cette salle ? Peux-tu m’en dire un peu plus ? Je voudrais savoir si elle est bien ! Merci d’avance ! Joanna Vous répondez à votre amie Joanna. Dans votre message, vous décrivez la salle de sport que vous fréquentez (activités, horaires, prix, etc.).',
                'correction' => 'Objet : Ma salle de sport

Salut Joanna,

Je vais à la salle FitZone, à Lyon, depuis six mois et je la trouve vraiment bien. Elle est propre, moderne et l’ambiance est agréable. On peut faire de la musculation, du cardio, du vélo, du tapis de course et aussi des cours collectifs comme le yoga, la zumba et le pilates. La salle est ouverte du lundi au dimanche, de 6 h à 22 h, donc c’est pratique pour s’organiser. L’abonnement coûte 35 euros par mois, avec un accès à tous les équipements. Il y a aussi des vestiaires propres et des coachs disponibles pour aider. Franchement, je te la conseille.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez suivi une formation (cours de langue, informatique, etc.). Sur un site Internet, vous écrivez un message pour raconter votre expérience (cours, participants, professeurs, etc.). Vous expliquez ce que vous avez aimé ou pas aimé pendant cette formation.',
                'correction' => 'Mon expérience dans une formation de français

Bonjour à tous,

L’an dernier, j’ai suivi une formation de français dans un centre près de chez moi, pendant trois mois. Nous étions une quinzaine de participants, de différents âges et de plusieurs pays, donc l’ambiance était très vivante. Les cours avaient lieu le soir, après le travail, ce qui était pratique mais parfois fatigant.

J’ai surtout aimé la méthode du professeur. Il expliquait clairement, corrigeait nos erreurs avec patience et proposait beaucoup d’activités orales. Grâce à cela, j’ai pris confiance et j’ai osé parler davantage. J’ai aussi apprécié les échanges avec les autres apprenants, car nous nous sommes souvent entraidés.

En revanche, je n’ai pas aimé la salle de classe, qui était trop petite et un peu bruyante. De plus, certains exercices de grammaire étaient répétitifs. Malgré cela, cette formation a été une très bonne expérience, utile et motivante pour moi.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les produits faits maison',
                'correction' => 'Les produits faits maison : avantages et limites

Les produits faits maison attirent de plus en plus de personnes. Le premier document souligne leurs atouts : composition contrôlée, ingrédients naturels et réduction des déchets. Cependant, le second rappelle certains dangers : mauvais choix d’ingrédients, manque d’hygiène, nécessité de bien s’informer et temps de préparation souvent important. (48 mots)

À mon avis, les produits faits maison sont intéressants, mais ils ne conviennent pas à tout le monde. D’abord, ils permettent de mieux connaître ce que l’on utilise chaque jour, ce qui est rassurant pour la santé et pour l’environnement. Ensuite, ils peuvent réduire les dépenses et éviter les emballages inutiles. De plus, préparer certains produits simples est accessible à beaucoup de personnes. Par exemple, un nettoyant à base de vinaigre et d’eau est facile à réaliser et efficace. Enfin, il faut rester prudent, car un savon ou une crème mal préparés peuvent être dangereux. Je pense donc que la fabrication maison est utile, à condition d’être bien informé et rigoureux. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Il est aujourd’hui possible de fabriquer soi-même tout type de produits à la maison : des shampooings, des savons, des crèmes, du maquillage mais aussi des produits d’entretien pour faire le ménage. C’est formidable car on peut ainsi contrôler leur composition. Il est préférable de sélectionner des ingrédients et des parfums naturels qui ne contiennent pas d’éléments chimiques. Par ailleurs, la fabrication maison permet de réduire les emballages et donc les déchets. N’hésitez plus : fabriquez vos propres produits !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Vous préférez fabriquer vous-même votre savon au lieu de l’acheter ? Attention, il y a des risques pour la santé si vous ne choisissez pas les bons ingrédients ou si les règles d’hygiène ne sont pas respectées. Ainsi, avant de vous lancer, il est nécessaire de bien se renseigner et de suivre les règles strictes de fabrication et de conservation. De plus, même si ces produits coûtent moins cher et représentent une économie d’argent au quotidien, la fabrication « maison » prend souvent beaucoup de temps.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 63,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'La bibliothèque de votre ville organise une rencontre avec un écrivain. Écrivez un message à votre ami(e) pour l’inviter à cet événement.',
                'correction' => 'Objet : Invitation à la rencontre avec un écrivain

Salut Karim,

J’espère que tu vas bien. Je t’écris pour t’inviter à une rencontre avec un écrivain organisée par la bibliothèque de notre ville, à Montréal, le samedi 25 mai à 16 h. L’auteur présentera son nouveau roman, parlera de son travail et répondra aux questions du public. Il y aura aussi une séance de dédicaces à la fin. Je pense que cet événement peut vraiment te plaire, car tu aimes lire et découvrir de nouveaux auteurs. L’entrée est gratuite, mais il faut arriver un peu en avance pour avoir une place. Si tu veux, on peut se retrouver devant la bibliothèque à 15 h 30.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un événement nommé “La semaine du goût”. Écrivez un article de blog pour raconter cette expérience.',
                'correction' => 'La semaine du goût : une expérience inoubliable

Bonjour à tous,

La semaine dernière, j’ai participé à “La semaine du goût” dans ma ville avec deux amis et ma sœur. L’événement a eu lieu sur la place centrale, où plusieurs stands étaient installés. Dès notre arrivée, l’ambiance était très animée : il y avait de la musique, beaucoup de monde et de délicieuses odeurs partout.

Nous avons commencé par un atelier de cuisine, où un chef nous a montré comment préparer une tarte aux pommes. Ensuite, nous avons goûté des spécialités régionales, comme des fromages, des confitures et du pain artisanal. J’ai aussi assisté à une démonstration de chocolat, qui était vraiment impressionnante.

J’ai beaucoup aimé cette journée, car j’ai découvert de nouvelles saveurs et appris plusieurs choses sur les produits locaux. Les gens étaient accueillants et l’atmosphère était chaleureuse. C’était une expérience riche, gourmande et très agréable que je n’oublierai pas.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'L’art Urbain : Pour Ou Contre ?',
                'correction' => 'L’art urbain en débat

L’art urbain suscite aujourd’hui des avis très partagés dans les villes modernes. Le premier document souligne qu’il embellit l’espace public, stimule la curiosité et favorise la rencontre entre artistes et habitants. Cependant, le second rappelle que les graffitis sont aussi vus comme une pollution visuelle, voire comme du vandalisme. (50 mots)

À mon avis, l’art urbain est positif lorsqu’il est encadré et pensé pour tous. D’abord, il rend la ville plus vivante et plus accessible sur le plan culturel, surtout pour les personnes qui ne fréquentent pas les musées. Ensuite, il permet à de jeunes artistes de se faire connaître sans passer par des circuits fermés. De plus, certaines fresques améliorent vraiment des quartiers tristes ou dégradés. Par exemple, dans plusieurs villes, des murs gris ont été transformés en œuvres colorées qui attirent les visiteurs. Enfin, je pense qu’il faut distinguer l’expression artistique autorisée des tags faits sans règle, car le respect des lieux publics reste essentiel. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Grâce à l’art, la curiosité des citadins est attisée dans l’espace urbain. Que ce soit par une peinture ou un spectacle de rue, l’atmosphère éphémère ainsi créée offre une interaction entre des artistes peu connus et un public hétéroclite, parfois peu habitué à côtoyer cette forme d’art. Les villes d’aujourd’hui n’ont plus la même vocation que celles d’hier. Plus qu’un lieu de résidence, la ville se réinvente, s’enrichit de la diversité de sa population, s’agence différemment et s’ouvre sur le monde. Les espaces verts se créent et les murs se parent de couleurs grâce au street art. Cela débouche sur des lieux où la qualité de vie est privilégiée, tout en respectant la nature et l’environnement.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Mais à une plus large échelle, cela n’est pas aussi évident. Les graffitis peuvent être perçus comme une dégradation de la communauté, une pollution visuelle ou une preuve de la rupture sociale. On les considère comme du vandalisme qui défigure les biens publics. Le débat sur les avantages et les inconvénients des graffitis fait rage parmi les artistes, les législateurs et les membres de la communauté.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 64,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous allez déménager. Des amis ont accepté de vous aider. Vous leur écrivez un message collectif pour leur expliquer comment le déménagement va se passer (lieux, horaires, durée, trajet, tâches à faire, etc.).',
                'correction' => 'Objet : Organisation du déménagement samedi 15 juin

Salut à tous,

Merci encore pour votre aide ! Le déménagement aura lieu samedi 15 juin. Rendez-vous à 8 h 30 devant mon appartement actuel, au 12 rue Victor-Hugo, à Lyon. Nous commencerons par descendre les cartons et les meubles jusqu’au camion. Le départ est prévu vers 10 h pour aller au nouveau logement, au 45 avenue Jean-Jaurès, à Villeurbanne, à environ 20 minutes. Sur place, il faudra surtout monter les cartons, installer les meubles dans les bonnes pièces et m’aider à brancher le réfrigérateur et la machine à laver. Je pense finir vers 14 h. Je prévois des boissons, des sandwiches et une pizza pour le déjeuner.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Participez à notre concours pour gagner un séjour pour deux personnes dans la ville de votre choix. Rédigez un article sur le thème : « La vie de mon artiste préféré(e) ». Vous participez à ce concours. Vous expliquez pourquoi vous avez choisi cet(te) artiste et vous racontez sa vie.',
                'correction' => 'La vie de mon artiste préféré

Bonjour à tous,

J’ai choisi de parler de Stromae, mon artiste préféré, parce que ses chansons m’ont accompagné pendant mon adolescence. La première fois que je l’ai écouté, c’était en 2014, chez un ami, pendant une soirée très simple mais chaleureuse. L’ambiance était calme, et j’ai tout de suite été touché par sa voix et ses paroles.

Stromae est né en Belgique en 1985. Il a grandi à Bruxelles avec sa famille. Sa vie n’a pas toujours été facile, car il a perdu son père quand il était jeune. Malgré cette douleur, il a continué à avancer. Plus tard, il a commencé à écrire des chansons et il est devenu célèbre avec “Alors on danse”. Ensuite, il a connu un grand succès avec ses albums et ses concerts originaux.

Ce que j’admire chez lui, c’est sa sensibilité, son style unique et sa façon de parler de la vie avec émotion.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les objets connectés',
                'correction' => 'Les objets connectés dans la vie moderne

Les objets connectés occupent une place croissante dans notre quotidien. Selon le premier document, ils offrent plus de confort, favorisent l’activité physique et aident au suivi médical. Cependant, le second document souligne des risques importants, notamment le piratage, les cambriolages facilités et la prise de contrôle à distance de certains appareils. (47 mots)

À mon avis, les objets connectés sont utiles, mais ils doivent être utilisés avec prudence. D\'abord, ils simplifient la vie quotidienne en permettant de gérer plusieurs équipements rapidement, même à distance. Ensuite, ils peuvent rendre de grands services dans le domaine de la santé, surtout pour les personnes âgées ou malades. Par exemple, une montre connectée peut rappeler la prise d’un médicament ou signaler une anomalie. De plus, ces outils encouragent parfois de meilleures habitudes, comme marcher davantage. Enfin, leurs avantages ne doivent pas faire oublier les dangers liés à la sécurité des données personnelles. Il est donc nécessaire de mieux protéger les systèmes avant de généraliser leur usage dans tous les foyers. (117 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les objets connectés facilitent notre vie quotidienne. Ce sont des objets ou des équipements pilotés à distance à l’aide d’un téléphone portable ou par Internet, comme le système de chauffage ou la fermeture des portes. De plus, avec une montre ou un bracelet connecté, toutes nos activités peuvent être analysées. Un logiciel va mesurer le nombre de nos pas pour nous inciter à faire davantage d’exercice. Un domaine où l’on comprend mieux leur utilité est celui de la surveillance de la santé. Certains objets agissent comme un carnet de santé, toujours prêts à vous rappeler un rendez-vous chez le médecin ou des médicaments à prendre.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Il y aurait 50 milliards d’objets connectés dans le monde : l’alarme, le téléviseur, la caméra de surveillance, les volets, le détecteur de fumée, etc. Face à cette évolution, la question de la sécurité se pose. En effet, un pirate informatique peut prendre le contrôle d’un objet connecté en quelques minutes. Ou encore, un cambrioleur pourrait vérifier grâce aux caméras de surveillance connectées d’une habitation si les occupants sont absents. Il est aussi possible d’entrer dans le système connecté d’une voiture et d’en prendre les commandes à distance.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 65,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous êtes locataire d’un appartement trop grand pour vous. Écrivez une annonce dans un journal pour chercher un colocataire. Il faut mentionner : la superficie, le caractère du colocataire, le prix, etc.',
                'correction' => 'Objet : Recherche colocataire pour appartement spacieux à Montréal

Bonjour,

Je cherche un colocataire sérieux et agréable pour partager mon appartement devenu trop grand pour moi seul. Il se situe à Montréal, dans le quartier Rosemont, près du métro et des commerces. L’appartement fait 85 m², comprend 3 pièces, une grande cuisine, un salon lumineux et un balcon. La chambre à louer est meublée. Le loyer est de 650 dollars par mois, charges et internet inclus. Je recherche une personne calme, propre, respectueuse et non-fumeuse. Un étudiant ou un jeune salarié est bienvenu. Disponible à partir du 1er juin. Si vous êtes intéressé, merci de me contacter pour plus d’informations ou une visite.

Cordialement,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous venez de commencer une nouvelle activité de loisir (sport, danse, etc.). Écrivez un article sur votre blog pour parler de cette expérience.',
                'correction' => '[Ma nouvelle passion : l’escalade]

Salut à tous,

Le mois dernier, j’ai commencé une nouvelle activité de loisir : l’escalade. J’ai fait mon premier cours dans une salle près de chez moi, un samedi matin, avec deux amis. Au début, j’étais un peu stressé, parce que je n’avais jamais essayé ce sport. La salle était grande, lumineuse et très animée. Il y avait de la musique, des murs colorés et beaucoup de personnes de tous âges.

Le moniteur nous a expliqué les règles de sécurité, puis nous avons commencé à grimper sur des parcours faciles. J’ai eu du mal au début, surtout pour placer mes pieds, mais après quelques essais, je me suis senti plus à l’aise. Quand je suis arrivé en haut d’un mur, j’ai ressenti une vraie fierté. J’étais fatigué, mais aussi très heureux.

Cette première expérience m’a vraiment plu, et depuis, j’y vais chaque semaine.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La Gratuité Des Musées : Pour Ou Contre ?',
                'correction' => 'L’accès aux musées : enjeux et perspectives

La question de la gratuité des musées divise le public. Le premier document souligne les risques de surfréquentation et la baisse des ressources, en proposant des tarifs réduits. En revanche, le second défend un accès gratuit, jugé plus juste, éducatif et favorable au partage du patrimoine culturel. (44 mots)

À mon avis, la gratuité des musées est une mesure positive, mais elle devrait être partielle. D’abord, elle permet aux personnes modestes, aux étudiants et aux familles de découvrir l’art plus facilement. Ensuite, elle encourage les visites régulières et développe la curiosité culturelle. Par exemple, dans certaines villes, les musées gratuits le premier dimanche du mois attirent un public plus varié. De plus, cette formule limite le coût pour les visiteurs sans supprimer totalement les revenus des établissements. Enfin, un accès totalement gratuit toute l’année peut créer trop d’affluence et réduire la qualité de la visite. Une gratuité ciblée me semble donc plus équilibrée, utile au public et raisonnable pour les musées. (116 mots)',
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
        'combo' => 67,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami Cédric a accepté de garder votre maison et jardin pendant vos vacances. Écrivez un message pour lui dire ce qu’il doit faire.',
                'correction' => 'Objet : Consignes pour la maison pendant mes vacances

Salut Cédric,

Merci encore d’avoir accepté de garder ma maison et le jardin pendant mes vacances, du 10 au 20 août. Peux-tu arroser les plantes du salon tous les deux jours et le jardin chaque soir vers 20 h, surtout les tomates et les fleurs près de la terrasse ? Il faut aussi donner à manger au chat, Milo, matin et soir, et changer son eau chaque jour. Le courrier arrive vers 11 h : merci de le prendre dans la boîte aux lettres. Si possible, ouvre les volets le matin et ferme-les le soir. Les clés sont chez ma voisine Mme Martin, au numéro 12.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Suite à un voyage récent effectué avec une agence de voyages, vous êtes insatisfait(e) des prestations reçues. Rédigez un courriel de réclamation en exprimant votre mécontentement. Décrivez les problèmes rencontrés et demandez une solution de la part de l’agence.',
                'correction' => 'Bonjour à tous,

Je vous écris pour exprimer mon profond mécontentement après le voyage organisé par votre agence à Marrakech, du 5 au 9 mai, avec ma sœur. Sur votre site, vous aviez promis un hôtel 4 étoiles, des transferts confortables et des visites bien encadrées. En réalité, l’expérience a été très décevante.

À notre arrivée, le chauffeur n’était pas présent et nous avons attendu presque une heure à l’aéroport. Ensuite, l’hôtel proposé était bruyant, mal entretenu, et la chambre avait une odeur désagréable. De plus, la climatisation ne fonctionnait pas. Les excursions annoncées ont aussi posé problème : le guide est arrivé en retard, le programme a été modifié sans explication, et nous n’avons pas visité deux lieux prévus.

J’étais très déçu et frustré, car cette escapade devait être reposante. Je vous demande donc un remboursement partiel ou un geste commercial rapide.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La Restauration Rapide',
                'correction' => 'La restauration rapide aujourd’hui

La restauration rapide occupe une place importante dans nos habitudes alimentaires. Selon le premier document, ces restaurants offrent des plats variés, respectent l’hygiène et laissent au client le choix de son menu. Cependant, le second document souligne les risques pour la santé et les effets négatifs des emballages plastiques sur l’environnement. (49 mots)

À mon avis, la restauration rapide ne doit pas devenir une habitude, même si elle peut être pratique. D’abord, ces repas sont souvent trop gras, trop sucrés et trop salés, ce qui peut provoquer des problèmes de santé à long terme. Ensuite, beaucoup de clients choisissent les menus les moins équilibrés, surtout par manque de temps ou d’information. Par exemple, un déjeuner composé d’un hamburger, de frites et d’un soda apporte souvent trop de calories. De plus, les emballages jetables utilisés en grande quantité créent une pollution inutile. Enfin, je pense qu’il vaut mieux réserver ce type de repas aux situations exceptionnelles et privilégier une alimentation plus saine au quotidien. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les restaurants rapides proposent des plats équilibrés et variés, et ils respectent les normes d’hygiène et les variétés de produits qui sont bons, et c’est le client qui compose son menu, donc il en est responsable.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les spécialistes affirment que manger régulièrement dans des restaurants de fast-food, qui proposent de la restauration rapide, est dangereux pour la santé. La nourriture servie est souvent la même : frites, hamburgers et boissons sucrées. Ces aliments contiennent une grande quantité de calories, bien trop pour un seul repas. De plus, la plupart des produits dans ces restaurants sont emballés dans du plastique. Par conséquent, manger dans un fast-food augmente la production de déchets plastiques, ce qui est nuisible pour l’environnement.',
                    ],
                ],
            ],
        ],
    ],
];
