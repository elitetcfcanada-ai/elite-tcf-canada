<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
[
            'combo' => 1,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous avez reçu ce message de votre ami Thomas : « Salut ! Je ne connais pas bien la culture et les traditions de ton pays. Peux-tu me parler d\'une grande fête célébrée chez toi ? À bientôt ! » Consigne : Répondez-lui en décrivant une fête importante de votre pays.',
                    'correction' => 'Salut Farouk,
Merci pour ton message ! Chez moi, une fête très importante est l\'Aïd. Ce jour-là, les familles se lèvent tôt pour assister à une prière spéciale, puis tout le monde se réunit pour partager un grand repas. Les maisons sont bien préparées, les enfants reçoivent souvent des cadeaux et des vêtements neufs. On rend visite aux proches pour leur souhaiter une belle fête et renforcer les liens familiaux. L\'ambiance est chaleureuse et pleine de générosité.
J\'espère que tu pourras vivre l\'Aïd avec nous un jour !
À bientôt !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez assisté à un festival de cinéma. Vous avez vu plusieurs films. Sur votre site personnel, vous racontez le film que vous avez préféré. Vous expliquez votre choix.',
                    'correction' => 'Mon coup de cœur du festival de cinéma

chers lecteurs,

La semaine dernière, j\'ai assisté à un festival de cinéma et j\'ai eu l\'occasion de voir plusieurs films très différents les uns des autres. Parmi eux, celui qui m\'a le plus marqué est « La Lumière du Silence ». Ce film raconte l\'histoire d\'un jeune musicien qui perd progressivement l\'audition mais qui continue malgré tout à poursuivre son rêve. J\'ai particulièrement aimé la manière dont le réalisateur a utilisé les sons et les silences pour nous faire ressentir les émotions du personnage principal. Les acteurs jouent de façon très naturelle, ce qui rend l\'histoire encore plus touchante.
J\'ai choisi ce film parce qu\'il m\'a profondément ému et parce qu\'il transmet un message inspirant sur la persévérance. Il rappelle que, même face aux difficultés, il est possible de trouver de nouvelles façons d\'avancer et de rester fidèle à ses passions. C\'est vraiment le film qui m\'a le plus impressionné durant ce festival.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Que pensez-vous des maisons de retraite?',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'J\'habite dans un pays où il y a très peu de maisons de retraite. Ici, quand une personne âgée ne peut plus être autonome, elle habite avec les générations plus jeunes, et dans l\'ensemble, cela ne pose pas de problèmes majeurs. Il nous paraît étrange de confier nos parents ou grands-parents à des personnes qu\'ils ne connaissent pas.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Beaucoup de gens critiquent les maisons de retraite, pourtant c\'est une bonne solution d\'accueil pour les personnes âgées ! D\'abord, pour lutter contre l\'isolement puisqu\'elles sont entourées de personnes de leur âge à la maison de retraite. Ensuite, pour les soins et les services médicaux offerts dans ce type d\'établissement. Enfin, parce que c\'est pratique et rassurant pour leurs enfants et petits-enfants. Malheureusement, ce type de logement reste encore trop cher pour certains.',
                        ],
                    ],
                    'correction' => 'Les documents abordent différentes façons de prendre soin des personnes âgées. Le premier explique qu\'il est courant, dans certains pays, que les aînés vivent avec leur famille. En revanche, le second document présente les maisons de retraite comme une solution positive : elles offrent compagnie, soins médicaux et soutien quotidien, malgré leur coût élevé.

À mon avis, les deux modèles peuvent être valables selon la situation de chaque famille. Héberger une personne âgée à la maison permet de maintenir des liens forts et de lui offrir un environnement affectif. Cependant, ce n\'est pas toujours possible : certaines personnes nécessitent des soins spécialisés que les proches ne peuvent pas assurer. Les maisons de retraite peuvent alors être une bonne alternative, à condition qu\'elles soient accessibles financièrement et qu\'elles garantissent un accompagnement humain de qualité. Je pense que l\'essentiel est de respecter le choix et le confort de la personne âgée, car elle doit se sentir en sécurité, entourée et considérée, quel que soit le mode d\'accueil choisi.',
                ],
            ],
        ],
        [
            'combo' => 2,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Salut, Tu as commencé ton nouveau travail ! C\'est comment ? Tu es content(e) ? Ali — Vous répondez à votre ami Ali. Dans votre message, vous décrivez votre nouveau travail (lieu, collègues, etc.) et vous donnez vos impressions.',
                    'correction' => 'Salut Ali,
Merci pour ton message ! Oui, j\'ai enfin commencé mon nouveau travail. L\'entreprise est située en plein centre-ville, dans un bâtiment moderne et très agréable. Les collègues sont accueillants et m\'aident beaucoup depuis mon premier jour. L\'ambiance est vraiment positive et je me sens déjà à l\'aise dans l\'équipe. Le travail lui-même est intéressant et correspond bien à ce que je recherchais. Pour l\'instant, je suis très content(e) de cette nouvelle expérience.
On se parle bientôt !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'INFOS FAMILLES — Vivre avec une personne âgée : comment faire ? Notre site cherche des témoignages. Vous avez vécu avec une personne âgée. Vous racontez votre expérience.',
                    'correction' => 'Bonjour à tous,

J\'ai eu l\'occasion de vivre pendant deux ans avec ma grand-mère, une période qui m\'a profondément marqué. Au début, j\'avais peur de ne pas savoir comment gérer son rythme et ses besoins particuliers, mais nous avons rapidement trouvé un équilibre. La communication a été essentielle : je lui demandais souvent son avis, et elle appréciait qu\'on la considère encore comme une personne active.
Il a fallu adapter certaines choses dans la maison, comme installer des appareils plus simples à utiliser ou organiser les espaces pour qu\'elle se déplace en sécurité. Malgré quelques moments difficiles, cette expérience a été très enrichissante. J\'ai appris la patience, l\'écoute et la valeur du temps passé avec nos aînés.
Vivre avec une personne âgée demande de l\'organisation, mais c\'est surtout un échange humain précieux. Aujourd\'hui, je suis reconnaissant d\'avoir partagé ces moments avec elle.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les cours de langue en ligne.',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Apprendre une langue en ligne grâce à Internet, c\'est possible et cela donne de bons résultats ! Contrairement aux cours classiques, on peut apprendre quand on veut : les cours sont disponibles tout le temps. Cela permet de mieux organiser sa journée. On n\'a pas non plus besoin de faire des kilomètres pour aller dans une école de langues. On peut apprendre de son salon, de son bureau ou même d\'un café près de chez soi ! Cela permet aussi de faire des économies.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Cela semble facile d\'apprendre une langue en ligne mais ce n\'est pas possible pour tout le monde. En effet, il faut avoir une bonne connexion à Internet et un outil numérique adapté (ordinateur, smartphone, tablette) pour apprendre en ligne. De plus, il faut être très autonome pour être capable d\'apprendre seul : il est difficile de se mettre au travail chez soi et de rester motivé quand on n\'a pas l\'aide d\'un professeur et de collègues. Dans ces conditions, on peut se décourager et abandonner très vite.',
                        ],
                    ],
                    'correction' => 'Aujourd\'hui, apprendre une langue en ligne devient courant. Le premier document met en avant la flexibilité et les économies possibles : on peut apprendre où et quand on veut. Cependant, le second document souligne les limites : besoin de matériel adapté, d\'une bonne connexion et d\'une grande autonomie, ce qui décourage certains apprenants.

