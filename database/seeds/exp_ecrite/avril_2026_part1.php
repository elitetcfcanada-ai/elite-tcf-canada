<?php
declare(strict_types=1);

/** Combinaisons 1 à 6 — Avril 2026 */
return [
    [
        'combo' => 1,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous partez en voyage et vous laissez votre appartement à un ami qui veut venir rester chez vous pendant vos vacances. Vous lui envoyez un message pour décrire votre appartement (immeuble, logement, accès…).',
                'correction' => "Salut,\nJ'espère que tu vas bien. Comme tu vas rester dans mon appartement pendant mes vacances, je t'explique rapidement. Il est situé au 3ᵉ étage d'un immeuble calme, avec ascenseur. Le quartier est sécurisé et proche des commerces. L'appartement est un 3 pièces : une chambre, un salon, une cuisine équipée et une salle de bain. Tu auras aussi un petit balcon.\nL'accès est simple : je te laisserai les clés chez le gardien. Le métro est à 5 minutes à pied et il y a un arrêt de bus juste en bas. N'hésite pas si tu as des questions.\nÀ bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un article de blog sur votre souvenir de voyage que vous avez le plus aimé.',
                'correction' => "Mon plus beau souvenir de voyage\nChers lecteurs,\nLors de mon dernier voyage, j'ai vécu un moment que je n'oublierai jamais. C'était pendant une excursion dans un petit village situé près de la mer. Dès mon arrivée, j'ai été impressionné par la beauté du paysage : le ciel était bleu, l'air était frais et l'ambiance très calme. J'ai marché longtemps dans les ruelles, entre les maisons traditionnelles, puis je suis arrivé sur une plage magnifique.\nCe que j'ai le plus aimé, c'est le coucher du soleil. Les couleurs étaient incroyables et tout le monde s'arrêtait pour admirer ce spectacle. J'ai pris plusieurs photos, mais aucune image ne peut vraiment représenter l'émotion que j'ai ressentie.\nCe souvenir m'a marqué parce qu'il m'a permis de me détendre, de réfléchir et de profiter pleinement du moment. C'est une expérience que je voudrais revivre un jour.",
            ],
            [
                'task' => 3,
                'prompt' => 'Vivre chez ses parents : pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Les avantages : penser à l'avenir, avoir les vêtements propres, être en confort, surtout que l'argent n'est pas toujours suffisant. Vivre avec ses parents pendant la période des études permet aux jeunes d'économiser les frais de logement, de profiter de plats faits maison et d'une certaine stabilité psychique. Les adolescents qui vivent avec leurs parents peuvent économiser leur argent pour des projets de vie (ils ne paient ni le loyer ni la nourriture). Par exemple, pour ne pas payer un loyer qui coûte cher, le ménage est fait, les vêtements sont propres. Il est favorable que les jeunes vivent avec leurs parents, selon le témoignage de deux jeunes : une jeune fille disait que le loyer est très cher et qu'étudier loin de chez soi coûte plus cher. Le deuxième jeune trouve que vivre avec ses parents est plus bénéfique pour lui, par exemple : la nourriture est bonne, les vêtements sont toujours propres et il n'y a pas de place pour la solitude.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Les inconvénients : manque de liberté. Vivre seuls leur permet d'être indépendants. Les adolescents qui vivent avec leurs parents s'ennuient car leurs parents décident à leur place et ils sont toujours dépendants de leurs parents. Un avis d'un certain monsieur de 25 ans qui a perdu son emploi : il était dans l'obligation de retourner vivre avec ses parents, et maintenant il a perdu son espace d'intimité. Contre la vie des jeunes avec leurs parents, un témoignage d'un jeune qui considère que revenir chez ses parents, c'est revenir en arrière.",
                    ],
                ],
                'correction' => "Vivre chez ses parents : pour ou contre ?\nVivre chez ses parents présente plusieurs avantages : cela permet d'économiser le loyer, de profiter de repas faits maison et d'avoir plus de confort pendant les études. Cependant, certains soulignent les inconvénients : manque de liberté, dépendance, décisions imposées par les parents et perte d'intimité, surtout à l'âge adulte.\n\nÀ mon avis, vivre chez ses parents peut être une bonne solution, surtout quand on est étudiant ou quand on n'a pas encore une situation stable. Cela permet d'économiser de l'argent et d'éviter le stress lié au loyer, qui est souvent très cher. Par exemple, un jeune peut utiliser cet argent pour financer ses études, passer son permis ou préparer un projet d'avenir. De plus, vivre en famille peut apporter un soutien moral et éviter la solitude. Cependant, je pense qu'il est important de chercher progressivement son indépendance. Quand on devient adulte, on a besoin de liberté, d'intimité et de responsabilités. Selon moi, l'idéal est de rester chez ses parents temporairement, puis de partir dès que la situation financière le permet.",
            ],
        ],
    ],
    [
        'combo' => 2,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami veut se mettre au sport. Vous lui envoyez un message pour lui conseiller une salle de sport située dans votre quartier (localisation, prix, type d\'activités, etc.).',
                'correction' => "Salut,\nJ'espère que tu vas bien. Si tu veux te mettre au sport, je te conseille une salle dans mon quartier : Fitness Plus, située près de la station de métro, à 5 minutes à pied. Elle est très pratique et l'ambiance est motivante.\nLes tarifs sont raisonnables : environ 35 dollars par mois, avec une formule sans engagement. La salle est ouverte tous les jours, de 6 h à 22 h, donc tu peux y aller quand tu veux.\nTu peux faire de la musculation, du cardio, et aussi participer à des cours collectifs comme le yoga, la zumba ou le stretching.\nSi tu veux, je peux t'accompagner pour une première séance.",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à un événement qui vous a marqué (anniversaire, mariage, etc.). Racontez votre souvenir.',
                'correction' => "Un anniversaire que je n'oublierai jamais\nIl y a quelques mois, j'ai participé à un anniversaire qui m'a vraiment marqué. C'était l'anniversaire surprise de mon meilleur ami. Nous avions tout organisé en secret avec ses proches. La fête avait lieu chez un ami, dans une maison décorée avec des ballons, des lumières et une grande banderole.\nQuand il est arrivé, tout le monde a crié « Surprise ! » et il était très ému. Ce moment m'a beaucoup touché, car il ne s'y attendait pas du tout. Ensuite, nous avons mangé un bon repas, pris des photos et écouté de la musique. Il y avait aussi un grand gâteau et chacun a raconté un souvenir drôle avec lui.\nCe que j'ai le plus aimé, c'est l'ambiance chaleureuse et l'énergie positive. Je garde un excellent souvenir de cette soirée, car elle était pleine de joie et de sincérité.",
            ],
            [
                'task' => 3,
                'prompt' => 'La lecture pour les enfants',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Avec l'avancée technologique et les produits high-tech qui envahissent de plus en plus notre quotidien, nos enfants oublient la lecture et s'intéressent davantage aux jeux vidéo, aux sports, à la musique… Contrairement à nous, les adultes, dont beaucoup ont lu des milliers de pages, la génération actuelle est toujours occupée par les réseaux sociaux et le gaming ou prend du plaisir à pratiquer du sport qui attire davantage de jeunes grâce aux stars internationales du football, du tennis, de l'athlétisme… Alors, avec tout ça, pourquoi devons-nous forcer les enfants à lire un bouquin ? Et comme le dit un proverbe, « le goût de la lecture ne peut pas s'imposer »… il faut laisser l'enfant choisir ce qu'il veut lire et surtout ne pas l'obliger à lire quand il n'a pas envie.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "L'amour de la lecture se transmet de génération en génération, bien que ces dernières années, on ne trouve plus beaucoup de bouquins entre les mains des enfants, laissant la place aux smartphones et aux tablettes. En apprenant à lire régulièrement, l'enfant acquiert le langage plus aisément tout en développant sa capacité d'audition et de concentration. De plus, pour prendre du plaisir ensemble, les parents peuvent consacrer quotidiennement 10 minutes à leurs enfants pour lire des bouquins ; une activité qui renforcera à coup sûr la complicité parent/enfant.",
                    ],
                ],
                'correction' => "La lecture pour les enfants : faut-il les encourager ?\n\nAujourd'hui, beaucoup d'enfants lisent moins à cause des technologies, des réseaux sociaux et des jeux vidéo. Certains pensent qu'il ne faut pas forcer un enfant à lire, car le plaisir de lire ne s'impose pas et l'enfant doit choisir librement. D'autres affirment que la lecture est essentielle pour le langage, la concentration et le lien parent-enfant.\n\nÀ mon avis, la lecture est très importante pour les enfants, mais il ne faut pas la présenter comme une obligation ou une punition. Lire permet de développer le vocabulaire, l'imagination et la concentration. Par exemple, un enfant qui lit régulièrement comprend mieux les textes à l'école et s'exprime plus facilement. Cependant, si on force un enfant à lire un livre qui ne l'intéresse pas, il peut détester la lecture. Selon moi, la meilleure solution est d'encourager doucement, en proposant des livres adaptés à son âge et à ses goûts : BD, histoires courtes, romans simples. Les parents peuvent aussi lire avec l'enfant quelques minutes par jour. Cela crée une bonne habitude et un moment agréable en famille.",
            ],
        ],
    ],
    [
        'combo' => 3,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous faites du sport dans un club. Vous venez de remporter une compétition. Vous écrivez un courriel à vos amis pour leur raconter cet événement sportif et annoncer votre réussite sportive.',
                'correction' => "Bonjour les amis,\nJ'espère que vous allez bien. Je vous écris pour vous annoncer une très bonne nouvelle : ce week-end, j'ai participé à une compétition avec mon club de sport et j'ai remporté la première place. Je suis vraiment heureux et très fier de cette réussite.\nL'événement s'est très bien passé. Il y avait beaucoup de participants et le niveau était assez élevé. Au début, j'étais un peu stressé, mais ensuite j'ai réussi à me concentrer et à donner le meilleur de moi-même. L'ambiance était excellente et les entraîneurs nous ont beaucoup encouragés.\nCette victoire me motive encore plus à continuer les entraînements et à progresser. J'espère qu'on pourra se voir bientôt pour fêter ça ensemble.\nÀ très bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Le site « colocation.com » recherche des témoignages sur vos expériences de colocation. Écrivez-nous ! Vous avez déjà habité en colocation avec des amis. Vous racontez votre expérience aux membres du site internet. Vous donnez votre opinion sur ce mode de logement.',
                'correction' => "Bonjour,\nJ'ai déjà vécu en colocation avec deux amis pendant mes études, et globalement c'était une très bonne expérience. D'abord, cela permet de réduire les dépenses, surtout le loyer et les factures. On partage aussi les tâches du quotidien, comme le ménage et les courses, ce qui rend la vie plus simple.\nJ'ai aimé l'ambiance : on n'est jamais seul, on peut discuter, cuisiner ensemble et s'entraider. Cependant, il y a aussi des difficultés. Par exemple, il faut respecter les règles, supporter les habitudes des autres et éviter les conflits, surtout pour le bruit ou la propreté.\nEn conclusion, je pense que la colocation est une bonne solution, surtout pour les étudiants ou les jeunes travailleurs, à condition de bien choisir ses colocataires et de communiquer clairement.",
            ],
            [
                'task' => 3,
                'prompt' => 'Le grossissement des villes',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "De nos jours, les villes grossissent toujours plus. Malheureusement, ce phénomène a un impact fort sur l'environnement, car plus une ville grossit, plus elle a des effets négatifs sur la nature et donc, ensuite, sur l'homme. L'effet négatif le plus visible est la déforestation, qui diminue les végétaux qui retiennent le carbone. Donc, quand on les supprime pour construire des bâtiments ou des rues, on supprime des espaces verts capables de retenir des millions de tonnes de carbone.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Plus de la moitié de l'humanité vit en ville (huit habitants sur dix dans les pays riches). La vie urbaine est donc le principal enjeu écologique. On entend souvent dire que l'organisation actuelle des villes n'est pas écologique, et que le grossissement des villes ne fait qu'augmenter le problème. Pourtant, il faut se méfier des apparences : les villes ne sont pas toujours aussi antiécologiques qu'on l'imagine. Par exemple, la consommation d'énergie d'un citadin est moins importante que celle d'un habitant de la campagne.",
                    ],
                ],
                'correction' => "Le grossissement des villes : problème ou solution ?\n\nAujourd'hui, les villes grandissent de plus en plus. Certains pensent que cela nuit fortement à l'environnement, notamment à cause de la déforestation et de la disparition des espaces verts qui retiennent le carbone. Cependant, d'autres affirment que la ville n'est pas toujours antiécologique, car un citadin consomme parfois moins d'énergie qu'un habitant de la campagne.\n\nÀ mon avis, le grossissement des villes est un phénomène inévitable, mais il doit être mieux contrôlé. D'un côté, l'expansion urbaine peut détruire la nature, réduire les forêts et augmenter la pollution. Par exemple, quand on construit trop de bâtiments, on supprime des parcs et cela rend la ville moins agréable. Mais d'un autre côté, vivre en ville peut aussi être plus écologique, car les transports en commun sont plus développés et les logements sont souvent plus petits, donc moins énergivores. Selon moi, le vrai problème n'est pas la ville, mais la manière dont elle se développe. Il faut créer des villes plus vertes, avec des espaces naturels, des pistes cyclables et des bâtiments économes en énergie",
            ],
        ],
    ],
    [
        'combo' => 4,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un message à vos amis pour les inviter à votre anniversaire et leur raconter comment va se dérouler votre fête.',
                'correction' => "Salut les amis,\nJ'espère que vous allez bien. Je vous écris pour vous inviter à mon anniversaire ! La fête aura lieu samedi prochain à partir de 19 h, chez moi. Je serais vraiment content de vous voir pour partager ce moment ensemble.\nAu programme : un petit apéritif, un bon dîner, de la musique et un gâteau. Ensuite, on pourra discuter, danser et prendre des photos. L'ambiance sera simple et conviviale.\nSi vous pouvez, merci d'apporter une boisson ou un petit dessert à partager. Confirmez-moi votre présence avant jeudi pour que je puisse bien organiser la soirée.\nÀ très bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => 'Répondez en commentaire d\'une publication sur Facebook au sujet des études à l\'étranger en citant les avantages et les inconvénients de cette expérience.',
                'correction' => "Bonjour à tous,\nÀ mon avis, faire des études à l'étranger est une expérience très enrichissante. D'abord, cela permet de découvrir une nouvelle culture, d'améliorer une langue étrangère et de devenir plus autonome. On apprend aussi à se débrouiller seul, à gérer son budget et à s'adapter à un nouvel environnement. Sur le plan professionnel, c'est un vrai avantage : cette expérience valorise un CV et peut ouvrir plus d'opportunités.\nCependant, il y a aussi des inconvénients. Le début peut être difficile à cause du choc culturel, de la solitude ou de la distance avec la famille. De plus, les études à l'étranger coûtent souvent cher (logement, transport, frais universitaires). Il faut donc bien préparer son projet.\nEn conclusion, malgré les difficultés, je pense que c'est une expérience très positive si on est motivé et bien organisé.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les nouvelles technologies pour les enfants',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Les enfants de l'école primaire peuvent utiliser la technologie pour trouver des vidéos sur les sujets qu'ils ont étudiés à l'école ou pour jouer à des jeux qui améliorent leur aisance en mathématiques, leur compréhension de la lecture ou leurs compétences en dactylographie.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Les nouvelles technologies encouragent les enfants à être sédentaires. C'est une préoccupation majeure pour les parents. Cela ne mène à aucun jeu libre créatif, à aucune interaction sociale face à face, conduisant à aucun effort physique ! Pourquoi ne pas essayer certaines de ces activités amusantes pour encourager vos enfants à ranger leurs appareils ?",
                    ],
                ],
                'correction' => "Les nouvelles technologies pour les enfants : utiles ou dangereuses ?\n\nLes nouvelles technologies peuvent aider les enfants de primaire à apprendre, par exemple grâce à des vidéos éducatives ou des jeux qui développent les mathématiques et la lecture. Cependant, elles sont aussi critiquées, car elles rendent les enfants plus sédentaires, réduisent les jeux créatifs, les interactions sociales et l'activité physique.\n\nÀ mon avis, les nouvelles technologies peuvent être positives pour les enfants, mais seulement si elles sont bien contrôlées. Elles offrent des outils intéressants pour apprendre autrement et rendre les cours plus motivants. Par exemple, un enfant peut regarder une vidéo éducative pour mieux comprendre une leçon ou s'entraîner avec un jeu de calcul. Toutefois, un usage excessif peut provoquer de la fatigue, une dépendance et un manque d'activité physique. De plus, si l'enfant passe trop de temps sur un écran, il risque de moins jouer, moins lire et moins communiquer avec les autres. Selon moi, les parents doivent fixer des limites claires, choisir les contenus et encourager aussi les activités sportives et les jeux en famille.",
            ],
        ],
    ],
    [
        'combo' => 5,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un message à vos amis pour leur proposer de passer un week-end avec vous pour faire du sport.',
                'correction' => "Salut les amis,\nJ'espère que vous allez bien. Je voulais vous proposer une idée pour le week-end prochain : pourquoi ne pas passer deux jours ensemble pour faire du sport ? On pourrait organiser un petit séjour près du lac ou à la montagne. Au programme : randonnée le matin, vélo ou footing l'après-midi, et pourquoi pas un match de football ou de volleyball.\nCe serait une bonne occasion de bouger, de prendre l'air et de passer un bon moment ensemble. On pourrait aussi prévoir un pique-nique et une soirée tranquille après l'effort.\nDites-moi si vous êtes disponibles et partants pour cette idée !",
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un article de blog sur un forum pour raconter pourquoi vous avez décidé de changer vos habitudes alimentaires.',
                'correction' => "Pourquoi j'ai décidé de changer mes habitudes alimentaires\nChers lecteurs,\nDepuis quelque temps, j'ai décidé de changer mes habitudes alimentaires, car je voulais me sentir mieux au quotidien. Avant, je mangeais souvent des plats rapides, trop gras ou trop sucrés, surtout quand j'étais pressé. Résultat : je me sentais fatigué, je dormais mal et j'avais parfois des problèmes de digestion.\nUn jour, j'ai compris que mon alimentation avait un impact direct sur ma santé. J'ai donc commencé à manger plus équilibré : plus de légumes, de fruits, de produits frais et moins de boissons sucrées. Par exemple, au lieu de manger des fast-foods, je prépare maintenant des repas simples à la maison.\nGrâce à ce changement, j'ai plus d'énergie et je me sens plus léger. Je pense que ce n'est pas difficile, il faut juste être motivé et progresser petit à petit.",
            ],
            [
                'task' => 3,
                'prompt' => 'La sieste au travail.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "La sieste au travail peut avoir de nombreux avantages pour les employés et les entreprises. En permettant aux travailleurs de faire une petite sieste, cela peut aider à augmenter leur productivité et à améliorer leur santé et leur bien-être. Les siestes courtes peuvent aider à améliorer la vigilance et la concentration, réduire les niveaux de stress et améliorer l'humeur. En outre, la pratique de la sieste peut aider à réduire les coûts pour les employeurs en diminuant les coûts liés à la fatigue et aux accidents du travail. Il est donc important pour les entreprises de considérer les avantages potentiels de la sieste au travail et d'envisager d'offrir cette option à leur personnel.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Malgré les nombreux avantages de la sieste au travail, il peut être difficile pour toutes les entreprises de mettre en place des lits et des salles dédiées à cette pratique. Cela peut être dû à des contraintes financières ou logistiques, ou encore à des politiques de travail strictes qui ne permettent pas aux employés de faire des siestes pendant les heures de travail. En outre, certains travailleurs peuvent ne pas se sentir à l'aise de faire une sieste au travail, ce qui peut limiter l'adoption de cette pratique. Il est donc important pour les entreprises de considérer les avantages et les limites de la sieste au travail et de trouver des solutions créatives pour permettre à leurs employés de se reposer et de se régénérer pendant la journée de travail.",
                    ],
                ],
                'correction' => "La sieste au travail : bonne ou mauvaise idée ?\n\nCertains estiment que la sieste au travail améliore la productivité, la concentration et le bien être des employés, tout en réduisant les accidents liés à la fatigue. Cependant, d'autres soulignent les difficultés d'organisation : manque d'espace, coûts financiers, règles strictes ou gêne de certains employés face à cette pratique.\n\nÀ mon avis, la sieste au travail peut être bénéfique si elle est bien encadrée. Une courte pause de 15 à 20 minutes peut aider à retrouver de l'énergie et à mieux se concentrer l'après-midi. Par exemple, après le déjeuner, beaucoup de personnes ressentent une baisse d'attention. Une sieste courte pourrait améliorer la performance et réduire les erreurs. Toutefois, toutes les entreprises n'ont pas les moyens d'aménager des espaces dédiés. Selon moi, il serait possible de proposer des solutions simples, comme une salle de repos ou des pauses flexibles. L'essentiel est de favoriser le bien-être des employés sans perturber l'organisation du travail.",
            ],
        ],
    ],
    [
        'combo' => 6,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous envisagez de déménager et avez trouvé un appartement. Vous souhaitez en informer un ami en lui fournissant les détails concernant le bien en question : nombre de pièces, emplacement et prix.',
                'correction' => "Bonjour,\nJ'espère que tu vas bien. Je voulais t'annoncer que j'envisage de déménager et j'ai trouvé un appartement qui me plaît beaucoup. Il s'agit d'un trois-pièces, avec deux chambres, un salon spacieux, une cuisine équipée et une salle de bain moderne. L'appartement est lumineux et situé au troisième étage d'un immeuble calme avec ascenseur. Il se trouve dans un quartier proche du centre-ville, à côté des transports en commun et des commerces, ce qui est très pratique pour le travail et les courses. Le loyer est de 850 euros par mois, charges comprises. Je pense que c'est un bon rapport qualité-prix pour la zone.\nDis-moi ce que tu en penses !",
            ],
            [
                'task' => 2,
                'prompt' => 'Une annonce de festival de musique gratuit a été publiée dans votre ville, et vous avez profité de cette occasion pour y assister avec votre ami. Écrivez un article de blog pour raconter l\'expérience que vous avez vécue lors de cet événement musical.',
                'correction' => "Un festival de musique gratuit : une soirée incroyable !\nLe week-end dernier, un festival de musique gratuit a été organisé dans ma ville, et j'ai décidé d'y aller avec un ami. Dès notre arrivée, l'ambiance était magnifique : beaucoup de monde, des familles, des jeunes, et une énergie très positive.\nIl y avait plusieurs styles de musique, comme la pop, le rap et même de la musique traditionnelle. Les artistes étaient très talentueux et le son était de bonne qualité. J'ai particulièrement aimé un groupe qui a mis tout le public à l'aise et a fait chanter tout le monde.\nNous avons aussi profité des stands de nourriture et des boissons. Tout était bien organisé et la sécurité était présente, ce qui nous a rassurés.\nJ'ai vraiment adoré cette expérience, car c'était un moment simple, joyeux et accessible à tous. Je pense que ce type d'événement est très important pour la vie culturelle d'une ville. J'espère qu'il y en aura d'autres bientôt.",
            ],
            [
                'task' => 3,
                'prompt' => "l'emploi des jeunes : diplôme ou expérience",
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "De nombreux jeunes diplômés de l'université se retrouvent au chômage. La reconnaissance des formations universitaires sur le marché du travail soulève des questions. En effet, les jeunes rencontrent souvent des difficultés pour trouver un emploi : les recruteurs leur reprochent d'avoir trop de diplômes et pas assez d'expérience, ou bien leur jeunesse constitue un obstacle. Plus de 60 % des jeunes diplômés n'ont toujours pas trouvé de travail un an après la fin de leurs études. Dans ce contexte, il semble urgent de revaloriser les diplômes et de favoriser l'emploi et la croissance.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "On entend souvent parler de célèbres chefs d'entreprise qui ont réussi sans aller à l'université. L'exemple de ces personnalités riches et admirées pourrait laisser croire qu'il suffit d'être brillant pour diriger une entreprise. Certains dirigeants estiment même que les études universitaires peuvent être un frein. Selon eux, l'université enseigne le conformisme et limite la créativité en incitant les étudiants à suivre des parcours classiques. Pour les futurs créateurs d'entreprise, rien ne vaudrait l'autoformation et l'expérience, sans l'aide d'aucune structure académique.",
                    ],
                ],
                'correction' => "L'emploi des jeunes : diplôme ou expérience ?\nLe premier document explique que de nombreux jeunes diplômés ont du mal à trouver un emploi, car les recruteurs estiment qu'ils manquent d'expérience malgré leurs études. Le second document souligne que certains entrepreneurs ont réussi sans diplôme universitaire et que l'expérience ou l'autoformation peuvent parfois être plus utiles que les études classiques.\n\nÀ mon avis, diplôme et expérience sont tous les deux importants. Un diplôme permet d'acquérir des connaissances solides et d'ouvrir des portes sur le marché du travail. Cependant, l'expérience pratique est essentielle pour comprendre le fonctionnement réel d'un métier. Par exemple, un étudiant peut avoir un bon niveau théorique, mais sans stage ou pratique, il peut se sentir perdu en entreprise. De plus, certaines personnes réussissent grâce à leur créativité et à leur capacité à apprendre seules. Selon moi, la meilleure solution est de combiner études et expérience, notamment à travers des stages, des formations en alternance ou des projets professionnels.",
            ],
        ],
    ],
];
