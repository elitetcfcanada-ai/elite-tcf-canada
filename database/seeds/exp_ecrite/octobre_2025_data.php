<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
    [
        'combo' => 1,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous venez de vous installer dans une nouvelle ville. Vous écrivez un message à un(e) ami(e) pour décrire votre nouvel environnement (quartier, voisins, magasins, etc.)',
                'correction' => "Salut Amine,\n\nJe voulais te donner de mes nouvelles : je viens de m'installer dans une nouvelle ville ! J'habite maintenant à Casablanca, dans un quartier très agréable et calme. Il y a beaucoup d'arbres, un joli parc à deux pas de chez moi et plusieurs cafés sympathiques où je vais souvent.\n\nMes voisins sont très gentils, ils m'ont même aidé à porter mes cartons le jour de mon arrivée. À cinq minutes, il y a un supermarché, une boulangerie et un petit marché local le week-end. Je me sens déjà bien ici, même si tout est encore nouveau pour moi.\n\nJ'espère que tu viendras bientôt me rendre visite !\n\nÀ très vite,\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une action pour la « Journée mondiale du nettoyage de notre planète ». Vous avez ramassé des déchets dans un lieu public (plage, forêt, rue, etc.) avec d\'autres personnes. Vous racontez cette expérience à vos amis. Vous expliquez pourquoi il est important de participer à ce type d\'action.',
                'correction' => "Salut les amis,\n\nHier, j'ai participé à une action pour la Journée mondiale du nettoyage de notre planète. Avec un groupe de bénévoles, nous avons passé la matinée à ramasser des déchets sur la plage près de chez moi. J'ai été surpris de voir la quantité de plastique et de papiers laissés par les gens. Malgré la fatigue, c'était une expérience très enrichissante, car nous avons travaillé ensemble dans une ambiance positive et solidaire.\n\nJe pense qu'il est très important de participer à ce genre d'action, car cela permet de protéger l'environnement et de sensibiliser les citoyens à l'importance de garder notre planète propre. Si chacun faisait un petit geste, notre monde serait beaucoup plus agréable à vivre.\n\nÀ bientôt,\nelite tcf canada",
            ],
            [
                'task' => 3,
                'prompt' => 'Les publicités : Pour ou contre ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Certaines personnes trouvent la publicité ennuyeuse, mais, à mon avis, elle est indispensable pour le commerce et les entreprises. Grâce à la publicité, on fait connaître un produit ou un service. Et puis, parfois, elles sont drôles ! J\'aime bien les écouter quand je suis en voiture. Cela me permet aussi d\'être informé des nouveautés et des promotions. Personnellement, j\'adore comparer les articles : je peux ainsi faire beaucoup d\'économies sur mes achats. Enfin, beaucoup de personnes jouent à des jeux gratuits sur leur téléphone : sans publicité, ces jeux seraient tous payants. Quentin, 28 ans'],
                    ['title' => 'Document 2', 'content' => 'La publicité est présente partout dans notre vie de tous les jours : journaux, radios, télévision, téléphone, Internet… Par exemple, sur certaines chaînes de télévision, les émissions sont coupées par la publicité et c\'est agaçant. Puis, recevoir des dizaines de kilos de papier de publicité par an dans la boîte aux lettres, ce n\'est pas très respectueux de l\'environnement ! Je pense qu\'il est nécessaire de faire voter une loi pour réduire les publicités par courrier et à la télévision. Si la publicité était plus discrète, elle serait plus appréciée. Estelle, 35 ans'],
                ],
                'correction' => "La publicité : utile ou envahissante ?\n\nLe premier document défend la publicité en expliquant qu'elle est nécessaire pour faire connaître les produits, informer des promotions et financer certains services gratuits. À l'inverse, le second document critique son omniprésence dans la vie quotidienne, la pollution qu'elle engendre et son manque de respect pour l'environnement, notamment à la télévision et dans les boîtes aux lettres.\n\nÀ mon avis, la publicité est utile lorsqu'elle est bien dosée. Elle permet aux entreprises de se développer et aux consommateurs de découvrir de nouveaux produits ou de profiter de réductions. Cependant, je trouve qu'elle est souvent trop envahissante : on la voit partout, même quand on ne la cherche pas. Cela devient fatigant et parfois manipulateur. Je pense qu'il faudrait mieux encadrer la publicité, surtout celle qui cible les enfants ou qui exagère les promesses des produits. Une publicité plus sobre, plus respectueuse de l'environnement et plus informative serait certainement mieux acceptée par le public. Tout est une question d'équilibre.",
            ],
        ],
    ],
    [
        'combo' => 2,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à vos amis pour les inviter à un anniversaire surprise de votre meilleur(e) ami(e). (Lieu, date, horaire, etc.).',
                'correction' => "Salut les amis,\n\nJ'espère que vous allez bien ! Je vous écris pour vous inviter à l'anniversaire surprise de ma meilleure amie Kenza. La fête aura lieu le samedi 12 octobre à 19h30, chez moi, au 25 rue des Lilas à Montréal. Merci d'arriver vers 19h00, afin qu'on prépare tout avant son arrivée.\n\nCe sera une soirée simple et conviviale avec de la musique, des jeux et un petit buffet. Chacun peut apporter quelque chose à boire ou à manger, selon ses envies.\nSurtout, ne dites rien à Kenza : je compte sur vous pour garder le secret !\n\nMerci de me confirmer votre présence avant le 10 octobre.\n\nÀ très bientôt !\nImane",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une brocante (achat / vente de produits d\'occasion) dans votre ville. Sur votre blog personnel, racontez pourquoi vous avez aimé cette activité.',
                'correction' => "Le week-end dernier, j'ai participé à une grande brocante organisée dans le centre-ville, et j'ai vraiment adoré cette expérience ! Dès le matin, les rues étaient pleines de monde, et l'ambiance était chaleureuse et joyeuse. J'avais préparé une table avec quelques objets que je n'utilisais plus : des livres, des vêtements et même un vieux vélo. À ma grande surprise, j'ai tout vendu rapidement et j'ai fait de belles rencontres avec des habitants du quartier.\n\nCe que j'ai le plus apprécié, c'est l'esprit de partage et de convivialité. Tout le monde discutait, échangeait, riait… C'était un vrai moment de vie locale. En plus, cette activité permet de recycler les objets et de consommer de manière plus responsable, ce qui est excellent pour l'environnement. Participer à une brocante, c'est à la fois utile, écologique et très amusant. Je le referai sans hésiter !",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Vols à Bas Prix',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Je fais souvent mes voyages avec des compagnies aériennes à bas prix. Les compagnies low-cost mettent à disposition des prix inférieurs à ceux proposés par les compagnies aériennes régulières. Cela me coûte des fois moins cher que de voyager en voiture ou en train. Avec ces tarifs-là, vous vous doutez qu\'il y a un hic : en effet, vous n\'aurez droit à aucun service à bord (ni aliments, ni boissons). Je dirais donc que le low-cost n\'est surtout pas fait pour les vols long-courriers.'],
                    ['title' => 'Document 2', 'content' => 'Récemment, j\'ai pris la décision de ne plus voyager avec les compagnies aériennes à bas prix. En effet, j\'ai longuement réfléchi afin de prendre ma décision, mais ce choix était évident : des sièges inconfortables, des conditions de travail pénibles et surtout des avions vétustes qui remettent en cause la sécurité ! Dès lors, pour certains voyages, je vais opter pour la voiture ou même le train, ce dernier permettant même de découvrir de jolis paysages. Quant aux longs trajets, mieux vaut prendre un vol en compagnie régulière.'],
                ],
                'correction' => "Le premier document met en avant les avantages des compagnies aériennes à bas prix, notamment leurs tarifs très attractifs qui permettent de voyager pour moins cher, même plus économiquement qu'en train. En revanche, le second document souligne les aspects négatifs : inconfort, manque de sécurité et absence de services, ce qui pousse certains voyageurs à les éviter.\n\nÀ mon avis, les vols à bas prix représentent une excellente option pour les courts trajets ou pour les personnes qui voyagent souvent avec un budget limité. Ces compagnies permettent à beaucoup de gens de découvrir de nouveaux pays sans se ruiner. Cependant, je pense qu'il faut rester vigilant concernant la sécurité et le confort. Pour les longs voyages, il est préférable de choisir une compagnie régulière offrant de meilleurs services et des sièges plus confortables. Le low-cost doit donc rester une solution pratique, mais pas systématique. En fin de compte, tout dépend des priorités du voyageur : le prix ou le confort.",
            ],
        ],
    ],
    [
        'combo' => 3,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut, Tu as commencé ton nouveau travail ! C\'est comment ? Tu es content(e) ? Ali — Vous répondez à votre ami Ali. Dans votre message, vous décrivez votre nouveau travail (lieu, collègues, etc.) et vous donnez vos impressions.',
                'correction' => "Salut Ali,\n\nMerci pour ton message ! Oui, j'ai enfin commencé mon nouveau travail la semaine dernière. Je travaille dans une entreprise de communication située en plein centre-ville. Les bureaux sont modernes et lumineux, ce qui rend l'ambiance très agréable.\n\nMes collègues sont vraiment sympathiques et toujours prêts à m'aider quand j'ai des questions. Mon chef est aussi très compréhensif, ce qui me met à l'aise. Les premières journées ont été un peu fatigantes, car il y a beaucoup de choses à apprendre, mais je me sens déjà bien intégré.\n\nEn général, je suis très content de ce nouveau départ et j'espère pouvoir évoluer rapidement dans cette entreprise.\n\nÀ bientôt,\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'INFOS FAMILLES Vivre avec une personne âgée : comment faire ? Notre site cherche des témoignages. Vous avez vécu avec une personne âgée. Vous racontez votre expérience.',
                'correction' => "Il y a deux ans, j'ai vécu pendant plusieurs mois avec ma grand-mère. Au début, j'avais un peu peur de ne pas réussir à m'adapter à son rythme, mais finalement, cette expérience a été très enrichissante. J'ai découvert une femme pleine d'énergie, avec beaucoup d'histoires passionnantes à raconter. Nous partagions les repas, regardions la télévision ensemble et faisions souvent de petites promenades dans le quartier.\n\nBien sûr, ce n'était pas toujours facile : il fallait être patient, attentif et parfois répéter les choses plusieurs fois. Mais j'ai beaucoup appris sur la valeur du respect, de la tolérance et de la solidarité entre générations. Aujourd'hui, je garde un très bon souvenir de cette période pleine d'affection et d'échanges humains.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les cours de langue en ligne.',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'Apprendre une langue en ligne grâce à Internet, c\'est possible et cela donne de bons résultats ! Contrairement aux cours classiques, on peut apprendre quand on veut : les cours sont disponibles tout le temps. Cela permet de mieux organiser sa journée. On n\'a pas non plus besoin de faire des kilomètres pour aller dans une école de langues. On peut apprendre de son salon, de son bureau ou même d\'un café près de chez soi ! Cela permet aussi de faire des économies.'],
                    ['title' => 'Document 2', 'content' => 'Cela semble facile d\'apprendre une langue en ligne mais ce n\'est pas possible pour tout le monde. En effet, il faut avoir une bonne connexion à Internet et un outil numérique adapté (ordinateur, smartphone, tablette) pour apprendre en ligne. De plus, il faut être très autonome pour être capable d\'apprendre seul : il est difficile de se mettre au travail chez soi et de rester motivé quand on n\'a pas l\'aide d\'un professeur et de collègues. Dans ces conditions, on peut se décourager et abandonner très vite.'],
                ],
                'correction' => "Le premier document souligne les nombreux avantages des cours de langue en ligne : flexibilité, gain de temps, apprentissage à distance et économies. En revanche, le second document montre leurs limites : tout le monde n'a pas accès à Internet ni la motivation nécessaire pour étudier seul, ce qui peut rendre l'apprentissage difficile.\n\nSelon moi, les cours de langue en ligne représentent une excellente solution pour apprendre à son rythme, surtout pour les personnes qui travaillent ou qui n'ont pas le temps de se déplacer. Grâce à Internet, on peut suivre des leçons à toute heure et pratiquer avec des locuteurs natifs. Cependant, il est vrai que ce type d'apprentissage demande beaucoup de discipline et une bonne organisation personnelle. Pour être efficace, il faut combiner l'autonomie avec des échanges réguliers, par exemple en rejoignant des groupes d'apprentissage ou des classes virtuelles interactives. En conclusion, l'apprentissage en ligne est très pratique, à condition d'être motivé et bien encadré.",
            ],
        ],
    ],
    [
        'combo' => 4,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez reçu ce message de votre ami Thomas : « Salut ! Je ne connais pas bien la culture et les traditions de ton pays. Peux-tu me parler d\'une grande fête célébrée chez toi ? À bientôt ! » — Répondez-lui en décrivant une fête importante de votre pays.',
                'correction' => "Salut Thomas,\n\nJe suis très content que tu t'intéresses à la culture de mon pays ! Ici, l'une des fêtes les plus importantes est l'Aïd al-Fitr, qu'on appelle aussi la fête de la rupture du jeûne. Elle marque la fin du mois sacré du Ramadan.\n\nCe jour-là, les familles se lèvent tôt pour aller à la prière, puis tout le monde partage un grand repas avec des plats traditionnels et beaucoup de pâtisseries. C'est aussi l'occasion de rendre visite aux proches, d'échanger des cadeaux et d'aider les personnes dans le besoin.\n\nL'ambiance est vraiment joyeuse : les rues sont animées, les enfants portent de nouveaux vêtements, et tout le monde se souhaite une bonne fête. C'est un moment de partage, de solidarité et de bonheur pour tous.\n\nÀ bientôt,\nelite tcf canada",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à un festival de cinéma. Vous avez vu plusieurs films. Sur votre site personnel, vous racontez le film que vous avez préféré. Vous expliquez votre choix.',
                'correction' => "La semaine dernière, j'ai assisté à un festival de cinéma organisé dans ma ville. J'ai eu la chance de voir plusieurs films venant de différents pays : des comédies, des drames et même des documentaires. Parmi tous ces films, celui que j'ai préféré s'intitule « Le chemin de la liberté ».\n\nCe film raconte l'histoire d'un jeune homme qui décide de quitter sa ville natale pour réaliser son rêve de devenir musicien. Ce qui m'a particulièrement touché, c'est la sincérité du personnage principal et le message d'espoir que le film transmet. La réalisation est magnifique, la musique est émouvante et les acteurs jouent de manière très naturelle.\n\nJ'ai choisi ce film parce qu'il m'a fait réfléchir sur l'importance de poursuivre ses rêves malgré les difficultés. Il montre que la persévérance et la passion peuvent vraiment changer une vie. En sortant de la salle, j'étais inspiré et plein d'énergie positive.\n\nJe recommande vivement ce film à tous ceux qui aiment les histoires humaines et inspirantes.",
            ],
            [
                'task' => 3,
                'prompt' => 'Que pensez-vous des maisons de retraite ?',
                'documents' => [
                    ['title' => 'Document 1', 'content' => 'J\'habite dans un pays où il y a très peu de maisons de retraite. Ici, quand une personne âgée ne peut plus être autonome, elle habite avec les générations plus jeunes, et dans l\'ensemble, cela ne pose pas de problèmes majeurs. Il nous paraît étrange de confier nos parents ou grands-parents à des personnes qu\'ils ne connaissent pas.'],
                    ['title' => 'Document 2', 'content' => 'Beaucoup de gens critiquent les maisons de retraite, pourtant c\'est une bonne solution d\'accueil pour les personnes âgées ! D\'abord, pour lutter contre l\'isolement puisqu\'elles sont entourées de personnes de leur âge à la maison de retraite. Ensuite, pour les soins et les services médicaux offerts dans ce type d\'établissement. Enfin, parce que c\'est pratique et rassurant pour leurs enfants et petits-enfants. Malheureusement, ce type de logement reste encore trop cher pour certains.'],
                ],
                'correction' => "D'un côté, certains estiment que les maisons de retraite sont inutiles, car dans leur pays les personnes âgées vivent avec leur famille, ce qui renforce les liens entre générations. D'un autre côté, d'autres pensent qu'elles constituent une bonne solution pour éviter l'isolement des aînés et garantir de meilleurs soins.\n\nÀ mon avis, les maisons de retraite peuvent être très utiles, surtout quand une personne âgée a besoin d'une assistance médicale quotidienne. C'est une option rassurante pour les familles qui ne peuvent pas offrir une présence constante. Cependant, je pense qu'il est essentiel de ne pas abandonner nos aînés dans ces établissements. Les visites régulières et le maintien du contact familial sont indispensables pour leur bien-être moral. L'idéal serait de trouver un équilibre entre la vie en établissement et le lien affectif avec la famille. Enfin, il serait souhaitable que ces structures deviennent plus accessibles financièrement pour permettre à tous d'en bénéficier.",
            ],
        ],
    ],
];