Selon moi, les cours de langue en ligne représentent une excellente opportunité, surtout pour ceux qui ont un emploi du temps chargé. La liberté d\'apprendre à son rythme est un avantage énorme, et les plateformes actuelles offrent des outils variés et efficaces. Toutefois, il est vrai que cette méthode demande de la discipline. Sans professeur présent physiquement, il est facile de perdre la motivation. Je pense que la meilleure solution est de combiner apprentissage en ligne et accompagnement régulier, par exemple des rencontres virtuelles avec un enseignant. Ainsi, on profite de la flexibilité du numérique tout en gardant un cadre motivant et structuré.',
                ],
            ],
        ],
        [
            'combo' => 3,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous terminez un séjour professionnel dans un pays étranger et vous souhaitez fêter votre départ. Vous écrivez un courriel à vos collègues pour les inviter au restaurant. Vous décrivez votre projet et vous leur donnez les informations nécessaires (adresse, date, heure, etc.).',
                    'correction' => 'Objet : Invitation pour fêter mon départ

Chers collègues,
Mon séjour professionnel touche à sa fin et j\'aimerais partager un moment convivial avec vous avant mon départ. Je vous propose de nous retrouver au restaurant Le Jardin Bleu, situé au 12 rue Central. Nous pourrions nous réunir vendredi prochain à 19h30 pour un dîner simple et chaleureux. Ce serait pour moi l\'occasion de vous remercier pour votre accueil et votre collaboration.
Merci de me confirmer votre présence.
À très bientôt !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez commencé une nouvelle activité sportive. Sur votre blog, vous racontez votre expérience. Vous expliquez pourquoi cette activité peut être intéressante pour tout le monde.',
                    'correction' => 'Ma nouvelle activité sportive : une expérience à découvrir

Chers lecteurs,

Depuis quelques semaines, j\'ai commencé une nouvelle activité sportive : le yoga dynamique. Au début, je pensais que ce serait une pratique lente et simple, mais j\'ai rapidement compris à quel point elle pouvait être complète. Les séances combinent respiration, force et souplesse, ce qui permet de travailler tout le corps tout en se relaxant. J\'ai aussi remarqué que je dors mieux et que je gère plus facilement le stress depuis que je pratique régulièrement.
Cette activité peut vraiment intéresser tout le monde, car elle s\'adapte à tous les âges et à tous les niveaux. On peut progresser à son rythme et choisir des séances plus douces ou plus intenses selon ses besoins. Pas besoin d\'équipement coûteux : un simple tapis suffit pour commencer.
Je recommande vivement cette pratique à ceux qui cherchent un sport bénéfique autant pour le corps que pour l\'esprit.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les animaux de compagnie au travail.',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Venir au bureau avec son animal de compagnie, c\'est à la mode. L\'intérêt de cette présence ? C\'est simple : diminuer le stress des employés. La présence d\'un chien ou d\'un chat change l\'ambiance générale d\'une entreprise : elle réduit les tensions entre collègues, ce qui n\'est pas négligeable car chaque année, l\'État français dépense entre 2 et 3 milliards d\'euros pour soigner les salariés malades du stress. Selon une enquête, un quart des employés pensent aussi que la présence des animaux permet d\'être plus motivé au travail. (D\'après challenges.fr)',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => '« Dans l\'entreprise où je travaille, les employés sont autorisés à venir avec leur animal de compagnie, chat ou chien. Personnellement, cela me dérange. Je ne suis pas très à l\'aise avec les animaux, qui peuvent avoir des comportements imprévisibles. D\'autre part, je trouve que cela peut poser des problèmes de santé. Que faire si un employé est allergique aux chats ? Sera-t-il obligé de rester enfermé dans son bureau ? Et puis c\'est aussi une source de distraction. Mes collègues et moi ne serons pas plus productifs grâce à un chien ou un chat ! » — Iris, 34 ans',
                        ],
                    ],
                    'correction' => 'La présence d\'animaux au travail suscite des avis différents. Le premier document souligne leurs effets positifs : réduction du stress, meilleure ambiance et motivation accrue. Cependant, le second document met en avant plusieurs inconvénients : malaise de certains employés, risques d\'allergies et distractions possibles dans l\'entreprise.

À mon avis, autoriser les animaux au travail peut être bénéfique, mais uniquement dans des conditions bien encadrées. Leur présence peut créer une atmosphère plus détendue et favoriser de meilleures relations entre collègues. Toutefois, il ne faut pas ignorer les personnes qui ont peur, qui n\'aiment pas les animaux ou qui souffrent d\'allergies. Une entreprise doit garantir le confort de tous. Je pense qu\'il serait préférable de réserver certains espaces aux animaux ou d\'instaurer des jours spécifiques pour leur venue. Ainsi, chacun pourrait travailler sereinement. Avec une bonne organisation, cette initiative pourrait améliorer le bien-être sans nuire à la productivité.',
                ],
            ],
        ],
        [
            'combo' => 4,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous venez de vous installer dans une nouvelle ville. Vous écrivez un message à un(e) ami(e) pour décrire votre nouvel environnement (quartier, voisins, magasins, etc.)',
                    'correction' => 'Bonjour Yassine,
Je viens de m\'installer dans ma nouvelle ville et je voulais te donner quelques nouvelles. Mon quartier est vraiment agréable : il est calme, bien organisé et entouré de petits parcs. Les voisins sont sympathiques et m\'ont déjà souhaité la bienvenue. On trouve aussi plusieurs magasins à proximité : une boulangerie, un supermarché et même un petit café très convivial.

Je commence à m\'y sentir bien et j\'aimerais que tu viennes découvrir l\'endroit avec moi bientôt !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez participé à une action pour la « Journée mondiale du nettoyage de notre planète ». Vous avez ramassé des déchets dans un lieu public (plage, forêt, rue, etc.) avec d\'autres personnes. Vous racontez cette expérience à vos amis. Vous expliquez pourquoi il est important de participer à ce type d\'action.',
                    'correction' => 'Une journée pour nettoyer notre planète

Chers amis,

La semaine dernière, j\'ai participé à la « Journée mondiale du nettoyage de notre planète » avec un groupe de bénévoles. Nous avons choisi de ramasser les déchets sur une plage très fréquentée. Au début, je ne pensais pas en trouver autant : plastiques, bouteilles, papiers… En quelques heures, nous avons rempli plusieurs sacs. Malgré la fatigue, l\'ambiance était motivante, car chacun avait envie de rendre l\'endroit plus propre.
Cette expérience m\'a fait comprendre à quel point nos petits gestes comptent. Participer à ce type d\'action est important : cela protège l\'environnement, sensibilise les habitants et montre l\'exemple aux plus jeunes. En voyant la plage propre à la fin, j\'ai ressenti une vraie satisfaction.
Je pense que si chacun participait au moins une fois par an, notre planète irait beaucoup mieux. C\'est une activité simple, utile et très enrichissante.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les publicités',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Certaines personnes trouvent la publicité ennuyeuse, mais, à mon avis, elle est indispensable pour le commerce et les entreprises. Grâce à la publicité, on fait connaître un produit ou un service. Et puis, parfois, elles sont drôles ! J\'aime bien les écouter quand je suis en voiture. Cela me permet aussi d\'être informé des nouveautés et des promotions. Personnellement, j\'adore comparer les articles : je peux ainsi faire beaucoup d\'économies sur mes achats. Enfin, beaucoup de personnes jouent à des jeux gratuits sur leur téléphone : sans publicité, ces jeux seraient tous payants. Quentin, 28 ans',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'La publicité est présente partout dans notre vie de tous les jours : journaux, radios, télévision, téléphone, Internet… Par exemple, sur certaines chaînes de télévision, les émissions sont coupées par la publicité et c\'est agaçant. Puis, recevoir des dizaines de kilos de papier de publicité par an dans la boîte aux lettres, ce n\'est pas très respectueux de l\'environnement ! Je pense qu\'il est nécessaire de faire voter une loi pour réduire les publicités par courrier et à la télévision. Si la publicité était plus discrète, elle serait plus appréciée. Estelle, 35 ans',
                        ],
                    ],
                    'correction' => 'La publicité occupe une place importante dans la vie moderne. Le premier document souligne ses avantages : elle informe, divertit et permet d\'accéder à des services gratuits. En revanche, le second document insiste sur ses aspects négatifs : interruption des émissions, pollution liée aux prospectus et nécessité de limiter son omniprésence.

