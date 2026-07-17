<?php
declare(strict_types=1);

/** Combinaisons 7 à 12 — Mai 2026 */
return [
    [
        'combo' => 7,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez inviter vos amis à découvrir un lieu touristique que vous appréciez particulièrement. Dans un message amical, présentez ce lieu, expliquez pourquoi vous l’aimez et proposez une date, une heure et un hébergement pour cette visite.',
                'correction' => 'Objet : Week-end à Chefchaouen

Salut les amis,

J’aimerais vous inviter à découvrir Chefchaouen, une ville touristique que j’apprécie énormément. Elle se trouve dans le nord du Maroc et elle est connue pour ses belles rues bleues, son ambiance calme et ses paysages magnifiques. J’aime ce lieu parce qu’on peut se promener tranquillement dans la médina, prendre de très belles photos et goûter à une cuisine locale délicieuse. Il y a aussi les montagnes autour de la ville, parfaites pour se détendre et respirer l’air frais. Je vous propose d’y aller le samedi 22 juin à 9 h. Nous pourrions passer une nuit dans une petite maison d’hôtes au centre-ville.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à un festival (de musique, de cinéma, de gastronomie…) mais vous avez été déçu. Rédigez un article sur votre blog dans lequel vous expliquez ce qui ne vous a pas plu.',
                'correction' => '[Mon week-end raté au festival]

Bonjour à tous,

Le mois dernier, je suis allé au festival de musique d’été à Lyon avec deux amis. Au début, nous étions très contents, car l’affiche semblait incroyable et le lieu, près du fleuve, paraissait magnifique sur Internet. Malheureusement, l’expérience a été très décevante. D’abord, nous avons attendu presque une heure à l’entrée, car l’organisation était mauvaise. Ensuite, le site était beaucoup trop plein, et il était difficile de circuler. L’ambiance était stressante au lieu d’être festive. En plus, le son était souvent trop fort ou de mauvaise qualité, donc nous ne profitions pas vraiment des concerts. Les stands de nourriture étaient très chers, et il n’y avait presque pas de places pour s’asseoir. J’ai aussi été déçu par la propreté du lieu, car il y avait des déchets partout. Franchement, je suis rentré fatigué, frustré et un peu triste.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'La vie à la campagne : pour ou contre ?',
                'correction' => 'Ville et campagne : deux modes de vie

Le choix entre la ville et la campagne suscite souvent des avis opposés. Le premier document souligne les loisirs variés, la proximité des services et la richesse culturelle de la ville. En revanche, le second met en avant le calme, le contact avec la nature et le logement moins cher à la campagne. (49 mots)

Pour ma part, je préfère la vie à la campagne, même si la ville présente des avantages. D\'abord, le calme permet de mieux se reposer et de réduire le stress du quotidien. Ensuite, vivre près de la nature améliore la qualité de vie, car on peut marcher, jardiner ou respirer un air plus pur. De plus, le coût du logement est souvent plus raisonnable, ce qui aide les familles à vivre dans un espace plus grand. Par exemple, un couple avec enfants peut avoir une maison avec jardin pour le prix d’un petit appartement en ville. Enfin, grâce à Internet et à la voiture, il est aujourd’hui plus facile de rester connecté et de se déplacer. (119 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'À mon avis, vivre en ville offre de nombreuses possibilités de divertissement. Il est facile d’aller au cinéma, de déjeuner dans un restaurant ou encore de faire du shopping. Tout est à proximité, ce qui évite de parcourir de longues distances pour se distraire. Il suffit de marcher un peu ou de prendre un taxi si le trajet est plus long. De plus, la ville propose une grande diversité d’événements culturels : musées, théâtres, opéras… Il y en a pour tous les goûts !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Dernièrement, j’ai décidé de quitter la ville pour vivre à la campagne, car j’avais besoin de me rapprocher de la nature et de profiter du calme. Désormais, au lieu de fréquenter quotidiennement les bars ou les restaurants, j’invite des amis à boire un verre sur la terrasse de ma maison ou j’organise de temps en temps un barbecue dans mon jardin. Un autre élément important a motivé ma décision : le coût du logement. Les prix des habitations sont bien plus abordables à la campagne qu’en ville.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 8,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Vous allez fêter votre anniversaire. Vous envoyez un message à vos amis pour les inviter. Vous leur décrivez le programme de la soirée et leur demandez de l’aide pour l’organisation.',
                'correction' => 'Salut les amis !

Je fête mon anniversaire le week-end prochain et j’aimerais beaucoup que vous soyez là. La soirée aura lieu chez moi à partir de 19h. On va commencer par un dîner, puis on écoutera de la musique et on dansera.

Si vous pouvez, j’aurais besoin d’un petit coup de main : quelqu’un pourrait apporter un dessert, et un autre des boissons. Dites-moi aussi si vous pouvez venir !

J’ai vraiment hâte de célébrer ça avec vous !
À très bientôt !',
            ],
            [
                'task' => 2,
                'prompt' => '« Vous avez participé à un concours de cuisine. Sur votre site Internet, vous écrivez un court article pour raconter cette journée. Vous expliquez pourquoi vous avez aimé ou pourquoi vous n’avez pas aimé cette expérience.',
                'correction' => 'Une journée savoureuse au concours de cuisine !

Chers lecteurs,

La semaine dernière, j’ai participé à un concours de cuisine organisé dans ma ville, et ce fut une expérience très enrichissante. Dès le matin, l’ambiance était dynamique : chaque participant préparait son plat avec beaucoup de concentration et de passion. J’ai décidé de réaliser une recette traditionnelle revisitée, ce qui m’a permis d’exprimer ma créativité.

J’ai particulièrement aimé échanger avec les autres participants et découvrir leurs idées originales. Les juges étaient exigeants, mais leurs conseils étaient très utiles. Même si j’étais un peu stressé au début, j’ai pris confiance au fil de la journée.

Je n’ai pas gagné, mais je suis très satisfait de cette expérience. Elle m’a permis de progresser et de partager ma passion pour la cuisine. Je recommencerai sans hésiter !

À bientôt !',
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail des jeunes pendant les vacances',
                'correction' => 'Le travail des jeunes pendant les vacances

Le travail des jeunes pendant les vacances fait débat. D’un côté, il présente des inconvénients : il peut être fatigant, mal payé et réduire le temps de repos et de loisirs. D’un autre côté, il offre des avantages importants, comme une première expérience professionnelle, un salaire et une certaine indépendance financière.

À mon avis, travailler pendant les vacances est une expérience positive, à condition de garder un équilibre. En effet, cela permet aux jeunes de découvrir le monde du travail, de développer des compétences et d’apprendre à être responsables. Gagner de l’argent est également motivant et peut aider à financer des projets personnels ou des études. Cependant, il ne faut pas négliger le besoin de repos après une année d’études. Les vacances doivent aussi être un moment pour se détendre et passer du temps avec ses proches. Il est donc préférable de travailler de manière raisonnable, par exemple pendant une partie des vacances, afin de profiter à la fois des avantages du travail et du repos.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => '« Les vacances sont le moment où les jeunes se détendent après plusieurs mois à l’université. Ils voyagent et profitent des beaux jours. Mais certains étudiants ne prennent pas de vacances et décident de travailler : ils gardent des enfants, servent dans des restaurants ou ramassent des fruits, par exemple. Même si le travail saisonnier présente beaucoup d’avantages, il prive en quelque sorte les jeunes de leur temps libre : ils n’ont pas le temps de se reposer, d’avoir des loisirs et de passer du temps avec leurs proches. De plus, ces emplois d’été sont parfois mal payés, fatigants ou ennuyeux. »',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => '« Pourquoi un grand nombre d’étudiants travaillent-ils pendant les vacances ? Ils font ce choix parce que cette expérience est un premier pas dans le monde professionnel : ils apprennent à se responsabiliser tout en découvrant un métier. Recevoir un salaire est aussi une source de motivation. Travailler pendant les vacances permet à certains d’être indépendants financièrement des parents : ils vont utiliser cet argent pour voyager ou se faire plaisir. Pour d’autres, il s’agit d’une nécessité : ils travaillent l’été pour payer leurs études ou leur loyer d’étudiant. »',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 9,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
                'correction' => 'Salut les amis !

Je vous propose qu’on parte en week-end le mois prochain, on pourrait aller à la montagne ou au bord de la mer pour se détendre. On pourrait y aller en voiture pour plus de liberté.

Sur place, on pourrait faire des randonnées, visiter la région et profiter de bons restaurants. J’ai aussi vu qu’il y a des activités comme du vélo ou des balades en bateau.

Dites-moi si ça vous intéresse et quelles dates vous conviennent. On peut organiser ça ensemble !

À bientôt',
            ],
            [
                'task' => 2,
                'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : “voyage.internaute.fr”. Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
                'correction' => 'Tout quitter pour voyager un an : une expérience unique ?

Chers lecteurs,

À mon avis, partir en voyage pendant un an est une très bonne idée, si l’on est bien préparé. Personnellement, j’ai eu l’occasion de partir plusieurs mois à l’étranger, et cela a complètement changé ma vision du monde. J’ai découvert de nouvelles cultures, rencontré des personnes différentes et appris à être plus autonome.

Bien sûr, quitter son travail ou ses habitudes peut faire peur. Il faut aussi penser à l’aspect financier et à l’organisation. Cependant, cette expérience permet de sortir de sa zone de confort et de vivre des moments inoubliables.

Je pense que voyager longtemps est une excellente opportunité pour se connaître soi-même et évoluer. Si vous en avez la possibilité, je vous conseille de tenter l’aventure !

À bientôt !',
            ],
            [
                'task' => 3,
                'prompt' => 'Le travail : favorable ou défavorable ?',
                'correction' => 'Le travail : favorable ou défavorable ?

Le travail occupe une place centrale dans notre société. D’un côté, il est souvent perçu comme une source de fatigue et de manque de temps pour la vie personnelle. Certains pensent qu’il faudrait travailler moins pour mieux vivre. D’un autre côté, le travail est essentiel, car il permet de construire son identité, de créer des relations sociales et de se sentir utile.

À mon avis, le travail est à la fois favorable et défavorable selon la manière dont il est organisé. Il est important, car il permet de gagner sa vie, de développer des compétences et de s’intégrer dans la société. De plus, il favorise les échanges humains et donne un sentiment d’utilité. Cependant, lorsque le travail devient trop envahissant, il peut nuire à la santé et à la vie personnelle. Il est donc essentiel de trouver un équilibre entre vie professionnelle et vie privée. Travailler moins, mais mieux, pourrait améliorer le bien-être des individus. Ainsi, le travail doit rester un moyen d’épanouissement et non une contrainte excessive.',
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
        'combo' => 10,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Vous répondez à ce message sur le forum. Vous parlez de votre pays et de votre culture. Vous essayez de donner envie aux internautes de découvrir votre pays (60 mots minimum/120 mots maximum). »',
                'correction' => 'Découvrez mon pays : une expérience unique !

Chers internautes,

Je vous invite à découvrir mon pays, riche en culture et en traditions. Vous y trouverez des paysages magnifiques, entre montagnes, plages et villes historiques. La cuisine locale est variée et savoureuse, avec des plats typiques à ne pas manquer.

Les habitants sont chaleureux et accueillants, toujours prêts à partager leur culture. De nombreux festivals et événements permettent de vivre des moments inoubliables.

Si vous aimez voyager, découvrir et vous émerveiller, mon pays est une destination idéale. N’hésitez pas à venir le visiter !

À bientôt !',
            ],
            [
                'task' => 2,
                'prompt' => '« Vous avez commencé un nouveau travail. Vous écrivez un courriel à vos amis pour leur raconter comment s’est passée votre première semaine et ce que vous pensez de ce nouveau poste (120 mots minimum/150 mots maximum). »',
                'correction' => 'Salut les amis,

Je voulais vous raconter ma première semaine dans mon nouveau travail. Au début, j’étais un peu stressé, car tout était nouveau : les collègues, les tâches et l’organisation. Heureusement, l’équipe m’a très bien accueilli, ce qui m’a permis de m’adapter rapidement.

J’ai déjà appris beaucoup de choses et mes responsabilités sont intéressantes. Le rythme de travail est assez soutenu, mais cela me motive à donner le meilleur de moi-même. J’apprécie particulièrement l’ambiance positive et le fait de travailler en équipe.

Même si je suis encore en phase d’apprentissage, je pense que ce poste me correspond bien. Je suis content de ce nouveau départ et j’ai hâte de progresser.

À très bientôt !',
            ],
            [
                'task' => 3,
                'prompt' => 'Égalité homme-femme',
                'correction' => 'L’égalité homme/femme en milieu de travail
La question de l’égalité entre les hommes et les femmes au travail reste d’actualité. Certains affirment que cette égalité est respectée, notamment au Québec, où les femmes peuvent accéder à tous les métiers, y compris les postes de direction. D’autres soulignent qu’il existe encore des inégalités, avec des métiers majoritairement masculins ou féminins.
À mon avis, l’égalité entre les hommes et les femmes doit être une priorité dans le monde du travail. Il est important que chacun puisse choisir son métier librement, sans être limité par des stéréotypes. Les femmes sont tout à fait capables d’occuper des postes à responsabilité ou des métiers considérés comme masculins. De plus, les hommes peuvent aussi exercer des professions traditionnellement féminines. Il ne faut pas associer les rôles familiaux uniquement aux femmes. Aujourd’hui, les responsabilités doivent être partagées. Favoriser l’égalité permet non seulement plus de justice, mais aussi une meilleure diversité dans les entreprises.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => '« Une des valeurs fondamentales du Québec est l’égalité entre les hommes et les femmes. Ils ont les mêmes droits et les mêmes obligations. Les femmes peuvent exercer le métier ou la profession de leur choix. Elles sont présentes aux postes de décision ; elles sont par exemple députées, mairesses, conseillères, administratrices ou dirigeantes de grandes entreprises. Beaucoup d’entre elles ont des métiers et des professions traditionnellement réservées aux hommes. »',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => '« C’est la première journée de cours pour Louis. Ce jeune homme de 27 ans est le seul étudiant masculin de sa classe. Diplômé, il sera aussi le tout premier homme sage- femme au Québec. C’est en travaillant comme masseur auprès de femmes enceintes qu’il a su que le métier de sage-femme était fait pour lui. Aider les femmes à mettre leur bébé au monde est une profession pratiquée depuis toujours par les femmes. L’arrivée d’un homme dans le programme de formation est la bienvenue, mais sa présence peut encore surprendre les patientes. »',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 11,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre amie Carole et sa famille prévoient de visiter votre ville prochainement. Dans un message, proposez-leur un hôtel où séjourner (tarifs, activités, transports, etc.)',
                'correction' => 'Salut Carole !

Je suis ravi que vous veniez visiter ma ville. Je vous conseille un hôtel très agréable près du centre-ville. Les chambres sont confortables et les prix sont raisonnables, environ 80 euros par nuit pour une famille. L’hôtel propose aussi un petit-déjeuner inclus.

Il est bien situé : vous pouvez visiter les principaux monuments à pied, et il y a des transports en commun à proximité. Il y a aussi des restaurants et des parcs tout près, ce qui est pratique avec les enfants.

Je pense que vous allez vraiment apprécier votre séjour !

À bientôt',
            ],
            [
                'task' => 2,
                'prompt' => 'Écrivez un article de blog pour partager votre expérience d\'apprentissage d\'une nouvelle langue (français ou autre). Décrivez les défis rencontrés, les progrès réalisés et les méthodes d\'apprentissage que vous avez trouvées efficaces. (120 mots minimum / 150 mots maximum)',
                'correction' => 'Apprendre une nouvelle langue : un défi passionnant !

Chers lecteurs,

Depuis quelques mois, j’ai commencé à apprendre une nouvelle langue, et cette expérience est à la fois enrichissante et exigeante. Au début, j’ai rencontré plusieurs difficultés, notamment la prononciation et la mémorisation du vocabulaire. Il n’était pas toujours facile de comprendre les règles grammaticales.

Cependant, avec de la pratique, j’ai fait des progrès. J’arrive maintenant à comprendre des conversations simples et à m’exprimer avec plus de confiance. Pour m’améliorer, j’utilise différentes méthodes : des applications mobiles, des vidéos en ligne et des échanges avec des locuteurs natifs.

Je pense que la clé du succès est la régularité et la motivation. Même si c’est parfois difficile, apprendre une langue ouvre de nombreuses portes et permet de découvrir de nouvelles cultures.

À bientôt !',
            ],
            [
                'task' => 3,
                'prompt' => 'Les jeux vidéo',
                'correction' => 'Les jeux vidéo : avantages et inconvénients

Les jeux vidéo suscitent des avis partagés. D’un côté, certaines études montrent qu’ils peuvent développer des capacités cognitives, comme la rapidité de réaction, notamment chez les adultes. De l’autre, ils peuvent favoriser la violence, l’addiction et avoir des effets négatifs, surtout chez les jeunes, en influençant leur comportement et leurs résultats scolaires.

À mon avis, les jeux vidéo ne sont pas dangereux en eux-mêmes, mais leur usage doit être encadré. Ils peuvent être bénéfiques s’ils sont utilisés avec modération, car ils stimulent certaines capacités comme la concentration, la coordination et la prise de décision. Cependant, un usage excessif peut entraîner une dépendance, du stress et un isolement social. Chez les enfants, les risques sont encore plus importants, car ils sont plus influençables. C’est pourquoi le rôle des parents est essentiel pour fixer des limites de temps et choisir des jeux adaptés à l’âge. En conclusion, les jeux vidéo peuvent être utiles, mais seulement dans un cadre contrôlé et équilibré.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Les jeux vidéo ont la réputation d\'être néfastes; cependant, plusieurs études montrent qu\'en y jouant, un adulte peut développer des capacités cognitives; ils permettent par exemple d\'améliorer l\'aptitude à réagir. Cela peut être considéré comme un point positif surtout si on sait que 88,4% des joueurs sont des adultes. Cela dit, il faut rester vigilant car de nombreux jeux favorisent plutôt la violence et l\'addiction et ne développent par conséquent aucune zone du cerveau.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Vu les chiffres d\'affaires de plus en plus importants qu\'ils réalisent, les jeux vidéo font régulièrement l\'objet de plusieurs études. Parmi ces études, une a duré trois ans et s\'est intéressée à des enfants âgés de 8 à 17 ans. Son résultat n\'est sans doute pas une grande surprise; les enfants les plus addicts aux jeux développent des comportements violents, nerveux et sont souvent stressés. Dans certains cas, cela peut même se répercuter sur les résultats scolaires. Il est donc primordial que les parents interviennent afin d\'imposer des règles et limiter les temps de jeu.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 12,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => '« Salut ! Je sais que tu fais du sport depuis le mois dernier. Ça m’intéresse beaucoup et j’aimerais bien venir avec toi. Tu peux m’en dire plus ? A bientôt ! Camille » Vous répondez à votre ami Camille. Dans votre message, vous décrivez votre activité sportive et vous donnez des informations utiles (lieu, durée, prix, etc.).',
                'correction' => 'Salut Camille !

Je fais du fitness dans une salle près de chez moi, trois fois par semaine. Les séances durent environ une heure et il y a différents exercices : cardio, musculation et étirements. L’ambiance est vraiment sympa et les coachs sont là pour nous aider.

L’abonnement coûte environ 30 euros par mois, et tu peux faire un cours d’essai gratuit si tu veux. Les séances ont lieu en fin d’après-midi ou le soir, donc c’est assez flexible.

Si ça te dit, on peut y aller ensemble la semaine prochaine !

À bientôt',
            ],
            [
                'task' => 2,
                'prompt' => '« Tout quitter pour changer de vie ? Il y a deux ans, nous avons décidé de changer de vie. Paul a quitté son poste de banquier à Paris et nous avons ouvert une boulangerie à Calgary ! Que pensez-vous de cette décision ? Avez-vous déjà vécu un grand changement, professionnel ou personnel ? Paul et Naïma » Vous avez lu ce message sur un forum internet. Vous répondez à Paul et Naïma. Dans votre message, vous donnez votre opinion sur le choix de Paul et de Naïma et vous racontez comment vous feriez si vous étiez à leur place.',
                'correction' => 'Changer de vie : un choix courageux

Chers Paul et Naïma,

Je trouve votre décision très courageuse et inspirante. Quitter un emploi stable pour ouvrir une boulangerie dans un autre pays demande beaucoup de détermination. À mon avis, c’est une excellente idée si vous êtes passionnés par votre nouveau projet. Le fait de changer de vie permet souvent de se sentir plus épanoui et de donner plus de sens à son quotidien.

Personnellement, je n’ai jamais fait un changement aussi radical, mais j’y ai déjà pensé. Si j’étais à votre place, je prendrais le temps de bien préparer mon projet, notamment sur le plan financier et professionnel. J’essaierais aussi de me former avant de me lancer.

En tout cas, votre expérience donne envie de suivre ses rêves. Je vous souhaite beaucoup de succès dans cette nouvelle aventure !

À bientôt !',
            ],
            [
                'task' => 3,
                'prompt' => 'Le Bien-être au Travail.',
                'correction' => 'Le bien-être au travail
Le bien-être au travail est devenu un enjeu important pour les entreprises. D’un côté, certaines initiatives, comme les bureaux réglables, améliorent le confort des employés, leur productivité et la communication entre collègues. D’un autre côté, certains salariés estiment que ces mesures servent surtout à les faire travailler davantage, sans réellement améliorer leurs conditions de travail.

À mon avis, le bien-être au travail est essentiel, mais il doit être sincère et durable. Améliorer les conditions matérielles, comme le mobilier ou l’ambiance, peut effectivement rendre les employés plus motivés et plus efficaces. Cependant, cela ne doit pas masquer d’autres problèmes, comme la surcharge de travail ou les heures supplémentaires non désirées. Les entreprises doivent aussi respecter l’équilibre entre vie professionnelle et vie personnelle. Un employé reposé et respecté sera naturellement plus productif. De plus, il est important d’écouter les besoins réels des salariés et de les impliquer dans les décisions. Le bien-être ne doit pas être une stratégie, mais une réelle priorité.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Nous avons installé, dans votre entreprise, des bureaux réglables qui montent et descendent, ceci pour permettre aux employés de choisir la position qui leur convient le mieux pour travailler. Les employés sont satisfaits et disent qu’ils sont plus productifs et plus efficaces lors des négociations au téléphone, par exemple. Même le climat social est plus convivial et la communication verbale entre collègues s’est renforcée. « Lorie, responsable ».',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'De nos jours, les entreprises veulent toujours plus de productivité et d’efficacité, et les employés passent plus de temps sur les lieux de travail. Les entreprises cherchent toujours des moyens ou astuces pour convaincre leurs employés qu’elles pensent à leur santé. Moi, ce qui m’importe, c’est de faire mon job sur mon temps de travail réel sans être obligé de faire des rallonges en heures supplémentaires. « Florian, ingénieur ».',
                    ],
                ],
            ],
        ],
    ],
];
