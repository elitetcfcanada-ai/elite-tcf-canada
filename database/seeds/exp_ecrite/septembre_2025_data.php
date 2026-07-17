<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 1,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
                'correction' => "Sujet : Week-end entre amis le mois prochain\n\nSalut à tous,\n\nJ'aimerais vous proposer une petite escapade le mois prochain pour passer du temps ensemble. J'ai pensé à un week-end à Québec, une ville magnifique et facile d'accès. Nous pourrions y aller en voiture, ce qui permettrait de partager les frais et de profiter du trajet.\n\nSur place, nous pourrions visiter le Vieux-Québec, faire une promenade le long de la Chute Montmorency, et le soir profiter d'un bon dîner dans un restaurant typique. Le lendemain, pourquoi ne pas organiser une activité plus relaxante comme une balade en bateau ou une visite culturelle ?\n\nQu'en pensez-vous ? J'attends vos retours pour qu'on réserve rapidement.\n\nÀ bientôt,\n[Votre prénom]",
            ],
            [
                'task' => 2,
                'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : "voyage.internaute.fr". Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
                'correction' => "Tout quitter pour voyager pendant un an : un choix enrichissant !\n\nBonjour,\n\nÀ mon avis, tout quitter pour voyager pendant un an est une excellente idée. J'ai moi-même vécu une expérience similaire il y a quelques années, lorsque j'ai décidé de prendre une pause professionnelle pour découvrir de nouveaux horizons.\n\nJ'ai passé trois mois en Espagne où j'ai appris l'espagnol en vivant avec une famille locale, puis je suis allé au Maroc pour m'imprégner de la culture et visiter des sites historiques. Ces voyages m'ont permis non seulement d'apprendre de nouvelles langues, mais aussi d'élargir ma vision du monde.\n\nBien sûr, cela demande une bonne organisation financière et parfois le courage de quitter ses habitudes, mais les bénéfices humains et personnels sont immenses. Voyager, c'est grandir.\n\nCordialement,\n[Prénom]",
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail : Favorable ou Défavorable ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Le travail est au centre de notre vie. Dès l\'enfance, on entend souvent la question : « Qu\'est-ce que tu veux faire quand tu seras grand ? ». Le travail devrait être synonyme de réussite et de satisfaction, mais il est trop souvent synonyme de fatigue et d\'emprisonnement. Aujourd\'hui, beaucoup pensent que l\'on ne passe pas assez de temps avec sa famille, ses amis. Il est urgent de revoir la place occupée par le travail dans notre société. Certains pensent que travailler moins permettrait d\'avoir plus de temps libre pour mieux vivre.'],
                    ['title' => 'Document 2', 'content' => 'Certaines personnes ont décidé d\'arrêter de travailler pour changer de mode de vie. Pourtant, aujourd\'hui, travailler, c\'est exister. La question : « Qu\'est-ce que tu fais dans la vie ? » revient souvent lors d\'une première rencontre. Elle prouve que l\'emploi fait partie de notre identité. D\'après le spécialiste Jean-Daniel Remond, la vie en entreprise est très importante. Les contacts quotidiens, les réseaux, les amitiés, l\'impression d\'être utile, mais aussi les difficultés, tout cela contribue à construire notre personnalité et notre identité.'],
                ],
                'correction' => "Le travail : entre contraintes et épanouissement\n\nLe travail occupe une place essentielle dans nos vies et suscite souvent des débats. Certains y voient une source d'épanouissement, d'autres une contrainte. Le premier document insiste sur les inconvénients du travail, comme le manque de temps pour la famille. Le second souligne son rôle positif dans la construction de l'identité et des relations sociales.\n\nÀ mon avis, le travail est à la fois indispensable et parfois contraignant. Certes, il permet de gagner sa vie et de subvenir à ses besoins, mais il offre aussi une place importante dans la société. Travailler, c'est se sentir utile, rencontrer des gens et développer des compétences. Cependant, si l'on accorde trop de place au travail, on risque de négliger la famille, les amis et sa propre santé. L'idéal serait donc de trouver un équilibre : travailler suffisamment pour assurer sa stabilité financière et son épanouissement personnel, tout en gardant du temps libre pour se reposer, pratiquer des loisirs et profiter des proches.",
            ],
        ],
    ],
    [
        'combo' => 2,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami Mehdi vient d\'emménager dans votre ville et cherche des renseignements sur les moyens de transport. Écrivez un message en lui donnant les informations nécessaires (types de transport, abonnement, tarif, etc.).',
                'correction' => "Salut Mehdi,\n\nJe suis très content que tu sois installé ici ! Pour te déplacer dans la ville, tu as plusieurs options. Il y a d'abord le bus, très pratique et qui dessert presque tous les quartiers. Tu peux aussi prendre le métro, rapide et idéal pour éviter les embouteillages. Il existe un abonnement mensuel qui coûte environ 40 €, valable pour tous les transports (bus, tram et métro). Si tu ne veux pas prendre l'abonnement, tu peux acheter des tickets à l'unité ou par carnet, c'est un peu plus cher à la longue. Enfin, beaucoup de gens utilisent aussi le vélo grâce aux stations en libre-service.\n\nÀ bientôt !\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Exprimez votre admiration pour une personnalité, célèbre ou non, en vous appuyant sur ses actions spécifiques. Rédigez un article de blog en détaillant les actions remarquables de cette personne et expliquez pourquoi vous l\'aimez.',
                'correction' => "Parmi toutes les personnes que j'admire, je voudrais parler de Malala Yousafzai. Son courage et sa détermination m'ont profondément marqué. Dès son adolescence, elle a pris la parole pour défendre le droit des jeunes filles à aller à l'école, dans un contexte où ce droit était menacé. Malgré les menaces et même après avoir survécu à une attaque, elle n'a jamais renoncé à son combat.\n\nElle a fondé une organisation internationale qui soutient l'éducation des enfants dans plusieurs pays et, en 2014, elle est devenue la plus jeune lauréate du prix Nobel de la paix.\n\nJ'admire Malala parce qu'elle a transformé sa souffrance personnelle en un message universel d'espoir et de liberté. Elle représente pour moi l'exemple parfait d'une personne qui croit en ses convictions et qui agit pour changer le monde. Son histoire m'encourage à persévérer et à défendre les valeurs qui me tiennent à cœur.",
            ],
            [
                'task' => 3,
                'prompt' => 'Vivre en colocation',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'La vie en colocation offre de nombreux avantages. Partager un logement avec d\'autres personnes permet de réduire les dépenses, que ce soit le loyer, les factures ou les frais généraux. De plus, cela favorise les interactions sociales et les échanges culturels. Vivre avec des colocataires permet de rencontrer des individus de différents horizons, de nouer des amitiés et de partager des expériences enrichissantes.'],
                    ['title' => 'Document 2', 'content' => 'La colocation peut cependant présenter des défis. Les différences de personnalité et de mode de vie entre les colocataires peuvent entraîner des tensions. La gestion des responsabilités et des tâches ménagères peut également être source de conflits. De plus, la colocation peut limiter l\'intimité et l\'espace personnel. Il est important d\'établir une communication ouverte et respectueuse, ainsi que des règles de vie commune, pour favoriser une cohabitation harmonieuse.'],
                ],
                'correction' => "Les deux documents présentent la colocation comme une expérience à la fois enrichissante et complexe. D'un côté, elle réduit les frais de logement et favorise les échanges sociaux et culturels. De l'autre, elle peut générer des tensions liées aux différences de personnalité, à la répartition des tâches et au manque d'intimité.\n\nÀ mon avis, la colocation reste une bonne solution pour les étudiants et les jeunes travailleurs, car elle aide à réduire les coûts et permet de rencontrer de nouvelles personnes. Toutefois, il est vrai qu'elle demande des efforts de tolérance et d'organisation. Personnellement, je pense que les règles doivent être établies dès le départ : chacun doit respecter l'espace des autres et participer équitablement aux tâches ménagères. De plus, il est important de garder une communication régulière pour éviter les malentendus. Malgré les inconvénients, je crois que la colocation est une opportunité précieuse, surtout pour les jeunes qui veulent apprendre à vivre en société et développer leur sens des responsabilités.",
            ],
        ],
    ],
    [
        'combo' => 3,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez invité votre ami Cédric à votre mariage au Château de Chombony, et il vous a répondu qu\'il ne connaît pas ce château. Décrivez-le à votre ami (lieu, localisation, transports, etc.).',
                'correction' => "Salut Cédric,\n\nJe suis content que tu viennes à mon mariage ! Tu m'as dit que tu ne connais pas le Château de Chombony, alors je vais t'expliquer. C'est un grand château historique situé à environ 15 kilomètres du centre-ville. Il se trouve dans un magnifique parc verdoyant, idéal pour les photos. Pour y aller, tu peux prendre la route nationale en direction de Saint-Pierre ; le château est bien indiqué par des panneaux. Si tu n'as pas de voiture, un bus part chaque heure depuis la gare centrale.\n\nÀ bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => 'Dans votre blog, racontez votre expérience de l\'apprentissage d\'une langue étrangère (vous écrivez sur un forum internet en racontant votre expérience en apprenant une langue étrangère).',
                'correction' => "Bonjour à tous,\n\nJe souhaite partager mon parcours dans l'apprentissage de l'anglais, une langue qui m'a toujours attiré et qui est indispensable aujourd'hui. Lorsque j'ai commencé, je me sentais perdu : la grammaire me paraissait compliquée et je n'osais pas parler par peur de faire des erreurs. Malgré ces difficultés, j'ai décidé de persévérer en me fixant des objectifs simples.\n\nChaque jour, j'écoutais des podcasts, je regardais des films en version originale et je notais les nouveaux mots. J'ai aussi trouvé des correspondants avec qui échanger en ligne. Peu à peu, j'ai pris confiance et j'ai constaté mes progrès.\n\nAujourd'hui, je suis capable de tenir une conversation fluide et de comprendre des documents professionnels. Cette expérience m'a appris que la régularité et la pratique sont les clés de la réussite.\n\nEt vous, comment vivez-vous votre apprentissage des langues étrangères ?",
            ],
            [
                'task' => 3,
                'prompt' => 'Cuisinier Amateur Ou Cuisinier Professionnel ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Les amateurs ont fait des recettes réussies, mais ils manquent toujours de compétences et de technique, c\'est pourquoi la formation et l\'expérience sont nécessaires pour être un vrai cuisinier.'],
                    ['title' => 'Document 2', 'content' => 'Il parle des cuisiniers qui ont appris le métier de cuisinier sur internet et qui ont le buzz sur les réseaux sociaux. Il raconte aussi l\'histoire d\'une amatrice qui est devenue professionnelle et qui a rédigé plusieurs livres sur la cuisine pour les amateurs de cuisine à la maison.'],
                ],
                'correction' => "Les documents présentés montrent deux points de vue différents sur la question de la cuisine. Le premier souligne l'importance de la formation et de l'expérience pour devenir un vrai cuisinier, tandis que le second met en avant les amateurs qui, grâce à Internet et aux réseaux sociaux, réussissent à se faire connaître et parfois à devenir professionnels.\n\nÀ mon avis, la cuisine professionnelle et la cuisine amateur sont complémentaires. En effet, un cuisinier formé possède des compétences techniques solides, ce qui garantit la qualité et la régularité des plats servis dans un restaurant. C'est essentiel dans un métier où l'exigence des clients est très élevée. Cependant, il ne faut pas négliger les talents des amateurs. Grâce à leur créativité et à la diffusion de leurs recettes en ligne, ils apportent de nouvelles idées, parfois originales, qui enrichissent l'art culinaire. Plusieurs exemples montrent qu'un amateur passionné peut se transformer en véritable professionnel.\n\nEn conclusion, je pense qu'un bon équilibre entre formation, passion et créativité est la meilleure recette pour réussir dans ce domaine.",
            ],
        ],
    ],
    [
        'combo' => 4,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '"Bonjour, ça y est, j\'ai obtenu mon visa pour le Canada. Je vais arriver le 3 mars. Est-ce que tu pourras m\'aider à trouver un hôtel pour la première semaine ? Merci d\'avance pour ton aide." Matthias. Vous avez trouvé un hôtel pour Matthias. Vous lui écrivez un courriel. Dans ce message vous décrivez l\'hôtel et vous lui donnez toutes les informations utiles (situation, tarif…).',
                'correction' => "Objet : Ton hôtel pour la première semaine au Canada\n\nBonjour Matthias,\n\nSuper nouvelle pour ton visa ! J'ai trouvé un hôtel pour toi : Hôtel Le Saint-Laurent, situé au centre-ville de Montréal, à seulement 10 minutes à pied de la station de métro. Il est bien noté et très propre.\n\nLa chambre simple coûte 95 $ CAN par nuit, petit-déjeuner inclus. Il y a aussi le Wi-Fi gratuit, une salle de sport et une réception ouverte 24h/24. Les avis sont très positifs, surtout pour l'accueil et le confort.\n\nJ'ai vérifié : il y a encore des chambres disponibles du 3 au 10 mars. Tu peux réserver directement sur leur site.\n\nDis-moi si tu as besoin d'aide pour la réservation.\n\nÀ bientôt,\nAYOUB",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous êtes allés voir un spectacle (film, pièce de théâtre, concert, etc.) avec des amis. Vous l\'avez aimé. Sur votre blog, vous racontez votre soirée et vous expliquez pourquoi vous avez aimé le spectacle.',
                'correction' => "Soirée inoubliable au théâtre\n\nChers lecteurs,\n\nHier soir, je suis allé voir une pièce de théâtre avec des amis, et c'était vraiment génial ! La pièce s'intitule « Les illusions perdues » et elle raconte l'histoire d'un jeune écrivain ambitieux. Les acteurs étaient incroyables, très expressifs et captivants. La mise en scène était originale, avec des décors simples mais très efficaces. J'ai particulièrement aimé la manière dont les émotions étaient transmises, ce qui m'a vraiment touché. Le rythme était bien équilibré entre moments drôles et scènes plus dramatiques. Cette soirée m'a permis de passer un moment convivial avec mes amis tout en découvrant un spectacle de qualité. Je recommande vivement cette pièce à tous ceux qui aiment le théâtre vivant et émouvant !\n\nÀ bientôt pour de nouvelles sorties culturelles.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les relations amicales au travail',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Les amitiés entre collègues au travail peuvent être extrêmement bénéfiques. Elles favorisent un climat de travail agréable et une ambiance positive au sein de l\'équipe. Avoir des amis parmi ses collègues permet de renforcer les liens professionnels et de créer un sentiment de camaraderie. Cela peut contribuer à une meilleure communication, une collaboration plus étroite et une résolution plus efficace des problèmes. De plus, partager des moments de convivialité en dehors du travail, comme des déjeuners et des activités après le bureau, peut renforcer les liens et créer une dynamique de groupe solide.'],
                    ['title' => 'Document 2', 'content' => 'Il est important de trouver un équilibre entre amitié et professionnalisme au travail. Les amitiés excessivement proches peuvent parfois créer des tensions ou des conflits lorsque des décisions professionnelles doivent être prises. De plus, les amitiés exclusives entre certains collègues peuvent exclure les autres membres de l\'équipe, ce qui peut nuire à la cohésion et à la collaboration. Il est essentiel de maintenir des limites claires et de veiller à ce que les amitiés ne compromettent pas le professionnalisme, la hiérarchie ou la productivité au sein de l\'organisation.'],
                ],
                'correction' => "Les Relations Amicales Au Travail\n\nLes relations amicales au travail sont fréquentes et influencent l'ambiance professionnelle. Le premier document explique qu'elles améliorent la communication, la collaboration et renforcent les liens entre collègues. Cependant, le second souligne que des amitiés trop proches peuvent provoquer des tensions, créer des exclusions et nuire au professionnalisme, d'où la nécessité de garder des limites claires. À mon avis, les relations amicales au travail sont très bénéfiques, à condition de bien gérer l'équilibre entre amitié et professionnalisme. Avoir des collègues proches rend l'environnement de travail plus agréable et motivant. Par exemple, dans mon précédent emploi, j'avais une bonne relation avec mon équipe, ce qui facilitait la communication et la résolution des problèmes. Cependant, il est important de ne pas laisser ces amitiés interférer avec les décisions professionnelles ou créer des groupes exclusifs. Cela pourrait nuire à la cohésion de l'équipe et à l'efficacité au travail. En somme, l'idéal est de cultiver de bonnes relations sans oublier ses responsabilités et en respectant les limites pour garantir un bon climat professionnel.",
            ],
        ],
    ],
    [
        'combo' => 5,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Invitez vos amis à célébrer votre anniversaire tout en sollicitant leur soutien pour organiser la fête.',
                'correction' => "Salut les amis,\n\nJe vous invite à célébrer mon anniversaire avec moi le samedi 12 octobre chez moi à partir de 19h. Ce serait super de passer cette soirée ensemble, avec musique, bonne nourriture et beaucoup de joie ! Pour que la fête soit réussie, j'aurais besoin de votre aide pour l'organisation : certains pourraient s'occuper des boissons, d'autres de la playlist ou encore de la décoration. N'hésitez pas à me dire ce que vous préférez faire. J'aimerais vraiment que ce moment soit convivial et mémorable grâce à vous tous.\n\nFaites-moi savoir rapidement si vous pouvez venir et comment vous pouvez m'aider.\n\nÀ très vite,\nAYOUB",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un concours de cuisine, vous allez décrire vos souvenirs dans votre blog en indiquant les détails.',
                'correction' => "Mon expérience au concours de cuisine\n\nChers lecteurs,\n\nJe souhaite partager avec vous mon expérience incroyable lors d'un concours de cuisine auquel j'ai participé récemment. C'était un défi excitant ! Le thème était la cuisine italienne, alors j'ai préparé des raviolis maison farcis à la ricotta et aux épinards, accompagnés d'une sauce tomate fraîche. La préparation a demandé beaucoup de patience et de précision, surtout pour réaliser la pâte fine et bien sceller les raviolis.\n\nLe jour du concours, l'ambiance était à la fois stressante et motivante, avec des chefs passionnés partout autour de moi. J'ai appris beaucoup en échangeant avec eux et en écoutant les conseils des juges. Même si je n'ai pas gagné, cette expérience m'a donné confiance en mes capacités et m'a donné envie de continuer à progresser en cuisine.\n\nMerci de me lire, à bientôt pour de nouvelles aventures culinaires !",
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail des étudiants pendant les vacances : Pour ou contre ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Alexandre, un conseiller d\'orientation : « Je suis fermement pour le travail des jeunes pendant les vacances. C\'est une excellente occasion d\'acquérir des compétences pratiques, d\'apprendre à travailler en équipe et de découvrir différentes industries. Les jeunes peuvent gagner de l\'argent qu\'ils peuvent économiser pour l\'avenir ou utiliser pour financer des activités ou des loisirs. De plus, le fait d\'avoir une expérience de travail sur leur CV peut leur donner un avantage lorsqu\'ils postulent pour des emplois ou des stages à l\'avenir. Les vacances d\'été sont longues et il y a amplement de temps pour se reposer et travailler. »'],
                    ['title' => 'Document 2', 'content' => 'Élise, une psychologue pour adolescents : « Je suis contre l\'idée que les jeunes doivent travailler pendant leurs vacances. Les adolescents sont déjà sous une énorme pression pendant l\'année scolaire avec les études, les examens et les activités parascolaires. Les vacances doivent être une période pour eux de se détendre, de se décompresser et de poursuivre des intérêts personnels. Les forcer à travailler pourrait contribuer au stress et à l\'épuisement. Il est également important pour les jeunes de disposer de temps non structuré pour explorer leur créativité, passer du temps avec leurs amis et leur famille, et tout simplement être des enfants. Le travail peut attendre. »'],
                ],
                'correction' => "Le travail des étudiants pendant les vacances : Pour ou contre ?\n\nLe travail des jeunes pendant les vacances est un sujet qui divise. Le premier document soutient qu'il s'agit d'une bonne occasion d'apprendre, de gagner de l'argent et d'enrichir son CV. Cependant, le second document estime que cela peut nuire au repos, à la créativité et au bien-être mental des adolescents déjà très sollicités. À mon avis, travailler pendant les vacances peut être très bénéfique pour les étudiants, à condition que cela reste équilibré. Cela leur permet de découvrir le monde du travail, de devenir plus autonomes et de financer certaines dépenses personnelles. Par exemple, un ami a travaillé deux semaines dans un magasin et a beaucoup appris sur la gestion du temps et le contact avec les clients. Cependant, il est aussi essentiel de garder du temps pour se reposer, profiter de ses proches et pratiquer des loisirs. Les vacances ne doivent pas devenir une deuxième école ou une obligation. L'idéal serait de combiner un petit job avec des moments de détente. Ainsi, les jeunes peuvent se former sans négliger leur bien-être.",
            ],
        ],
    ],
    [
        'combo' => 6,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
                'correction' => "Salut tout le monde,\n\nJ'ai une super idée pour le mois prochain : et si on partait en week-end ensemble ? Je propose qu'on aille à Annecy, c'est une ville magnifique avec un lac, des montagnes et une vieille ville charmante. On pourrait y aller en train, c'est pratique et écolo. Sur place, on pourrait faire du canoë, des balades à vélo autour du lac, et pourquoi pas une petite randonnée. On dormira dans un petit chalet ou un Airbnb sympa. Le soir, on pourra cuisiner ensemble ou tester des restos locaux. Ce serait l'occasion parfaite de se détendre et passer du bon temps entre amis.\n\nDites-moi ce que vous en pensez !\n\nÀ bientôt,\nAYOUB",
            ],
            [
                'task' => 2,
                'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : "voyage.internaute.fr". Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
                'correction' => "Message sur voyage.internaute.fr\n\nTout quitter pour partir en voyage pendant un an peut sembler risqué, mais pour moi, c'est une excellente décision quand elle est bien réfléchie. Il y a deux ans, j'ai mis mon travail en pause pour voyager en Asie. Je voulais sortir de ma routine et découvrir le monde. J'ai visité plusieurs pays comme le Vietnam, la Thaïlande et le Japon. Ce voyage m'a ouvert les yeux sur d'autres modes de vie, d'autres cultures, et surtout sur moi-même. Cependant, tout n'a pas été facile : j'ai dû gérer mon budget, la solitude parfois, et l'éloignement de ma famille. Mais au final, cette expérience m'a transformé. J'ai gagné en confiance, en autonomie, et j'ai compris ce qui compte vraiment pour moi. Aujourd'hui, je ne regrette rien. Voyager pendant un an, c'est une école de vie que je recommande à tous ceux qui cherchent un nouveau souffle. N'ayez pas peur de tenter l'aventure !",
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail : Favorable ou Défavorable ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Le travail est au centre de notre vie. Dès l\'enfance, on entend souvent la question : « Qu\'est-ce que tu veux faire quand tu seras grand ? » Le travail devrait être synonyme de réussite et de satisfaction, mais il est trop souvent synonyme de fatigue et d\'emprisonnement. Aujourd\'hui, beaucoup pensent que l\'on ne passe pas assez de temps avec sa famille, ses amis. Il est urgent de revoir la place occupée par le travail dans notre société. Certains pensent que travailler moins permettrait d\'avoir plus de temps libre pour mieux vivre.'],
                    ['title' => 'Document 2', 'content' => 'Certaines personnes ont décidé d\'arrêter de travailler pour changer de mode de vie. Pourtant, aujourd\'hui, travailler, c\'est exister. La question : « Qu\'est-ce que tu fais dans la vie ? » revient souvent lors d\'une première rencontre. Elle prouve que l\'emploi fait partie de notre identité. D\'après le spécialiste Jean-Daniel Remond, la vie en entreprise est très importante. Les contacts quotidiens, les réseaux, les amitiés, l\'impression d\'être utile, mais aussi les difficultés, tout cela contribue à construire notre personnalité et notre identité.'],
                ],
                'correction' => "Le Travail : Favorable ou Défavorable ?\n\nLe travail joue un rôle central dans nos vies, mais il suscite des avis différents. Le premier document souligne qu'il provoque fatigue et empêche de profiter de la vie personnelle. Certains aimeraient travailler moins. Cependant, le second affirme que le travail construit notre identité grâce aux relations humaines, aux responsabilités et au sentiment d'être utile en société. À mon avis, le travail est à la fois favorable et défavorable, selon la manière dont il est vécu. Il est vrai que le travail peut être source de stress et de fatigue, surtout quand il prend trop de place au détriment de la vie personnelle. Par exemple, certains de mes amis travaillent de longues heures et manquent de temps pour leur famille. Cependant, le travail apporte aussi un sentiment d'utilité, de stabilité et de lien social important. Il permet de se sentir reconnu et de construire son identité. L'idéal serait de trouver un équilibre, avec des horaires raisonnables et du temps pour soi. Ainsi, le travail devient une source d'épanouissement, sans nuire à la qualité de vie.",
            ],
        ],
    ],
    [
        'combo' => 7,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Salut, je suis vraiment intéressé à l\'idée de voyager et de découvrir un autre pays. Peux-tu me parler un peu de ton pays et de sa culture ? Marc. » Écrivez un message à votre ami Marc, qui veut voyager et découvrir un autre pays, pour lui parler de votre pays et de sa culture (lieux, sites touristiques, monuments, etc.).',
                'correction' => "Salut Marc,\n\nJe suis ravi que tu sois intéressé par le voyage ! Mon pays, la France, est riche en culture et en histoire. Tu peux commencer par Paris, la capitale, avec la célèbre Tour Eiffel, le Louvre et Notre-Dame. Ensuite, visite le Mont-Saint-Michel, un site spectaculaire au milieu de la mer. Ne manque pas la Provence, connue pour ses champs de lavande, ni les Châteaux de la Loire. Côté gastronomie, tu vas adorer le fromage, le vin et les croissants. La culture française est marquée par l'art, la mode et la philosophie. Les gens aiment discuter, partager et prendre leur temps. Viens découvrir tout ça, tu vas adorer !\n\nÀ bientôt,\nJACK",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous venez d\'avoir un nouveau travail. Envoyez un courriel à vos amis pour leur raconter comment vous avez passé votre première semaine de travail (entreprise, poste, tâches, etc.).',
                'correction' => "Ma première semaine de travail !\n\nSalut tout le monde,\n\nJe voulais vous donner des nouvelles : j'ai commencé mon nouveau travail cette semaine ! Je travaille dans une entreprise de marketing digital, dans le centre-ville. Mon poste est assistant chef de projet. L'équipe est super sympa et m'a très bien accueilli.\n\nCette semaine, j'ai surtout observé, pris des notes et participé à plusieurs réunions. J'ai aussi commencé à aider à la préparation de campagnes publicitaires pour des clients. C'est vraiment intéressant et très dynamique ! Il y a beaucoup à apprendre, mais je me sens motivé et bien encadré.\n\nLes journées passent vite et je commence à m'habituer au rythme. Je suis content d'avoir trouvé un travail qui me plaît et j'ai hâte de progresser. On se voit bientôt pour en parler autour d'un café !\n\nÀ très vite,\nAYOUB",
            ],
            [
                'task' => 3,
                'prompt' => 'Égalité Homme/Femme en Milieu de Travail',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Certains postes sont majoritairement occupés par des hommes. Au Québec, l\'égalité entre les femmes et les hommes est respectée, les femmes peuvent exercer les métiers réservés aux hommes tels que les postes de direction.'],
                    ['title' => 'Document 2', 'content' => 'Malgré les efforts pour imposer la parité à tous les postes dans les entreprises et les collectivités, il y a, dans les faits, des métiers où les femmes sont largement majoritaires tels que : les sages-femmes. De plus, les femmes ne doivent pas faire les métiers d\'homme, car elles doivent prendre soin de leurs enfants.'],
                ],
                'correction' => "Égalité Homme/Femme en Milieu de Travail\n\nL'égalité entre les sexes au travail suscite encore des débats. Le premier document affirme qu'au Québec, les femmes peuvent occuper des postes autrefois réservés aux hommes, comme ceux de direction. Cependant, le second document souligne que certaines professions restent féminisées et que les femmes devraient prioriser leur rôle de mère plutôt que d'exercer des métiers masculins. À mon avis, hommes et femmes doivent avoir les mêmes droits et chances dans le monde du travail. Chacun devrait choisir sa carrière selon ses compétences et ses intérêts, sans stéréotypes. Par exemple, une femme peut devenir ingénieure ou cheffe d'entreprise, tout comme un homme peut être infirmier ou éducateur. Dire qu'une femme doit rester à la maison pour s'occuper des enfants est une idée dépassée. Aujourd'hui, de nombreux pères partagent les tâches familiales. La société évolue, et il est essentiel de briser les barrières liées au genre. L'égalité, ce n'est pas seulement un droit, c'est une richesse pour les entreprises et la société tout entière. Ensemble, avançons vers un monde plus juste et équilibré.",
            ],
        ],
    ],
];