À mon avis, la publicité est utile, mais elle doit être mieux encadrée. Elle permet aux entreprises de se faire connaître et aide les consommateurs à comparer les produits, ce qui peut réduire les dépenses. Cependant, lorsqu\'elle devient trop intrusive, elle perd tout son sens. Les coupures répétées à la télévision ou les publicités papier qui remplissent les boîtes aux lettres créent une vraie nuisance. Je pense qu\'un équilibre est nécessaire : limiter les formats les plus envahissants, développer des publicités numériques plus discrètes et encourager les messages réellement informatifs. Une publicité mieux régulée pourrait être bénéfique pour l\'économie tout en respectant davantage le public et l\'environnement.',
                ],
            ],
        ],
        [
            'combo' => 5,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Écrivez un message dans le journal de votre université pour rechercher un partenaire avec qui faire du sport.',
                    'correction' => '',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Écrivez un article de blog pour raconter votre arrivée dans un pays étranger en donnant vos impressions.',
                    'correction' => '',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Rôle de la télévision dans l\'éducation des enfants.',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'La télévision est un outil de communication et de divertissement largement répandu dans notre société moderne. Son influence est incontestable, tant sur les individus que sur la culture en général. Elle permet de diffuser des informations, d\'offrir des divertissements variés et de favoriser la diffusion de la culture. La télévision est présente dans de nombreux foyers et constitue une source d\'information et de divertissement accessibles à tous. Grâce à sa portée et à sa capacité à toucher un large public, la télévision joue un rôle important dans la transmission des connaissances et la sensibilisation aux enjeux sociaux.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'La télévision peut également présenter certains inconvénients. Les émissions télévisées peuvent parfois véhiculer des stéréotypes, des préjugés et des valeurs discutables. De plus, le temps passé devant la télévision peut réduire le temps consacré à d\'autres activités plus enrichissantes, telles que la lecture, les interactions sociales ou la pratique d\'un sport. Il est important de faire preuve de discernement et de réguler l\'exposition à la télévision, en particulier pour les enfants, afin de préserver un équilibre sain entre les différentes formes d\'apprentissage et de divertissement.',
                        ],
                    ],
                    'correction' => '',
                ],
            ],
        ],
        [
            'combo' => 6,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Un nouveau restaurant vient d\'ouvrir près de chez vous. Vous écrivez à un(e) ami(e) pour lui proposer d\'y aller avec vous. Vous décrivez le restaurant (cuisine, prix, décoration, etc.).',
                    'correction' => '',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez visité une ville que vous ne connaissiez pas. Vous avez envie de partager votre découverte. Vous postez un message sur un site Internet dédié aux voyages. Racontez votre expérience et expliquez ce qui vous a plu et ce qui vous a déplu dans la ville.',
                    'correction' => '',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Vie en colocation entre adultes.',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Vivre avec d\'autres personnes demande d\'avoir une bonne entente et de respecter certaines règles. Il n\'est pas toujours possible d\'écouter sa musique préférée à volume élevé, d\'inviter tous ses amis pour faire la fête ou de laisser de la vaisselle sale dans la cuisine. Chaque individu a des habitudes susceptibles d\'irriter les autres. C\'est pourquoi il est essentiel d\'établir des règles de vie en communauté et de les respecter mutuellement. Il est important de communiquer avec ses colocataires chaque fois qu\'un problème survient. L\'organisation et la discussion sont les clés d\'une colocation réussie ou non.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Être adulte et vivre en colocation ? C\'est un choix qui permet d\'accéder facilement à un logement plus spacieux et économique. Il est vrai que vous n\'aurez qu\'une chambre pour vous et que vous devrez partager la cuisine, le salon et la salle de bain. Toutefois, une colocation peut inclure une maison avec jardin ou un grand appartement en centre-ville ! De plus, en partageant le loyer et les charges avec vos colocataires, vous réduirez considérablement vos dépenses par rapport à un appartement individuel.',
                        ],
                    ],
                    'correction' => '',
                ],
            ],
        ],
        [
            'combo' => 7,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Écrivez un message à votre ami(e) qui souhaite suivre des cours de langue dans votre école. Donnez les détails spécifiques pour aider votre ami(e) à faire son choix. (lieu, tarifs, types de cours disponibles, etc.).',
                    'correction' => '',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous travaillez dans une association qui aide les personnes âgées. Rédigez un article de blog pour raconter vos expériences et convaincre d\'autres personnes de rejoindre l\'association.',
                    'correction' => '',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les animaux de compagnie pour les enfants : pour ou contre ?',
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
                    'correction' => '',
                ],
            ],
        ],
        [
            'combo' => 8,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Votre ami souhaite commencer à faire du sport. Rédigez un message pour lui recommander une salle de sport située dans votre quartier (localisation, tarifs, types d\'activités, etc.).',
                    'correction' => 'Salut,
J\'espère que tu vas bien. Je suis très content que tu veuilles commencer le sport ! Je te conseille une salle que j\'aime beaucoup dans mon quartier : Fitness Center Verdier. Elle est située près du métro, à seulement 5 minutes à pied, donc c\'est très pratique.
Les tarifs sont raisonnables : environ 35 dollars par mois, avec un abonnement sans engagement. La salle est propre, bien équipée et il y a plusieurs activités : musculation, cardio, cours collectifs (zumba, yoga, spinning) et même un espace étirements.
Si tu veux, on peut y aller ensemble une première fois pour te montrer comment ça fonctionne.',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez participé à un événement qui vous a marqué (anniversaire, mariage, etc.). Racontez votre souvenir en décrivant ce qui vous a le plus marqué.',
                    'correction' => 'Un anniversaire inoubliable
L\'événement qui m\'a le plus marqué est l\'anniversaire surprise que nous avons organisé pour mon meilleur ami. Ce jour-là, nous nous sommes retrouvés chez moi en fin d\'après-midi pour préparer la décoration, la musique et le gâteau. Tout le monde était très motivé et l\'ambiance était joyeuse.
Ce qui m\'a le plus marqué, c\'est le moment où mon ami est arrivé. Lorsqu\'il a ouvert la porte et qu\'il a vu tous ses proches réunis, il est resté quelques secondes sans parler, puis il a souri avec émotion. On voyait clairement qu\'il ne s\'attendait pas à cette surprise.
Ensuite, nous avons partagé un bon repas, dansé et pris beaucoup de photos. Cette soirée m\'a vraiment rappelé l\'importance de l\'amitié et des moments simples passés ensemble.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'La lecture pour les enfants.',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Avec l\'avancée technologique et les produits high-tech qui envahissent de plus en plus notre quotidien, nos enfants oublient la lecture et s\'intéressent davantage aux jeux vidéo, aux sports, à la musique… Contrairement à nous, les adultes, dont beaucoup d\'entre nous ont lu des milliers de pages, la génération actuelle est toujours occupée par les réseaux sociaux et le gaming ou prend du plaisir à pratiquer du sport qui attire davantage de jeunes grâce aux stars internationales du football, du tennis, de l\'athlétisme… alors avec tout ça, pourquoi devons-nous forcer les enfants à lire un bouquin ? Et comme le dit un proverbe, « le goût de la lecture ne peut pas s\'imposer »… il faut laisser l\'enfant choisir ce qu\'il veut lire et surtout ne pas l\'obliger à lire quand il n\'a pas envie.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'L\'amour de la lecture se transmet de génération en génération bien que, ces dernières années, on ne trouve plus beaucoup de bouquins entre les mains des enfants, laissant la place aux smartphones et aux tablettes. En apprenant à lire régulièrement, l\'enfant acquiert le langage plus aisément tout en développant sa capacité d\'audition et de concentration. De plus, et pour prendre du plaisir ensemble, les parents peuvent consacrer quotidiennement 10 minutes à leurs enfants pour lire des bouquins ; une activité qui renforcera à coup sûr la complicité parent/enfant.',
                        ],
                    ],
                    'correction' => 'La lecture chez les enfants : faut-il insister ?
