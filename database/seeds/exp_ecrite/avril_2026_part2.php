<?php
declare(strict_types=1);

/** Combinaisons 7 à 13 — Avril 2026 */
return [
    [
        'combo' => 7,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => "Envoyez un courriel à votre ami francophone afin de lui demander de l'aide pour la recherche d'un logement, en lui fournissant toutes les informations nécessaires (type de logement, budget, date).",
                'correction' => "Objet : Besoin d'aide pour trouver un logement\nBonjour,\nJ'espère que tu vas bien. Je t'écris parce que je vais bientôt m'installer dans ta ville et j'aurais besoin de ton aide pour trouver un logement.\nJe cherche de préférence un petit appartement (studio ou 2 pièces), dans un quartier calme et proche des transports en commun. Mon budget est d'environ 700 euros par mois, charges comprises. Idéalement, je voudrais emménager à partir du 1er du mois prochain.\nSi tu connais des personnes qui louent un logement, ou si tu vois une annonce intéressante, pourrais-tu me la partager ? Même une colocation pourrait m'intéresser si l'ambiance est sérieuse.\nMerci beaucoup pour ton aide.\nÀ bientôt,\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => "Votre école vous a chargé d'organiser une journée spéciale pour accueillir les nouveaux étudiants francophones. Vous rédigez un courriel destiné à ces étudiants dans lequel vous donnez tous les détails pour le bon déroulement de cette journée.",
                'correction' => "Objet : Journée d'accueil des nouveaux étudiants francophones\nBonjour,\nJ'espère que vous allez bien et bienvenue dans notre école. Je vous écris pour vous informer de l'organisation de la journée spéciale d'accueil des nouveaux étudiants francophones.\nCette journée aura lieu le lundi 9 septembre à partir de 9 h, dans le hall principal de l'école. Nous commencerons par une présentation de l'établissement et des services disponibles (bibliothèque, cafétéria, activités). Ensuite, une visite guidée du campus sera organisée.\nÀ 12 h 30, un déjeuner gratuit sera offert à tous les nouveaux étudiants. L'après-midi, vous participerez à des ateliers pratiques : inscription aux cours, création de votre compte étudiant et présentation des clubs.\nMerci d'arriver 15 minutes en avance et d'apporter une pièce d'identité.\nCordialement,\nAyoub",
            ],
            [
                'task' => 3,
                'prompt' => 'La vie à la campagne ou en ville',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "À mon avis, le fait de vivre en ville, cela vous donne la possibilité de vous divertir de plusieurs manières ; comme aller au cinéma, déjeuner dans un restaurant, faire du shopping… Tout est à côté, vous n'avez pas besoin de parcourir plusieurs kilomètres pour prendre un taxi (si le trajet est un peu long) et vous aurez à disposition tout ce dont vous désirez. Même les amoureux des évènements culturels sont servis ; musée, théâtre, opéra… tout y est !",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Dernièrement, j'ai décidé de quitter la ville pour vivre à la campagne, car j'avais vraiment besoin de me rapprocher de la nature et de profiter du calme. Désormais, au lieu de partir quotidiennement au bar du coin ou au restaurant, j'invite des amis à boire un verre sur la terrasse de ma maison ou j'organise de temps en temps un barbecue dans mon jardin. Un autre détail d'importance m'a encouragé à prendre cette décision ; c'est la disponibilité des logements avec des prix largement inférieurs à ceux proposés en ville. Avec mon budget actuel, j'ai une grande maison avec terrasse et jardin privatif, alors qu'en ville, j'avais droit à un petit appartement au 5e étage d'un immeuble.",
                    ],
                ],
                'correction' => "La vie à la campagne ou en ville\n\nLe premier document met en avant les avantages de la ville : de nombreux loisirs, des commerces proches et un accès facile aux transports et aux activités culturelles. Le second document défend la vie à la campagne, en soulignant le calme, la proximité avec la nature, une vie plus conviviale et des logements plus grands et moins chers.\n\nÀ mon avis, la ville et la campagne ont chacune des avantages, mais je pense que la ville est plus pratique au quotidien. On y trouve facilement un emploi, des écoles, des hôpitaux et tous les services nécessaires. Par exemple, en cas d'urgence médicale, il est plus simple d'accéder rapidement à un médecin. Cependant, je comprends aussi le choix de la campagne, car elle offre un cadre de vie plus calme et moins stressant. Pour moi, l'idéal serait de vivre en ville pendant la semaine pour le travail, et de profiter de la campagne le week-end afin de se reposer et respirer.",
            ],
        ],
    ],
    [
        'combo' => 8,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous habitez dans un grand appartement et vous recherchez un colocataire. Décrivez le type de colocation que vous proposez ainsi que les caractéristiques de votre appartement.',
                'correction' => "Bonjour,\nJe recherche un(e) colocataire sérieux(se) pour partager mon grand appartement. Il est situé dans un quartier calme, proche des transports, des commerces et d'un parc. L'appartement est spacieux, lumineux et entièrement meublé. Il comprend deux chambres, un grand salon, une cuisine équipée, une salle de bain et un balcon.\nJe propose une colocation conviviale et respectueuse, avec une bonne organisation pour le ménage et les dépenses communes. La chambre disponible est grande, avec un lit, un bureau et une armoire.\nLe loyer est de 550 euros par mois, charges comprises (eau, électricité, Internet). L'appartement est disponible immédiatement. N'hésitez pas à me contacter si vous êtes intéressé(e).",
            ],
            [
                'task' => 2,
                'prompt' => "Une troupe de théâtre s'est installée dans votre ville, et vous avez assisté à l'un de ses spectacles. Rédigez un article de blog pour décrire cette expérience.",
                'correction' => "Une soirée inoubliable au théâtre\nChers lecteurs,\nLa semaine dernière, une troupe de théâtre s'est installée dans ma ville, et j'ai eu la chance d'assister à l'un de ses spectacles. La représentation avait lieu dans une petite salle chaleureuse, presque intime, ce qui a rendu l'expérience encore plus spéciale.\nLa pièce racontait une histoire moderne, pleine d'humour et d'émotion. Les comédiens étaient très talentueux et jouaient avec beaucoup d'énergie. J'ai particulièrement apprécié la façon dont ils interagissaient avec le public. Les décors étaient simples, mais efficaces, et les jeux de lumière créaient une ambiance captivante.\nCe que j'ai le plus aimé, c'est l'émotion transmise par les acteurs. On riait à certains moments, puis on était touché par des scènes plus profondes. Cette soirée m'a rappelé à quel point le théâtre est vivant et authentique. Je recommande vivement d'aller voir cette troupe si vous en avez l'occasion.",
            ],
            [
                'task' => 3,
                'prompt' => 'Influence de la publicité sur les enfants : pour ou contre ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Une étude a révélé que les enfants sont constamment exposés à de nombreuses publicités, que ce soit à la télévision, dans les magazines ou sur Internet, et ce, dans des endroits vulnérables à l'influence publicitaire. Les enfants sont particulièrement ciblés par des publicités pour des produits alimentaires peu sains, des jouets, des jeux vidéo et autres produits, ce qui peut influencer leurs choix de consommation et leurs demandes envers leurs parents.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Un article de recherche publié dans une revue scientifique souligne que les enfants ont des capacités cognitives limitées pour comprendre et interpréter les messages publicitaires, et qu'ils sont généralement conscients du caractère persuasif de la publicité. Toutefois, d'autres facteurs tels que l'éducation parentale, les influences sociales et culturelles ont un rôle plus important dans les choix de consommation des enfants que la publicité elle-même.",
                    ],
                ],
                'correction' => "Influence de la publicité sur les enfants : pour ou contre ?\n\nLe premier document explique que les enfants sont très exposés à la publicité, notamment à la télévision et sur Internet, et que cela influence leurs envies, surtout pour des produits peu sains ou des jeux. Le second document affirme que les enfants comprennent partiellement la publicité, mais que l'éducation et l'environnement familial jouent un rôle plus important.\n\nÀ mon avis, la publicité influence réellement les enfants, même si ce n'est pas le seul facteur. Les enfants sont facilement attirés par les images, les couleurs et les messages qui promettent du plaisir. Par exemple, après avoir vu une publicité, un enfant peut demander immédiatement un jouet ou un snack à ses parents. Même s'il sait que la publicité veut vendre, il n'a pas toujours le recul nécessaire pour résister. C'est pourquoi je pense qu'il faut limiter la publicité destinée aux enfants, surtout pour les produits mauvais pour la santé. En parallèle, les parents doivent expliquer et accompagner leurs enfants pour développer un esprit critique.",
            ],
        ],
    ],
    [
        'combo' => 9,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Répondez au courriel de votre ami Lucas pour lui donner des informations sur les nouveaux locaux de votre entreprise (lieu, disposition des pièces, équipements, etc.).',
                'correction' => "Salut Lucas,\nJ'espère que tu vas bien. Merci pour ton message ! Je suis très content de te parler des nouveaux locaux de notre entreprise. Ils sont situés en centre-ville, près de la station de métro principale, ce qui est très pratique.\nLes bureaux sont modernes et lumineux. Il y a un grand open space, plusieurs salles de réunion bien équipées avec des écrans et du matériel de visioconférence, ainsi qu'un espace détente avec une cuisine et des canapés. Nous avons aussi une petite terrasse pour les pauses.\nÀ bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez assisté à un événement intitulé "Une semaine sans voiture". Racontez votre expérience et donnez votre impression sur cette initiative. Décrivez le déroulement de l\'événement (dates, lieu, activités proposées).',
                'correction' => "Une semaine sans voiture : une expérience surprenante\nLa semaine dernière, j'ai participé à un événement intitulé « Une semaine sans voiture », organisé du 3 au 9 juin dans le centre-ville. Pendant sept jours, certaines rues ont été fermées à la circulation afin de favoriser les déplacements à pied, à vélo et en transports en commun.\nPlusieurs activités étaient proposées : balades à vélo en groupe, ateliers de réparation de bicyclettes, conférences sur l'environnement et animations pour les enfants dans les parcs.\nDes stands d'information expliquaient aussi les avantages de la mobilité durable.\nAu début, j'étais un peu sceptique, mais j'ai rapidement apprécié l'ambiance plus calme et l'air plus pur. Cette initiative m'a permis de redécouvrir ma ville autrement. À mon avis, ce type d'événement devrait être organisé plus souvent pour sensibiliser les citoyens.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Vêtements de Grandes Marques',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Les vêtements de marques sont très importants pour les enfants et les adolescents. C'est un moyen de s'exprimer et de se rattacher à un groupe social. Cette attirance pour les marques est très présente chez les adolescents qui se cherchent et montrent leur personnalité. Les enfants aiment également porter des vêtements de marques avec des images des dessins animés qu'ils regardent ou des logos qu'ils apprécient.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Les enfants grandissent très vite et les vêtements sont portés pendant une courte période. Ainsi, les vêtements deviennent rapidement trop petits. Mais il y a aussi le fait que les enfants usent assez rapidement les vêtements en jouant à l'extérieur avec les copains, en s'amusant dans l'herbe ou à l'aire de jeux. Les habits sont très vite sales ou troués.",
                    ],
                ],
                'correction' => "Les vêtements de grandes marques : un choix raisonnable ?\nLes vêtements de marque attirent particulièrement les enfants et les adolescents. Pour les jeunes, ils représentent un moyen d'exprimer leur personnalité et de s'intégrer dans un groupe. Cependant, comme les enfants grandissent vite et abîment facilement leurs habits, ces achats peuvent sembler peu pratiques et coûteux.\nÀ mon avis, il faut rester raisonnable face à cette tendance. Il est vrai que les marques peuvent donner confiance aux adolescents, surtout à un âge où l'apparence est importante. Porter un vêtement à la mode peut les aider à se sentir acceptés. Cependant, les enfants changent rapidement de taille et utilisent beaucoup leurs vêtements en jouant. Dépenser une grande somme pour des habits qu'ils porteront seulement quelques mois n'est pas toujours justifié. Je pense qu'il vaut mieux privilégier la qualité et le confort plutôt que le logo. Les parents peuvent aussi apprendre à leurs enfants que la valeur d'une personne ne dépend pas des marques qu'elle porte.",
            ],
        ],
    ],
    [
        'combo' => 10,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => "Vous souhaitez proposer à un(e) ami(e) de faire du sport avec vous. Vous lui écrivez un message pour décrire votre projet (type d'activités, lieu, équipement, etc.)",
                'correction' => "Salut,\nJ'espère que tu vas bien. Je voulais te proposer de faire du sport avec moi cette semaine. J'aimerais qu'on se motive ensemble et qu'on fasse une séance simple mais efficace. On pourrait aller courir au parc près de chez moi, puis faire quelques exercices de renforcement (abdos, squats, pompes). L'idéal serait samedi matin vers 10 h, car il fait plus frais et il y a moins de monde. Il te suffit d'apporter une tenue de sport, une bouteille d'eau et des baskets confortables.\nDis-moi si ça te tente !",
            ],
            [
                'task' => 2,
                'prompt' => "Vous avez étudié dans une université à l'étranger pendant six mois. Vous écrivez un message à vos amis pour raconter votre expérience et vous expliquez ce que vous avez aimé (120 mots minimum/150 mots maximum).",
                'correction' => "Salut les amis,\nJ'espère que vous allez bien. Je voulais vous raconter mon expérience, car j'ai étudié pendant six mois dans une université à l'étranger, et c'était vraiment une aventure inoubliable. Ce que j'ai le plus aimé, c'est la découverte d'une nouvelle culture et d'un nouveau mode de vie. Les cours étaient différents : plus de travaux en groupe, plus de participation orale et une relation plus simple avec les professeurs.\nJ'ai aussi rencontré des étudiants venant de plusieurs pays, ce qui m'a permis d'améliorer mon français et mon anglais en même temps. En dehors des études, j'ai beaucoup voyagé, visité des musées, et goûté des plats locaux.\nHonnêtement, cette expérience m'a rendu plus autonome et plus confiant.",
            ],
            [
                'task' => 3,
                'prompt' => "L'uniforme à l'école : Pour ou contre ?",
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Aujourd'hui, les goûts des enfants sont influencés par la mode et les parents doivent souvent leur acheter de nouveaux vêtements. L'uniforme est une solution à ce problème et permet aux parents d'économiser de l'argent. De plus, les jeunes qui portent un uniforme ont un sentiment fort d'appartenance à leur école et ressentent une certaine fierté. Par ailleurs, une tenue identique pour tous les élèves permet de masquer les différences de classe sociale et la discrimination basée sur le style. Le port de l'uniforme est donc positif pour tout le monde !",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Au Québec, l'uniforme scolaire n'est plus réservé uniquement aux écoles privées: de plus en plus d'écoles publiques font ce choix pour leurs élèves. Mais il représente un problème pour les adolescents qui ne sont pas toujours d'accord avec ce choix. Ils disent souvent que ces uniformes ne sont pas confortables, qu'ils ne sont pas beaux car ils manquent de couleurs et qu'ils tiennent trop chaud en été. De plus, comme tous les élèves de l'école sont habillés de la même manière, les jeunes trouvent que cela ne leur permet pas d'exprimer leur personnalité.",
                    ],
                ],
                'correction' => "L'uniforme à l'école : pour ou contre ?\nPremière partie (40–60 mots)\nLe premier document présente l'uniforme scolaire comme une solution économique et sociale : il réduit les dépenses des parents, renforce le sentiment d'appartenance à l'école et limite les inégalités entre élèves. Le second document insiste sur ses inconvénients : inconfort, manque de couleurs et difficulté pour les adolescents d'exprimer leur personnalité.\nDeuxième partie (120 mots)\nÀ mon avis, l'uniforme scolaire a des points positifs, mais il ne doit pas être imposé sans réflexion. D'un côté, il permet de réduire la pression liée aux marques et d'éviter certaines moqueries entre élèves. Il aide aussi les familles à dépenser moins pour les vêtements. De plus, l'uniforme peut créer une ambiance plus sérieuse et plus égalitaire à l'école. Cependant, les adolescents ont besoin de se sentir libres et différents. Porter la même tenue chaque jour peut les frustrer et leur donner l'impression d'être contrôlés. Selon moi, la meilleure solution serait un uniforme simple avec quelques choix possibles, comme plusieurs couleurs ou modèles, afin de respecter l'identité de chacun.",
            ],
        ],
    ],
    [
        'combo' => 11,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez fêter votre anniversaire dans un restaurant. Vous invitez vos amis. Vous leur écrivez un courriel pour leur donner toutes les informations nécessaires (lieu, date, horaires, menus, prix) et vous leur demandez une réponse (60 mots minimum/120 mots maximum).',
                'correction' => "Bonjour les amis,\nJ'espère que vous allez bien. Je vous écris pour vous inviter à fêter mon anniversaire au restaurant. La soirée aura lieu le samedi 22 février, à partir de 19 h 30, au restaurant Le Jardin Gourmand, situé au centre-ville, près de la station de métro.\nLe restaurant propose un menu varié (viande, poisson, plats végétariens). Le prix du menu est d'environ 25 à 30 dollars par personne, boisson non incluse. L'ambiance est très sympa et la décoration est moderne.\nMerci de me confirmer votre présence avant mercredi, afin que je puisse réserver la table.\nÀ très bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez quitté la ville pour vous installer à la campagne. Sur un forum internet, vous expliquez pourquoi vous avez fait ce choix et vous présentez les avantages de votre nouvelle vie (120 mots minimum/150 mots maximum).',
                'correction' => "Pourquoi j'ai choisi la vie à la campagne\nIl y a quelques mois, j'ai décidé de quitter la ville pour m'installer à la campagne. Ce choix est venu après plusieurs années de stress et de fatigue. En ville, je passais beaucoup de temps dans les transports, je subissais le bruit constant et je ressentais une pression permanente. J'avais besoin de ralentir et de retrouver une meilleure qualité de vie.\nDepuis mon installation, je vois clairement les avantages. D'abord, l'environnement est plus calme et plus agréable : je me réveille avec le chant des oiseaux, et je respire un air plus pur. Ensuite, le logement est plus spacieux et moins cher. J'ai maintenant une maison avec un petit jardin, ce qui me permet de profiter de l'extérieur.\nBien sûr, il y a moins de commerces à proximité, mais je me sens beaucoup plus serein et heureux au quotidien.",
            ],
            [
                'task' => 3,
                'prompt' => 'Les caméras de surveillance à l\'école sont-elles utiles ?',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "À Montréal, dans l'école où j'enseigne, des caméras sont présentes un peu partout. L'installation de ces appareils permet de faire comprendre aux jeunes que tout acte de violence sera puni. Ce mode de surveillance est très bien accepté par les parents, les enseignants et la plupart des élèves. Si les parents sont rassurés concernant la sécurité de leurs enfants, les professeurs y voient une garantie de pouvoir exercer leur métier dans les meilleures conditions possibles. Seuls certains élèves critiquent ces caméras en disant qu'elles ne respectent pas leur vie privée.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Je suis contre la présence de caméras de surveillance dans nos écoles de Montréal. Dans les pays où ce système a été mis en place, les résultats ne sont pas très positifs. Comme les caméras sont très visibles, les personnes extérieures qui voudraient entrer dans l'école peuvent le faire en passant par des endroits non surveillés. Pour résoudre les problèmes de discipline, il suffit souvent d'améliorer la communication entre les élèves, l'administration et les enseignants. Au lieu de placer des caméras, il suffirait de bien faire comprendre et appliquer le règlement intérieur de l'école par tous.",
                    ],
                ],
                'correction' => "Caméras de surveillance à l'école : utiles ou excessives ?\nPremière partie (40–60 mots)\nLe premier document explique que les caméras installées dans une école à Montréal renforcent la sécurité et dissuadent la violence. Parents et enseignants y sont favorables, même si certains élèves évoquent le respect de la vie privée. Le second document s'y oppose, estimant que ce système est inefficace et que la discipline passe plutôt par le dialogue et le règlement.\nDeuxième partie (120 mots)\nÀ mon avis, les caméras de surveillance peuvent être utiles dans certaines situations, mais elles ne doivent pas devenir une solution automatique. Elles peuvent dissuader certains actes violents et rassurer les familles, surtout dans les établissements où il y a déjà eu des incidents. Cependant, je pense qu'une école doit rester un lieu de confiance, pas un endroit où les élèves se sentent observés en permanence. De plus, la surveillance ne règle pas les vraies causes des conflits : manque de communication, absence de règles claires ou tensions entre élèves. Selon moi, il faut d'abord renforcer la prévention, le dialogue et la présence des adultes. Les caméras devraient être un dernier recours, limité à quelques zones sensibles.",
            ],
        ],
    ],
    [
        'combo' => 12,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Vous souhaitez aller au concert de votre artiste préféré(e). Vous proposez à votre ami(e) de vous accompagner. Vous lui écrivez un courriel avec les informations utiles (date, prix, lieu, artiste, durée) (60 mots minimum/120 mots maximum).',
                'correction' => "Bonjour,\nJ'espère que tu vas bien. Je t'écris parce que mon artiste préféré va bientôt donner un concert, et j'aimerais beaucoup que tu viennes avec moi. Le concert aura lieu le samedi 16 mars à 20 h, au Centre Bell, à Montréal.\nLe prix des billets commence à 45 dollars, et le spectacle dure environ 2 heures. L'ambiance promet d'être incroyable, car c'est un artiste très connu et ses concerts sont toujours réussis.\nDis-moi rapidement si tu es disponible, afin que je puisse réserver nos places à temps.\nÀ bientôt !",
            ],
            [
                'task' => 2,
                'prompt' => "Les professeurs de l'école de votre quartier souhaitent présenter différents métiers aux élèves. Vous voulez participer à ce projet pour parler de votre expérience. Vous leur écrivez. Vous décrivez votre profession (activités, collègues, etc.). Vous expliquez pourquoi vous trouvez votre métier intéressant (120 mots minimum / 150 mots maximum).",
                'correction' => "Bonjour,\nJ'espère que vous allez bien. Je vous écris car j'ai appris que votre école souhaite organiser un projet pour présenter différents métiers aux élèves. Je serais ravi de participer afin de partager mon expérience professionnelle.\nJe travaille dans le domaine de l'éducation et de la formation. Mon travail consiste à accompagner des apprenants, à préparer des cours, à corriger des productions et à proposer des conseils pour progresser. Je travaille aussi avec d'autres collègues, notamment des formateurs et des responsables pédagogiques, afin d'améliorer les contenus et d'aider les élèves à atteindre leurs objectifs.\nJe trouve ce métier très intéressant car il me permet d'être utile, de transmettre des connaissances et de voir les progrès des personnes que j'accompagne. C'est un travail motivant et enrichissant au quotidien.",
            ],
            [
                'task' => 3,
                'prompt' => 'Locations de logement de courte durée.',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "De plus en plus de gens louent leur logement ou une chambre à des voyageurs, pour gagner un revenu supplémentaire ou pour rencontrer de nouvelles personnes. Ce système, basé sur la confiance, permet aux visiteurs de se loger en payant moins cher que dans un hôtel. De plus, si le propriétaire est d'accord, ils peuvent même utiliser la cuisine et éviter d'aller au restaurant! La personne qui les accueille peut leur servir de guide et les conseiller sur les visites à faire. C'est un échange précieux pour tout le monde!",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "L'année dernière, j'ai loué mon appartement à un couple de touristes pour leurs vacances d'une semaine à Marseille. C'était une très mauvaise expérience qui m'a fait changer d'avis sur ce mode de location ! Ils sont arrivés en retard et j'ai dû les attendre pour leur donner la clé. Ils ont fait beaucoup de bruit et dérangé mes voisins. En plus, ils ont quitté mon appartement sans faire la vaisselle et en laissant leurs déchets. J'ai passé trois heures à nettoyer. Julien, 38 ans",
                    ],
                ],
                'correction' => "Location de courte durée : opportunité ou risque ?\nPremière partie (40–60 mots)\nLe premier document présente la location de courte durée comme un système avantageux : elle permet de gagner de l'argent, de faire des rencontres et d'offrir un logement moins cher que l'hôtel. Le second document raconte une expérience négative : nuisances, manque de respect et problèmes d'organisation.\nDeuxième partie (120 mots)\nÀ mon avis, louer son appartement à des inconnus peut être une bonne idée, mais cela comporte des risques. D'un côté, ce système permet d'obtenir un revenu supplémentaire et de rencontrer des personnes d'autres cultures. Les voyageurs profitent d'un logement plus économique et d'un accueil personnalisé. Cependant, comme le montre le deuxième document, tout ne se passe pas toujours bien. Certains locataires peuvent manquer de respect ou ne pas prendre soin du logement. Je pense donc qu'il faut être prudent : vérifier les avis, demander une caution et établir des règles claires. Avec de bonnes précautions, cette pratique peut être positive, mais elle demande de la vigilance.",
            ],
        ],
    ],
    [
        'combo' => 13,
        'tasks' => [
            [
                'task' => 1,
                'prompt' => 'Écrivez un courriel à vos amis pour les inviter à un anniversaire surprise de votre meilleur(e) ami(e). (Lieu, date, horaire, etc.).',
                'correction' => "Salut tout le monde,\n\nJ'espère que vous allez bien ! J'organise un anniversaire surprise pour notre ami(e) Sarah, et j'aimerais que vous soyez tous là pour rendre ce moment mémorable.\n\nÇa se passe le samedi 16 décembre à 19h chez moi (adresse : 45 rue des Lilas). Le thème de la soirée est « années 80 », alors n'hésitez pas à venir déguisés ! Merci d'arriver un peu en avance pour crier « surprise » quand Sarah arrivera vers 19h30.\n\nConfirmez-moi votre présence et surtout, gardez le secret ! À très vite pour une soirée inoubliable.\n\nAyoub",
            ],
            [
                'task' => 2,
                'prompt' => 'Vous avez participé à une brocante (achat / vente de produits d\'occasion) dans votre ville. Sur votre blog personnel, racontez pourquoi vous avez aimé cette activité.',
                'correction' => "3 Raisons de Participer à une Brocante : Une Expérience Unique\n\nLes brocantes sont des événements où l'on peut acheter ou vendre des objets d'occasion dans une ambiance conviviale. C'est une activité parfaite pour dénicher des trésors à petits prix et donner une seconde vie à des objets oubliés.\n\nLe week-end dernier, j'ai participé à la brocante de ma ville, et c'était génial ! J'ai vendu des livres et des vêtements, tout en rencontrant des acheteurs intéressants. En retour, j'ai trouvé une superbe montre vintage et un tableau artisanal à un prix imbattable. L'ambiance était festive avec des musiciens de rue et des stands de snacks délicieux.\n\nJe recommande cette expérience à tous. Que vous souhaitiez faire de bonnes affaires, vous débarrasser de vos affaires inutilisées ou passer un moment agréable, une brocante est idéale. À tester absolument !",
            ],
            [
                'task' => 3,
                'prompt' => 'Les Vols À Bas Prix',
                'documents' => [
                    [
                        'title' => 'Document 1',
                        'content' => "Je fais souvent mes voyages avec des compagnies aériennes à bas prix. Les compagnies Low cost mettent à disposition des prix inférieurs à ceux proposés par les compagnies aériennes régulières. Cela me coûte des fois moins cher que de voyager en voiture ou en train. Avec ces tarifs-là, vous vous doutez qu'il y a un hic ; en effet, vous n'aurez droit à aucun service à bord (ni aliments, ni boissons). Je dirais donc que le low-cost n'est surtout pas fait pour les vols long courrier.",
                    ],
                    [
                        'title' => 'Document 2',
                        'content' => "Récemment, j'ai pris la décision de ne plus voyager avec les compagnies aériennes à bas prix. En effet, j'ai longuement réfléchi afin de prendre ma décision, mais ce choix était évident : des sièges inconfortables, des conditions de travail pénibles et surtout des avions vétustes qui remettent en cause la sécurité ! Dès lors, pour certains voyages, je vais opter pour la voiture ou même le train, ce dernier permet même de découvrir de jolis paysages. Quant aux longs trajets, il vaut mieux prendre un vol en compagnie régulière.",
                    ],
                ],
                'correction' => "Vols Low-Cost : Bon Plan ou Mauvais Pari ?\n\nDans le débat sur les vols à bas prix, économies et confort s'opposent. Le Document 1 met en avant l'aspect financier, en montrant que voyager avec des compagnies low-cost est souvent plus économique que d'autres moyens de transport. À l'inverse, le Document 2 critique leur inconfort et soulève des questions sur la sécurité.\n\nÀ mon sens, les vols low-cost sont une solution pratique pour les trajets courts ou les budgets limités. Ils permettent à de nombreuses personnes de voyager à moindre coût. Cependant, comme le souligne le Document 2, les concessions en termes de confort, de services et parfois de sécurité ne sont pas négligeables. Les longs trajets ou les voyages où le confort est une priorité justifient l'investissement dans des compagnies régulières. Ainsi, le choix dépend avant tout des besoins et des attentes de chaque voyageur.",
            ],
        ],
    ],
];
