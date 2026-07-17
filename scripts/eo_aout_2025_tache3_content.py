# -*- coding: utf-8 -*-
"""Contenu Tâche 3 — Expression orale Août 2025 (importé par build_eo_aout_2025_json.py)"""


def sub(title, prompt, correction, icon="bx bx-message-detail"):
    return {
        "title": title,
        "prompt": prompt.strip(),
        "correction": correction.strip(),
        "icon_class": icon,
    }


def part(task_key, part_num, subjects):
    return {
        "task_key": task_key,
        "part_number": part_num,
        "part_title": f"Partie {part_num}",
        "subjects": subjects,
    }


TACHE3 = [
    part("tache3", 1, [
        sub(
            "Diplôme et réussite professionnelle",
            "La réussite professionnelle dépend du niveau d'étude et du diplôme obtenu. Qu'en pensez-vous ?",
            """La relation entre niveau d'étude, diplôme obtenu et réussite professionnelle
La question de savoir si la réussite professionnelle est étroitement liée au niveau d'étude et au diplôme obtenu suscite un vif débat. L'éducation est traditionnellement perçue comme un levier essentiel pour l'ascension sociale et la sécurité de l'emploi. Toutefois, la réalité du monde professionnel moderne, avec ses changements rapides et ses exigences diversifiées, complexifie cette relation. Nous examinerons ici les différentes perspectives et les données actuelles sur ce sujet.

L'Importance des Diplômes
Historiquement, un niveau d'étude élevé, sanctionné par un diplôme, ouvre la porte à des emplois mieux rémunérés et plus stables. Les statistiques montrent souvent que les taux de chômage sont inversement proportionnels au niveau d'études : plus le niveau d'éducation est élevé, plus le taux de chômage est faible. Par exemple, un diplôme universitaire peut être un prérequis pour accéder à certaines professions réglementées comme la médecine, le droit ou l'ingénierie. Dans ces cas, le diplôme n'est pas simplement un avantage, mais une nécessité absolue.

Compétences et Adaptabilité
Cependant, dans de nombreux secteurs, les compétences pratiques et l'adaptabilité peuvent s'avérer aussi importantes, sinon plus, que les qualifications formelles. Avec l'avènement de la technologie et la transformation digitale des entreprises, de nombreux emplois exigent des compétences qui ne sont pas toujours couvertes par les cursus traditionnels. Par exemple, dans les domaines de la technologie de l'information, du marketing digital et du design, l'expérience pratique et la capacité à apprendre de manière autonome sont souvent plus valorisées que le diplôme lui-même.

La Valeur des Expériences Non Traditionnelles
L'essor de l'économie gig et des carrières entrepreneuriales offre des perspectives où le niveau d'étude traditionnel peut être moins déterminant. Des figures de succès dans la technologie et les affaires, telles que Steve Jobs ou Mark Zuckerberg, bien que des cas atypiques, illustrent que des parcours non conventionnels peuvent également mener à d'énormes succès.

Éducation et Équité
Il est essentiel de reconnaître que l'accès à l'éducation reste inégalement réparti. Des études montrent que les individus issus de milieux défavorisés ont moins accès à l'éducation supérieure, ce qui peut limiter leurs opportunités professionnelles. L'éducation peut fonctionner à la fois comme un moteur d'égalité sociale quand elle est accessible à tous, et un facteur de perpétuation des inégalités quand elle ne l'est pas.

Conclusion
En conclusion, si le niveau d'étude et le diplôme obtenu restent des facteurs importants de réussite professionnelle, ils ne sont pas les seuls déterminants. La réussite professionnelle dépend également de la capacité à s'adapter aux changements, d'acquérir des compétences en réponse aux demandes du marché et de saisir les opportunités qui se présentent, parfois en dehors des chemins éducatifs traditionnels. L'équilibre entre éducation formelle et compétences pratiques semble être la clé pour naviguer avec succès dans le paysage professionnel contemporain.""",
            "bx bx-graduation",
        ),
        sub(
            "Travail des personnes âgées",
            "Le travail des personnes âgées est-il une bonne chose pour la société ?",
            """Le travail des personnes âgées est un sujet complexe qui recouvre des dimensions économiques, sociales et psychologiques importantes. Analysons les aspects positifs et les défis associés à la participation des seniors au marché du travail, afin de déterminer si c'est une bonne chose pour la société.

Avantages du travail des personnes âgées
Transfert de connaissances et de compétences
Les travailleurs âgés apportent une richesse de connaissances et d'expérience acquises tout au long de leur carrière. Leur présence dans le milieu de travail peut faciliter le transfert de savoir-faire et de compétences intergénérationnel, enrichissant ainsi la main-d'œuvre.

Diversité et innovation
Une main-d'œuvre diversifiée, incluant des personnes de différents âges, peut encourager l'innovation et la créativité. Les perspectives variées des employés âgés peuvent aider à résoudre des problèmes de manière unique et enrichir la prise de décision.

Santé mentale et bien-être
Travailler peut aider les personnes âgées à maintenir une bonne santé mentale, à se sentir valorisées et à rester socialement connectées. Le travail peut également offrir un but et une routine, éléments cruciaux pour le bien-être psychologique.

Impact économique
L'emploi des seniors peut avoir un impact positif sur l'économie. En travaillant, les personnes âgées continuent de contribuer à l'économie par leur consommation et leurs impôts, tout en réduisant la pression sur les systèmes de retraite et d'assistance sociale.

Défis associés au travail des personnes âgées
Santé physique
Les défis physiques liés à l'âge peuvent rendre le travail difficile, surtout dans les emplois exigeant de l'effort physique. Il est donc crucial que les employeurs adaptent les postes de travail pour répondre aux besoins spécifiques des employés âgés.

Barrières à l'emploi
Les préjugés et les discriminations liés à l'âge peuvent limiter les opportunités d'emploi pour les seniors. La société doit travailler à éliminer ces obstacles pour permettre une réelle égalité des chances sur le marché du travail.

Équilibre avec les jeunes générations
Il est essentiel de maintenir un équilibre entre maintenir les seniors dans la main-d'œuvre et ouvrir des opportunités pour les jeunes. Un équilibre doit être trouvé pour éviter de compromettre les perspectives d'emploi des plus jeunes.

Conclusion
En somme, le travail des personnes âgées peut être très bénéfique pour la société, à condition qu'il soit bien encadré. Il favorise le transfert de compétences, améliore la diversité et stimule l'économie tout en soutenant la santé mentale et le bien-être des seniors. Pour maximiser ces avantages, il est essentiel d'adapter les environnements de travail, de combattre la discrimination liée à l'âge, et de veiller à un équilibre harmonieux entre les générations sur le marché du travail. Ainsi, la participation des personnes âgées au travail peut être vue non seulement comme une nécessité économique mais aussi comme une contribution précieuse à une société plus inclusive et dynamique.""",
            "bx bx-user-voice",
        ),
        sub(
            "Engagement associatif",
            "Pendant leur temps libre, certaines personnes s'engagent dans des associations pour aider les autres. Qu'en pensez-vous ?",
            """L'engagement bénévole dans des associations pour aider les autres pendant le temps libre est largement reconnu comme bénéfique, tant pour les individus que pour la société dans son ensemble. Voici quelques réflexions sur les impacts positifs de ce type d'activités, ainsi que sur quelques considérations pratiques.

Avantages pour les individus
Développement personnel
S'engager bénévolement permet aux individus de développer de nouvelles compétences, comme la gestion de projet, la communication, et le leadership. Ces compétences peuvent être transférables à leur vie professionnelle et personnelle, enrichissant ainsi leur expérience et leur CV.

Bien-être psychologique
Le bénévolat est souvent associé à une amélioration du bien-être mental. Aider les autres peut procurer un sentiment d'accomplissement et de satisfaction, réduire le stress et combattre la dépression. Des études montrent que l'engagement communautaire peut même avoir des effets positifs sur la santé physique, notamment en diminuant la pression artérielle et en prolongeant la durée de vie.

Renforcement des relations sociales
Participer à des activités associatives permet de rencontrer des personnes partageant les mêmes intérêts et valeurs, ce qui peut conduire à des amitiés durables. Ces interactions sociales renforcent le sentiment d'appartenance à une communauté et peuvent aider à combattre la solitude.

Avantages pour la société
Renforcement de la cohésion sociale
L'engagement bénévole renforce les liens communautaires et favorise l'inclusion sociale. En rassemblant des personnes de différents milieux, les associations aident à promouvoir la compréhension mutuelle et la solidarité entre les citoyens.

Contribution économique
Bien que leur travail ne soit pas rémunéré, les bénévoles apportent une contribution économique considérable. Leur travail soutient de nombreux services et initiatives qui autrement nécessiteraient un financement significatif de la part des pouvoirs publics.

Innovation sociale
Les associations jouent souvent un rôle de pionnier dans le traitement de problèmes sociaux nouveaux ou négligés. L'engagement des citoyens dans ces organisations peut conduire à des solutions innovantes qui inspirent des politiques publiques et des pratiques commerciales.

Considérations pratiques
Équilibre avec la vie personnelle
Il est important que les bénévoles gèrent leur temps efficacement pour ne pas se surmener. Un engagement trop intense peut conduire à l'épuisement et affecter d'autres aspects de la vie.

Choix de l'association
Il est crucial de choisir une association dont la mission et les activités correspondent aux passions et valeurs de l'individu. Un alignement fort augmente la satisfaction et l'impact de l'engagement.

Conclusion
L'engagement dans des associations pour aider les autres pendant le temps libre est non seulement bénéfique pour les individus en termes de développement personnel, de bien-être, et de construction de relations, mais il joue également un rôle crucial dans le renforcement de la cohésion sociale et l'innovation. Toutefois, il est essentiel d'approcher le bénévolat avec un esprit de balance pour en tirer pleinement parti sans compromettre le bien-être personnel.""",
            "bx bx-group",
        ),
        sub(
            "Théâtre ou cinéma",
            "Préférez-vous regarder des pièces de théâtre ou des films ? Expliquez pourquoi.",
            """Les pièces de théâtre et les films offrent des expériences distinctes et appréciées pour différentes raisons. Voici quelques points de comparaison :

Pièces de théâtre
Interactivité et Immédiateté : Le théâtre se caractérise par sa nature « live », offrant une connexion immédiate et souvent interactive entre les acteurs et le public. Cette dynamique crée une expérience unique à chaque représentation.

Expression Artistique : Le théâtre permet souvent une expression artistique plus raw et authentique, où les performances peuvent être plus nuancées et chargées d'émotion en raison de la présence physique des acteurs sur scène.

Simplicité de la Production : Contrairement aux films, le théâtre repose moins sur des effets spéciaux et plus sur le dialogue, le jeu d'acteur et la mise en scène pour raconter une histoire.

Films
Portée Visuelle : Les films peuvent offrir des spectacles visuels époustouflants grâce aux technologies modernes de cinématographie et d'effets spéciaux. Ils peuvent transporter le spectateur dans des mondes complètement différents.

Accessibilité : Les films sont généralement plus accessibles au grand public, disponibles dans les salles de cinéma, à la télévision ou en streaming. Cette facilité d'accès permet de toucher une audience beaucoup plus large.

Diversité des Genres : Le cinéma explore une vaste gamme de genres et de styles, allant des documentaires aux blockbusters, offrant ainsi une variété de choix pour tous les goûts.""",
            "bx bx-movie-play",
        ),
        sub(
            "Âge idéal pour immigrer",
            "Selon vous, quel est l'âge idéal pour immigrer ? Pourquoi ?",
            """Choisir l'âge idéal pour émigrer peut dépendre de plusieurs facteurs, et cela varie en fonction des objectifs personnels, des circonstances de vie, et des opportunités disponibles. Voici quelques éléments à considérer pour différents âges :

Jeunes Adultes (20-30 ans)
Avantages :
Flexibilité : Les jeunes adultes ont souvent moins d'attaches familiales ou professionnelles, ce qui leur offre une plus grande flexibilité pour s'adapter à un nouvel environnement et prendre des risques.
Éducation et carrière : Émigrer à cet âge peut être idéal pour poursuivre des études supérieures ou commencer une carrière à l'étranger, profitant ainsi de meilleures opportunités ou de formations spécifiques non disponibles dans leur pays d'origine.
Considérations :
Stabilité financière : Les jeunes adultes peuvent ne pas avoir encore acquis une stabilité financière, ce qui peut rendre l'émigration plus risquée ou difficile sans le soutien de bourses d'études ou d'emploi sécurisé.

Âge moyen (30-50 ans)
Avantages :
Expérience professionnelle : À cet âge, beaucoup ont accumulé une expérience professionnelle précieuse, ce qui peut faciliter l'obtention de visas de travail et améliorer les perspectives d'emploi à l'étranger.
Ressources financières : Les individus sont plus susceptibles d'avoir des économies ou des ressources financières pour soutenir leur déménagement et leur intégration dans un nouveau pays.
Considérations :
Famille : Émigrer avec une famille peut impliquer des défis supplémentaires, comme l'éducation des enfants et le soutien du conjoint dans sa carrière et son adaptation sociale.

Âge mûr (50 ans et plus)
Avantages :
Retraite : Émigrer après la retraite peut être attrayant pour ceux qui cherchent un meilleur climat, un coût de la vie moins élevé, ou de nouvelles expériences culturelles.
Expérience de vie : Avec une richesse d'expériences de vie, les immigrants plus âgés peuvent mieux naviguer dans les défis de l'adaptation culturelle.
Considérations :
Santé et bien-être : Les considérations de santé deviennent plus importantes, et l'accès à des soins de santé de qualité dans le pays d'accueil est crucial.
Réseau social : Développer un nouveau réseau social peut être plus difficile à cet âge, mais pas impossible.

Conclusion
Il n'y a pas d'âge « idéal » universel pour émigrer; cela dépend largement des motivations personnelles, des circonstances de vie, et des buts à long terme. Chaque tranche d'âge offre des avantages uniques et des défis à considérer. La décision doit être prise sur la base d'une évaluation approfondie de ces facteurs et des opportunités disponibles dans le pays d'accueil.""",
            "bx bx-world",
        ),
    ]),
    part("tache3", 2, [
        sub(
            "Intégration des nouveaux employés",
            "Comment les entreprises peuvent-elles aider les nouveaux employés à bien s'intégrer ?",
            """Eh bien, je pense que l'intégration des nouveaux employés est une étape cruciale pour la réussite d'une entreprise. Quand une personne commence un nouveau poste, elle se retrouve souvent un peu perdue : nouveaux collègues, nouvelle culture, nouveaux outils… Si l'entreprise n'apporte pas de soutien, le risque est que l'employé se sente isolé et perde rapidement sa motivation. À mon avis, il y a plusieurs moyens concrets pour faciliter cette intégration.

D'abord, je dirais qu'un programme d'accueil structuré est essentiel. Cela peut prendre la forme d'une journée d'orientation où l'on présente l'entreprise, ses valeurs, ses objectifs et ses règles de fonctionnement. Ce premier contact officiel donne aux employés le sentiment d'appartenir à un collectif et réduit leur stress.

Ensuite, je trouve très utile de mettre en place un système de parrainage ou de mentorat. Un employé expérimenté accompagne le nouveau pendant les premières semaines. Cela permet d'avoir un repère, une personne à qui poser toutes les questions pratiques, même celles qui paraissent « bêtes ». Cette méthode favorise aussi les relations humaines et crée des liens de confiance.

Par ailleurs, l'entreprise doit encourager la communication et la convivialité. Par exemple, organiser un déjeuner d'équipe, une activité collective ou même des petites pauses informelles aide beaucoup à briser la glace. Souvent, ce n'est pas seulement le travail technique qui compte, mais le sentiment d'être accepté par l'équipe.

Il y a aussi la question de la formation. Les nouveaux employés doivent avoir accès à des ressources claires pour comprendre leurs missions, les logiciels utilisés et les processus internes. Sans formation, l'employé risque de commettre des erreurs et de perdre confiance.

Enfin, je crois qu'il est très important que les managers fassent preuve de bienveillance et de disponibilité. Un manager qui prend le temps de demander régulièrement « comment ça se passe ? » montre qu'il se soucie du bien-être du nouvel arrivant. Ce soutien moral est parfois aussi important que l'accompagnement technique.

En conclusion, je dirais que les entreprises peuvent aider leurs nouveaux employés à s'intégrer en combinant plusieurs actions : orientation, mentorat, convivialité, formation et soutien managérial. Une bonne intégration n'est pas un luxe : c'est un investissement qui permet à l'entreprise de garder des collaborateurs motivés et fidèles.""",
            "bx bx-building",
        ),
        sub(
            "Vivre dans plusieurs pays",
            "Selon vous, vivre dans plusieurs pays offre-t-il un meilleur avenir professionnel ?",
            """À mon avis, vivre dans plusieurs pays est une véritable richesse, surtout sur le plan professionnel. Aujourd'hui, le monde du travail est de plus en plus internationalisé, et les entreprises recherchent des profils capables de s'adapter, de parler plusieurs langues et de comprendre différentes cultures.

D'abord, vivre dans plusieurs pays permet de développer une ouverture d'esprit exceptionnelle. On apprend à travailler avec des personnes qui n'ont pas la même manière de penser ou d'agir. Par exemple, la ponctualité est primordiale dans certains pays, alors que dans d'autres, la flexibilité est davantage tolérée. Cette capacité d'adaptation est très appréciée dans le monde professionnel.

Ensuite, cela permet d'acquérir des compétences linguistiques. Parler plusieurs langues est un énorme avantage sur le marché du travail. Un employé qui maîtrise le français, l'anglais et peut-être une troisième langue, comme l'espagnol ou le chinois, a beaucoup plus d'opportunités dans les grandes entreprises internationales.

Par ailleurs, vivre à l'étranger développe la confiance en soi. Quand on s'installe dans un pays inconnu, il faut se débrouiller, comprendre les codes, surmonter les difficultés administratives et sociales. Cette expérience forge le caractère et prouve aux employeurs que la personne est capable de relever des défis.

Cependant, il ne faut pas oublier les limites. Vivre dans plusieurs pays peut aussi représenter des sacrifices : éloignement de la famille, difficulté à créer des relations stables, ou encore problèmes d'adaptation culturelle. Tout le monde n'est pas prêt à ces changements.

Pour ma part, je pense que le bilan reste très positif. Vivre dans plusieurs pays offre effectivement un meilleur avenir professionnel, car cela apporte non seulement des compétences pratiques, comme les langues et la flexibilité, mais aussi une richesse humaine que l'on ne peut pas acquérir autrement.""",
            "bx bx-globe",
        ),
        sub(
            "Matière préférée à l'école",
            "Quelle matière aimiez-vous le plus à l'école ? Pourquoi ?",
            """Quand j'étais à l'école, la matière que j'aimais le plus était sans hésiter le français. Pourquoi ? Parce que c'était une matière qui me permettait d'exprimer ma créativité et de réfléchir en profondeur sur des sujets variés.

En français, il ne s'agissait pas seulement d'apprendre des règles de grammaire, mais aussi de lire des textes littéraires, de découvrir des auteurs et de développer un esprit critique. J'adorais particulièrement les cours où l'on devait rédiger des dissertations ou des commentaires de texte. Cela me donnait l'occasion de structurer mes idées et de défendre un point de vue, ce qui est une compétence très utile dans la vie professionnelle.

Le français, c'était aussi la porte vers la culture : lire Molière, Victor Hugo ou Camus, c'était comme voyager dans d'autres époques et comprendre d'autres visions du monde. Cela m'a appris à mieux communiquer et à apprécier la richesse de la langue.

Bien sûr, j'aimais aussi les mathématiques pour leur logique, mais ce qui me passionnait dans le français, c'était la liberté de penser et d'écrire. À travers les mots, on peut exprimer ses émotions, ses opinions et même influencer les autres.

En résumé, le français était ma matière préférée à l'école, parce qu'elle m'a permis à la fois d'apprendre, de m'exprimer et de grandir intellectuellement.""",
            "bx bx-book-open",
        ),
        sub(
            "Matières à enseigner davantage",
            "Quelles matières devraient être davantage enseignées à l'école ? Pourquoi ?",
            """À mon avis, le système éducatif devrait évoluer pour mieux préparer les jeunes au monde actuel. Certaines matières traditionnelles restent indispensables, comme les mathématiques, les langues ou l'histoire. Mais je pense qu'il faudrait accorder beaucoup plus de place à des matières pratiques et modernes.

Par exemple, il serait très utile d'enseigner davantage l'éducation financière. Beaucoup de jeunes sortent de l'école sans savoir gérer un budget, économiser ou comprendre comment fonctionnent les impôts. C'est pourtant une compétence essentielle pour la vie adulte.

Une autre matière qui devrait être renforcée est l'informatique. Aujourd'hui, la technologie est présente dans tous les métiers. Les élèves devraient apprendre non seulement à utiliser un ordinateur, mais aussi à comprendre la programmation, la cybersécurité et le fonctionnement des outils numériques.

Je pense aussi que l'éducation civique et écologique doit occuper une place centrale. Nous vivons dans un monde confronté à de grands défis environnementaux, et il est important que les jeunes comprennent l'impact de leurs choix de consommation et l'importance de protéger la planète.

Enfin, je dirais qu'il serait bénéfique de développer des cours sur la communication et le développement personnel. Savoir s'exprimer clairement, gérer ses émotions et travailler en équipe sont des compétences essentielles dans la vie professionnelle et personnelle.

En conclusion, l'école devrait non seulement transmettre des savoirs théoriques, mais aussi former les élèves à devenir des citoyens responsables, autonomes et adaptés au monde moderne.""",
            "bx bx-chalkboard",
        ),
        sub(
            "Famille ou amis",
            "Pensez-vous qu'il vaut mieux avoir une grande famille ou de bons amis ? Pourquoi ?",
            """C'est une question intéressante, parce que la famille et les amis occupent deux places différentes mais très importantes dans la vie.

D'un côté, avoir une grande famille peut être une source de sécurité et de soutien. Les liens familiaux sont souvent solides, et on sait qu'on peut compter sur ses proches dans les moments difficiles. Partager des repas, des traditions et des souvenirs crée une stabilité affective unique.

D'un autre côté, les amis jouent un rôle complémentaire. Les amis, on les choisit. Ils partagent nos passions, nos loisirs, et ils apportent une liberté d'expression que l'on n'a pas toujours dans la famille. Parfois, on peut même se confier plus facilement à un ami qu'à un membre de sa famille.

Personnellement, je pense qu'il vaut mieux avoir de bons amis, même si la famille est précieuse. Pourquoi ? Parce que la qualité des relations est plus importante que la quantité. Une grande famille ne garantit pas forcément une bonne entente, tandis que quelques amis sincères peuvent apporter un vrai bonheur au quotidien.

Cela dit, le mieux reste un équilibre : profiter de l'amour de la famille tout en cultivant des amitiés solides.

En résumé, si je devais choisir, je dirais que je préfère avoir de bons amis, car ce sont eux qui enrichissent ma vie sociale et me soutiennent dans mes projets personnels.""",
            "bx bx-group",
        ),
    ]),
    part("tache3", 3, [
        sub(
            "Matières pour capter l'intérêt des élèves",
            "Quelles matières devraient être enseignées à l'école pour capter davantage l'intérêt des élèves ? Pourquoi ?",
            """Bonjour madame, bonjour monsieur,
À mon avis, si on veut vraiment capter l'intérêt des élèves à l'école, il faut que les matières enseignées soient plus proches de leur vie quotidienne, de leurs préoccupations, et surtout de leur futur.

Par exemple, je pense qu'on devrait introduire des cours de gestion de budget ou de vie pratique dès le collège. Beaucoup de jeunes quittent l'école sans savoir comment gérer un compte bancaire, faire une déclaration d'impôts, ou comprendre une facture. Et pourtant, ce sont des choses qu'on utilise toute notre vie. Apprendre ça à l'école, ça donnerait du sens à ce qu'on fait, et ça intéresserait les élèves.

Une autre matière que je trouve importante, c'est tout ce qui touche à la santé mentale, la communication et le développement personnel. Apprendre à mieux se connaître, à gérer ses émotions, à communiquer avec les autres… Ce sont des compétences essentielles dans la vie professionnelle comme personnelle. Malheureusement, ce n'est pas assez présent dans les programmes actuels.

Je pense aussi qu'on devrait intégrer davantage de nouvelles technologies et de créativité. Beaucoup d'élèves s'ennuient parce qu'ils ne se reconnaissent pas dans les cours classiques comme l'histoire ou les maths. Mais si on leur propose des projets concrets, comme créer une vidéo, développer une appli, faire du design ou de la robotique, là ils s'impliquent beaucoup plus. Et en plus, ce sont des compétences recherchées sur le marché du travail.

Enfin, je crois qu'il faut aussi parler de l'environnement, du climat et des enjeux actuels. Les jeunes sont très concernés par ce qui se passe dans le monde, mais souvent ils ne savent pas comment agir. Un cours sur l'écologie appliquée, par exemple, pourrait les aider à comprendre les défis et à devenir des citoyens responsables.

Donc pour moi, capter l'intérêt des élèves, ce n'est pas une question de méthode uniquement, c'est aussi une question de contenu. Il faut leur montrer que ce qu'ils apprennent leur sera utile, que ça les concerne, et que ça peut les aider à construire leur avenir.
Merci de m'avoir écouté.""",
            "bx bx-chalkboard",
        ),
        sub(
            "Famille ou amis proches",
            "Selon vous, est-il préférable d'avoir une grande famille ou des amis proches ? Qu'en pensez-vous ?",
            """Bonjour madame, bonjour monsieur,
C'est une question intéressante, et je pense que la réponse dépend beaucoup des expériences personnelles de chacun. Mais pour ma part, je pense qu'il est préférable d'avoir des amis proches plutôt qu'une grande famille, et je vais vous expliquer pourquoi.

D'abord, avoir une grande famille ne garantit pas forcément une vie sociale épanouie. On peut avoir beaucoup de cousins, d'oncles ou de frères et sœurs, mais ne pas être proche d'eux. Parfois, les relations familiales sont compliquées, avec des tensions, des jalousies ou simplement des différences de mode de vie.

À l'inverse, les vrais amis qu'on choisit sont souvent ceux avec qui on partage les mêmes valeurs, les mêmes passions. Ce sont eux qu'on appelle quand on a besoin d'aide, qu'on a un coup de mou, ou juste envie de partager un bon moment. Ce lien d'amitié, quand il est sincère, est très fort.

Je connais des gens qui ont peu ou pas de famille autour d'eux, mais qui sont très bien entourés par leurs amis. Ils organisent des repas, des voyages, ils se soutiennent comme une vraie « famille choisie ».

Cela dit, je ne dis pas que la famille n'est pas importante. Si on a la chance d'avoir une grande famille unie, c'est super. Mais ce n'est pas la quantité de personnes qui compte, c'est la qualité des relations.

Pour moi, il vaut mieux avoir quelques amis fidèles qu'on peut appeler à tout moment, plutôt que beaucoup de proches qu'on ne connaît qu'en surface.
Merci de votre attention.""",
            "bx bx-user-plus",
        ),
        sub(
            "Accompagnement des nouveaux employés",
            "Pensez-vous que les entreprises doivent accompagner les nouveaux employés pour faciliter leur intégration ? Êtes-vous d'accord ?",
            """Bonjour madame, bonjour monsieur,
Oui, je suis tout à fait d'accord avec cette idée : les entreprises devraient accompagner les nouveaux employés dès leur arrivée, et je pense que c'est même essentiel pour plusieurs raisons.

D'abord, quand on arrive dans une nouvelle entreprise, on est souvent un peu perdu. Il faut apprendre comment tout fonctionne, qui fait quoi, quelles sont les règles, les outils utilisés, la culture interne… Et si personne ne nous aide au départ, on peut vite se sentir seul, stressé, voire démotivé. C'est pourquoi un accompagnement dès le début peut faire une grande différence.

Par exemple, une entreprise qui organise un accueil personnalisé, avec une visite des locaux, une présentation de l'équipe, ou un petit guide d'intégration, montre qu'elle respecte ses employés. Ça crée un bon climat de confiance dès le départ, et ça donne envie de s'impliquer.

Il y a aussi le rôle du tuteur ou du référent, très important. Avoir une personne à qui on peut poser des questions librement, pendant les premières semaines, c'est rassurant. On n'a pas peur de faire des erreurs ou de déranger tout le monde. Ça aide à prendre ses marques plus vite.

L'intégration, ce n'est pas seulement comprendre son poste, c'est aussi s'intégrer dans l'équipe. Et là encore, l'entreprise peut aider, en organisant des moments de rencontre : déjeuner d'équipe, réunion informelle, activité de groupe… Ça permet de créer du lien, d'éviter que le nouveau reste isolé, et de construire une bonne ambiance de travail.

Au final, quand un employé est bien accueilli, il est plus motivé, il progresse plus vite, et il reste plus longtemps dans l'entreprise. C'est aussi un gain pour l'employeur, car un salarié bien intégré, c'est un salarié plus efficace.

Je dirais donc que l'intégration, ce n'est pas un « plus », c'est une étape indispensable pour le bon fonctionnement de l'entreprise.
Merci beaucoup de m'avoir écouté.""",
            "bx bx-building-house",
        ),
        sub(
            "Cours favori à l'école",
            "Quel était votre cours favori à l'école ? Expliquez pourquoi vous l'aimiez.",
            """Bonjour madame, bonjour monsieur,
Quand j'étais à l'école, le cours que j'aimais le plus, c'était le français. Et je vais vous dire pourquoi : parce que c'était un cours qui me permettait de m'exprimer librement, de comprendre les autres, et surtout de développer ma créativité.

D'abord, ce que j'aimais dans le cours de français, c'était la lecture. On découvrait des livres, des histoires, des personnages. Ça me faisait voyager sans quitter la classe. Parfois, je continuais les lectures chez moi, juste par plaisir. Ça m'a beaucoup aidé à développer mon vocabulaire, à mieux écrire et à réfléchir sur des sujets variés.

Ensuite, il y avait l'expression écrite. J'aimais inventer des histoires, écrire des textes, donner mon opinion. Même les rédactions étaient un vrai plaisir. J'avais l'impression que je pouvais créer quelque chose, que ce soit une lettre, un dialogue ou un petit récit. C'était un moment où je pouvais être moi-même, sans avoir peur de me tromper.

Ce que j'aimais aussi, c'était la grammaire. Oui, je sais que ça peut paraître bizarre, mais j'aimais bien les règles, les exercices, les accords. C'était comme un jeu logique, et j'aimais réussir les conjugaisons sans fautes. C'était gratifiant.

Mais ce qui rendait le cours encore plus intéressant, c'était la professeure. Elle était passionnée, patiente, et elle nous donnait confiance. Elle ne se contentait pas de suivre le manuel. Elle nous posait des questions, elle nous faisait réfléchir, et elle s'intéressait à ce qu'on pensait. Grâce à elle, j'ai aimé apprendre le français, et ça m'a même donné envie de m'exprimer davantage à l'oral.

Je pense que quand on aime un cours, ce n'est pas seulement pour le contenu, mais aussi pour la manière dont il est transmis. Et pour moi, le français, c'était à la fois utile, vivant, et inspirant.
Merci de m'avoir écouté.""",
            "bx bx-book-reader",
        ),
        sub(
            "Réussite professionnelle à l'étranger",
            "À votre avis, vivre à l'étranger garantit-il une réussite professionnelle ? Pourquoi ou pourquoi pas ?",
            """Bonjour madame, bonjour monsieur,
C'est une question qu'on se pose souvent, surtout quand on envisage de partir à l'étranger : est-ce que vivre dans un autre pays garantit qu'on va réussir sa carrière ?

Pour moi, la réponse est non. Vivre à l'étranger peut être une opportunité, mais ce n'est pas une garantie de réussite professionnelle. Et je vais vous expliquer pourquoi.

D'abord, partir à l'étranger demande beaucoup d'adaptation. Il faut s'habituer à une nouvelle langue, à une culture différente, à des règles de travail qu'on ne connaît pas toujours. Et même si on est très motivé, ce n'est pas toujours facile. Certaines personnes mettent du temps à trouver un emploi, ou doivent accepter des postes en dessous de leur niveau.

Ensuite, le marché du travail n'est pas le même partout. Il y a des pays où la concurrence est forte, où il faut avoir des diplômes reconnus localement, ou même une autorisation de travail spéciale. Donc ce n'est pas parce qu'on arrive dans un pays développé qu'on va tout de suite réussir professionnellement.

Mais attention, ça ne veut pas dire que c'est impossible. Au contraire, vivre à l'étranger peut offrir de très belles opportunités : on apprend de nouvelles compétences, on élargit son réseau, on devient plus autonome. Mais il faut se préparer, se former, et parfois commencer par des petits boulots avant d'obtenir ce qu'on cherche vraiment.

Moi, je pense que la réussite professionnelle dépend surtout de la préparation, du niveau de langue, de la détermination, et aussi un peu de chance. Ce n'est pas l'endroit qui fait la réussite, c'est la personne.

Donc non, vivre à l'étranger ne garantit rien. Mais bien préparé, ça peut être une très belle étape pour avancer dans sa carrière.
Merci de votre attention.""",
            "bx bx-globe-alt",
        ),
    ]),
    part("tache3", 4, [
        sub(
            "Gentillesse et respect",
            "La gentillesse aide-t-elle toujours à se faire écouter et respecter ? Êtes-vous d'accord avec cette idée ?",
            """Bonjour madame, bonjour monsieur,
Alors aujourd'hui, je vais vous donner mon avis sur cette question : est-ce que la gentillesse permet toujours d'être écouté et respecté ?

Pour moi, la réponse est un peu nuancée. Oui, la gentillesse est une qualité précieuse, mais non, elle ne suffit pas toujours pour se faire respecter dans toutes les situations.

D'abord, il faut bien comprendre que la gentillesse, ce n'est pas de la faiblesse. Être gentil, c'est faire preuve d'écoute, d'empathie, de respect envers les autres. C'est une force humaine. Dans la vie personnelle comme au travail, les gens gentils créent souvent une ambiance plus agréable. On se sent plus à l'aise avec eux, on leur fait confiance. Et ça, ça peut clairement aider à se faire écouter.

Mais malheureusement, dans certaines situations, surtout dans un cadre professionnel compétitif ou dans des milieux très hiérarchisés, la gentillesse peut être mal perçue. Certaines personnes pensent à tort qu'un collègue gentil est forcément naïf ou qu'il manque d'autorité. Du coup, on ne le respecte pas toujours à sa juste valeur. C'est injuste, mais ça arrive.

C'est pour ça que je pense qu'il faut savoir doser. Être gentil, oui, mais sans se laisser marcher sur les pieds. Il faut poser ses limites, savoir dire non quand c'est nécessaire, et être ferme tout en restant respectueux.

Moi personnellement, j'essaie d'être gentil avec tout le monde, mais j'ai appris que si je ne montre pas un peu de caractère, les gens en profitent. Donc la gentillesse doit aller avec l'affirmation de soi.

Pour conclure, je dirais que la gentillesse est une qualité qui peut vraiment aider à se faire écouter et respecter, à condition qu'elle soit accompagnée de confiance en soi. Il ne faut jamais perdre sa bienveillance, mais il faut savoir aussi défendre ses idées et poser des limites.
Merci de m'avoir écouté.""",
            "bx bx-happy-heart-eyes",
        ),
        sub(
            "Bonheur et célibat",
            "Pensez-vous qu'on puisse être heureux même en restant célibataire ? Quelle est votre opinion ?",
            """Bonjour madame, bonjour monsieur,
Aujourd'hui, je vais vous donner mon point de vue sur une question qu'on se pose souvent : peut-on vraiment être heureux même si on reste célibataire ?

Honnêtement, je pense que oui, on peut tout à fait être heureux sans être en couple. Le bonheur ne dépend pas uniquement de notre situation amoureuse. Il dépend de ce qu'on vit, de ce qu'on ressent, de nos projets, de nos relations sociales, et surtout de notre relation avec nous-mêmes.

Bien sûr, être en couple peut apporter beaucoup de choses positives : de l'amour, du soutien, du partage, de la tendresse. Mais ce n'est pas une garantie de bonheur. Il y a des couples malheureux, et des célibataires épanouis.

Le célibat permet souvent de mieux se connaître, de se concentrer sur ses objectifs, sa carrière, ses passions. Il y a une grande liberté dans le fait de ne pas devoir faire de compromis au quotidien. On organise sa vie comme on le souhaite, et ça peut être très satisfaisant.

Je pense aussi qu'il vaut mieux être seul que mal accompagné. Certaines personnes restent en couple juste pour ne pas être seules, mais elles souffrent. Alors que d'autres, seules, se sentent libres, en paix, et entourées d'amis ou de leur famille.

Cela dit, je comprends que certaines personnes ressentent le besoin de partager leur vie avec quelqu'un. Et c'est aussi très naturel. Mais ce n'est pas parce qu'on est célibataire qu'on est automatiquement malheureux.

Pour conclure, je dirais que le bonheur dépend avant tout de notre équilibre intérieur. Être en couple ou non, ce n'est qu'un élément parmi d'autres. L'essentiel, c'est de se sentir bien avec soi-même.
Merci de m'avoir écouté.""",
            "bx bx-heart-circle",
        ),
        sub(
            "Journée des droits des femmes",
            "Selon vous, est-il important de dédier une journée aux droits des femmes ? Pourquoi ?",
            """Bonjour madame, bonjour monsieur,
Aujourd'hui, je vais vous donner mon point de vue sur une question de société très importante : est-ce qu'il est vraiment utile de consacrer une journée aux droits des femmes ?

Personnellement, ma réponse est un grand oui. Je pense que c'est non seulement important, mais même nécessaire. Pourquoi ? Parce que, malgré les progrès qu'on a faits dans beaucoup de pays, les femmes continuent de vivre des inégalités dans leur vie quotidienne, que ce soit au travail, dans la famille ou dans la société en général.

Prenons l'exemple du monde du travail. Dans beaucoup d'endroits, à compétence égale, les femmes gagnent encore moins que les hommes. Elles ont plus de difficultés à accéder à des postes de responsabilité, et elles sont souvent confrontées à des remarques sexistes ou à des stéréotypes. Une journée consacrée aux droits des femmes permet de rappeler que ces injustices existent encore et qu'il faut continuer à les combattre.

Mais ce n'est pas seulement au travail. Dans la vie personnelle aussi, les femmes assument souvent une charge mentale plus importante, c'est-à-dire qu'elles gèrent plus de choses à la maison, en plus de leur travail professionnel. Et bien sûr, il y a aussi la question des violences faites aux femmes. C'est un sujet grave, et cette journée permet d'en parler, de sensibiliser les gens, et d'encourager les victimes à ne pas rester seules.

Certaines personnes disent qu'il ne devrait pas y avoir de journée spéciale, que les droits des femmes devraient être respectés tous les jours. C'est vrai dans l'idéal, mais la réalité est différente. Le fait de consacrer une journée symbolique chaque année permet d'attirer l'attention des médias, des écoles, des entreprises. On organise des débats, des conférences, des ateliers. Et c'est souvent grâce à ces événements qu'on ouvre les yeux sur des situations injustes qu'on n'avait pas forcément remarquées.

C'est aussi un moment pour célébrer les progrès réalisés. On peut mettre en avant des femmes inspirantes, qu'elles soient connues ou anonymes, et montrer l'impact positif qu'elles ont eu dans leur communauté, leur pays ou même dans le monde.

Pour moi, ce genre de journée ne doit pas être vue comme un simple symbole, mais comme un outil de sensibilisation et d'action. C'est un rappel collectif que l'égalité entre les femmes et les hommes est encore un combat à mener.

Donc oui, je pense qu'une journée consacrée aux droits des femmes est essentielle. Elle ne règle pas tout, mais elle joue un rôle important dans l'évolution des mentalités.
Merci beaucoup de m'avoir écouté.""",
            "bx bx-female-sign",
        ),
        sub(
            "Télévision et apprentissage",
            "Regarder la télévision, est-ce un bon moyen pour apprendre de nouvelles choses ? Qu'en pensez-vous ?",
            """Bonjour madame, bonjour monsieur,
Alors aujourd'hui, je vais vous parler d'un sujet très actuel : est-ce que regarder la télévision peut vraiment nous permettre d'apprendre des choses utiles ?

Personnellement, je pense que oui, mais tout dépend du contenu qu'on regarde et de la manière dont on l'utilise.

On a parfois tendance à critiquer la télévision, à dire que ça rend les gens passifs, qu'on y passe trop de temps pour rien… Et c'est vrai que si on regarde uniquement des émissions de divertissement ou de télé-réalité, on n'apprend pas grand-chose. Mais la télé, c'est aussi un outil incroyable pour découvrir le monde.

Par exemple, il y a des documentaires très bien faits sur la nature, l'histoire, la science ou les civilisations. Moi, j'ai appris plein de choses grâce à ce type de programmes : comment fonctionnent les volcans, les secrets des pyramides, les enjeux du réchauffement climatique… Des sujets qu'on ne prend pas toujours le temps de lire, mais qu'on peut comprendre facilement avec des images et des explications simples.

Il y a aussi des chaînes éducatives qui proposent des émissions pour apprendre les langues, ou pour aider les jeunes à mieux comprendre les cours de maths ou de sciences. C'est un bon complément à l'école, surtout quand on a du mal à se concentrer en lisant un livre.

Même les films ou les séries peuvent nous apprendre des choses. Par exemple, une série historique peut nous donner envie d'en savoir plus sur une période. Ou bien un film étranger peut nous aider à mieux comprendre une autre culture, ou même à améliorer notre compréhension orale d'une langue étrangère.

Mais attention : il faut rester critique. Il y a aussi beaucoup de fausses informations, de clichés, ou de contenus peu fiables à la télé. Il faut savoir faire la différence entre un documentaire sérieux et une émission qui cherche juste à faire du buzz.

Donc à mon avis, la télévision peut être un excellent moyen d'apprentissage, à condition de bien choisir ce qu'on regarde et de garder un esprit curieux. Ce n'est pas la télé elle-même qui est bonne ou mauvaise, c'est la manière dont on l'utilise.
Merci de m'avoir écouté.""",
            "bx bx-tv",
        ),
        sub(
            "Aimer son travail",
            "À votre avis, est-ce indispensable d'aimer son travail pour réussir sa carrière professionnelle ? Pourquoi ?",
            """Bonjour madame, bonjour monsieur,
C'est une question que beaucoup de gens se posent : est-ce qu'il faut absolument aimer son travail pour réussir sa carrière ?

À mon avis, ce n'est pas indispensable, mais ça aide énormément.

Je m'explique. Il y a des gens qui réussissent très bien dans leur carrière sans forcément être passionnés par ce qu'ils font. Ils travaillent sérieusement, ils sont compétents, et ils progressent parce qu'ils sont organisés, efficaces, et parfois, tout simplement parce qu'ils ont de bonnes opportunités.

Mais quand on aime vraiment ce qu'on fait, on est plus motivé. On se donne à fond, on cherche à s'améliorer, on accepte mieux les difficultés. Et cette attitude se voit, elle fait souvent la différence. Aimer son travail, c'est aussi avoir envie de se lever le matin, de relever des défis, de créer, d'innover. Et cette énergie positive peut mener loin.

En plus, quand on aime ce qu'on fait, on gère mieux le stress. On ne travaille pas seulement pour le salaire ou pour les responsabilités, mais aussi pour le plaisir d'apprendre, de contribuer à quelque chose qui nous parle.

Évidemment, ce n'est pas toujours possible de faire exactement ce qu'on aime. Parfois, on prend un emploi par nécessité. Mais même dans ce cas, on peut essayer de trouver un intérêt, un aspect qu'on apprécie. Et si on ne peut pas changer de travail tout de suite, on peut au moins réfléchir à un projet à long terme qui nous rapprochera d'une activité qui nous plaît.

Pour moi, aimer son travail, ce n'est pas indispensable pour avoir une belle carrière, mais c'est un atout précieux pour s'épanouir et durer dans le temps.
Merci beaucoup pour votre attention.""",
            "bx bx-briefcase-alt",
        ),
    ]),
]