Le premier texte explique que les enfants s\'intéressent davantage aux écrans et aux loisirs modernes qu\'à la lecture, et qu\'il ne faut pas les forcer à lire, car le goût ne s\'impose pas. En revanche, le second souligne que lire régulièrement développe le langage, la concentration et renforce le lien entre parents et enfants.
À mon avis, la lecture est essentielle pour le développement des enfants, mais elle ne doit pas être imposée de manière autoritaire. Lire permet d\'enrichir le vocabulaire, d\'améliorer l\'expression orale et écrite et de stimuler l\'imagination. Cependant, si l\'enfant se sent obligé, il risque de rejeter totalement les livres. Je pense qu\'il vaut mieux l\'encourager doucement, en choisissant des histoires adaptées à son âge et à ses centres d\'intérêt. Les parents peuvent aussi donner l\'exemple en lisant eux-mêmes. Ainsi, la lecture devient un moment agréable et partagé, et non une contrainte.',
                ],
            ],
        ],
        [
            'combo' => 9,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Répondez au courriel de votre ami Lucas pour lui donner des informations sur les nouveaux locaux de votre entreprise (lieu, disposition des pièces, équipements, etc.).',
                    'correction' => 'Salut Lucas,
Merci pour ton message ! Je suis content de te parler de nos nouveaux locaux. Ils sont situés au centre-ville, près de la station de métro, ce qui est très pratique pour tout le monde.
Les bureaux sont beaucoup plus spacieux qu\'avant. Il y a un grand espace ouvert pour le travail en équipe, plusieurs salles de réunion bien équipées avec des écrans et du matériel de visioconférence, ainsi qu\'une salle de détente avec une petite cuisine. Nous avons aussi une terrasse où l\'on peut prendre une pause quand il fait beau.
L\'ambiance est moderne et agréable, on s\'y sent vraiment bien pour travailler.',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez assisté à un événement intitulé « Une semaine sans voiture ». Racontez votre expérience et donnez votre impression sur cette initiative. Décrivez le déroulement de l\'événement (dates, lieu, activités proposées).',
                    'correction' => 'Une semaine sans voiture : une initiative à refaire !
La semaine dernière, j\'ai participé à un événement intitulé « Une semaine sans voiture », organisé du 5 au 11 février dans le centre-ville. Pendant cette période, plusieurs rues principales ont été fermées aux voitures afin de favoriser les déplacements à pied, à vélo et en transports en commun.
Chaque jour, des activités étaient proposées : balades à vélo, ateliers de réparation, animations pour les enfants, et stands d\'information sur l\'environnement. Il y avait aussi des conférences sur la pollution et des démonstrations de transports écologiques.
J\'ai beaucoup apprécié cette initiative, car la ville était plus calme, l\'air semblait plus propre et il y avait moins de stress. Cette expérience m\'a permis de redécouvrir ma ville autrement. À mon avis, ce type d\'événement devrait être organisé plus souvent.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les vêtements de marque pour les enfants',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Les vêtements de marques sont très importants pour les enfants et les adolescents. C\'est un moyen de s\'exprimer et de se rattacher à un groupe social. Cette attirance pour les marques est très présente chez les adolescents qui se cherchent et montrent leur personnalité. Les enfants aiment également porter des vêtements de marques avec des images des dessins animés qu\'ils regardent ou des logos qu\'ils apprécient.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Les enfants grandissent très vite et les vêtements sont portés pendant une courte période. Ainsi, les vêtements deviennent rapidement trop petits. Mais il y a aussi le fait que les enfants usent assez rapidement les vêtements en jouant à l\'extérieur avec les copains, en s\'amusant dans l\'herbe ou à l\'aire de jeu. Les habits sont très vite sales ou troués.',
                        ],
                    ],
                    'correction' => 'Les vêtements de grandes marques : nécessaires pour les enfants ?
