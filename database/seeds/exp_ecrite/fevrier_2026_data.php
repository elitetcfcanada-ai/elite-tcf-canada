<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 1,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un message dans le journal de votre université pour rechercher un partenaire avec qui faire du sport.',
                'correction' => "Bonjour,\nJe suis étudiant à l'université et je cherche un partenaire pour faire du sport régulièrement. J'aimerais pratiquer surtout le jogging, la musculation ou le fitness, deux à trois fois par semaine, après les cours ou le week-end. Je suis motivé, débutant à intermédiaire, et je cherche quelqu'un de sérieux pour rester motivé ensemble. Si vous êtes intéressé(e), n'hésitez pas à me contacter pour en discuter et organiser les séances.\n\nMerci et à bientôt.",
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un article de blog pour raconter votre arrivée dans un pays étranger en donnant vos impressions.',
                'correction' => "Ma première impression en arrivant dans un pays étranger\n\nChers lecteurs,\n\nMon arrivée dans ce pays étranger a été une expérience à la fois excitante et impressionnante. Dès les premiers instants, j'ai ressenti un mélange de curiosité et d'appréhension. Tout était nouveau : la langue, les paysages, les habitudes et même la manière de se déplacer. J'ai été surpris par l'organisation et la différence culturelle par rapport à mon pays d'origine.\n\nCe qui m'a le plus marqué, c'est l'accueil des habitants. Même si la communication n'était pas toujours facile, beaucoup de personnes se montraient patientes et bienveillantes. J'ai également apprécié la propreté des lieux et le respect des règles.\n\nCependant, l'adaptation n'a pas été immédiate. Le climat et le rythme de vie étaient différents, ce qui demandait un temps d'adaptation. Malgré cela, cette arrivée reste une expérience enrichissante qui m'a donné envie de découvrir davantage ce pays et sa culture.",
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
                'correction' => "Le rôle de la télévision dans l'éducation des enfants\n\nLa télévision occupe une place importante dans la vie quotidienne des enfants. Elle permet de diffuser des informations, des divertissements variés et de transmettre des connaissances culturelles accessibles à tous. Cependant, certaines émissions peuvent véhiculer des stéréotypes et réduire le temps consacré à des activités plus enrichissantes, comme la lecture, le sport ou les interactions sociales.\n\nÀ mon avis, la télévision peut jouer un rôle positif dans l'éducation des enfants si son utilisation est bien encadrée. Elle peut éveiller la curiosité, développer la culture générale et sensibiliser les enfants à des sujets importants. Par exemple, les documentaires ou les émissions éducatives peuvent compléter l'apprentissage scolaire. Toutefois, une exposition excessive peut nuire au développement de l'enfant et limiter son imagination. Il est donc essentiel que les parents contrôlent le temps passé devant l'écran et choisissent des programmes adaptés à l'âge de l'enfant. Un bon équilibre entre télévision, études et activités physiques est indispensable.",
            ],
        ],
    ],
    [
        'combo' => 2,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Un nouveau restaurant vient d\'ouvrir près de chez vous. Vous écrivez à un(e) ami(e) pour lui proposer d\'y aller avec vous. Vous décrivez le restaurant (cuisine, prix, décoration, etc.).',
                'correction' => "Salut,\nJ'espère que tu vas bien. Je voulais te proposer d'aller ensemble dans un nouveau restaurant qui vient d'ouvrir près de chez moi. Il propose une cuisine variée, surtout des plats méditerranéens, avec des produits frais. Les prix sont raisonnables et la décoration est moderne, avec une ambiance chaleureuse. J'aimerais beaucoup le découvrir avec toi ce week-end, par exemple samedi soir. Dis-moi si tu es disponible.\n\nÀ bientôt,\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez visité une ville que vous ne connaissiez pas. Vous avez envie de partager votre découverte. Vous postez un message sur un site Internet dédié aux voyages. Racontez votre expérience et expliquez ce qui vous a plu et ce qui vous a déplu dans la ville.',
                'correction' => "J'ai récemment visité une ville que je ne connaissais pas et j'ai eu envie de partager mon expérience. Dès mon arrivée, j'ai été impressionné par l'architecture et l'ambiance générale. Le centre-ville était très animé, avec de nombreux cafés, musées et espaces verts. J'ai particulièrement aimé me promener à pied et découvrir les spécialités locales dans les restaurants. Les habitants étaient accueillants et toujours prêts à aider.\n\nCependant, certains aspects m'ont moins plu. Les transports en commun étaient parfois en retard et les prix dans les zones touristiques étaient assez élevés. De plus, certains quartiers étaient très fréquentés, ce qui rendait les visites moins agréables.\n\nMalgré ces petits points négatifs, cette ville reste une très belle découverte. J'en garde un bon souvenir et je la recommande aux voyageurs curieux qui aiment explorer de nouveaux endroits.",
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
                        'content' => 'Être adulte et vivre en colocation ? C\'est un choix qui permet d\'accéder facilement à un logement plus spacieux et économique. Il est vrai que vous n\'aurez qu\'une chambre pour vous et que vous devrez partager la cuisine, le salon et la salle de bain. Toutefois, une colocation peut inclure une maison avec jardin ou un grand appartement en centre-ville ! De plus, en partageant le loyer et les charges avec vos colocataires, vous réduirez considérablement vos dépenses par rapport à un appartement individuel. Alors que vous n\'aurez qu\'une chambre pour vous et que vous devrez partager les espaces communs, une colocation offre des opportunités de logements bien plus abordables.',
                    ],
                ],
                'correction' => "La vie en colocation entre adultes\n\nVivre en colocation entre adultes demande une bonne organisation et le respect de règles communes afin d'éviter les conflits du quotidien. Il est important de communiquer et de respecter les habitudes de chacun. Cependant, la colocation permet aussi d'accéder à un logement plus spacieux et plus économique, en partageant le loyer et les charges.\n\nÀ mon avis, la colocation entre adultes peut être une très bonne solution si les colocataires sont responsables. Elle permet de réduire les dépenses et d'améliorer le confort de vie, surtout dans les grandes villes. Par exemple, partager un grand appartement ou une maison avec jardin est souvent impossible seul. Toutefois, la colocation exige de la tolérance, du dialogue et du respect des règles. Sans communication, les petits problèmes peuvent vite devenir des conflits. C'est pourquoi cette option convient surtout aux personnes capables de s'adapter et de vivre en communauté.",
            ],
        ],
    ],
    [
        'combo' => 3,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un message à votre ami(e) qui souhaite suivre des cours de langue dans votre école. Donnez les détails spécifiques pour aider votre ami(e) à faire son choix. (lieu, tarifs, types de cours disponibles, etc.).',
                'correction' => "Salut Raoul,\n\nJ'espère que tu vas bien, tu m'as parlé de ton projet de suivre des cours de langue, et je te conseille vraiment mon école. Elle est située en centre-ville, facilement accessible en bus et en métro. Les tarifs sont raisonnables, avec des formules mensuelles et des réductions pour les inscriptions longues. Il y a plusieurs types de cours : cours intensifs, cours du soir et cours en ligne, selon ton emploi du temps. Les classes sont en petits groupes et les professeurs sont très expérimentés. Tu peux aussi passer un test de niveau gratuit avant de commencer. À mon avis, c'est une très bonne option pour progresser rapidement.\n\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous travaillez dans une association qui aide les personnes âgées. Rédigez un article de blog pour raconter vos expériences et convaincre d\'autres personnes de rejoindre l\'association.',
                'correction' => "Une expérience humaine enrichissante au service des personnes âgées\n\nJe travaille depuis plusieurs mois dans une association qui aide les personnes âgées, et cette expérience m'a beaucoup marqué. Chaque jour, nous accompagnons des personnes souvent seules, en les aidant dans leur quotidien : discussions, sorties, aide administrative ou simplement présence et écoute. Ces moments sont très précieux, autant pour elles que pour nous.\n\nCe que j'ai le plus apprécié, c'est le lien humain qui se crée rapidement. Les personnes âgées ont beaucoup d'histoires à raconter et de conseils à partager. Grâce à cette association, je me sens utile et engagé dans une action solidaire.\n\nJe recommande vivement à toute personne disponible et motivée de rejoindre notre association. Même quelques heures par semaine peuvent faire une grande différence. Aider les autres, c'est aussi se sentir mieux soi-même et donner du sens à son temps libre.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les animaux de compagnie pour les enfants, pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Offrir un animal de compagnie à un enfant présente de nombreux avantages, comme le soulignent beaucoup de psychologues. Pour des enfants qui n\'ont pas des frères et/ou des sœurs, l\'animal est un compagnon qui leur évitera la solitude. Grâce à lui, un enfant prendra confiance en lui et il apprendra vite qu\'un animal est un être vivant qui a besoin d\'attention et de respect. En sa présence, l\'enfant se sentira en sécurité et pourra agir de manière autonome, sans l\'aide de ses parents.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Beaucoup d\'enfants demandent, un jour ou l\'autre, un animal à leurs parents, le plus souvent un chien ou un chat. Mais même si vous avez envie de faire plaisir à votre enfant, il vaut mieux réfléchir sérieusement avant d\'acheter un animal domestique. L\'animal devient un nouveau membre de la famille et représente un engagement sur de nombreuses années. Or, avoir un animal coûte souvent très cher, et c\'est une grande responsabilité. On ne peut pas le traiter comme un jouet que l\'on met à la poubelle quand l\'enfant s\'en désintéresse.',
                    ],
                ],
                'correction' => "Les animaux de compagnie pour les enfants : pour ou contre ?\n\nOffrir un animal de compagnie à un enfant présente plusieurs avantages, notamment sur le plan affectif et éducatif. L'animal peut aider l'enfant à se sentir moins seul, à gagner en confiance et à apprendre le respect des êtres vivants. Cependant, accueillir un animal implique aussi un engagement important, des coûts élevés et une responsabilité sur le long terme.\n\nÀ mon avis, offrir un animal de compagnie à un enfant peut être une bonne idée si les parents sont prêts à s'impliquer. Un animal peut apprendre à l'enfant la responsabilité et l'attention envers un être vivant. Par exemple, nourrir un animal ou s'en occuper chaque jour aide l'enfant à développer de bonnes habitudes. Toutefois, l'animal ne doit jamais être considéré comme un jouet. Les parents doivent assumer les dépenses et le temps nécessaires, même si l'enfant se désintéresse avec le temps. Cette décision doit donc être réfléchie et prise par toute la famille.",
            ],
        ],
    ],
    [
        'combo' => 4,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un message pour inviter vos amis à une fête de fin d\'année.',
                'correction' => "Salut les amis,\nJe vous invite à une fête de fin d'année pour célébrer ensemble autour d'un bon moment. La fête aura lieu samedi 28 décembre, à partir de 19 h, chez moi. Il y aura de la musique, de quoi manger et une bonne ambiance pour clôturer l'année. Chacun peut apporter quelque chose à boire ou à grignoter. J'espère vraiment vous voir nombreux pour partager cette soirée ensemble. Merci de me confirmer si vous pouvez venir.\n\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé des vacances au Canada par le biais d\'une agence de voyage. Écrivez un commentaire pour raconter votre expérience vécue durant ce voyage.',
                'correction' => "J'ai passé des vacances au Canada grâce à une agence de voyage, et cette expérience a été très positive. L'organisation était excellente, du début à la fin. Les hôtels étaient confortables et bien situés, et les transports étaient bien organisés. J'ai pu visiter plusieurs villes et découvrir des paysages magnifiques, comme les lacs, les montagnes et les parcs nationaux.\n\nCe que j'ai le plus apprécié, c'est le professionnalisme des guides, toujours disponibles et attentifs. Les activités proposées étaient variées et adaptées à tous. Grâce à cette agence, j'ai pu profiter pleinement de mon voyage sans stress. Je recommande vivement cette expérience à toutes les personnes qui souhaitent découvrir le Canada dans de bonnes conditions.",
            ],
            [
                'task' => 3,
                'prompt' => 'Limitation des voitures dans les centres-villes.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Avec des taux de pollution alarmants constatés dans plusieurs endroits dans le monde, plusieurs villes ont réussi leur pari d\'interdire la circulation des voitures en zones urbaines. La capitale de Norvège, Oslo, a récemment opté pour cette solution et s\'en félicite, estimant que c\'est une décision bénéfique pour tout le monde. Après un certain temps, les accidents diminueront, la dépendance au pétrole baissera et la qualité d\'air sera meilleure !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Beaucoup de villes se lancent dans des projets d\'interdiction de voitures en zone urbaine sans mettre en place les outils et les infrastructures nécessaires pour réussir cette transition. Certes, en diminuant les voitures, on aura moins pollué, mais en contrepartie, il faut prévoir, entre autres, de gigantesques parkings pour garer les voitures, opter davantage pour le transport en commun (métros et bus) et prévoir des autorisations de circulation pour certains corps de métier (comme la police, les urgentistes, les livreurs, etc.).',
                    ],
                ],
                'correction' => "Limiter les voitures dans les centres-villes : une bonne solution ?\n\nFace à la pollution croissante, plusieurs villes ont décidé de limiter ou d'interdire la circulation des voitures dans les centres-villes afin d'améliorer la qualité de l'air et de réduire les accidents. Cependant, certains estiment que ces décisions sont prises trop rapidement, sans prévoir suffisamment d'infrastructures adaptées, comme les transports en commun ou les parkings.\n\nÀ mon avis, limiter les voitures dans les centres-villes est une bonne initiative, mais elle doit être bien préparée. Réduire la circulation permet de diminuer la pollution et d'améliorer la qualité de vie des habitants. Par exemple, des rues sans voitures sont plus calmes et plus sûres pour les piétons. Toutefois, cette transition doit s'accompagner de solutions efficaces, comme des transports en commun performants et des parkings accessibles. Sans ces aménagements, les habitants et les professionnels peuvent rencontrer de grandes difficultés. Une bonne organisation est donc indispensable pour réussir ce changement.",
            ],
        ],
    ],
    [
        'combo' => 5,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Je cherche un vélo en bon état et bon marché. Contactez-moi par courriel : mathieu@gmail.com » Vous avez un vélo à vendre. Vous écrivez un courriel pour décrire votre vélo et proposer un prix. Vous lui donnez un rendez-vous pour essayer le vélo.',
                'correction' => "Objet : Vélo à vendre en bon état\n\nBonjour,\n\nJ'ai vu votre annonce concernant la recherche d'un vélo en bon état et à bon prix. Je possède justement un vélo à vendre. C'est un vélo de ville, très bien entretenu, utilisé occasionnellement. Les freins et les vitesses fonctionnent parfaitement, et les pneus sont en bon état.\n\nJe le vends au prix de 120 dollars, négociable. Si vous le souhaitez, vous pouvez venir l'essayer avant de prendre une décision. Je vous propose un rendez-vous samedi après-midi, vers 15 h, dans mon quartier, près du parc central.\n\nN'hésitez pas à me confirmer votre disponibilité.\n\nCordialement,\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé une journée à la campagne avec vos amis. À votre retour, vous écrivez un message sur votre forum pour raconter à vos amis comment cette journée s\'est passée. Vous expliquez ce que vous avez aimé (activités, lieu, animaux, etc…)',
                'correction' => "Bonjour à tous,\nJe voulais partager avec vous ma journée à la campagne que j'ai passée récemment avec des amis. C'était une journée vraiment agréable et reposante. Le lieu était magnifique, avec beaucoup de verdure, des champs et un air très frais. Nous avons fait une longue promenade, puis un pique-nique près d'une petite rivière.\n\nCe que j'ai le plus aimé, c'est le calme et le contact avec la nature. Nous avons vu des animaux comme des vaches, des moutons et des chevaux, ce qui était très dépaysant. L'après-midi, nous avons joué à des jeux en plein air et pris beaucoup de photos. Cette journée m'a permis de me détendre et d'oublier le stress de la ville. C'était une très belle expérience que j'aimerais refaire.",
            ],
            [
                'task' => 3,
                'prompt' => 'La sévérité des parents envers les enfants',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je vais bientôt avoir 22 ans et j\'habite toujours chez mes parents. Mon père et ma mère restent autoritaires avec moi, même si je suis majeure. Quand j\'étais mineure, je n\'avais pas le droit de dormir dehors, ni même de dépasser 21h lorsque je sortais avec des amies. Maintenant, peu de choses ont changé ; certes, j\'ai le droit de veiller plus tard la nuit, mais ma mère ne cesse de m\'appeler sur mon téléphone portable jusqu\'à ce que je sois de retour.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Les parents ont parfois peur d\'être trop sévères avec leurs enfants. Ils craignent qu\'à cause d\'un excès d\'autorité, leurs enfants ne s\'épanouissent pas et manquent plus tard de personnalité. Même si les parents acceptent, par amour, tout ce que leurs enfants demandent, cela pourrait avoir des effets négatifs lorsqu\'ils passent à l\'âge adulte. En effet, pour vivre en communauté, il y a certaines règles à respecter.',
                    ],
                ],
                'correction' => "La sévérité des parents envers les enfants\n\nCertains parents restent très autoritaires avec leurs enfants, même à l'âge adulte, par souci de protection et de sécurité. Cependant, d'autres estiment qu'un excès d'autorité peut freiner l'épanouissement des jeunes et nuire au développement de leur personnalité, surtout lorsqu'ils deviennent adultes et doivent respecter des règles par eux-mêmes.\n\nÀ mon avis, la sévérité des parents peut être utile, mais seulement si elle est équilibrée. Les règles sont nécessaires pour apprendre le respect et les limites. Par exemple, fixer des horaires permet d'assurer la sécurité des enfants. Toutefois, une autorité trop stricte peut provoquer de la frustration et empêcher l'autonomie. Les parents doivent donc adapter leur attitude à l'âge de l'enfant et instaurer un dialogue. En laissant progressivement plus de liberté tout en maintenant certaines règles, ils aident leurs enfants à devenir responsables et capables de vivre en société.",
            ],
        ],
    ],
    [
        'combo' => 6,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez changer la décoration de votre appartement (meubles, peinture, objets, etc.). Vous écrivez un message à un(e) ami(e). Vous lui décrivez votre projet et vous lui demandez de vous aider.',
                'correction' => "Salut Yassine,\nJe pense changer complètement la décoration de mon appartement et j'aimerais te demander de l'aide. Je voudrais remplacer quelques meubles, repeindre les murs et ajouter des objets décoratifs pour rendre l'espace plus agréable et moderne. J'hésite entre plusieurs styles et couleurs, et j'ai besoin de ton avis pour faire les bons choix. Tu pourrais m'aider à choisir la peinture, les meubles ou même m'accompagner dans les magasins.\n\nSi tu es disponible ces prochains jours, ça me ferait vraiment plaisir de préparer ce projet avec toi.",
            ],
            [
                'task' => 2,
                'prompt' => '« École de musique ! Cours gratuits, concerts, jeux. Rendez-vous vendredi, à partir de 9 heures » Vous avez participé à cet évènement. Vous écrivez à vos amis pour raconter votre expérience et vous donnez votre opinion sur cette journée.',
                'correction' => "Mes chers amis,\n\nJ'espère que vous allez bien. Je voulais vous raconter ma participation à la journée de l'École de musique qui a eu lieu vendredi. J'y suis allé dès le matin et j'ai trouvé l'événement très bien organisé. Il y avait des cours gratuits pour découvrir différents instruments, des jeux musicaux et même des concerts en direct. L'ambiance était conviviale et accessible à tous, même aux débutants.\n\nCe que j'ai le plus apprécié, c'est la possibilité d'essayer les instruments sans pression et d'échanger avec les professeurs. Cette journée m'a permis de mieux comprendre le monde de la musique et de passer un très bon moment. À mon avis, c'est une excellente initiative que je recommande vivement.",
            ],
            [
                'task' => 3,
                'prompt' => 'Faut-il faire ses courses dans des petits magasins ou dans des supermarchés ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Le supermarché est très pratique ; on y trouve une grande variété de produits, tous à portée de main. Vous pouvez garer votre voiture dans le parking et faire le tour des rayons pour acheter tout ce dont vous avez besoin : fruits, légumes, fromages, viandes, boissons… De plus, les supermarchés offrent plusieurs marques pour un même produit, tout en proposant régulièrement des promotions et des remises.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'ASSOCIATION POUR LA SAUVEGARDE DES PETITS COMMERCES — Le défi « Février sans supermarché » a été créé pour limiter la superpuissance des supermarchés et, par conséquent, permettre aux petits commerces de survivre et de réaliser des chiffres d\'affaires plus conséquents. Ce défi consiste à boycotter les supermarchés pendant une durée d\'un mois, en faisant toutes ses courses dans les épiceries de quartier. Le client aura tout à gagner : il bénéficiera non seulement de produits frais de meilleure qualité, mais aura également l\'opportunité de papoter avec les voisins.',
                    ],
                ],
                'correction' => "Faut-il faire ses courses dans les petits magasins ou dans les supermarchés ?\n\nLes supermarchés sont appréciés pour leur praticité, car ils proposent une grande variété de produits, des promotions régulières et un accès facile grâce aux parkings. Cependant, les petits commerces de quartier offrent des produits frais de meilleure qualité et favorisent les échanges humains, tout en soutenant l'économie locale.\n\nÀ mon avis, les deux solutions présentent des avantages, mais il est important de ne pas négliger les petits commerces. Les supermarchés permettent de gagner du temps et de réduire les dépenses grâce aux promotions. Toutefois, faire ses courses dans les magasins de quartier aide à préserver les commerces locaux et à créer du lien social. Par exemple, acheter chez un commerçant permet de recevoir des conseils personnalisés et de discuter avec les voisins. Selon moi, l'idéal est d'alterner entre les deux, en utilisant les supermarchés pour les achats importants et les petits commerces pour les produits frais et de qualité.",
            ],
        ],
    ],
    [
        'combo' => 7,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à vos amis pour les inviter à un anniversaire surprise de votre meilleur(e) ami(e). (Lieu, date, horaire, etc.).',
                'correction' => "Objet : Invitation à un anniversaire surprise\n\nSalut tout le monde,\n\nJ'espère que vous allez bien. Je vous écris pour vous inviter à l'anniversaire surprise de notre meilleur(e) ami(e). L'événement aura lieu samedi 15 juin, à partir de 19 h, chez moi, au 17682 rue Verdier, à Saint-Léonard.\n\nL'idée est de garder la surprise jusqu'au dernier moment, donc merci de ne rien lui dire. Chacun peut apporter quelque chose à manger ou à boire afin de partager un moment convivial. Nous prévoyons de la musique, un gâteau et quelques jeux pour passer une excellente soirée ensemble.\n\nMerci de me confirmer votre présence avant mercredi afin que je puisse bien organiser la soirée.\n\nÀ très bientôt,\nelite tcf canada Boulal",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une brocante (achat / vente de produits d\'occasion) dans votre ville. Sur votre blog personnel, racontez pourquoi vous avez aimé cette activité.',
                'correction' => "Une brocante pleine de bonnes surprises\n\nRécemment, j'ai participé à une brocante organisée dans ma ville, et cette expérience m'a beaucoup plu. J'y suis allé à la fois pour vendre et pour acheter des produits d'occasion. Dès mon arrivée, j'ai apprécié l'ambiance conviviale et détendue. Les gens discutaient facilement et partageaient des histoires sur les objets qu'ils vendaient.\n\nCe que j'ai le plus aimé, c'est la possibilité de donner une seconde vie aux objets. J'ai vendu quelques vêtements et des livres que je n'utilisais plus, et j'ai aussi trouvé de très bonnes affaires à petit prix. Par exemple, j'ai acheté un objet décoratif en très bon état pour beaucoup moins cher qu'en magasin.\n\nCette activité m'a permis de faire des économies, de réduire le gaspillage et de passer un bon moment. La brocante est donc une expérience à la fois utile, écologique et agréable, que je recommencerai avec plaisir.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les vols à bas prix',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Je fais souvent mes voyages avec des compagnies aériennes à bas prix. Les compagnies low cost mettent à disposition des prix inférieurs à ceux proposés par les compagnies aériennes régulières. Cela me coûte des fois moins cher que de voyager en voiture ou en train. Avec ces tarifs-là, vous vous doutez qu\'il y a un hic : en effet, vous n\'aurez droit à aucun service à bord (ni aliments, ni boissons). Je dirais donc que le low-cost n\'est surtout pas fait pour les vols long courriers.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Récemment, j\'ai pris la décision de ne plus voyager avec les compagnies aériennes à bas prix. En effet, j\'ai longuement réfléchi afin de prendre ma décision, mais ce choix était évident : des sièges inconfortables, des conditions de travail pénibles et surtout des avions vétustes qui remettent en cause la sécurité ! Dès lors, pour certains voyages, je vais opter pour la voiture ou même le train, ce dernier permet même de découvrir de jolis paysages. Quant aux longs trajets, il vaut mieux prendre un vol en compagnie régulière.',
                    ],
                ],
                'correction' => "Les vols à bas prix : avantage ou inconvénient ?\n\nLes compagnies aériennes à bas prix attirent de nombreux voyageurs grâce à leurs tarifs très avantageux, souvent moins chers que les autres moyens de transport. Cependant, ces prix réduits impliquent peu ou pas de services à bord, un confort limité et des conditions parfois critiquées, notamment pour les vols de longue durée.\n\nÀ mon avis, les vols à bas prix peuvent être une bonne solution pour les trajets courts. Ils permettent de voyager à moindre coût et de faire des économies importantes. Par exemple, pour un week-end ou un court séjour, l'absence de repas ou de services n'est pas vraiment un problème. En revanche, pour les longs trajets, le confort devient essentiel. Des sièges inconfortables et un manque de services peuvent rendre le voyage pénible. C'est pourquoi, selon moi, les compagnies low-cost sont adaptées aux vols courts, mais pour les longs voyages, il vaut mieux choisir une compagnie régulière.",
            ],
        ],
    ],
    [
        'combo' => 8,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami souhaite commencer à faire du sport. Rédigez un message pour lui recommander une salle de sport située dans votre quartier (localisation, tarifs, types d\'activités, etc.).',
                'correction' => "Salut,\nSi tu souhaites commencer le sport, je te recommande la salle Énergie Fitness, située dans mon quartier, à deux rues du parc municipal. Elle est facile d'accès à pied ou en transport en commun. Les tarifs sont raisonnables, avec un abonnement mensuel à partir de 40 dollars, sans engagement.\n\nLa salle propose plusieurs activités : musculation, cardio, cours collectifs comme le yoga, le stretching et la zumba. Il est aussi possible de bénéficier d'un accompagnement avec un coach pour les débutants. L'ambiance est agréable et le personnel est très professionnel. C'est un endroit idéal pour commencer le sport progressivement et rester motivé.",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un événement qui vous a marqué (anniversaire, mariage, etc.). Racontez votre souvenir en décrivant ce qui vous a le plus marqué.',
                'correction' => "Un mariage inoubliable\n\nChers lecteurs,\n\nJ'ai participé à un événement qui m'a profondément marqué : le mariage de mon meilleur ami. Cette journée restera gravée dans ma mémoire, car elle était remplie d'émotions et de moments forts. La cérémonie s'est déroulée dans une grande salle élégamment décorée, entourée de la famille et des amis proches. L'atmosphère était chaleureuse et festive.\n\nCe qui m'a le plus marqué, c'est l'émotion des mariés lorsqu'ils ont échangé leurs vœux. Leur sincérité et leur bonheur étaient visibles, et beaucoup de personnes avaient les larmes aux yeux. J'ai également apprécié l'ambiance de la soirée. La musique était entraînante, les plats étaient délicieux et tout le monde dansait avec le sourire.\n\nCet événement m'a rappelé l'importance des relations humaines et des moments partagés. C'est un souvenir précieux que je garderai longtemps.",
            ],
            [
                'task' => 3,
                'prompt' => 'La lecture pour les enfants.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Avec l\'avancée technologique et les produits high-tech qui envahissent de plus en plus notre quotidien, nos enfants oublient la lecture et s\'intéressent davantage aux jeux vidéo, aux sports, à la musique… Contrairement à nous, les adultes, dont beaucoup d\'entre nous ont lu des milliers de pages, la génération actuelle est toujours occupée par les réseaux sociaux et le gaming ou prend du plaisir à pratiquer du sport, qui attirent davantage de jeunes grâce aux stars internationales du football, du tennis, de l\'athlétisme… alors, avec tout ça, pourquoi devons-nous forcer les enfants à lire un bouquin ? Et comme le dit un proverbe, « le goût de la lecture ne peut pas s\'imposer »… il faut laisser l\'enfant choisir ce qu\'il veut lire et surtout ne pas l\'obliger à lire quand il n\'a pas envie.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'L\'amour de la lecture se transmet de génération en génération bien que, ces dernières années, on ne trouve plus beaucoup de bouquins entre les mains des enfants, laissant la place aux smartphones et aux tablettes. En apprenant à lire régulièrement, l\'enfant acquiert le langage plus aisément tout en développant sa capacité d\'audition et de concentration. De plus, et pour prendre du plaisir ensemble, les parents peuvent consacrer quotidiennement 10 minutes à leurs enfants pour lire des bouquins ; une activité qui renforcera à coup sûr la complicité parent-enfant.',
                    ],
                ],
                'correction' => "Débat autour de la lecture pour les enfants\n\nAvec le développement des technologies, de nombreux enfants s'intéressent davantage aux écrans, aux jeux vidéo ou au sport qu'à la lecture. Certains pensent qu'il ne faut pas forcer les enfants à lire et qu'ils doivent choisir librement leurs activités. Cependant, d'autres estiment que la lecture reste essentielle pour le développement du langage, de la concentration et du lien parent-enfant.\n\nÀ mon avis, la lecture est très importante pour les enfants, même à l'ère du numérique. Lire régulièrement aide l'enfant à enrichir son vocabulaire, à mieux s'exprimer et à développer son imagination. Par exemple, lire quelques minutes par jour permet d'améliorer la concentration et la compréhension. Il n'est pas nécessaire de forcer l'enfant, mais il faut l'encourager en lui proposant des livres adaptés à son âge et à ses goûts. Partager un moment de lecture avec les parents peut aussi rendre cette activité plus agréable et renforcer la relation familiale.",
            ],
        ],
    ],
];
