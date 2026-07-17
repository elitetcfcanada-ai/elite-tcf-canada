<?php
declare(strict_types=1);

/** Combinaisons 43 à 48 — Mai 2026 */
return [
    [
        'combo' => 43,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Salut, Tu as commencé ton nouveau travail ! C’est comment ? Tu es content(e) ? Ali Vous répondez à votre ami Ali. Dans votre message, vous décrivez votre nouveau travail (lieu, collègues, etc.) et vous donnez vos impressions.',
                'correction' => 'Objet : Mon nouveau travail

Salut Ali,

Merci pour ton message ! Oui, j’ai commencé mon nouveau travail il y a une semaine et, pour le moment, je suis content. Je travaille dans une petite entreprise de communication au centre-ville de Montréal, près de la gare. Les bureaux sont modernes, lumineux et très calmes. Mes collègues sont sympathiques et patients avec moi, surtout parce que je suis encore en période d’adaptation. Mon responsable est exigeant, mais il explique bien les tâches. Je commence à 9 h et je termine à 17 h, donc j’ai un bon équilibre. Le travail est parfois stressant, mais il est intéressant et j’apprends beaucoup chaque jour.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'INFOS FAMILLES Vivre avec une personne âgée : comment faire ? Notre site cherche des témoignages. Vous avez vécu avec une personne âgée. Vous racontez votre expérience.',
                'correction' => '[Vivre avec ma grand-mère : une expérience inoubliable]

Bonjour à tous,

L’année dernière, j’ai vécu pendant six mois avec ma grand-mère dans son petit appartement à Rabat, après une opération du genou. Au début, j’étais un peu inquiet, car je pensais que la cohabitation allait être difficile. Pourtant, tout s’est très bien passé. Ma grand-mère était calme, patiente et très organisée. Chaque matin, je l’aidais à préparer le petit-déjeuner, puis nous allions doucement au parc près de chez elle. L’après-midi, elle me racontait son enfance, ses souvenirs de famille et la vie d’autrefois. J’ai beaucoup aimé ces moments, car l’ambiance était chaleureuse et sincère. Bien sûr, il y a eu quelques difficultés : elle était parfois fatiguée et je devais faire plus de tâches à la maison. Mais cette expérience m’a appris la patience, l’écoute et le respect. J’en garde un souvenir très fort et très émouvant.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les cours de langues en ligne',
                'correction' => 'Les cours de langues en ligne : avantages et limites

Les cours de langues en ligne suscitent des avis différents. Le premier document souligne leur flexibilité, les économies réalisées et la possibilité d’étudier partout, à tout moment. Cependant, le second rappelle que cette solution exige un bon équipement, une connexion fiable et une grande autonomie pour éviter la démotivation. (48 mots)

À mon avis, les cours de langues en ligne sont utiles, mais ils ne conviennent pas à tous les apprenants. D’abord, ils offrent une grande liberté, ce qui est pratique pour les personnes qui travaillent ou qui ont des horaires irréguliers. Ensuite, ils permettent de revoir les leçons plusieurs fois, donc chacun avance à son rythme. De plus, ils peuvent coûter moins cher que les cours en présentiel. Par exemple, un étudiant peut suivre une leçon le soir après son travail sans se déplacer. Enfin, je pense que ces cours sont plus efficaces si l’apprenant est sérieux et bien organisé. Sans motivation ni accompagnement régulier, il risque de progresser lentement ou d’abandonner rapidement. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Apprendre une langue en ligne grâce à Internet, c\'est possible et cela donne de bons résultats ! Contrairement aux cours classiques, on peut apprendre quand on veut : les cours sont disponibles tout le temps. Cela permet de mieux organiser sa journée. On n\'a pas non plus besoin de faire des kilomètres pour aller dans une école de langues. On peut apprendre de son salon, de son bureau ou même d\'un café près de chez soi ! Cela permet aussi de faire des économies.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Cela semble facile d\'apprendre une langue en ligne, mais ce n\'est pas possible pour tout le monde. En effet, il faut avoir une bonne connexion à Internet et un outil numérique adapté (ordinateur, smartphone, tablette) pour apprendre en ligne. De plus, il faut être très autonome pour être capable d\'apprendre seul : il est difficile de se mettre au travail chez soi et de rester motivé quand on n\'a pas l\'aide d\'un professeur et de collègues. Dans ces conditions, on peut se décourager et abandonner très vite.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 44,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez passé un week-end à la campagne. Écrivez un message à votre ami(e) pour lui décrire ce qui s’est passé.',
                'correction' => 'Objet : Mon week-end à la campagne

Salut Karim,

Je t’écris pour te raconter mon week-end à la campagne. Samedi matin, je suis parti avec mes cousins dans un petit village près de Tours. Nous sommes arrivés vers 10 h et le temps était magnifique. D’abord, nous avons fait une longue promenade dans les champs et près de la rivière. Ensuite, nous avons déjeuné dehors avec du pain, du fromage et des fruits. L’après-midi, j’ai aidé un agriculteur à nourrir les poules et les lapins, c’était très amusant. Le soir, nous avons fait un barbecue et regardé les étoiles. Dimanche, nous avons visité un marché local avant de rentrer. C’était calme, reposant et vraiment agréable.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Votre direction est à la recherche d’une salle pour la fête de fin d’année, capable d’accueillir 100 invités. Rédigez un message à la direction pour leur dire que vous avez trouvé un local idéal. (lieu, tarifs, services, etc.).',
                'correction' => 'Bonjour à tous,

La semaine dernière, j’ai visité avec Mme Laurent la salle « Les Jardins du Lac » pour chercher un lieu pour notre fête de fin d’année. Dès notre arrivée, j’ai eu une très bonne impression, car l’endroit était spacieux, lumineux et bien décoré. La responsable nous a expliqué que la salle pouvait accueillir jusqu’à 120 personnes, donc elle convient parfaitement pour nos 100 invités.

Le tarif était de 1 800 euros pour la soirée, avec les tables, les chaises et le ménage inclus. J’ai aussi appris qu’ils proposaient un service traiteur, une piste de danse, un parking gratuit et du matériel de sonorisation. Pendant la visite, l’ambiance était calme et agréable, et j’ai vraiment imaginé nos collègues passer une belle soirée là-bas.

Franchement, j’ai trouvé ce local idéal, pratique et élégant. À mon avis, c’est une excellente option pour notre événement.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Utilisation des nouvelles technologies dans les écoles : pour ou contre ?',
                'correction' => 'Les technologies à l’école : enjeux et perspectives

L’usage des nouvelles technologies à l’école suscite un débat important. Jean estime qu’elles sont indispensables pour préparer les élèves au monde numérique, tout en favorisant l’engagement, la créativité et l’autonomie grâce à un accès varié aux ressources. En revanche, Sara craint une baisse des relations humaines et une dépendance excessive aux écrans. (52 mots)

À mon avis, les nouvelles technologies ont leur place à l’école, mais leur utilisation doit rester équilibrée. D’abord, elles permettent de diversifier les apprentissages et de rendre les cours plus interactifs. Ensuite, elles donnent accès rapidement à des informations nombreuses et actuelles, ce qui aide les élèves à travailler de façon plus autonome. De plus, elles peuvent développer des compétences utiles pour les études et le monde du travail. Par exemple, apprendre à faire une recherche sérieuse en ligne ou à utiliser un logiciel de présentation est devenu essentiel. Enfin, la technologie ne doit pas remplacer l’enseignant, car les échanges humains, l’écoute et l’accompagnement restent nécessaires pour bien apprendre et grandir. (118 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Jean : Je suis fermement convaincu que l’intégration des nouvelles technologies dans les écoles est cruciale pour préparer les élèves à un avenir numérique. Je pense que l’usage des tablettes et des ordinateurs stimule non seulement l’engagement des élèves mais enrichit également leur expérience éducative en leur offrant un accès facile à une variété de ressources, encourageant ainsi leur créativité et leur autonomie.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Sara : Je suis sceptique quant à l’usage intensif des technologies dans l’enseignement. Je crois que cela peut réduire les interactions humaines essentielles et favoriser une dépendance préoccupante aux écrans. À mon avis, les méthodes d’enseignement traditionnelles et le contact direct entre enseignants et élèves restent indispensables pour un développement équilibré et complet des compétences des jeunes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 45,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez commandé un objet sur Internet et après réception du colis, vous constatez que l’objet est cassé. Rédigez un e-mail au service clientèle pour signaler le problème, décrivez le dommage de l’objet et précisez ce que vous attendez comme solution.',
                'correction' => 'Objet : Réclamation concernant un objet cassé à la livraison

Bonjour,

Je vous écris au sujet de ma commande reçue aujourd’hui. J’ai acheté une lampe de bureau sur votre site, mais en ouvrant le colis, j’ai constaté qu’elle était cassée. Le pied de la lampe est fendu et l’abat-jour est complètement déformé. De plus, l’objet ne fonctionne pas quand je le branche. L’emballage extérieur était aussi abîmé, ce qui montre que le colis a sans doute été mal transporté.

Je suis très déçu, car j’avais besoin de cet article rapidement. Je vous demande donc soit un remplacement en bon état, soit un remboursement complet dans les plus brefs délais.

Cordialement,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez visité une exposition de votre artiste préféré. Rédigez un article exprimant votre expérience lors de la visite. Décrivez ce que vous avez vu et vos impressions.',
                'correction' => '[Une journée inoubliable au musée]

Salut à tous,

Le week-end dernier, j’ai enfin visité l’exposition de mon artiste préféré, Claude Monet, au grand musée du centre-ville, avec ma sœur. J’attendais ce moment depuis des semaines. Dès notre arrivée, l’ambiance était calme, élégante et un peu magique. Les salles étaient lumineuses, et il y avait une musique douce qui rendait la visite encore plus agréable.

J’ai vu plusieurs tableaux célèbres, avec des couleurs magnifiques et une lumière incroyable. Devant les paysages et les jardins, je suis resté longtemps à observer les détails. J’ai aussi découvert des œuvres que je ne connaissais pas, ce qui m’a vraiment surpris. Chaque salle racontait une partie de la vie de l’artiste.

J’ai ressenti beaucoup d’émotion pendant la visite, surtout devant les tableaux de nénuphars. J’étais impressionné, heureux et très inspiré. Cette exposition m’a donné envie de retourner au musée très bientôt.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Devoirs À La Maison : pour ou contre ?',
                'correction' => 'Les devoirs à la maison : un débat scolaire

Les devoirs à la maison divisent les familles et le monde éducatif. Selon le premier document, ils favorisent l’autonomie de l’élève et renforcent le lien entre parents et école. Cependant, le second document affirme qu’ils n’améliorent pas vraiment les résultats et qu’ils créent des inégalités entre les enfants. (48 mots)

À mon avis, les devoirs à la maison doivent être limités, mais non supprimés. D’abord, ils peuvent aider l’élève à revoir calmement une leçon et à prendre de bonnes habitudes de travail. Ensuite, ils permettent aux parents de suivre la scolarité de leur enfant, même quelques minutes. De plus, des devoirs courts réduisent le stress et évitent les conflits familiaux. Par exemple, relire un texte ou apprendre cinq mots utiles peut être bénéfique sans fatiguer l’élève. Enfin, tous les enfants n’ont pas la même aide chez eux ; l’école doit donc donner des tâches simples, claires et raisonnables. Ainsi, les devoirs restent utiles seulement s’ils sont adaptés à chaque âge. (116 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Selon des associations de parents d’élèves, les devoirs à la maison sont utiles, car ils permettent aux élèves d’apprendre à organiser leur temps de manière autonome. Pour les parents, les devoirs sont un lien quotidien avec l’école. Même s’il est parfois difficile de suivre les devoirs après une journée de travail fatigante, ils apprécient ce moment partagé avec leurs enfants parce que ceux-ci sont contents que leurs parents s’intéressent à eux. C’est valorisant !',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Nous protestons depuis longtemps contre les devoirs à la maison pour plusieurs raisons. Personne n’a jamais prouvé leur utilité pour améliorer les résultats des élèves. Beaucoup de parents ont peu de temps pour encadrer les devoirs de leurs enfants et certains parents ne savent pas le faire. Quant aux élèves, ceux qui ont réussi les exercices en classe perdent leur temps à les faire à la maison. Ceux qui ne sont pas aidés à la maison ne réussissent toujours pas, ils sont défavorisés. C’est pourquoi nous pensons qu’il faut supprimer les devoirs à la maison.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 46,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'France Télévision prépare un reportage sur le sport amateur. Et vous, quel sportif êtes-vous ? Envoyez-nous vos témoignages sur francetélévision.fr.',
                'correction' => 'Objet : Mon témoignage sur le sport amateur

Bonjour,

Je m’appelle Ayoub et je pratique le sport amateur depuis plusieurs années. Je fais surtout du football dans un club de quartier à Lyon, trois soirs par semaine, de 19 h à 21 h. Le dimanche matin, nous avons souvent des matchs dans différents stades de la ville. J’aime ce sport parce qu’il me permet de rester en forme, mais aussi de partager des moments forts avec mon équipe. L’ambiance est conviviale, même si les entraînements sont parfois difficiles. Pour moi, le sport amateur est essentiel : il développe la discipline, le respect et l’esprit d’équipe. C’est une vraie passion dans ma vie.

Cordialement,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez passé des vacances dans une belle région de votre pays. Vous écrivez un message à vos amis dans lequel vous décrivez votre expérience, vous expliquez pourquoi vous avez beaucoup aimé ce séjour.',
                'correction' => 'Salut à tous,

Le mois dernier, j’ai passé une semaine de vacances dans la région de Chefchaouen, au nord de mon pays, avec deux amis. Dès notre arrivée, j’ai été impressionné par la beauté des montagnes et par les maisons bleues de la ville. L’ambiance était très calme, et les habitants étaient chaleureux.

Pendant le séjour, nous avons visité la médina, pris beaucoup de photos et goûté des plats traditionnels dans de petits restaurants. Un matin, nous avons fait une randonnée jusqu’aux cascades d’Akchour. Le paysage était magnifique : l’eau était claire, l’air était frais et le silence était vraiment reposant. Nous avons aussi passé une soirée sur une terrasse avec vue sur toute la ville.

J’ai beaucoup aimé ce séjour parce que j’ai pu me détendre, découvrir une belle culture et passer de bons moments avec mes amis. C’était une expérience simple, mais inoubliable.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Les Écoles Privées Ou Publiques ?',
                'correction' => 'Écoles privées et écoles publiques : quel équilibre ?

Le choix entre école privée et école publique suscite un débat important. Le premier document explique que le privé attire surtout par sa réputation, son encadrement et la confiance donnée aux parents. En revanche, le second souligne que ce modèle limite la mixité sociale et renforce les inégalités entre élèves. (48 mots)

À mon avis, l’école publique doit rester le modèle principal, car elle offre une éducation accessible à tous. D’abord, elle permet aux élèves de milieux différents de se rencontrer, ce qui développe l’ouverture d’esprit et le respect. Ensuite, elle défend une certaine égalité des chances, puisque les familles n’ont pas à payer des frais élevés. De plus, le privé n’assure pas toujours de meilleurs résultats ; son succès repose souvent sur son image. Par exemple, dans certains collèges publics de centre-ville, les élèves réussissent très bien grâce à des enseignants investis. Enfin, il faut surtout améliorer les établissements publics pour rassurer les parents et réduire les écarts sociaux. (114 mots)',
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
        'combo' => 47,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous avez trouvé un nouveau travail. Vous écrivez à votre amie Francophone pour lui annoncer la bonne nouvelle. Vous décrivez votre poste, vos collègues et votre lieu de travail.',
                'correction' => 'Objet : Bonne nouvelle : j’ai trouvé un nouveau travail !

Salut Sarah,

J’espère que tu vas bien. Je t’écris pour t’annoncer une très bonne nouvelle : j’ai trouvé un nouveau travail ! Je travaille maintenant comme assistant administratif dans une entreprise de transport à Montréal. Mon poste consiste à répondre aux courriels, organiser les rendez-vous et préparer des documents pour l’équipe. Mes collègues sont très gentils et accueillants. Ils m’aident beaucoup depuis mon arrivée, alors je me sens déjà à l’aise. Mon lieu de travail est moderne, lumineux et bien situé, près du centre-ville. Il y a aussi une petite cafétéria et une belle salle de repos. Je suis vraiment content de cette nouvelle expérience.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Une étudiante qui à 19 ans veut aller à l’étranger pour les études. Elle demande aux internautes du forum de partager leurs expériences et vous avez fait un an à l’étranger. Parlez- lui de votre expérience (décrire le séjour à l’étranger, les activités…).',
                'correction' => 'Mon année d’études à l’étranger

Bonjour à tous,

L’année dernière, j’ai passé un an à Barcelone pour mes études de commerce, dans le cadre d’un échange universitaire. Au début, j’étais très stressé, car je quittais ma famille et je ne connaissais personne. La ville était magnifique, vivante et pleine d’énergie. J’habitais dans une colocation avec deux étudiants, une Italienne et un Mexicain, donc je parlais français, espagnol et anglais chaque jour.

Pendant l’année, j’ai suivi des cours à l’université, mais j’ai aussi beaucoup découvert la culture locale. Le week-end, je visitais des musées, je me promenais au bord de la mer et je goûtais des plats typiques. J’ai aussi participé à des soirées étudiantes et à des voyages organisés avec d’autres jeunes étrangers.

Cette expérience m’a rendu plus autonome et plus ouvert d’esprit. Ce n’était pas toujours facile, mais c’était vraiment une des plus belles années de ma vie.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'L’aide Aux Personnes Pauvres',
                'correction' => 'Les formes d’aide aux personnes pauvres

La pauvreté pousse la société à réfléchir aux meilleures formes d’aide. Marie défend une solidarité immédiate, par le don de temps et d’argent, surtout envers les sans-abri en hiver. En revanche, Paul privilégie l’engagement associatif, qui aide les plus démunis à trouver un logement et un emploi durables. (46 mots)

À mon avis, les deux formes d’aide sont utiles, mais l’accompagnement vers l’autonomie est plus efficace. D’abord, donner de l’argent ou des vêtements répond à une urgence réelle, surtout quand une personne a faim ou dort dehors. Ensuite, cet appui reste souvent temporaire si rien ne change dans sa situation. De plus, les associations peuvent proposer un suivi concret, comme la recherche d’un logement, l’aide administrative ou l’accès à une formation. Par exemple, un sans-abri qui trouve un emploi grâce à une association peut reconstruire sa vie durablement. Enfin, la meilleure solution me semble être de combiner l’aide immédiate et l’insertion, afin de répondre au présent tout en préparant l’avenir. (116 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'Marie, 58 ans :J’ai pris l’habitude, depuis des années maintenant, de donner un peu de mon temps et de mon argent afin d’aider les personnes vivant dans la précarité extrême, surtout ceux qui vivent sans abri en période hivernale. Pour our moi, c’est un devoir d’aider et de contribuer afin que ces personnes puissent vivre le ou des normalement possible. En assistant des pauvres ou des associations, on se sent utile au sein d’une société qui devient de plus en plus impitoyable.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'Paul 63, ans :Pour aider les personnes vivant dans la précarité, on doit s’investir davantage dans des associations caritatives. Personnellement, c’est ce que je fais, je suis bénévole dans une association située dans mon quartier. Elle a pour principal but, aider les sans-abris à trouver un logement et un travail afin qu’ils vivent le plus normalement possible. Cette action est plus bénéfique à long terme car elle permet de les rendre autonomes.',
                    ],
                ],
            ],
        ],
    ],
    [
        'combo' => 48,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Votre ami Mehdi vient de d’emménager dans votre ville et cherche des renseignements sur les moyens de transports. Écrivez un message en lui donnant les informations nécessaires (types de transport, abonnement, tarif, etc.).',
                'correction' => 'Objet : Informations sur les transports dans la ville

Salut Mehdi,

Bienvenue dans ma ville ! Pour te déplacer, tu as plusieurs moyens de transport. Il y a les bus, le tramway et aussi des vélos en libre-service dans le centre-ville. Les bus passent souvent de 6 h à 22 h, et le tram circule jusqu’à minuit le week-end. Si tu prends les transports tous les jours, le mieux est d’acheter un abonnement mensuel à 42 euros. Sinon, le ticket simple coûte 1,80 euro et il est valable pendant une heure. Tu peux acheter les tickets aux distributeurs, à l’application mobile ou dans certains kiosques. Si tu veux, je peux t’aider à faire la carte de transport.

À bientôt,

AYOUB',
            ],
            [
                'task' => 2,
                'prompt' => 'Exprimez votre admiration pour une personnalité, célèbre ou non, en vous appuyant sur ses actions spécifiques. Rédigez un article de blog en détaillant les actions remarquables de cette personne et expliquez pourquoi vous l’aimez.',
                'correction' => '**La personne que j’admire le plus**

Bonjour à tous,

Je voudrais vous parler d’une personne que j’admire beaucoup : mon ancienne professeure de français au lycée, Mme Karim. Je l’ai vraiment connue il y a trois ans, pendant une période difficile pour notre classe. À ce moment-là, plusieurs élèves avaient perdu confiance, et l’ambiance était assez triste.

Un jour, elle a décidé d’organiser des ateliers après les cours pour nous aider gratuitement. Elle a aussi créé une petite bibliothèque dans la salle avec ses propres livres. Je me souviens qu’elle restait souvent jusqu’à 18 heures pour corriger nos textes et nous encourager. Grâce à ses conseils, j’ai progressé rapidement et j’ai même participé à un concours d’écriture.

J’ai aimé sa patience, son énergie et surtout sa générosité. Elle ne faisait pas seulement son travail : elle croyait en nous. Cette expérience m’a beaucoup touché, et depuis, je respecte encore plus les personnes qui aident les autres avec le cœur.

À bientôt,

AYOUB',
            ],
            [
                'task' => 3,
                'prompt' => 'Vivre En Colocation : Pour Ou Contre ?',
                'correction' => 'La colocation au quotidien

La colocation suscite des avis partagés. Le premier document souligne ses limites : différences de caractère, conflits sur les tâches et manque d’intimité peuvent compliquer la vie commune. En revanche, le second met en avant ses atouts : économies, rencontres variées et enrichissement social grâce au partage du logement. (46 mots)

À mon avis, vivre en colocation est une bonne solution, surtout pour les étudiants et les jeunes travailleurs. D’abord, elle permet de réduire fortement les dépenses, ce qui aide à mieux gérer un budget limité. Ensuite, elle apprend à respecter les autres, à dialoguer et à faire des compromis, des qualités utiles dans la vie. De plus, elle peut rompre la solitude et créer une ambiance plus chaleureuse au quotidien. Par exemple, un étudiant qui arrive dans une nouvelle ville peut s’intégrer plus facilement grâce à ses colocataires. Enfin, cette expérience reste positive si chacun respecte des règles claires concernant le ménage, le bruit et les invités. (113 mots)',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => 'La colocation peut cependant présenter des défis. Les différences de personnalité et de mode de vie entre les colocataires peuvent entraîner des tensions. La gestion des responsabilités et des tâches ménagères peut également être source de conflits. De plus, la colocation peut limiter l’intimité et l’espace personnel. Il est important d’établir une communication ouverte et respectueuse, ainsi que des règles de vie commune, pour favoriser une cohabitation harmonieuse.',
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => 'La vie en colocation offre de nombreux avantages. Partager un logement avec d’autres personnes permet de réduire les dépenses, que ce soit le loyer, les factures ou les frais généraux. De plus, cela favorise les interactions sociales et les échanges culturels. Vivre avec des colocataires permet de rencontrer des individus de différents horizons, de nouer des amitiés et de partager des expériences enrichissantes.',
                    ],
                ],
            ],
        ],
    ],
];