Le premier document explique que les vêtements de marque permettent aux enfants et aux adolescents de s\'exprimer et de s\'intégrer à un groupe. Les logos et images attirent particulièrement les jeunes. Le second document souligne que ces vêtements sont peu rentables, car les enfants grandissent vite et les abîment rapidement.
À mon avis, acheter des vêtements de grandes marques pour les enfants n\'est pas indispensable. Je comprends que les adolescents veuillent suivre la mode et se sentir acceptés par leurs amis. Cependant, les enfants grandissent très vite et changent souvent de taille. Dépenser beaucoup d\'argent pour des vêtements qu\'ils porteront seulement quelques mois me semble excessif. De plus, en jouant à l\'extérieur, les habits peuvent facilement se salir ou se déchirer. Je pense qu\'il vaut mieux privilégier des vêtements de bonne qualité, confortables et adaptés à leur âge, sans forcément payer une marque connue. L\'essentiel est que l\'enfant se sente bien, pas qu\'il porte un logo célèbre.',
                ],
            ],
        ],
        [
            'combo' => 10,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous souhaitez proposer à un(e) ami(e) de faire du sport avec vous. Vous lui écrivez un message pour décrire votre projet (type d\'activités, lieu, équipement, etc.)',
                    'correction' => '',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez étudié dans une université à l\'étranger pendant six mois. Vous écrivez un message à vos amis pour raconter votre expérience et vous expliquez ce que vous avez aimé.',
                    'correction' => '',
                ],
                [
                    'task' => 3,
                    'prompt' => 'L\'uniforme scolaire',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Au Québec, l\'uniforme scolaire n\'est plus réservé uniquement aux écoles privées : de plus en plus d\'écoles publiques font ce choix pour leurs élèves. Mais il représente un problème pour les adolescents qui ne sont pas toujours d\'accord avec ce choix. Ils disent souvent que ces uniformes ne sont pas confortables, qu\'ils ne sont pas beaux car ils manquent de couleurs et qu\'ils tiennent trop chaud en été. De plus, comme tous les élèves de l\'école sont habillés de la même manière, les jeunes trouvent que cela ne leur permet pas d\'exprimer leur personnalité.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'L\'uniforme scolaire présente aussi des avantages : il réduit les inégalités entre les élèves, car tout le monde s\'habille de la même façon. Il simplifie le quotidien des parents, qui n\'ont plus à choisir chaque matin une tenue adaptée. Enfin, il peut renforcer le sentiment d\'appartenance à l\'établissement et limiter certaines tensions liées à la mode.',
                        ],
                    ],
                    'correction' => '',
                ],
            ],
        ],
        [
            'combo' => 11,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Analysez le sujet d\'examen suivant : Vous souhaitez fêter votre anniversaire dans un restaurant. Vous invitez vos amis. Vous leur écrivez un courriel pour leur donner toutes les informations nécessaires (lieu, date, horaires, menus, prix) et vous leur demandez une réponse.',
                    'correction' => 'Objet : Invitation à mon anniversaire
Bonjour à tous,
Je vous invite à fêter mon anniversaire au restaurant ! La soirée aura lieu le samedi 20 avril à 19h au restaurant « Le Gourmet », situé au centre-ville.
Le restaurant propose plusieurs menus (viande, poisson et végétarien) à partir de 20 euros. Les boissons sont également incluses dans certaines formules.
Ce sera l\'occasion de passer un bon moment ensemble dans une ambiance conviviale.
Merci de me confirmer votre présence avant le 15 avril afin que je puisse effectuer la réservation.
J\'espère vous voir nombreux !
À très bientôt.',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez quitté la ville pour vous installer à la campagne. Sur un forum internet, vous expliquez pourquoi vous avez fait ce choix et vous présentez les avantages de votre nouvelle vie.',
                    'correction' => 'Bonjour à tous,

J\'ai récemment quitté la ville pour m\'installer à la campagne, et je ne regrette absolument pas ce choix. La vie en ville devenait trop stressante pour moi : bruit, pollution et rythme de vie rapide.

À la campagne, je profite du calme, de l\'air pur et de la nature. Je me sens beaucoup plus détendu(e) et j\'ai un meilleur équilibre de vie. De plus, le coût de la vie est souvent moins élevé, ce qui est un avantage important.

J\'apprécie aussi la simplicité du quotidien et les relations plus chaleureuses avec les habitants. Bien sûr, il y a quelques inconvénients, comme l\'éloignement des services, mais pour moi, les avantages sont bien plus nombreux.

Et vous, seriez-vous prêt(e) à faire ce choix ?',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les caméras de surveillance',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'À Montréal, dans l\'école où j\'enseigne, des caméras sont présentes un peu partout. L\'installation de ces appareils permet de faire comprendre aux jeunes que tout acte de violence sera puni. Ce mode de surveillance est très bien accepté par les parents, les enseignants et la plupart des élèves. Si les parents sont rassurés concernant la sécurité de leurs enfants, les professeurs y voient une garantie de pouvoir exercer leur métier dans les meilleures conditions possibles. Seuls certains élèves critiquent ces caméras en disant qu\'elles ne respectent pas leur vie privée.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Je suis contre la présence de caméras de surveillance dans nos écoles de Montréal. Dans les pays où ce système a été mis en place, les résultats ne sont pas très positifs. Comme les caméras sont très visibles, les personnes extérieures qui voudraient entrer dans l\'école peuvent le faire en passant par des endroits non surveillés. Pour résoudre les problèmes de discipline, il suffit souvent d\'améliorer la communication entre les élèves, l\'administration et les enseignants. Au lieu de placer des caméras, il suffirait de bien faire comprendre et appliquer le règlement intérieur de l\'école par tous.',
                        ],
                    ],
                    'correction' => 'Débat autour des caméras de surveillance
L\'utilisation des caméras de surveillance suscite aujourd\'hui de nombreux débats. Selon le premier document, leur présence dans les écoles permet de prévenir la violence et de rassurer les parents. En revanche, le second document souligne que ce système n\'est pas toujours efficace et peut poser des problèmes.
À mon avis, les caméras de surveillance peuvent être utiles, mais elles doivent être utilisées avec précaution. D\'un côté, elles contribuent à améliorer la sécurité et à dissuader certains comportements violents. Elles peuvent aussi faciliter l\'identification des responsables en cas de problème. D\'un autre côté, elles peuvent porter atteinte à la vie privée et créer un sentiment de surveillance permanente. De plus, elles ne résolvent pas les causes profondes de la violence. Il serait donc préférable de compléter ce dispositif par des actions éducatives. Ainsi, les caméras peuvent être utiles, mais elles ne doivent pas être la seule solution.',
                ],
            ],
        ],
        [
            'combo' => 12,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous avez passé un week-end à la campagne. Écrivez un message à votre ami(e) pour lui décrire ce qui s\'est passé.',
                    'correction' => 'Salut !
Je voulais te raconter mon week-end à la campagne. C\'était vraiment génial ! J\'ai passé beaucoup de temps en plein air, loin du bruit et du stress de la ville. J\'ai fait de longues promenades dans la nature et j\'ai profité du calme.
Le samedi, j\'ai aussi visité un petit village très charmant et goûté des spécialités locales. Le soir, on a fait un dîner convivial avec des produits frais, c\'était délicieux.
Ce week-end m\'a permis de me détendre et de me ressourcer. Franchement, ça m\'a fait beaucoup de bien !
On devrait y aller ensemble la prochaine fois.',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Votre direction est à la recherche d\'une salle pour la fête de fin d\'année, capable d\'accueillir 100 invités. Rédigez un message à la direction pour leur dire que vous avez trouvé un local idéal. (lieu, tarifs, services, etc.).',
                    'correction' => 'Objet : Proposition de salle pour la fête de fin d\'année
Madame, Monsieur,

Suite à votre recherche d\'une salle pour la fête de fin d\'année, je me permets de vous proposer un local qui me semble parfaitement adapté à vos besoins.

Il s\'agit d\'une salle située au centre-ville, facilement accessible en transport. Elle peut accueillir jusqu\'à 120 personnes, ce qui correspond à notre nombre d\'invités. Le lieu est spacieux, moderne et bien équipé.

Concernant les tarifs, le prix est raisonnable et comprend plusieurs services : sonorisation, éclairage, tables et chaises, ainsi qu\'un service de restauration sur demande. Un parking est également disponible à proximité.

Je pense que cet espace répond à tous nos critères et serait idéal pour cet événement.

Je reste à votre disposition pour toute information complémentaire.

Cordialement,',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Utilisation des nouvelles technologies dans les écoles : pour ou contre ?',
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
                    'correction' => 'Utilisation des nouvelles technologies dans les écoles : pour ou contre ?
L\'intégration des nouvelles technologies dans les écoles suscite de nombreux débats. Selon le premier document, elles permettent de mieux préparer les élèves à un avenir numérique et favorisent leur engagement. En revanche, le second document souligne que leur usage peut réduire les interactions humaines et créer une dépendance aux écrans.

À mon avis, les nouvelles technologies sont utiles dans l\'éducation, mais leur utilisation doit être équilibrée. D\'un côté, elles offrent des outils modernes et interactifs qui facilitent l\'apprentissage et rendent les cours plus attractifs. Elles permettent aussi d\'accéder à de nombreuses ressources en ligne. D\'un autre côté, un usage excessif peut nuire à la concentration des élèves et limiter les échanges avec les enseignants. Le contact humain reste essentiel pour un apprentissage complet. Ainsi, il est important de combiner les méthodes traditionnelles avec les outils numériques afin de profiter des avantages des deux.',
                ],
            ],
        ],
        [
            'combo' => 13,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Salut, Je sais que tu vas dans une salle de sport. Qu\'est-ce que tu penses de cette salle ? Peux-tu m\'en dire un peu plus ? Je voudrais savoir si elle est bien ! Merci d\'avance ! Joanna — Vous répondez à votre amie Joanna. Dans votre message, vous décrivez la salle de sport que vous fréquentez (activités, horaires, prix, etc.)',
                    'correction' => '',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous avez suivi une formation (cours de langue, informatique, etc.). Sur un site Internet, vous écrivez un message pour raconter votre expérience (cours, participants, professeurs, etc.). Vous expliquez ce que vous avez aimé ou pas aimé pendant cette formation.',
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
            'combo' => 14,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous cherchez un(e) partenaire de sport. Vous publiez un message sur le site Internet des étudiants de votre école. Vous précisez vos disponibilités.',
                    'correction' => 'Bonjour à tous,
Je cherche un(e) partenaire de sport pour m\'entraîner régulièrement. Je pratique surtout le fitness et un peu de course à pied, mais je suis ouvert(e) à d\'autres activités.
Je suis disponible en semaine en fin d\'après-midi (à partir de 18h) et le week-end selon les horaires.
L\'objectif est de rester motivé(e), progresser et partager de bons moments dans une bonne ambiance.
Si vous êtes intéressé(e), n\'hésitez pas à me contacter !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous venez d\'arriver dans un nouveau pays. Vous écrivez à vos amis pour leur raconter votre arrivée et décrire vos premières impressions.',
                    'correction' => 'Salut à tous,

Je viens d\'arriver dans mon nouveau pays et je voulais partager mes premières impressions avec vous ! Le voyage s\'est bien passé et, dès mon arrivée, j\'ai été surpris(e) par l\'ambiance et les différences culturelles.

Les gens sont très accueillants et la ville est vraiment magnifique. Tout est nouveau pour moi : la langue, la nourriture, les habitudes… mais c\'est justement ce qui rend l\'expérience excitante. Même si je me sens encore un peu perdu(e), je commence petit à petit à m\'adapter.

J\'ai hâte de découvrir encore plus de choses et de vous raconter la suite !

À très bientôt.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'La télévision pour les enfants',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Aujourd\'hui, les chaînes de télévision proposent une grande variété d\'émissions pour la jeunesse de bonne qualité. Toutefois, les parents doivent éviter de laisser leurs enfants regarder passivement la télévision. Au contraire, les spécialistes recommandent de les accompagner dans le choix des programmes et également de se mettre d\'accord sur le nombre d\'heures passées devant la télévision. Il s\'agit d\'aider les enfants à développer leurs goûts, à comprendre ce qu\'ils aiment et à savoir quand éteindre le petit écran. La télévision peut ainsi être bénéfique pour les enfants.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Les enfants sont fascinés par la télévision et beaucoup de parents laissent leurs enfants la regarder sans mettre de limites. Le petit écran peut être pratique pour occuper les enfants après une longue journée de travail. Le problème, c\'est que la télévision limite la créativité des enfants. En effet, elle leur donne un produit déjà complet (image, son, histoire) et les enfants n\'ont plus besoin de développer leur imagination dans le jeu ou la lecture. Or, le jeu et la lecture sont nécessaires au développement des enfants.',
                        ],
                    ],
                    'correction' => 'La télévision pour les enfants
