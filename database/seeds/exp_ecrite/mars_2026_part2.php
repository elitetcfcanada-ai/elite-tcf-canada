<?php

declare(strict_types=1);



/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */

return [

    [

        'combo' => 9,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Votre ami souhaite commencer à faire du sport. Rédigez un message pour lui recommander une salle de sport située dans votre quartier (localisation, tarifs, types d\'activités, etc.).',

                'correction' => "Salut,\nJ'espère que tu vas bien. Je suis très content que tu veuilles commencer le sport ! Je te\nconseille une salle que j'aime beaucoup dans mon quartier : Fitness Center Verdier. Elle est\nsituée près du métro, à seulement 5 minutes à pied, donc c'est très pratique.\nLes tarifs sont raisonnables : environ 35 dollars par mois, avec un abonnement sans\nengagement. La salle est propre, bien équipée et il y a plusieurs activités : musculation,\ncardio, cours collectifs (zumba, yoga, spinning) et même un espace étirements.\nSi tu veux, on peut y aller ensemble une première fois pour te montrer comment ça\nfonctionne.",

            ],

            [

                'task' => 2,

                'prompt' => 'Vous avez participé à un événement qui vous a marqué (anniversaire, mariage, etc.). Racontez votre souvenir en décrivant ce qui vous a le plus marqué.',

                'correction' => "Un anniversaire inoubliable\nL'événement qui m'a le plus marqué est l'anniversaire surprise que nous avons organisé pour\nmon meilleur ami. Ce jour-là, nous nous sommes retrouvés chez moi en fin d'après-midi pour\npréparer la décoration, la musique et le gâteau. Tout le monde était très motivé et l'ambiance\nétait joyeuse.\nCe qui m'a le plus marqué, c'est le moment où mon ami est arrivé. Lorsqu'il a ouvert la porte\net qu'il a vu tous ses proches réunis, il est resté quelques secondes sans parler, puis il a souri\navec émotion. On voyait clairement qu'il ne s'attendait pas à cette surprise.\nEnsuite, nous avons partagé un bon repas, dansé et pris beaucoup de photos. Cette soirée m'a\nvraiment rappelé l'importance de l'amitié et des moments simples passés ensemble.",

            ],

            [

                'task' => 3,

                'prompt' => 'La lecture pour les enfants.',

                'documents' => [

                    [

                        'title' => 'Document 1',

                        'content' => 'Avec l\'avancée technologique et les produits high-tech qui envahissent de plus en plus notre quotidien, nos enfants oublient la lecture et s\'intéressent davantage aux jeux vidéo, aux sports, à la musique… Contrairement à nous, les adultes, dont beaucoup d\'entre nous ont lu des milliers de pages, la génération actuelle est toujours occupée par les réseaux sociaux et le gaming ou prend du plaisir à pratiquer du sport qui attire davantage de jeunes grâce aux stars internationales du football, du tennis, de l\'athlétisme… alors, avec tout ça, pourquoi devons-nous forcer les enfants à lire un bouquin ? Et comme le dit un proverbe, « le goût de la lecture ne peut pas s\'imposer »… il faut laisser l\'enfant choisir ce qu\'il veut lire et surtout ne pas l\'obliger à lire quand il n\'a pas envie.',

                    ],

                    [

                        'title' => 'Document 2',

                        'content' => 'L\'amour de la lecture se transmet de génération en génération bien que, ces dernières années, on ne trouve plus beaucoup de bouquins entre les mains des enfants, laissant la place aux smartphones et aux tablettes. En apprenant à lire régulièrement, l\'enfant acquiert le langage plus aisément tout en développant sa capacité d\'audition et de concentration. De plus, et pour prendre du plaisir ensemble, les parents peuvent consacrer quotidiennement 10 minutes à leurs enfants pour lire des bouquins ; une activité qui renforcera à coup sûr la complicité parent-enfant.',

                    ],

                ],

                'correction' => "La lecture chez les enfants : faut-il insister ?\nLe premier texte explique que les enfants s'intéressent davantage aux écrans et aux loisirs\nmodernes qu'à la lecture, et qu'il ne faut pas les forcer à lire, car le goût ne s'impose pas. En\nrevanche, le second souligne que lire régulièrement développe le langage, la concentration et\nrenforce le lien entre parents et enfants.\nÀ mon avis, la lecture est essentielle pour le développement des enfants, mais elle ne doit pas\nêtre imposée de manière autoritaire. Lire permet d'enrichir le vocabulaire, d'améliorer\nl'expression orale et écrite et de stimuler l'imagination. Cependant, si l'enfant se sent obligé, il\nrisque de rejeter totalement les livres. Je pense qu'il vaut mieux l'encourager doucement, en\nchoisissant des histoires adaptées à son âge et à ses centres d'intérêt. Les parents peuvent aussi\ndonner l'exemple en lisant eux-mêmes. Ainsi, la lecture devient un moment agréable et partagé,\net non une contrainte.",

            ],

        ],

    ],

    [

        'combo' => 10,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Écrivez un message à votre ami(e) qui souhaite suivre des cours de langue dans votre école. Donnez les détails spécifiques pour aider votre ami(e) à faire son choix. (lieu, tarifs, types de cours disponibles, etc.).',

                'correction' => "Salut,\nJ'espère que tu vas bien. Je suis très content que tu veuilles suivre des cours de langue dans\nmon école ! Elle est située en centre-ville, près de la station de métro, donc c'est facile\nd'accès.\nLes tarifs sont raisonnables : environ 120 dollars par mois, avec possibilité de payer par\nsession. Il y a plusieurs types de cours : cours de français général, cours intensifs, cours du\nsoir pour les personnes qui travaillent, et aussi des ateliers de conversation pour améliorer\nl'oral. Les groupes sont petits, donc le professeur peut bien suivre chaque étudiant.\nSi tu veux, je peux aussi t'accompagner le premier jour pour t'aider à t'inscrire.",

            ],

            [

                'task' => 2,

                'prompt' => 'Vous travaillez dans une association qui aide les personnes âgées. Rédigez un article de blog pour raconter vos expériences et convaincre d\'autres personnes de rejoindre l\'association.',

                'correction' => "Une expérience humaine inoubliable : rejoignez notre association !\nDepuis plusieurs mois, je travaille dans une association qui aide les personnes âgées, et cette\nexpérience m'a profondément marqué. Chaque semaine, nous rendons visite à des seniors isolés\npour leur apporter du soutien, de la compagnie et parfois une aide pratique. Nous les\naccompagnons aussi pour faire des courses, aller à un rendez-vous médical ou simplement sortir\nprendre l'air.\nCe que j'aime le plus, c'est le lien humain que nous créons. Beaucoup de personnes âgées ont\nsurtout besoin d'écoute. Un simple moment de discussion peut illuminer leur journée.\nPersonnellement, j'ai appris à être plus patient, plus attentif et plus reconnaissant.\nJe conseille vraiment à tout le monde de rejoindre cette association. Même avec peu de temps,\non peut faire une grande différence. En plus, l'ambiance entre bénévoles est très chaleureuse.\nSi vous cherchez une activité utile et enrichissante, n'hésitez pas : venez nous rejoindre !",

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

                'correction' => "Les animaux de compagnie pour les enfants : bonne ou mauvaise idée ?\nLe premier document met en avant les bienfaits d'un animal pour un enfant : il lutte contre la\nsolitude, développe la confiance en soi et apprend le respect et la responsabilité. En revanche,\nle second texte rappelle qu'un animal représente un engagement long, coûteux et ne doit pas\nêtre considéré comme un simple jouet.\nÀ mon avis, avoir un animal de compagnie peut être très positif pour un enfant, mais cela doit\nêtre une décision réfléchie. Un animal peut aider l'enfant à devenir plus responsable, plus\nattentionné et moins seul. Par exemple, nourrir un chien ou nettoyer la cage d'un lapin apprend\nle sens du devoir. Cependant, il ne faut pas oublier que la responsabilité principale revient aux\nparents. Si l'enfant perd de l'intérêt, ce sont eux qui devront s'occuper de l'animal. De plus, les\nfrais vétérinaires et l'entretien peuvent être coûteux. Selon moi, adopter un animal est une\nexcellente idée, à condition que toute la famille soit prête à s'engager sur le long terme.",

            ],

        ],

    ],

    [

        'combo' => 11,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Répondez au courriel de votre ami Lucas pour lui donner des informations sur les nouveaux locaux de votre entreprise (lieu, disposition des pièces, équipements, etc.).',

                'correction' => "Salut Lucas,\nMerci pour ton message ! Je suis content de te parler de nos nouveaux locaux. Ils sont situés au\ncentre-ville, près de la station de métro, ce qui est très pratique pour tout le monde.\nLes bureaux sont beaucoup plus spacieux qu'avant. Il y a un grand espace ouvert pour le travail\nen équipe, plusieurs salles de réunion bien équipées avec des écrans et du matériel de\nvisioconférence, ainsi qu'une salle de détente avec une petite cuisine. Nous avons aussi une\nterrasse où l'on peut prendre une pause quand il fait beau.\nL'ambiance est moderne et agréable, on s'y sent vraiment bien pour travailler.",

            ],

            [

                'task' => 2,

                'prompt' => 'Vous avez assisté à un événement intitulé "Une semaine sans voiture". Racontez votre expérience et donnez votre impression sur cette initiative. Décrivez le déroulement de l\'événement (dates, lieu, activités proposées).',

                'correction' => "Une semaine sans voiture : une initiative à refaire !\nLa semaine dernière, j'ai participé à un événement intitulé « Une semaine sans voiture »,\norganisé du 5 au 11 février dans le centre-ville. Pendant cette période, plusieurs rues principales\nont été fermées aux voitures afin de favoriser les déplacements à pied, à vélo et en transports\nen commun.\nChaque jour, des activités étaient proposées : balades à vélo, ateliers de réparation, animations\npour les enfants, et stands d'information sur l'environnement. Il y avait aussi des conférences\nsur la pollution et des démonstrations de transports écologiques.\nJ'ai beaucoup apprécié cette initiative, car la ville était plus calme, l'air semblait plus propre et\nil y avait moins de stress. Cette expérience m'a permis de redécouvrir ma ville autrement. À\nmon avis, ce type d'événement devrait être organisé plus souvent.",

            ],

            [

                'task' => 3,

                'prompt' => 'Les Vêtements de Grandes Marques',

                'documents' => [

                    [

                        'title' => 'Document 1',

                        'content' => 'Les vêtements de marques sont très importants pour les enfants et les adolescents. C\'est un moyen de s\'exprimer et de se rattacher à un groupe social. Cette attirance pour les marques est très présente chez les adolescents qui se cherchent et montrent leur personnalité. Les enfants aiment également porter des vêtements de marques avec des images des dessins animés qu\'ils regardent ou des logos qu\'ils apprécient.',

                    ],

                    [

                        'title' => 'Document 2',

                        'content' => 'Les enfants grandissent très vite et les vêtements sont portés pendant une courte période. Ainsi, les vêtements deviennent rapidement trop petits. Mais il y a aussi le fait que les enfants usent assez rapidement les vêtements en jouant à l\'extérieur avec les copains, en s\'amusant dans l\'herbe ou à l\'aire de jeux. Les habits sont très vite sales ou troués.',

                    ],

                ],

                'correction' => "Les vêtements de grandes marques : nécessaires pour les enfants ?\nPremière partie (40–60 mots)\nLe premier document explique que les vêtements de marque permettent aux enfants et aux\nadolescents de s'exprimer et de s'intégrer à un groupe. Les logos et images attirent\nparticulièrement les jeunes. Le second document souligne que ces vêtements sont peu\nrentables, car les enfants grandissent vite et les abîment rapidement.\nDeuxième partie (120 mots)\nÀ mon avis, acheter des vêtements de grandes marques pour les enfants n'est pas\nindispensable. Je comprends que les adolescents veuillent suivre la mode et se sentir acceptés\npar leurs amis. Cependant, les enfants grandissent très vite et changent souvent de taille.\nDépenser beaucoup d'argent pour des vêtements qu'ils porteront seulement quelques mois me\nsemble excessif. De plus, en jouant à l'extérieur, les habits peuvent facilement se salir ou se\ndéchirer. Je pense qu'il vaut mieux privilégier des vêtements de bonne qualité, confortables et\nadaptés à leur âge, sans forcément payer une marque connue. L'essentiel est que l'enfant se\nsente bien, pas qu'il porte un logo célèbre.",

            ],

        ],

    ],

    [

        'combo' => 12,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Analysez le sujet d\'examen suivant : Vous voulez organiser une visite culturelle dans votre ville. Vous envoyez un message pour inviter vos amis. Vous leur donnez toutes les informations nécessaires (activités, date, lieu, etc.).',

                'correction' => "Objet : Visite culturelle à Lyon samedi prochain\n\nSalut les amis,\n\nJ'aimerais organiser une visite culturelle dans notre ville, Lyon, le samedi 15 juin. Je vous invite à passer une belle journée ensemble pour découvrir quelques lieux intéressants. Nous nous retrouverons à 10 h devant l'Hôtel de Ville. Ensuite, nous visiterons le musée des Beaux-Arts, puis nous ferons une promenade dans le Vieux Lyon pour voir les traboules et la cathédrale Saint-Jean. À 13 h, nous déjeunerons dans un petit restaurant du centre. L'après-midi, nous irons au théâtre gallo-romain de Fourvière. La sortie se terminera vers 17 h. Merci de me dire avant jeudi si vous êtes disponibles.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 2,

                'prompt' => 'Vous avez assisté à une soirée écologique pour protéger la planète qui avait lieu dans votre université. Racontez-la dans votre blog et expliquez pourquoi vous l\'avez aimée.',

                'correction' => "La soirée écologique à l'université\n\nBonjour à tous,\n\nLa semaine dernière, j'ai assisté à une soirée écologique organisée dans mon université avec plusieurs amis de ma promotion. L'événement a eu lieu dans le grand hall du campus, qui était décoré avec des affiches sur le recyclage, l'énergie verte et la protection de la biodiversité. Dès mon arrivée, j'ai trouvé l'ambiance très chaleureuse et motivante.\n\nPendant la soirée, nous avons participé à des ateliers pratiques. J'ai appris à trier correctement les déchets, à fabriquer un objet avec des matériaux recyclés et à réduire le plastique au quotidien. Il y avait aussi une conférence courte, mais très intéressante, donnée par une association locale. Ensuite, nous avons dégusté des produits locaux et bio, ce qui a rendu la soirée encore plus agréable.\n\nJ'ai beaucoup aimé cette expérience parce qu'elle était à la fois utile, conviviale et inspirante. Je suis rentré chez moi plus conscient et vraiment motivé à changer certaines habitudes.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 3,

                'prompt' => 'Les Devoirs à la Maison',

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

                'correction' => "Les devoirs à la maison : quels enjeux ?\n\nLes devoirs à la maison suscitent un débat entre familles et éducateurs. Pour certains parents, ils développent l'autonomie, l'organisation et renforcent le lien quotidien avec l'école grâce à un moment partagé. En revanche, leurs opposants jugent leur efficacité non prouvée et estiment qu'ils accentuent les inégalités entre élèves. (47 mots)\n\nÀ mon avis, les devoirs à la maison doivent être limités, mais non supprimés. D'abord, ils peuvent aider l'élève à revoir calmement la leçon et à prendre de bonnes habitudes de travail. Ensuite, ils ne devraient pas demander l'aide constante des parents, car tous n'ont ni le temps ni les connaissances nécessaires. De plus, des exercices courts et clairs sont plus utiles que des tâches longues et répétitives. Par exemple, relire une poésie ou préparer deux questions sur un texte peut renforcer l'apprentissage sans créer de stress. Enfin, l'école devrait privilégier des devoirs adaptés à l'âge des enfants, afin de soutenir la réussite sans creuser les écarts sociaux. (117 mots)",

            ],

        ],

    ],

    [

        'combo' => 13,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Écrivez un message à votre ami(e) pour lui faire part de votre programme de déménagement vers votre nouveau logement, en demandant son aide ( date, lieu, programme)',

                'correction' => "Objet : Mon déménagement samedi prochain\n\nSalut Karim,\n\nJe t'écris pour te parler de mon déménagement vers mon nouveau logement et pour te demander un petit coup de main. Je déménage le samedi 18 mai à Montréal, dans un appartement situé au 25, rue Beaubien, près du métro. Le programme est simple : à 8 h, je récupère le camion de location ; à 9 h, on commence à charger les cartons dans mon ancien studio ; vers 12 h, on fera une pause déjeuner ; puis, l'après-midi, on transportera les meubles et on les installera dans le nouvel appartement. Si tu es disponible, ton aide me serait vraiment très utile, surtout pour porter le canapé et les cartons lourds.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 2,

                'prompt' => 'Vous avez participé à un concours pour gagner un séjour de deux semaines dans votre ville préférée. Le thème de ce concours est "Mon artiste préféré". Écrivez un article de blog pour parler de votre artiste préféré.',

                'correction' => "[Mon artiste préféré]\n\nBonjour à tous,\n\nL'an dernier, j'ai participé à un concours pour gagner deux semaines dans ma ville préférée, Montréal. Le thème était \"Mon artiste préféré\", et j'ai tout de suite pensé à Stromae. Je l'ai découvert il y a quelques années avec ma sœur, un soir d'hiver, dans notre salon. À cette époque, j'écoutais souvent ses chansons, parce qu'elles étaient originales, profondes et pleines d'énergie.\n\nEn 2023, j'ai eu la chance d'assister à son concert dans une grande salle au centre-ville, avec deux amis. L'ambiance était incroyable : les lumières étaient magnifiques, le public chantait fort, et tout le monde semblait heureux. Quand Stromae est arrivé sur scène, j'ai ressenti beaucoup d'émotion. Il a interprété mes chansons préférées, et j'ai été impressionné par sa voix et sa présence. Ce soir-là, j'ai vraiment compris pourquoi il était mon artiste préféré.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 3,

                'prompt' => 'Objets Connectés',

                'documents' => [

                    [

                        'title' => 'Document 1',

                        'content' => 'Les objets connectés facilitent notre vie quotidienne. Ce sont des objets ou des équipements pilotés à distance à l\'aide d\'un téléphone portable ou par Internet, comme le système de chauffage ou la fermeture des portes. De plus, avec une montre ou un bracelet connecté, toutes nos activités peuvent être analysées. Un logiciel va mesurer le nombre de nos pas pour nous inciter à faire davantage d\'exercice. Un domaine où l\'on comprend mieux leur utilité est celui de la surveillance de la santé. Certains objets agissent comme un carnet de santé, toujours prêts à vous rappeler un rendez-vous chez le médecin ou des médicaments à prendre.',

                    ],

                    [

                        'title' => 'Document 2',

                        'content' => 'Il y aurait 50 milliards d\'objets connectés dans le monde : l\'alarme, le téléviseur, la caméra de surveillance, les volets, le détecteur de fumée, etc. Face à cette évolution, la question de la sécurité se pose. En effet, un pirate informatique peut prendre le contrôle d\'un objet connecté en quelques minutes. Ou encore, un cambrioleur pourrait vérifier grâce aux caméras de surveillance connectées d\'une habitation que les occupants sont absents. Il est aussi possible d\'entrer dans le système connecté d\'une voiture, et d\'en prendre les commandes à distance.',

                    ],

                ],

                'correction' => "Les objets connectés dans la vie moderne\n\nLes objets connectés occupent une place croissante dans notre quotidien. Le premier document souligne leur utilité pour le confort, le suivi des activités et la santé. Cependant, le second met en garde contre des risques importants, notamment le piratage, la surveillance des habitants et la prise de contrôle de certains équipements. (49 mots)\n\nÀ mon avis, les objets connectés sont utiles, mais leur usage doit rester prudent. D'abord, ils offrent un vrai confort, car on peut gérer son logement plus facilement et suivre sa santé en temps réel. Ensuite, ils peuvent aider des personnes âgées ou malades à ne pas oublier un traitement. Par exemple, une montre connectée peut signaler une anomalie du rythme cardiaque et prévenir rapidement un proche. De plus, ces outils permettent parfois d'économiser de l'énergie grâce à un chauffage mieux réglé. Enfin, leurs avantages ne doivent pas faire oublier la sécurité. Si les utilisateurs choisissent des mots de passe solides et des systèmes fiables, les risques peuvent être limités. (119 mots)",

            ],

        ],

    ],

    [

        'combo' => 14,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => 'Analysez le sujet d\'examen suivant : Vous faites du sport dans un club. Vous venez de remporter une compétition, vous écrivez un courriel à vos amis pour leur raconter cet évènement sportif et annoncer votre réussite sportive.',

                'correction' => "Objet : Une super nouvelle : j'ai gagné la compétition !\n\nSalut Karim,\n\nJe voulais te raconter une excellente nouvelle. Dimanche dernier, j'ai participé à une compétition de natation avec mon club, à la piscine municipale de Lyon, de 9 h à 17 h. Il y avait beaucoup de participants et l'ambiance était incroyable. J'étais un peu stressé au début, mais je me suis bien concentré. J'ai nagé le 100 mètres nage libre et, à ma grande surprise, j'ai terminé premier ! Mon entraîneur et mes coéquipiers étaient très contents de ma performance. Après la remise des médailles, nous avons fêté cette victoire ensemble. Je suis vraiment fier de cette réussite sportive et je voulais partager ce moment avec toi.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 2,

                'prompt' => 'Le site « colocation.com » recherche des témoignages sur vos expériences de colocation. Vous avez déjà habité en colocation avec des amis. Vous racontez votre expérience aux membres du site internet. Vous donnez votre opinion sur ce mode de logement.',

                'correction' => "La colocation avec des amis : une belle aventure\n\nBonjour à tous,\n\nIl y a deux ans, j'ai habité en colocation à Lyon avec trois amis d'université. Nous avons loué un grand appartement près du centre-ville, dans un quartier très animé. Au début, j'étais un peu inquiet, car je n'avais jamais partagé mon logement avec d'autres personnes. Finalement, cette expérience s'est très bien passée. L'ambiance était chaleureuse et nous avons vite trouvé notre organisation. Nous faisions les courses ensemble, nous préparions parfois le dîner et nous passions souvent nos soirées à discuter ou à regarder des films. Bien sûr, il y a eu aussi quelques petits conflits, surtout à cause du ménage et du bruit, mais nous avons toujours réussi à parler calmement. Cette colocation m'a appris à être plus patient et plus responsable. À mon avis, c'est un mode de logement économique, pratique et très enrichissant, surtout quand on vit avec des personnes respectueuses.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 3,

                'prompt' => 'Le grossissement des villes.',

                'documents' => [

                    [

                        'title' => 'Document 1',

                        'content' => 'De nos jours, les villes grossissent toujours plus. Malheureusement, ce phénomène a un impact fort sur l\'environnement. Car plus une ville grossit, plus elle a des effets négatifs sur la nature et donc, ensuite, sur l\'homme. L\'effet négatif le plus visible est la déforestation, qui diminue les végétaux qui retiennent le carbone. Donc, quand on les supprime pour construire des bâtiments ou des rues, on supprime des espaces verts capables de retenir des millions de tonnes de carbone.',

                    ],

                    [

                        'title' => 'Document 2',

                        'content' => 'Plus de la moitié de l\'humanité vit en ville (huit habitants sur dix dans les pays riches) : la vie urbaine est donc le principal enjeu écologique. On entend souvent dire que l\'organisation actuelle des villes n\'est pas écologique, et que le grossissement des villes ne fait qu\'augmenter le problème. Pourtant, il faut se méfier des apparences : les villes ne sont pas toujours aussi antiécologiques qu\'on l\'imagine. Par exemple, la consommation d\'énergie d\'un citadin est moins importante que celle d\'un habitant de la campagne.',

                    ],

                ],

                'correction' => "Le grossissement des villes\n\nLe grossissement des villes suscite un débat important. Le document 1 affirme que cette expansion détruit les forêts et réduit les espaces verts capables d'absorber le carbone. Cependant, le document 2 souligne qu'une ville plus dense peut aussi être plus écologique, car les citadins consomment parfois moins d'énergie que les ruraux. (49 mots)\n\nÀ mon avis, le grossissement des villes n'est pas forcément négatif, mais il doit être bien contrôlé. D'abord, une ville dense permet de mieux organiser les transports publics, ce qui réduit l'usage de la voiture et donc la pollution. Ensuite, elle facilite l'accès aux écoles, aux hôpitaux et aux emplois, ce qui améliore la vie quotidienne. De plus, il est possible de construire sans détruire toute la nature, par exemple en créant des parcs, des toits végétalisés et des bâtiments plus écologiques. Enfin, le vrai problème n'est pas la taille des villes, mais une mauvaise gestion de leur développement. Une grande ville bien pensée peut donc être plus durable qu'une urbanisation dispersée. (117 mots)",

            ],

        ],

    ],

    [

        'combo' => 15,

        'tasks' => [

            [

                'task' => 1,

                'prompt' => '« Vous allez fêter votre anniversaire. Vous envoyez un message à vos amis pour les inviter. Vous leur décrivez le programme de la soirée et leur demandez de l\'aide pour l\'organisation (60 mots minimum/120 mots maximum). »',

                'correction' => "Objet : Invitation à mon anniversaire\n\nSalut les amis,\n\nJe vais fêter mon anniversaire samedi 18 mai chez moi, à Montréal, à partir de 19 h, et j'aimerais beaucoup que vous veniez. On commencera par un petit apéritif, puis on dînera ensemble vers 20 h 30. Après le repas, on écoutera de la musique, on fera quelques jeux et, bien sûr, on mangera le gâteau vers 22 h. Si le temps est beau, on ira aussi un moment sur la terrasse. J'aurais besoin de votre aide pour l'organisation : est-ce que quelqu'un peut apporter des boissons, des chips ou un dessert ? Et qui peut arriver un peu plus tôt pour m'aider à décorer ?\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 2,

                'prompt' => '« Vous avez participé à un concours de cuisine. Sur votre site Internet, vous écrivez un court article pour raconter cette journée. Vous expliquez pourquoi vous avez aimé ou pourquoi vous n\'avez pas aimé cette expérience (120 mots minimum/150 mots maximum). »',

                'correction' => "Mon expérience au concours de cuisine\n\nBonjour à tous,\n\nLe week-end dernier, j'ai participé à un concours de cuisine organisé dans une grande salle municipale près de chez moi. J'y suis allé avec ma sœur, qui m'a beaucoup encouragé. L'ambiance était très animée : il y avait des odeurs délicieuses, des participants stressés, et un jury très sérieux. J'ai préparé un tajine au poulet avec des légumes frais. Au début, j'étais assez nerveux, parce que je n'avais jamais cuisiné devant autant de monde. Ensuite, je me suis senti plus confiant, surtout quand les visiteurs ont commencé à sourire en regardant mon plat.\n\nJ'ai aimé cette expérience parce qu'elle a été à la fois intense et enrichissante. J'ai appris à mieux gérer mon stress et à travailler plus vite. Même si je n'ai pas gagné le premier prix, j'ai reçu de bons commentaires du jury. Cette journée a été fatigante, mais vraiment inoubliable.\n\nÀ bientôt,\n\nAYOUB",

            ],

            [

                'task' => 3,

                'prompt' => 'Le travail des jeunes pendant les vacances',

                'documents' => [

                    [

                        'title' => 'Document 1',

                        'content' => 'Les vacances sont le moment où les jeunes se détendent après plusieurs mois à l\'université. Ils voyagent et profitent des beaux jours. Mais certains étudiants ne prennent pas de vacances et décident de travailler : ils gardent des enfants, servent dans des restaurants ou ramassent des fruits, par exemple. Même si le travail saisonnier présente beaucoup d\'avantages, il prive en quelque sorte les jeunes de leur temps libre : ils n\'ont pas le temps de se reposer, d\'avoir des loisirs et de passer du temps avec leurs proches. De plus, ces emplois d\'été sont parfois mal payés, fatigants ou ennuyeux.',

                    ],

                    [

                        'title' => 'Document 2',

                        'content' => 'Pourquoi un grand nombre d\'étudiants travaillent-ils pendant les vacances ? Ils font ce choix parce que cette expérience est un premier pas dans le monde professionnel : ils apprennent à se responsabiliser tout en découvrant un métier. Recevoir un salaire est aussi une source de motivation. Travailler pendant les vacances permet à certains d\'être indépendants financièrement des parents : ils vont utiliser cet argent pour voyager ou se faire plaisir. Pour d\'autres, il s\'agit d\'une nécessité : ils travaillent l\'été pour payer leurs études ou leur loyer d\'étudiant.',

                    ],

                ],

                'correction' => "Le travail des jeunes pendant les vacances\n\nPendant les vacances, beaucoup de jeunes hésitent entre repos et emploi saisonnier. Le premier document souligne que ces travaux peuvent être fatigants, mal payés et réduire le temps libre. En revanche, le second montre qu'ils offrent une première expérience professionnelle, un salaire utile et parfois une vraie autonomie financière. (50 mots)\n\nÀ mon avis, travailler pendant les vacances est une bonne idée, à condition que l'emploi reste raisonnable. D'abord, cette expérience permet aux jeunes d'apprendre la ponctualité, l'effort et le sens des responsabilités. Ensuite, gagner de l'argent donne plus de liberté et évite de dépendre totalement des parents. De plus, un travail d'été peut aider à découvrir un métier et à préparer l'avenir. Par exemple, un étudiant qui travaille dans un restaurant améliore souvent sa communication et sa résistance au stress. Enfin, il faut garder un équilibre, car les vacances servent aussi à se reposer. Un emploi à temps partiel me paraît donc la meilleure solution pour concilier expérience et détente. (118 mots)",

            ],

        ],

    ],

];

