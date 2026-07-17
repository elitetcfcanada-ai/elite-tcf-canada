<?php
declare(strict_types=1);

/** @return list<array{combo:int,tasks:list<array{task:int,prompt:string,correction:string,documents?:list<array{title?:string,content:string}>}>}> */
return [
  0 => 
  array (
    'combo' => 1,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => '« Salut,

Comment ça va ?

Alors, comment est la nouvelle université ? Est-ce que les étudiants sont sympas ? Comment sont les profs ?

À bientôt.

Alex »

Vous répondez à Alex dans un message où vous décrivez votre université (professeurs, étudiants, activités, etc.).',
        'correction' => 'Salut Alex,

Ça va très bien, merci ! J\'espère que toi aussi.

La nouvelle université me plaît beaucoup. Le campus est grand et moderne, avec une bibliothèque bien équipée et plusieurs espaces pour travailler en groupe.

Les professeurs sont sérieux mais accessibles. Ils expliquent bien les cours et prennent le temps de répondre aux questions. J\'apprécie surtout leur méthode, car les cours sont souvent interactifs et pratiques, pas seulement théoriques.

Franchement, je me sens bien ici et je ne regrette pas mon choix.

À très bientôt !',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous avez décidé de ne plus utiliser votre réseau social préféré (Instagram, Facebook, etc.). Vous écrivez à vos amis pour leur raconter cette expérience et expliquer pourquoi vous avez pris cette décision.',
        'correction' => 'Chers amis,

J\'ai pris une décision un peu surprenante : j\'ai arrêté d\'utiliser mon réseau social préféré. Au début, ce n\'était pas prévu, mais je me suis rendu compte que je passais trop de temps dessus, souvent sans vraiment m\'en rendre compte.

Cette habitude me faisait perdre beaucoup de temps et de concentration. Je comparais ma vie à celle des autres, ce qui me stressait inutilement. J\'ai aussi remarqué que cela avait un impact négatif sur mon sommeil et ma motivation.

Depuis que j\'ai arrêté, je me sens plus calme et plus organisé. Je lis davantage, je me concentre mieux sur mes études et je passe plus de temps de qualité avec mes proches. Bien sûr, ce n\'est pas toujours facile, surtout au début, mais je ne regrette pas cette décision.

Je pense que prendre de la distance avec les réseaux sociaux peut vraiment aider à se recentrer sur l\'essentiel.',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Photo sur le CV : pour ou contre ?',
        'correction' => 'La place de la photo dans un CV aujourd\'hui

Aujourd\'hui, la question de la photo sur le CV fait débat. D\'un côté, le premier document explique que la photo est inutile et peut favoriser la discrimination, car les employeurs privilégient surtout l\'expérience et les diplômes. En revanche, le second document montre que, pour certains postes, une photo professionnelle peut être utile.

À mon avis, il n\'est pas nécessaire d\'imposer la photo sur un CV. L\'important devrait rester les compétences, la formation et l\'expérience professionnelle. Une photo peut parfois influencer le recruteur de manière inconsciente, ce qui peut créer des inégalités entre les candidats. Cependant, je pense que dans certains métiers, comme l\'accueil ou la relation client, une photo professionnelle peut être un avantage. Dans tous les cas, ce choix devrait rester personnel et dépendre du poste visé. Pour moi, un recrutement juste doit avant tout se baser sur les capacités et non sur l\'apparence physique.',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Aujourd\'hui, certains candidats mettent une photo sur leur CV, d\'autres non. Il faudrait interdire les photos sur les CV pour éviter les discriminations et les injustices. Selon une étude menée par des spécialistes de l\'emploi, cette pratique est inutile. L\'étude révèle que les employeurs prêtent plus attention à l\'expérience professionnelle (32%) et aux diplômes (15%) qu\'à l\'apparence physique. Seulement 2% des employeurs commencent la lecture d\'un CV par la photo. C\'est une statistique surprenante, surtout quand on connaît l\'importance que certaines personnes accordent à leur apparence physique.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'La question de la photo sur le CV divise les employeurs. Pour certains, la photo permet de mieux se représenter la personne avant de la rencontrer. De plus, une photo peut aider à se souvenir d\'un candidat lorsqu\'un grand nombre de CV sont reçus. Pour d\'autres, cela dépend du poste : pour des métiers d\'accueil, par exemple, il peut être pertinent d\'inclure une photo. Cependant, elle doit toujours être professionnelle pour donner une impression positive.',
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'combo' => 2,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous avez trouvé un nouveau travail. Vous écrivez à votre ami(e) francophone pour lui annoncer la nouvelle. Vous décrivez votre poste, vos collègues et votre lieu de travail.',
        'correction' => 'Bonjour,

J\'espère que tu vas bien. Je t\'écris pour t\'annoncer une excellente nouvelle : j\'ai trouvé un nouveau travail ! Je suis très content, car ce poste correspond vraiment à mes compétences et à mes objectifs professionnels.

Je travaille maintenant comme assistant administratif dans une entreprise située en centre-ville. Mes missions principales consistent à gérer les dossiers, organiser les réunions et assurer la communication interne. Le poste est intéressant et varié, ce qui rend mes journées très dynamiques.

J\'espère te voir bientôt pour en discuter.

À très bientôt,
Ayoub',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => '« Chers internautes, J\'ai 19 ans, je vais bientôt partir à l\'étranger pour continuer mes études. J\'aimerais bien lire les témoignages et avis des étudiants qui ont déjà fait des études loin de chez eux. Merci de me répondre. Julie » Vous avez fait des études à l\'étranger pendant un an. Vous écrivez une réponse sur le forum. Vous racontez votre séjour à l\'étranger, vous dites si vous avez aimé ou non cette expérience et vous dites pourquoi.',
        'correction' => 'Bonjour Julie,

J\'ai fait des études à l\'étranger pendant un an et je peux te dire que c\'était une expérience très marquante. Au début, ce n\'était pas facile, surtout à cause de la langue et de l\'éloignement de ma famille. Je me sentais parfois seul et un peu perdu.

Avec le temps, j\'ai appris à m\'adapter. J\'ai rencontré des étudiants de différents pays, ce qui m\'a beaucoup ouvert l\'esprit. Les cours étaient intéressants et différents de ceux de mon pays, plus pratiques et plus interactifs. J\'ai aussi appris à être plus autonome et organisé.

J\'ai beaucoup aimé cette expérience, car elle m\'a permis de gagner en confiance et de découvrir une nouvelle culture. Aujourd\'hui, je pense que partir à l\'étranger est une excellente opportunité pour grandir, aussi bien sur le plan personnel que professionnel.

Bon courage pour ton projet !',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'L\'aide aux personnes pauvres.',
        'correction' => 'Les différentes formes d\'aide aux personnes en difficulté

Les deux documents abordent la question de l\'aide aux personnes pauvres. Le premier document insiste sur l\'importance des dons d\'argent et de temps comme geste de solidarité accessible à tous. En revanche, le second document privilégie l\'engagement associatif et l\'aide durable, visant l\'autonomie plutôt qu\'un soutien financier ponctuel.

À mon avis, les deux formes d\'aide sont utiles et complémentaires. Donner de l\'argent peut répondre à des besoins urgents, surtout en hiver, comme se nourrir ou se protéger du froid. Cependant, je pense que l\'engagement dans des associations est plus efficace sur le long terme, car il aide les personnes à retrouver une stabilité grâce au logement et au travail. Aider quelqu\'un à devenir autonome est essentiel pour sortir durablement de la précarité. Selon moi, chacun doit agir selon ses moyens, mais l\'important est de ne pas rester indifférent face à la pauvreté.',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Tous les ans, en hiver, je donne un peu de mon temps et de mon argent pour aider les personnes qui vivent dans la rue et ont froid. Pour moi, c\'est important de se soucier de ceux qui sont dans le besoin. Je trouve très important de donner quelques dollars à ces personnes ou aux associations qui peuvent les aider. Et puis, cette action me donne l\'impression d\'être utile, au moins une fois dans l\'année ! C\'est un geste de solidarité qui est à la portée de tout le monde.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Plutôt que de donner de l\'argent à des personnes pauvres, il vaudrait mieux revoir notre mode de vie. C\'est pourquoi je préfère m\'engager au quotidien dans une association de mon quartier, où je suis bénévole. Les sans domicile fixe ont besoin d\'un logement et d\'un travail et pas seulement d\'argent. Il faut les aider à devenir autonomes. C\'est le but d\'organismes qui aident ceux qui sont dans la rue à trouver un emploi. Donner de l\'argent est un acte inutile qui sert seulement à apaiser notre conscience.',
          ),
        ),
      ),
    ),
  ),
  2 => 
  array (
    'combo' => 3,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Écrivez un courriel à votre ami pour l\'inviter à passer une journée avec vous (lieu, date, activités, etc.).',
        'correction' => 'Objet : Invitation pour une journée ensemble

Salut,

J\'espère que tu vas bien. Je t\'écris pour t\'inviter à passer une journée avec moi le samedi 20 janvier à Montréal. On pourrait commencer par une promenade au Vieux-Port, puis déjeuner dans un petit restaurant du centre-ville. L\'après-midi, je te propose de visiter un musée ou de faire un peu de shopping, selon tes envies. En fin de journée, on pourrait prendre un café et discuter tranquillement.

Dis-moi si tu es disponible, ça me ferait vraiment plaisir de te voir.

À très bientôt,
Ton ami',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous avez participé à une journée de formation dans votre entreprise. Écrivez un courriel à vos collègues pour raconter cette journée et exprimer ce que vous avez apprécié.',
        'correction' => 'Objet : Retour sur la journée de formation

Bonjour à toutes et à tous,

Je voulais prendre un moment pour partager avec vous mon ressenti concernant la journée de formation à laquelle nous avons participé récemment. Globalement, j\'ai trouvé cette journée très enrichissante et bien organisée.

Les thèmes abordés étaient clairs et directement liés à notre travail quotidien. J\'ai particulièrement apprécié les exemples concrets et les études de cas, qui nous ont permis de mieux comprendre certaines situations professionnelles. Les échanges entre collègues ont également été très intéressants, car ils ont favorisé le partage d\'expériences et d\'idées.

De plus, l\'intervenant était dynamique et à l\'écoute, ce qui a rendu les séances interactives et agréables. Cette formation m\'a permis d\'acquérir de nouvelles compétences et de renforcer celles que j\'avais déjà.

En résumé, c\'était une expérience positive et utile. J\'espère que nous aurons d\'autres formations de ce type à l\'avenir.

Bien cordialement,
Ayoub',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Vie en colocation entre adultes.',
        'correction' => 'La colocation entre adultes : avantages et contraintes

Les deux documents traitent de la vie en colocation entre adultes. Le premier document met l\'accent sur la nécessité de respecter des règles, de communiquer et de bien s\'organiser pour éviter les conflits. En revanche, le second document insiste surtout sur les avantages économiques et le confort d\'un logement plus spacieux grâce au partage des frais.

À mon avis, la colocation peut être une bonne solution pour les adultes, surtout d\'un point de vue financier. Elle permet de vivre dans un logement plus grand et mieux situé tout en réduisant les dépenses. Cependant, je pense que ce mode de vie demande beaucoup de tolérance et de communication. Sans règles claires, les conflits peuvent apparaître rapidement. Pour que la colocation fonctionne, il est essentiel de respecter les habitudes des autres et de discuter calmement en cas de problème. Personnellement, je trouve que la colocation est une expérience enrichissante, à condition de choisir des colocataires compatibles et de bien s\'organiser dès le départ.',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Vivre avec d\'autres personnes demande d\'avoir une bonne entente et de respecter certaines règles. Il n\'est pas toujours possible d\'écouter sa musique préférée à volume élevé, d\'inviter tous ses amis pour faire la fête ou de laisser de la vaisselle sale dans la cuisine. Chaque individu a des habitudes susceptibles d\'irriter les autres. C\'est pourquoi il est essentiel d\'établir des règles de vie en communauté et de les respecter mutuellement. Il est important de communiquer avec ses colocataires chaque fois qu\'un problème survient. L\'organisation et la discussion sont les clés d\'une colocation réussie ou non.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Être adulte et vivre en colocation ? C\'est un choix qui permet d\'accéder facilement à un logement plus spacieux et économique. Il est vrai que vous n\'aurez qu\'une chambre pour vous, et que vous devrez partager la cuisine, le salon et la salle de bain. Toutefois, une colocation peut inclure une maison avec jardin ou un grand appartement en centre-ville ! En partageant le loyer et les charges avec vos colocataires, vous réduirez considérablement vos dépenses par rapport à un appartement individuel. Ainsi, même si vous n\'aurez qu\'une chambre à vous et devrez partager les espaces communs, la colocation offre des opportunités de logements bien plus abordables.',
          ),
        ),
      ),
    ),
  ),
  3 => 
  array (
    'combo' => 4,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Votre ami Cédric a accepté de garder votre maison et jardin pendant vos vacances. Écrivez un message pour lui dire ce qu\'il doit faire.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Suite à un voyage récent effectué avec une agence de voyages, vous êtes insatisfait(e) des prestations reçues. Rédigez un courriel de réclamation en exprimant votre mécontentement. Décrivez les problèmes rencontrés et demandez une solution de la part de l\'agence.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'La restauration rapide',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Les restaurants rapides proposent des plats équilibrés et variés, et ils respectent les normes d\'hygiène. Les produits sont bons, et c\'est le client qui compose son menu, donc il en est responsable.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Les spécialistes affirment que manger régulièrement dans des restaurants de fast-food, qui proposent de la restauration rapide, est dangereux pour la santé. La nourriture servie est souvent la même : frites, hamburgers et boissons sucrées. Ces aliments contiennent une grande quantité de calories, bien trop pour un seul repas. De plus, la plupart des produits dans ces restaurants sont emballés dans du plastique. Par conséquent, manger dans un fast-food augmente la production de déchets plastiques, ce qui est nuisible pour l\'environnement.',
          ),
        ),
      ),
    ),
  ),
  4 => 
  array (
    'combo' => 5,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous souhaitez faire du sport et vous voulez que votre ami vous accompagne. Écrivez-lui un message pour lui proposer de pratiquer ensemble.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous avez déjà étudié dans une université à l\'étranger. Écrivez un article sur votre blog pour raconter cette expérience.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'L\'uniforme scolaire : pour ou contre ?',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Le port de l\'uniforme étouffe et écrase la personnalité des garçons. Ils ne peuvent s\'habiller comme ils veulent, en aucune circonstance. De l\'autre côté de l\'échelle (chez les pros), il y a des gens qui préfèrent pour eux-mêmes et leurs enfants avoir la possibilité de s\'exprimer à travers l\'habillement, en décidant eux-mêmes ce qu\'ils porteront chaque jour. Ainsi, avec un uniforme, les jeunes qui aiment s\'exprimer à travers la mode, se démarquer de la foule grâce à un accessoire ou un vêtement particulier, se retrouveront déçus et emprisonnés dans l\'uniforme.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Le port de l\'uniforme développe un sentiment d\'appartenance à son établissement, et à la communauté des élèves. Il nourrit chez le jeune le sens du collectif et engendre souvent la fierté d\'appartenir à son établissement. De plus, il réduit la discrimination basée sur le style ou sur la classe sociale de l\'élève. En effet, l\'uniforme permet aux parents d\'économiser beaucoup d\'argent, ce ne sont pas tous les parents qui peuvent payer à leurs enfants de beaux vêtements griffés ou de marques populaires. La mise en place d\'un code vestimentaire réduit donc les différences entre les classes sociales.',
          ),
        ),
      ),
    ),
  ),
  5 => 
  array (
    'combo' => 6,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous partez en vacances avec vos amis, vous avez trouvé un hôtel. Vous écrivez un message à vos amis pour décrire cet hôtel (localisation, prix, équipements, etc.) et vous leur proposez de réserver cet hôtel.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous êtes parti(e) travailler à l\'étranger. Vous envoyez un message à vos amis pour raconter cette nouvelle expérience professionnelle. Vous expliquez ce que vous avez le plus aimé.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Les jeux vidéo, pour ou contre ?',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Des études ont montré que certaines zones du cerveau de l\'adulte peuvent être développées en jouant aux jeux vidéo. Ainsi, il peut être intéressant de jouer aux jeux vidéo, car ils permettent, par exemple, d\'améliorer la capacité d\'analyse, la capacité à faire des choix et la rapidité de réaction. C\'est une bonne nouvelle car la répartition des joueurs par âge montre que 83 % des joueurs sont des adultes. Cependant, il convient de rester prudent car certains jeux vidéo ne favorisent pas ce type d\'amélioration sur le cerveau.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Pendant trois ans, des enfants âgés de 8 à 17 ans ont participé à une étude sur les jeux vidéo. Les résultats de cette étude montrent que les enfants qui jouent beaucoup aux jeux vidéo sont plus violents, plus nerveux et plus stressés que ceux qui ne jouent pas ou peu. Ceux qui jouent beaucoup ont également moins de bons résultats à l\'école. Il est donc conseillé aux parents d\'être vigilants et de limiter l\'usage de ces jeux par leurs enfants.',
          ),
        ),
      ),
    ),
  ),
  6 => 
  array (
    'combo' => 7,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Écrivez un message dans le journal de votre université pour rechercher un partenaire avec qui faire du sport.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Écrivez un article de blog pour raconter votre arrivée dans un pays étranger en donnant vos impressions.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Rôle de la télévision dans l\'éducation des enfants.',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'La télévision est un outil de communication et de divertissement largement répandu dans notre société moderne. Son influence est incontestable, tant sur les individus que sur la culture en général. Elle permet de diffuser des informations, d\'offrir des divertissements variés et de favoriser la diffusion de la culture. La télévision est présente dans de nombreux foyers et constitue une source d\'information et de divertissement accessibles à tous. Grâce à sa portée et à sa capacité à toucher un large public, la télévision joue un rôle important dans la transmission des connaissances et la sensibilisation aux enjeux sociaux.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'La télévision peut également présenter certains inconvénients. Les émissions télévisées peuvent parfois véhiculer des stéréotypes, des préjugés et des valeurs discutables. De plus, le temps passé devant la télévision peut réduire le temps consacré à d\'autres activités plus enrichissantes, telles que la lecture, les interactions sociales ou la pratique d\'un sport. Il est important de faire preuve de discernement et de réguler l\'exposition à la télévision, en particulier pour les enfants, afin de préserver un équilibre sain entre les différentes formes d\'apprentissage et de divertissement.',
          ),
        ),
        'correction' => '',
      ),
    ),
  ),
  7 => 
  array (
    'combo' => 8,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Écrivez un message à votre ami(e) qui souhaite suivre des cours de langue dans votre école. Donnez les détails spécifiques pour aider votre ami(e) à faire son choix. (lieu, tarifs, types de cours disponibles, etc.).',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous travaillez dans une association qui aide les personnes âgées. Rédigez un article de blog pour raconter vos expériences et convaincre d\'autres personnes de rejoindre l\'association.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Les animaux de compagnie pour les enfants : pour ou contre ?',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Offrir un animal de compagnie à un enfant présente de nombreux avantages, comme le soulignent beaucoup de psychologues. Pour des enfants qui n\'ont pas de frères et/ou de soeurs, l\'animal est un compagnon qui leur évitera la solitude. Grâce à lui, un enfant prendra confiance en lui et il apprendra vite qu\'un animal est un être vivant qui a besoin d\'attention et de respect. En sa présence, l\'enfant se sentira en sécurité et pourra agir de manière autonome, sans l\'aide de ses parents.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Beaucoup d\'enfants demandent, un jour ou l\'autre, un animal à leurs parents, le plus souvent un chien ou un chat. Mais même si vous avez envie de faire plaisir à votre enfant, il vaut mieux réfléchir sérieusement avant d\'acheter un animal domestique. L\'animal devient un nouveau membre de la famille et représente un engagement sur de nombreuses années. Or, avoir un animal coûte souvent très cher, et c\'est une grande responsabilité. On ne peut pas le traiter comme un jouet que l\'on met à la poubelle quand l\'enfant s\'en désintéresse.',
          ),
        ),
      ),
    ),
  ),
  8 => 
  array (
    'combo' => 9,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Écrivez un message pour inviter vos amis à une fête de fin d\'année.',
        'correction' => 'Objet : Invitation à la fête de fin d\'année

Salut les amis,

J\'espère que vous allez bien. Je vous invite à ma fête de fin d\'année le samedi 28 décembre à partir de 19 h, chez moi, au 15 rue des Lilas à Lyon. Ce sera une soirée simple et sympa pour passer un bon moment ensemble avant la nouvelle année. Au programme : musique, jeux, dîner, boissons et quelques surprises. Chacun peut apporter un petit plat, un dessert ou une boisson à partager. Vous pouvez aussi venir avec votre bonne humeur et vos idées pour animer la soirée. Merci de me confirmer votre présence avant le 24 décembre pour mieux organiser la fête.

À bientôt,

AYOUB',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous avez passé des vacances au Canada par le biais d\'une agence de voyage. Écrivez un commentaire pour raconter votre expérience que vous avez vécue durant ce voyage.',
        'correction' => 'Mes vacances au Canada avec une agence de voyage

Bonjour à tous,

L\'été dernier, j\'ai passé dix jours au Canada avec ma sœur grâce à une agence de voyage. Nous avons commencé notre séjour à Montréal, où l\'ambiance était très chaleureuse et les rues étaient pleines de musique. Le programme était bien organisé, ce qui nous a permis de visiter le Vieux-Montréal, le parc du Mont-Royal et plusieurs petits cafés très agréables.

Ensuite, nous sommes allés à Québec. J\'ai adoré cette ville parce qu\'elle était calme, propre et vraiment magnifique. Nous avons marché dans les rues historiques, pris beaucoup de photos et goûté des spécialités locales. Le meilleur moment a été notre excursion aux chutes Montmorency. Le paysage était impressionnant, et j\'ai ressenti beaucoup de joie et d\'émerveillement.

Dans l\'ensemble, j\'ai vécu une expérience inoubliable. L\'agence a bien préparé le voyage, même si les journées étaient parfois un peu trop chargées. Je recommande vraiment cette expérience.

À bientôt,

AYOUB',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Limitation des voitures dans les centres-villes.',
        'correction' => 'La place de la voiture en centre-ville

La limitation des voitures dans les centres-villes suscite un vif débat. Le premier document souligne les effets positifs de cette mesure, comme la baisse des accidents, de la pollution et de la dépendance au pétrole. Cependant, le second rappelle qu\'une telle transition exige des infrastructures adaptées et des exceptions pour certains professionnels.

À mon avis, limiter les voitures en centre-ville est une bonne décision, à condition de bien préparer ce changement. D\'abord, cette mesure améliore la santé publique, car un air moins pollué réduit les problèmes respiratoires. Ensuite, elle rend la ville plus agréable et plus sûre pour les piétons et les cyclistes. De plus, elle encourage l\'utilisation des transports en commun, ce qui peut diminuer les embouteillages. Par exemple, dans certaines villes européennes, des rues devenues piétonnes attirent davantage de familles et de commerces. Enfin, cette politique ne peut réussir que si les autorités créent des parkings relais, renforcent les bus et prévoient des accès pour les services essentiels.',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Avec des taux de pollution alarmants constatés dans plusieurs endroits dans le monde, plusieurs villes ont réussi leur pari d\'interdire la circulation des voitures en zone urbaine. La capitale de Norvège, Oslo, a récemment opté pour cette solution et s\'en félicite, estimant que c\'est une décision bénéfique pour tout le monde. Après un certain temps, les accidents diminueront, la dépendance au pétrole baissera et la qualité d\'air sera meilleure !',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Beaucoup de villes se lancent dans des projets d\'interdiction de voitures en zone urbaine sans mettre en place les outils et les infrastructures nécessaires pour réussir cette transition. Certes, en diminuant les voitures, on aura moins pollué, mais en contrepartie, il faut prévoir entre autres de gigantesques parkings pour garer les voitures, opter davantage pour le transport en commun (métros et bus) et prévoir des autorisations de circulation pour certains corps de métier (comme la police, les urgentistes, les livreurs, etc.).',
          ),
        ),
      ),
    ),
  ),
  9 => 
  array (
    'combo' => 10,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous préparez la célébration de votre anniversaire. Vous écrivez à vos amis pour les inviter. Vous leur présentez le déroulement de la soirée et leur demandez de participer à l\'organisation.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Vous avez pris part à une compétition culinaire. Sur votre site web, vous rédigez un petit article pour décrire le déroulement de la journée. Vous précisez les raisons pour lesquelles cette expérience vous a plu ou non.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Étudiants et travail saisonnier',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Après une longue période à l\'université, les vacances permettent aux jeunes de se détendre. Ils partent en voyage et profitent de l\'été. Pourtant, certains étudiants préfèrent travailler au lieu de partir en vacances : garde d\'enfants, service en restauration ou cueillette de fruits. Bien que ces emplois saisonniers aient des points positifs, ils limitent le temps libre des jeunes, qui manquent de repos, de loisirs et de moments en famille ou entre amis. De plus, ces travaux sont parfois difficiles, ennuyeux ou mal payés.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Pour quelles raisons de nombreux étudiants travaillent-ils durant les vacances ? Cette expérience représente souvent une entrée dans la vie professionnelle : elle leur permet de développer le sens des responsabilités et de connaître un métier. Gagner de l\'argent est aussi motivant. Certains étudiants travaillent pour ne plus dépendre financièrement de leurs parents et pour financer des loisirs ou des voyages. Pour d\'autres, travailler l\'été est indispensable pour payer les frais d\'études ou le loyer.',
          ),
        ),
      ),
    ),
  ),
  10 => 
  array (
    'combo' => 11,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous voulez partir en week-end avec vos amis le mois prochain. Vous leur écrivez un message pour décrire votre projet (lieu, transport, activités, etc.).',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'COURRIER DES LECTEURS Tout quitter pour partir en voyage pendant un an: bonne ou mauvaise idée ? Répondez sur notre site Internet : "voyage.internaute.fr". Vous écrivez un message sur ce site internet, vous répondez à la question posée en prenant des exemples de votre vie personnelle.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Le travail : Favorable ou Défavorable ?',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Le travail est au centre de notre vie. Dès l\'enfance, on entend souvent la question : « Qu\'est-ce que tu veux faire quand tu seras grand ? ». Le travail devrait être synonyme de réussite et de satisfaction, mais il est trop souvent synonyme de fatigue et d\'emprisonnement. Aujourd\'hui, beaucoup pensent que l\'on ne passe pas assez de temps avec sa famille, ses amis. Il est urgent de revoir la place occupée par le travail dans notre société. Certains pensent que travailler moins permettrait d\'avoir plus de temps libre pour mieux vivre.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Certaines personnes ont décidé d\'arrêter de travailler pour changer de mode de vie. Pourtant, aujourd\'hui, travailler, c\'est exister. La question : « Qu\'est-ce que tu fais dans la vie ? » revient souvent lors d\'une première rencontre. Elle prouve que l\'emploi fait partie de notre identité. D\'après le spécialiste Jean-Daniel Remond, la vie en entreprise est très importante. Les contacts quotidiens, les réseaux, les amitiés, l\'impression d\'être utile, mais aussi les difficultés, tout cela contribue à construire notre personnalité et notre identité.',
          ),
        ),
      ),
    ),
  ),
  11 => 
  array (
    'combo' => 12,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous partez en voyage et vous laissez votre appartement à un ami qui veut venir rester chez-vous pendant vos vacances. Vous lui envoyez un message pour décrire votre appartement (immeuble, logement, accès…).',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Écrivez un article de blog sur le souvenir de voyage que vous avez le plus aimé.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Vivre chez ses parents, pour ou contre ?',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Les avantages : penser à l\'avenir, avoir les vêtements propres, être en confort surtout que l\'argent n\'est pas tjrs suffisant, vivre avec ses parents pendant la période des études permet aux jeunes d\'économiser les frais de logement, des plats faits maison et une certaine stabilité psychique, tous les adolescents qui vivent avec leurs parents leur permettent d\'économiser leur argent pour des projets de vie (ils ne payent ni le loyer ni la nourriture) (pour ne pas payer le loyer qui coûte cher, le ménage est fait, les vêtements sont propres).',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Les inconvénients : manque de liberté, vivre seul permet d\'être indépendant. Les adolescents qui vivent avec leurs parents s\'ennuient car leurs parents décident à leur place et restent dépendants. Témoignage d\'un jeune de 25 ans qui a perdu son emploi et a dû revenir chez ses parents : il a perdu son espace d\'intimité. Pour certains, revenir chez ses parents, c\'est revenir en arrière. Vivre seul permet de prendre ses propres décisions, de gérer son budget et de construire sa vie comme on l\'entend. Même si cela demande plus d\'efforts financiers, beaucoup de jeunes préfèrent cette autonomie pour se sentir adultes et responsables.',
          ),
        ),
      ),
    ),
  ),
  12 => 
  array (
    'combo' => 13,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Vous faites du sport dans un club. Vous venez de remporter une compétition. Vous écrivez un courriel à vos amis pour leur raconter cet évènement sportif et annoncer votre réussite sportive.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Le site « colocation.com » recherche des témoignages sur vos expériences de colocation. Vous avez déjà habité en colocation avec des amis. Vous racontez votre expérience aux membres du site internet. Vous donnez votre opinion sur ce mode de logement.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Le grossissement des villes',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'De nos jours, les villes grossissent toujours plus. Malheureusement, ce phénomène a un impact fort sur l\'environnement. Car plus une ville grossit, plus elle a des effets négatifs sur la nature et donc, ensuite, sur l\'homme. L\'effet négatif le plus visible est la déforestation, qui réduit les espaces verts capables de retenir le carbone.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Plus de la moitié de l\'humanité vit en ville ; la vie urbaine est donc le principal enjeu écologique. On entend souvent dire que l\'organisation actuelle des villes n\'est pas écologique, et que le grossissement des villes ne fait qu\'augmenter le problème. Pourtant, il faut se méfier des apparences : les villes ne sont pas toujours aussi antiécologiques qu\'on l\'imagine. Par exemple, la consommation d\'énergie d\'un citadin est moins importante que celle d\'un habitant de la campagne.',
          ),
        ),
      ),
    ),
  ),
  13 => 
  array (
    'combo' => 14,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Analysez le sujet d\'examen suivant : Vous souhaitez fêter votre anniversaire dans un restaurant. Vous invitez vos amis. Vous leur écrivez un courriel pour leur donner toutes les informations nécessaires (lieu, date, horaires, menus, prix) et vous leur demandez une réponse.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Un internaute a publié le message suivant : « Je vais partir étudier un an à l\'étranger et j\'ai peur ». Rédigez une réponse pour partager votre expérience personnelle. Parlez des défis que vous avez rencontrés, des solutions que vous avez trouvées, et des bénéfices que vous avez tirés de cette expérience.',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'Utilisation des nouvelles technologies dans les écoles : pour ou contre ?',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Jean : Je suis fermement convaincu que l\'intégration des nouvelles technologies dans les écoles est cruciale pour préparer les élèves à un avenir numérique. Je pense que l\'usage des tablettes et des ordinateurs stimule non seulement l\'engagement des élèves mais enrichit également leur expérience éducative en leur offrant un accès facile à une variété de ressources, encourageant ainsi leur créativité et autonomie.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Sara : Je suis sceptique quant à l\'usage intensif des technologies dans l\'enseignement. Je crois que cela peut réduire les interactions humaines essentielles et favoriser une dépendance préoccupante aux écrans. À mon avis, les méthodes d\'enseignement traditionnelles et le contact direct entre enseignants et élèves restent indispensables pour un développement équilibré et complet des compétences des jeunes.',
          ),
        ),
        'correction' => '',
      ),
    ),
  ),
  14 => 
  array (
    'combo' => 16,
    'tasks' => 
    array (
      0 => 
      array (
        'task' => 1,
        'prompt' => 'Envoyez un message à vos amis pour les inviter à passer un week-end chez vous et à pratiquer ensemble des activités sportives.',
        'correction' => '',
      ),
      1 => 
      array (
        'task' => 2,
        'prompt' => 'Écrivez un article sur votre blog pour raconter pourquoi vous avez décidé de changer votre alimentation (vos habitudes alimentaires). Écrivez un article sur votre blog pour raconter pourquoi vous avez décidé de changer votre alimentation (vos habitudes alimentaires).',
        'correction' => '',
      ),
      2 => 
      array (
        'task' => 3,
        'prompt' => 'La sieste au travail.',
        'correction' => '',
        'documents' => 
        array (
          0 => 
          array (
            'title' => 'Document 1',
            'content' => 'Faire une sieste au travail présente de nombreux bénéfices pour les salariés et les entreprises. Permettre aux employés de se reposer quelques minutes peut accroître leur productivité et améliorer leur santé et leur bien-être. Les courtes siestes renforcent la concentration, diminuent le stress et améliorent l\'humeur. Elles peuvent aussi réduire les coûts liés à la fatigue et aux accidents. Les entreprises devraient donc envisager d\'intégrer cette pratique pour leur personnel.',
          ),
          1 => 
          array (
            'title' => 'Document 2',
            'content' => 'Même si la sieste au travail présente de nombreux bénéfices, il n\'est pas facile pour toutes les entreprises d\'aménager des espaces ou des lits pour cela. Les contraintes financières, logistiques ou les règles strictes peuvent empêcher les salariés de se reposer. De plus, certains employés peuvent se sentir mal à l\'aise. Il est donc important que les entreprises évaluent avantages et limites et trouvent des solutions pour favoriser le repos.',
          ),
        ),
      ),
    ),
  ),
];