La télévision occupe une place importante dans la vie des enfants. Selon le premier document, elle peut proposer des programmes de qualité, mais il est nécessaire de limiter le temps d\'écran. En revanche, le second document souligne que la télévision peut être pratique pour occuper les enfants, tout en présentant des effets négatifs sur leur créativité.

À mon avis, la télévision peut être utile pour les enfants, mais elle doit être utilisée avec modération. D\'un côté, elle permet d\'apprendre de nouvelles choses grâce à des émissions éducatives et de se divertir. Elle peut aussi aider les parents à occuper leurs enfants pendant un certain temps. D\'un autre côté, une utilisation excessive peut nuire au développement des enfants, en limitant leur imagination et leur activité physique. De plus, regarder la télévision passivement peut réduire les interactions sociales. Ainsi, il est important que les parents encadrent son utilisation et encouragent d\'autres activités plus enrichissantes.',
                ],
            ],
        ],
        [
            'combo' => 15,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous partez en voyage et vous laissez votre appartement à un ami qui veut venir rester chez-vous pendant vos vacances. Vous lui envoyez un message pour décrire votre appartement (immeuble, logement, accès…).',
                    'correction' => 'Salut !

Je te donne quelques informations sur mon appartement pour ton séjour. J\'habite dans un immeuble calme avec un digicode à l\'entrée. L\'appartement se trouve au troisième étage, avec ascenseur.

C\'est un deux-pièces assez lumineux, avec un salon, une chambre, une cuisine équipée et une salle de bain. Tu trouveras tout ce dont tu as besoin sur place.

Pour y accéder, le métro est à 5 minutes à pied et il y a aussi des commerces juste à côté.

Si tu as besoin de quelque chose ou si tu as des questions, n\'hésite pas à me contacter. Profite bien de ton séjour !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Écrivez un article de blog sur le souvenir de voyage que vous avez le plus aimé.',
                    'correction' => 'Mon plus beau souvenir de voyage au Maroc
Chers lecteurs,
Mon plus beau souvenir de voyage reste sans aucun doute mon séjour au Maroc. C\'était la première fois que je découvrais un pays aussi riche en culture et en traditions, et cette expérience m\'a profondément marqué.
Dès mon arrivée à Marrakech, j\'ai été impressionné(e) par l\'ambiance de la ville : les couleurs, les odeurs, les marchés animés et l\'accueil chaleureux des habitants. J\'ai particulièrement aimé me promener dans les souks, où l\'on trouve des objets artisanaux magnifiques. Chaque coin de rue semblait raconter une histoire.
Mais le moment le plus mémorable de ce voyage a été mon excursion dans le désert. Passer une nuit sous les étoiles, loin du bruit de la ville, a été une expérience unique. Le silence, le ciel rempli d\'étoiles et la beauté des paysages m\'ont donné un sentiment de liberté incroyable.
Ce voyage m\'a permis de découvrir une nouvelle culture, de rencontrer des personnes formidables et de sortir de ma zone de confort. C\'est un souvenir que je garderai toujours avec moi.',
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
                    'correction' => 'Vivre chez ses parents : pour ou contre ?
La question de vivre chez ses parents fait débat aujourd\'hui. Selon le premier document, cela présente des avantages, notamment économiques, car les jeunes peuvent économiser de l\'argent et vivre dans de bonnes conditions. En revanche, le second document souligne un manque de liberté et d\'indépendance chez ceux qui restent chez leurs parents.
À mon avis, vivre chez ses parents peut être une bonne solution, surtout pendant les études. Cela permet de réduire les dépenses, d\'avoir un certain confort et de se concentrer sur ses objectifs. De plus, le soutien familial est important dans certaines périodes de la vie. Cependant, il est vrai que cela peut limiter l\'autonomie. Vivre seul permet de prendre des responsabilités, d\'apprendre à gérer son quotidien et de devenir plus indépendant. C\'est pourquoi je pense que vivre chez ses parents est utile temporairement, mais qu\'il est important de quitter le foyer familial à un moment donné pour se construire pleinement.',
                ],
            ],
        ],
        [
            'combo' => 16,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous faites du sport dans un club. Vous venez de remporter une compétition. Vous écrivez un courriel à vos amis pour leur raconter cet évènement sportif et annoncer votre réussite sportive.',
                    'correction' => 'Salut à tous,
Je voulais vous annoncer une excellente nouvelle : j\'ai remporté la compétition organisée par mon club de sport ! Je suis vraiment très heureux(se) et fier(ère) de ce résultat, car j\'ai beaucoup travaillé pour y arriver.
La compétition était intense, avec de très bons participants, mais j\'ai réussi à donner le meilleur de moi-même. L\'ambiance était incroyable et le soutien du public m\'a beaucoup motivé(e).
Cette victoire est très importante pour moi et me donne encore plus envie de progresser.
J\'espère pouvoir fêter ça avec vous très bientôt !
À très vite.',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Le site « colocation.com » recherche des témoignages sur vos expériences de colocation. Vous avez déjà habité en colocation avec des amis. Vous racontez votre expérience aux membres du site internet. Vous donnez votre opinion sur ce mode de logement.',
                    'correction' => 'J\'ai eu l\'occasion de vivre en colocation avec des amis pendant deux ans, et cette expérience a été globalement très positive. Au début, j\'avais quelques inquiétudes, notamment concernant l\'organisation et le respect des règles de vie. Cependant, nous avons rapidement trouvé un bon équilibre.
Nous avions mis en place une répartition des tâches ménagères et des règles simples pour éviter les conflits. Par exemple, chacun participait au nettoyage et respectait les espaces communs. La communication a joué un rôle essentiel dans la réussite de notre colocation.
Ce mode de logement présente de nombreux avantages. Il permet de réduire les coûts, de ne pas se sentir seul et de partager des moments conviviaux. Cependant, il faut aussi accepter certaines contraintes, comme le manque d\'intimité ou les différences d\'habitudes.
À mon avis, la colocation est une expérience enrichissante, à condition de bien choisir ses colocataires et de maintenir une bonne communication.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Le grossissement des villes',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'De nos jours, les villes grossissent toujours plus. Malheureusement, ce phénomène a un impact fort sur l\'environnement. Car plus une ville grossit, plus elle a des effets négatifs sur la nature et donc, ensuite, sur l\'homme. L\'effet négatif le plus visible est la déforestation, qui réduit les espaces verts capables de retenir le carbone.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Plus de la moitié de l\'humanité vit en ville ; la vie urbaine est donc le principal enjeu écologique. On entend souvent dire que l\'organisation actuelle des villes n\'est pas écologique, et que le grossissement des villes ne fait qu\'augmenter le problème. Pourtant, il faut se méfier des apparences : les villes ne sont pas toujours aussi antiécologiques qu\'on l\'imagine. Par exemple, la consommation d\'énergie d\'un citadin est moins importante que celle d\'un habitant de la campagne.',
                        ],
                    ],
                    'correction' => 'Le grossissement des villes : débat ouvert
Aujourd\'hui, les villes connaissent une croissance importante. Selon le premier document, ce phénomène a des effets négatifs sur l\'environnement, notamment en augmentant la pollution. En revanche, le second document souligne que la vie urbaine peut aussi représenter un enjeu écologique majeur et offrir des solutions pour mieux organiser les ressources.
À mon avis, le grossissement des villes présente à la fois des risques et des opportunités. D\'un côté, l\'augmentation de la population urbaine entraîne des problèmes comme la pollution, la circulation ou la réduction des espaces naturels. Cela peut avoir un impact négatif sur la qualité de vie des habitants. D\'un autre côté, les villes peuvent devenir plus écologiques si elles sont bien organisées. Par exemple, le développement des transports en commun, des espaces verts et des énergies renouvelables peut réduire les effets négatifs. Ainsi, le problème ne vient pas seulement de la taille des villes, mais surtout de leur gestion.',
                ],
            ],
        ],
        [
            'combo' => 17,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Votre ami Cédric a accepté de garder votre maison et jardin pendant vos vacances. Écrivez un message pour lui dire ce qu\'il doit faire.',
                    'correction' => 'Salut Cédric,
Merci encore d\'avoir accepté de garder la maison pendant mon absence !
Peux-tu arroser les plantes deux fois par semaine, surtout celles du jardin ? Il faudra aussi nourrir le chat matin et soir et vérifier qu\'il a toujours de l\'eau. Pense à ouvrir les fenêtres un peu chaque jour pour aérer la maison.
Le courrier arrivera dans la boîte aux lettres, tu peux le garder sur la table du salon. Si tu remarques quelque chose d\'anormal, n\'hésite pas à me contacter.
Encore merci pour ton aide, ça me rassure beaucoup !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Suite à un voyage récent effectué avec une agence de voyages, vous êtes insatisfait(e) des prestations reçues. Rédigez un courriel de réclamation en exprimant votre mécontentement. Décrivez les problèmes rencontrés et demandez une solution de la part de l\'agence.',
                    'correction' => 'Objet : Réclamation concernant mon voyage récent
Madame, Monsieur,
Je me permets de vous écrire afin d\'exprimer mon mécontentement concernant le voyage que j\'ai récemment effectué avec votre agence.
En effet, plusieurs problèmes ont affecté mon séjour. Tout d\'abord, l\'hôtel réservé ne correspondait pas à la description annoncée : la chambre était peu propre et les équipements étaient insuffisants. De plus, certaines activités prévues dans le programme ont été annulées sans explication. Enfin, le service client sur place était difficilement joignable, ce qui a rendu la situation encore plus frustrante.
Ces désagréments ont considérablement nui à la qualité de mon voyage, alors que j\'avais choisi votre agence pour son sérieux.
Je vous demande donc de bien vouloir me proposer une solution, telle qu\'un remboursement partiel ou un geste commercial.
Dans l\'attente de votre réponse, je vous prie d\'agréer, Madame, Monsieur, l\'expression de mes salutations distinguées.',
                ],
                [
                    'task' => 3,
                    'prompt' => 'La restauration rapide',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Les restaurants rapides proposent des plats équilibrés et variés, et ils respectent les normes d\'hygiène et les variétés de produits qui sont bons, et c\'est le client qui compose son menu, donc il en est responsable.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Les spécialistes affirment que manger régulièrement dans des restaurants de fast-food, qui proposent de la restauration rapide, est dangereux pour la santé. La nourriture servie est souvent la même : frites, hamburgers et boissons sucrées. Ces aliments contiennent une grande quantité de calories, bien trop pour un seul repas. De plus, la plupart des produits dans ces restaurants sont emballés dans du plastique. Par conséquent, manger dans un fast-food augmente la production de déchets plastiques, ce qui est nuisible pour l\'environnement.',
                        ],
                    ],
                    'correction' => 'La restauration rapide
La restauration rapide est aujourd\'hui très présente dans notre quotidien. Selon le premier document, elle propose des plats variés et respecte les normes d\'hygiène, tout en laissant au client la liberté de choisir son menu. En revanche, le second document souligne que ces aliments peuvent être dangereux pour la santé en raison de leur composition.

À mon avis, la restauration rapide présente à la fois des avantages et des inconvénients, mais elle doit être consommée avec modération. D\'un côté, elle est pratique, rapide et souvent accessible à tous. Elle permet de manger facilement lorsqu\'on manque de temps. De plus, certains restaurants proposent aujourd\'hui des options plus équilibrées, comme des salades ou des menus personnalisés. Cependant, il ne faut pas ignorer les effets négatifs. Une consommation régulière peut entraîner des problèmes de santé, comme l\'obésité ou des maladies cardiovasculaires, à cause de la quantité de sucre, de sel et de graisses. Ainsi, il est important d\'adopter une alimentation équilibrée et de ne pas abuser de ce type de restauration.',
                ],
            ],
        ],
        [
            'combo' => 18,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'Vous partez en vacances avec vos amis, vous avez trouvé un hôtel. Vous écrivez un message à vos amis pour décrire cet hôtel (localisation, prix, équipements, etc.) et vous leur proposez de réserver cet hôtel.',
                    'correction' => 'Salut tout le monde !
J\'ai trouvé un super hôtel pour nos vacances. Il est très bien situé, près de la plage et du centre-ville, donc on pourra tout faire à pied. Le prix est raisonnable et les chambres sont confortables. Il y a aussi une piscine, le Wi-Fi gratuit et un petit-déjeuner inclus.
Les avis sont très positifs, surtout pour la propreté et l\'accueil. Franchement, ça a l\'air parfait pour nous !
Qu\'est-ce que vous en pensez ? Si vous êtes d\'accord, je peux faire la réservation rapidement',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Vous êtes parti(e) travailler à l\'étranger. Vous envoyez un message à vos amis pour raconter cette nouvelle expérience professionnelle. Vous expliquez ce que vous avez le plus aimé.',
                    'correction' => 'Salut tout le monde !
Je voulais vous donner des nouvelles depuis mon arrivée à l\'étranger. Cette expérience professionnelle est vraiment incroyable ! Au début, ce n\'était pas facile de m\'adapter à une nouvelle culture et à une langue différente, mais petit à petit, je me sens de plus en plus à l\'aise.
Ce que j\'ai le plus aimé, c\'est de travailler avec des personnes de nationalités différentes. Cela m\'a permis de découvrir de nouvelles façons de travailler et de penser. J\'apprends aussi beaucoup au niveau professionnel et je gagne en confiance.
Franchement, c\'est une expérience très enrichissante que je ne regrette pas du tout !',
                ],
                [
                    'task' => 3,
                    'prompt' => 'Les jeux vidéo, pour ou contre ?',
                    'documents' => [
                        [
                            'title' => 'Document 1',
                            'content' => 'Des études ont montré que certaines zones du cerveau de l\'adulte peuvent être développées en jouant aux jeux vidéo. Ainsi, il peut être intéressant de jouer aux jeux vidéo, car ils permettent, par exemple, d\'améliorer la capacité d\'analyse, la capacité à faire des choix et la rapidité de réaction. C\'est une bonne nouvelle car la répartition des joueurs par âge montre que 83 % des joueurs sont des adultes. Cependant, il convient de rester prudent car certains jeux vidéo ne favorisent pas ce type d\'amélioration sur le cerveau.',
                        ],
                        [
                            'title' => 'Document 2',
                            'content' => 'Pendant trois ans, des enfants âgés de 8 à 17 ans ont participé à une étude sur les jeux vidéo. Les résultats de cette étude montrent que les enfants qui jouent beaucoup aux jeux vidéo sont plus violents, plus nerveux et plus stressés que ceux qui ne jouent pas ou peu. Ceux qui jouent beaucoup ont également moins de bons résultats à l\'école. Il est donc conseillé aux parents d\'être vigilants et de limiter l\'usage de ces jeux par leurs enfants.',
                        ],
                    ],
                    'correction' => 'Les jeux vidéo : pour ou contre ?
Les jeux vidéo occupent aujourd\'hui une place importante dans la société. Selon le premier document, ils permettent d\'améliorer certaines compétences comme la capacité d\'analyse, la prise de décision et la rapidité de réaction. En revanche, le second document souligne que les joueurs, notamment les jeunes, peuvent devenir plus violents et stressés.

À mon avis, les jeux vidéo présentent à la fois des avantages et des inconvénients, mais tout dépend de l\'usage que l\'on en fait. D\'un côté, ils peuvent être bénéfiques, car ils stimulent le cerveau et développent certaines compétences utiles dans la vie quotidienne. De plus, ils peuvent être un moyen de se détendre et de se divertir après une journée de travail ou d\'études. D\'un autre côté, une utilisation excessive peut entraîner des effets négatifs, comme la dépendance, le manque d\'activité physique ou encore des problèmes de comportement. C\'est pourquoi il est important de fixer des limites et de jouer de manière raisonnable. Ainsi, les jeux vidéo ne sont pas dangereux en eux-mêmes, mais leur utilisation doit être encadrée.',
                ],
            ],
        ],
        [
            'combo' => 19,
            'tasks' => [
                [
                    'task' => 1,
                    'prompt' => 'L\'anniversaire de votre ami approche à grands pas et vous avez décidé de lui offrir un voyage comme cadeau. Rédigez un message pour l\'informer des préparatifs que vous avez faites (destination, dates, préparatifs, etc).',
                    'correction' => 'Salut ! Ton anniversaire approche et j\'ai une surprise pour toi.
Je t\'ai organisé un voyage ! Nous allons à Montréal du 15 au 18 avril. J\'ai déjà réservé les billets d\'avion et un bel hôtel au centre-ville. On pourra visiter la ville, goûter des spécialités locales et même faire quelques activités culturelles.
Prépare des vêtements confortables et adaptés à la météo, je m\'occupe du reste ! J\'ai aussi prévu quelques surprises sur place !
J\'espère que ça te fera plaisir, j\'ai vraiment hâte de partager ce moment avec toi. Prépare-toi pour une aventure inoubliable !',
                ],
                [
                    'task' => 2,
                    'prompt' => 'Un internaute a publié le message suivant : « Je vais partir étudier un an à l\'étranger et j\'ai peur ». Rédigez une réponse pour partager votre expérience personnelle. Parlez des défis que vous avez rencontrés, des solutions que vous avez trouvées, et des bénéfices que vous avez tirés de cette expérience.',
                    'correction' => 'Je comprends très bien cette peur, car partir étudier à l\'étranger est une grande étape. Avant mon départ, j\'avais moi aussi beaucoup d\'appréhension face à l\'inconnu : une nouvelle culture, une langue différente et l\'éloignement de mes proches. Les premières semaines ont été difficiles, notamment à cause de la solitude et du manque de repères.
Pour surmonter ces difficultés, j\'ai fait l\'effort de m\'intégrer en participant à des activités et en parlant avec d\'autres étudiants. J\'ai également essayé de pratiquer la langue le plus possible, même si ce n\'était pas toujours facile. Le fait de rester en contact avec ma famille m\'a aussi beaucoup aidé à me sentir rassuré.
Avec le temps, j\'ai pris confiance en moi et je me suis adapté à mon nouvel environnement. Cette expérience m\'a permis de devenir plus autonome, d\'améliorer mes compétences linguistiques et de découvrir une nouvelle culture. Aujourd\'hui, je considère que c\'est une expérience très enrichissante qui en vaut vraiment la peine.',
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
                    'correction' => 'La réduction du temps de travail
La question de la réduction du temps de travail suscite de nombreux débats. Selon le premier document, travailler moins permet aux employés de mieux équilibrer leur vie personnelle et professionnelle et de réduire le stress. En revanche, le second document souligne que cela exige une bonne organisation pour maintenir l\'efficacité des entreprises.
À mon avis, la réduction du temps de travail présente plus d\'avantages que d\'inconvénients. En effet, elle permet d\'améliorer la qualité de vie des employés, qui peuvent consacrer davantage de temps à leur famille et à leurs loisirs. Cela contribue à leur bien-être et diminue les risques de fatigue ou de burn-out. De plus, un employé reposé est généralement plus motivé et plus productif. Cependant, il est essentiel que les entreprises s\'adaptent en mettant en place une organisation efficace. Par exemple, une meilleure répartition des tâches ou l\'utilisation des nouvelles technologies peut compenser la réduction du temps de travail. Ainsi, cette mesure peut être bénéfique pour tous si elle est bien appliquée.',
                ],
            ],
        ],
];
