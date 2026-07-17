# -*- coding: utf-8 -*-
"""Contenu Tâche 2 — Expression orale Septembre 2025."""


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


TACHE2 = [
    part("tache2", 1, [
        sub(
            """Sujet 1""",
            """Je suis votre ami(e). Vous venez de vous installer au Canada et vous cherchez un logement. Je vous propose une chambre en colocation dans mon appartement. Posez-moi des questions.""",
            """1. Est-ce que la chambre est meublée ?
2. Quels sont les espaces communs que nous partagerons ?
3. Y a-t-il des règles spécifiques pour la colocation ?
4. Quel est le montant du loyer et des charges ?
5. La chambre est-elle lumineuse et bien insonorisée ?
6. Combien de colocataires vivent déjà dans l’appartement ?
7. Y a-t-il des commerces ou des transports en commun à proximité ?
8. Peut-on inviter des amis sans problème ?
9. Le quartier est-il calme et sécurisé ?
10. Est-il possible de faire un contrat de location formel ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis votre ami(e) et j’ai commencé un nouveau travail. Posez-moi des questions sur cette expérience.""",
            """1. Quelles sont tes premières impressions sur ton nouveau travail ?
2. Quelles sont les principales responsabilités de ton poste ?
3. Est-ce que tu t’entends bien avec tes collègues ?
4. Quels sont les horaires de travail ?
5. Le travail est-il proche de chez toi ?
6. Quels défis rencontres-tu dans ce poste ?
7. Qu’est-ce qui te plaît le plus dans ce nouvel emploi ?
8. Le salaire est-il satisfaisant pour toi ?
9. Y a-t-il des possibilités d’évolution dans l’entreprise ?
10. Est-ce que tu reçois des formations ou du soutien au travail ?""",
        ),
        sub(
            """Sujet 3""",
            """Je suis l’assistante de votre médecin de famille, actuellement il est en vacances et remplacé par un autre médecin. Vous m’appelez pour obtenir des informations à son sujet. Posez-moi des questions.""",
            """1. Quel est le nom du médecin remplaçant ?
2. A-t-il les mêmes spécialités que mon médecin habituel ?
3. Est-il possible de prendre rendez-vous cette semaine ?
4. Peut-il accéder à mon dossier médical ?
5. Quels sont ses horaires de consultation ?
6. Comment se passe la prise de rendez-vous avec ce médecin ?
7. Est-il recommandé pour des cas particuliers comme les miens ?
8. Y a-t-il des frais supplémentaires avec ce médecin ?
9. Combien de temps remplacera-t-il mon médecin de famille ?
10. Est-ce que je peux le contacter directement en cas d’urgence ?""",
        ),
        sub(
            """Sujet 4""",
            """Je suis votre ami(e) et je tiens un blog. Vous souhaitez voyager et me demandez des conseils sur la création d’un blog. Posez-moi des questions.""",
            """1. Quel hébergeur me conseilles-tu pour lancer mon blog ?
2. Quels thèmes de blog sont les plus populaires actuellement ?
3. Dois-je investir dans un design personnalisé ?
4. Comment fais-tu pour attirer des lecteurs sur ton blog ?
5. Combien de temps par semaine consacres-tu à la gestion du blog ?
6. Quels sujets ou rubriques penses-tu que je devrais aborder ?
7. Est-il essentiel de publier régulièrement pour fidéliser les lecteurs ?
8. Quels réseaux sociaux sont les plus efficaces pour promouvoir un blog ?
9. As-tu des conseils pour écrire des articles intéressants ?
10. Comment choisis-tu les images et le contenu visuel pour ton blog ?""",
        ),
        sub(
            """Sujet 5""",
            """Je suis votre voisin(e). Vous venez de vous installer au Canada et vous souhaitez organiser l’anniversaire de votre conjoint(e). Posez-moi des questions.""",
            """1. Quels sont les meilleurs restaurants de la ville pour un anniversaire ?
2. Y a-t-il des lieux romantiques pour une soirée spéciale ?
3. As-tu des idées d’activités à faire en couple pour cette occasion ?
4. Est-ce qu’il y a des salles ou espaces pour organiser un petit événement privé ?
5. Quels endroits recommandes-tu pour acheter un bon gâteau d’anniversaire ?
6. Connais-tu des magasins où je pourrais trouver des décorations ?
7. Quels types de restaurants plaisent généralement ici (cuisine locale, internationale, etc.) ?
8. Est-il possible de réserver un espace extérieur pour une fête ?
9. As-tu des contacts de photographes pour immortaliser la soirée ?
10. Connais-tu des musiciens ou DJ locaux pour animer la fête ?""",
        ),
    ]),
    part("tache2", 2, [
        sub(
            """Sujet 1""",
            """Je suis une amie / un ami(e). Je pars pour le week-end et je vous demande de garder ma fille de 3 ans. Vous la connaissez un peu. Je vous indique les horaires et les choses à faire. Vous me posez des questions pour bien vous organiser (repas, jeux, sieste, etc.).""",
            """Quels sont les horaires exacts de garde ?
À quelle heure se réveille-t-elle le matin ?
Que mange-t-elle au petit-déjeuner ?
Y a-t-il des aliments qu’elle ne doit pas manger ?
Quelle est l’heure habituelle de sa sieste ?
Combien de temps dort-elle en général ?
Quels jeux aime-t-elle particulièrement ?
Peut-elle regarder un peu la télévision ou pas du tout ?
A-t-elle un jouet préféré ou un doudou indispensable ?
Comment réagit-elle quand ses parents ne sont pas là ?
Est-ce qu’il faut lui lire une histoire avant de dormir ?
Que dois-je faire si elle pleure beaucoup ?
Y a-t-il des règles particulières pour le coucher le soir ?
Dois-je lui donner un bain ou pas nécessaire ?
En cas de problème, qui dois-je appeler en priorité ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis une collègue. Je vous propose d’aller voir un spectacle. Vous me posez des questions sur ce spectacle (genre, lieu, heure, etc.) et sur la sortie (transport, personnes présentes, etc.).""",
            """De quel type de spectacle s’agit-il (théâtre, concert, danse…) ?
Où a lieu le spectacle exactement ?
À quelle heure commence-t-il ?
Combien de temps dure le spectacle ?
Quel est le prix du billet ?
Peut-on acheter les billets sur place ou en ligne ?
Faut-il réserver à l’avance ?
Y aura-t-il beaucoup de monde, à ton avis ?
Qui d’autre viendra avec nous ?
Comment allons-nous nous rendre sur place (voiture, bus, métro…) ?
Est-ce que la salle est facile d’accès en transport en commun ?
Y a-t-il un parking à proximité ?
Penses-tu qu’on pourra dîner ensemble avant ou après le spectacle ?
Quel est le style ou l’ambiance de ce spectacle ?
As-tu déjà vu ce spectacle ou un spectacle du même genre ?""",
        ),
        sub(
            """Sujet 3""",
            """Je travaille au service culturel de la mairie. Vous voulez en savoir plus sur les activités culturelles de la ville. Vous me posez des questions (expositions, musées, ateliers, prix, accès, etc.).""",
            """Quelles expositions sont actuellement proposées ?
Quels sont les principaux musées de la ville ?
Y a-t-il des visites guidées pour les touristes ?
Quels types d’ateliers culturels existent (peinture, danse, théâtre…) ?
Quels sont les prix moyens des activités ?
Y a-t-il des réductions pour les étudiants ou les familles ?
Les activités sont-elles accessibles aux enfants ?
Où puis-je trouver le programme complet des événements ?
Y a-t-il des festivals organisés dans l’année ?
Est-ce que les musées sont ouverts le dimanche ?
Quels sont les horaires d’ouverture des centres culturels ?
Peut-on participer à des ateliers sans inscription ?
Est-ce que certaines activités sont gratuites ?
Y a-t-il un service en ligne pour réserver les places ?
Quels sont les événements culturels incontournables de la ville ?""",
        ),
        sub(
            """Sujet 4""",
            """Je travaille dans une agence de voyage. Vous êtes client(e) et vous cherchez un séjour au Canada. Vous me posez des questions (endroits à visiter, prix, hébergements, activités, etc.).""",
            """Quelles sont les destinations les plus populaires au Canada ?
Quels endroits recommandez-vous absolument de visiter ?
Quels sont les prix moyens pour un séjour d’une semaine ?
Les billets d’avion sont-ils inclus dans le forfait ?
Quel type d’hébergement proposez-vous (hôtels, auberges, appartements…) ?
Y a-t-il des circuits organisés pour découvrir plusieurs villes ?
Quelles activités touristiques sont incluses dans vos offres ?
Proposez-vous des excursions dans la nature, comme des parcs nationaux ?
Est-ce que vos séjours incluent des visites guidées ?
Quelles sont les meilleures périodes pour voyager au Canada ?
Offrez-vous des voyages adaptés aux familles avec enfants ?
Y a-t-il des réductions pour les groupes ?
Comment se passent les repas, sont-ils inclus ou pas ?
Peut-on personnaliser le séjour selon nos envies ?
Quels documents sont nécessaires pour réserver ce voyage ?""",
        ),
        sub(
            """Sujet 5""",
            """Vous êtes en voiture au Canada et vous tombez en panne. Vous appelez votre assurance pour savoir comment elle peut vous aider (dépannage, réparations, retour, etc.). Je suis l’agent qui vous répond.""",
            """Pouvez-vous m’envoyer rapidement une dépanneuse ?
En combien de temps le dépanneur peut-il arriver ?
Est-ce que le dépannage est entièrement couvert par mon assurance ?
Que se passe-t-il si la panne est grave ?
Ma voiture sera-t-elle réparée sur place ou transportée au garage ?
Est-ce que vous collaborez avec un garage spécifique ?
Combien de temps prend généralement la réparation ?
Est-ce que l’assurance prend en charge les frais de réparation ?
Ai-je droit à une voiture de remplacement ?
Pendant combien de jours puis-je garder la voiture de remplacement ?
Si la réparation dure longtemps, est-ce que vous prenez en charge l’hôtel ?
Dois-je avancer les frais et demander un remboursement ensuite ?
Quels documents dois-je fournir pour bénéficier de l’assistance ?
Est-ce que l’assurance couvre aussi les passagers ?
Que dois-je faire si la panne se reproduit loin de la ville ?""",
        ),
    ]),
    part("tache2", 3, [
        sub(
            """Sujet 1""",
            """Je suis votre ami(e) francophone. Vous devez rédiger un article sur un film pour la revue de votre association culturelle francophone. Vous cherchez de l’inspiration. Vous m’interrogez sur les films que j’ai appréciés (lieu, genre, prix, etc.).""",
            """Quel est le dernier film que vous avez vu récemment ?
Où l’avez-vous regardé : au cinéma, chez vous, sur une plateforme ?
Quel était le genre du film (comédie, drame, action, documentaire…) ?
Qui sont les acteurs principaux de ce film ?
L’histoire vous a-t-elle semblé originale ?
Le film a-t-il reçu des prix ou des récompenses ?
Comment était l’ambiance dans la salle ou votre ressenti personnel ?
Est-ce un film adapté d’un livre ou d’une histoire vraie ?
Comment avez-vous trouvé la musique et la bande sonore ?
Quels thèmes principaux sont abordés dans ce film ?
Recommanderiez-vous ce film à d’autres personnes ? Pourquoi ?
Le film était-il long ou court ? Est-ce que cela vous a convenu ?
Quel passage ou quelle scène vous a le plus marqué ?
Le réalisateur est-il connu ? Quels autres films a-t-il faits ?
Préférez-vous regarder ce type de film seul(e) ou avec des amis ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis employé(e) dans une agence de voyages. Vous souhaitez organiser un voyage touristique. Vous entrez dans mon agence et vous me posez des questions sur les destinations (activités, circuits, tarifs, etc.).""",
            """Quelles sont les destinations touristiques les plus demandées en ce moment ?
Quels types d’activités proposez-vous dans vos circuits ?
Avez-vous des voyages organisés pour les familles ?
Quels sont les tarifs moyens pour un séjour d’une semaine ?
Est-ce que vous proposez des réductions pour les étudiants ?
Quels types de logements sont inclus dans vos offres ?
Est-il possible de personnaliser son circuit selon ses envies ?
Quelles destinations recommandez-vous pour découvrir la nature ?
Avez-vous des circuits culturels (musées, monuments, festivals) ?
Comment se passent les réservations : en ligne ou sur place ?
Quels sont les documents nécessaires pour réserver un voyage ?
Y a-t-il des voyages avec accompagnateur ou guide inclus ?
Est-ce que vos forfaits incluent le transport aérien ?
Quelles précautions conseillez-vous avant de partir en voyage ?
Pouvez-vous me donner un exemple de voyage “tout compris” ?""",
        ),
        sub(
            """Sujet 3""",
            """Je suis votre voisin(e). Vous voulez organiser une fête entre voisins. Vous me demandez des suggestions pour l’organisation (musique, invités, repas, etc.).""",
            """Selon vous, quel est le meilleur moment pour organiser la fête ?
Combien de personnes pensez-vous inviter ?
Faut-il demander l’autorisation à la mairie ou au syndic ?
Quel type de musique conviendrait pour cette occasion ?
Préférez-vous engager un DJ ou utiliser une playlist ?
Quels plats pourrait-on préparer pour satisfaire tout le monde ?
Est-ce mieux de préparer nous-mêmes ou de commander à un traiteur ?
Que pensez-vous d’organiser des jeux pour les enfants ?
Faut-il prévoir des chaises et des tables supplémentaires ?
Pensez-vous qu’il est préférable de faire la fête en intérieur ou dehors ?
Comment pouvons-nous prévenir les voisins pour éviter les plaintes ?
Est-ce que chacun pourrait apporter un plat ou une boisson ?
Que diriez-vous d’installer une décoration spéciale ?
Est-ce une bonne idée de fixer une heure précise pour terminer la fête ?
Comment pouvons-nous faire en sorte que tout le monde participe ?""",
        ),
        sub(
            """Sujet 4""",
            """Je suis votre voisin(e). Vous venez d’emménager dans la ville et vous ne connaissez encore personne. Vous me demandez conseil pour faire des rencontres.""",
            """Quels sont les lieux les plus fréquentés par les habitants ici ?
Y a-t-il des associations culturelles ou sportives ouvertes aux nouveaux ?
Existe-t-il une maison de quartier où je peux rencontrer des gens ?
Connaissez-vous des cafés ou restaurants conviviaux dans le coin ?
Est-il possible de s’inscrire à des clubs de sport ?
Quelles activités collectives sont organisées dans la ville ?
Y a-t-il des marchés ou foires régulières où je pourrais rencontrer du monde ?
Que pensez-vous des bibliothèques ou médiathèques comme lieu de rencontre ?
Les habitants participent-ils à des fêtes ou événements annuels ?
Est-ce que les voisins se retrouvent parfois pour des repas communs ?
Y a-t-il des activités pour les jeunes ou les familles ?
Quels sont les endroits où les gens aiment se promener ici ?
Peut-on trouver des cours de danse, de cuisine ou de musique ouverts à tous ?
Les réseaux sociaux locaux sont-ils utilisés pour créer des rencontres ?
Que me conseilleriez-vous pour m’intégrer plus rapidement ?""",
        ),
        sub(
            """Sujet 5""",
            """Vous vous intéressez au rythme de travail au Canada. Vous m’interrogez sur les jours de congé et les horaires de travail au Canada. Vous comparez avec la situation dans votre pays.""",
            """Combien d’heures travaille-t-on en moyenne par semaine au Canada ?
Quels sont les horaires de travail habituels (début et fin de journée) ?
Est-ce que les gens travaillent souvent le week-end ?
Quels sont les jours fériés les plus importants au Canada ?
Combien de semaines de congés payés a-t-on par an ?
Y a-t-il des pauses obligatoires pendant la journée de travail ?
Est-ce que le télétravail est répandu au Canada ?
Les horaires sont-ils flexibles selon les entreprises ?
Est-ce que les magasins ferment tôt ou restent ouverts tard ?
Les Canadiens prennent-ils une longue pause pour le déjeuner ?
Existe-t-il des différences entre les horaires dans les grandes villes et les petites ?
Est-ce que les employés ont droit à des congés de maternité ou paternité longs ?
Quelles sont les périodes de vacances scolaires au Canada ?
Est-ce que les Canadiens travaillent beaucoup d’heures supplémentaires ?
Par rapport à mon pays, diriez-vous que le rythme est plus rapide ou plus tranquille ?""",
        ),
    ]),
    part("tache2", 4, [
        sub(
            """Sujet 1""",
            """Je suis votre ami(e). J’ai commencé un nouvel emploi et vous voulez savoir comment s’est déroulée ma première journée : l’ambiance, les collègues, les tâches, etc.""",
            """Alors, comment s’est passée ta première journée ?
Est-ce que l’accueil a été chaleureux ?
Comment est l’ambiance dans ton entreprise ?
As-tu rencontré beaucoup de collègues ?
Les gens sont-ils sympathiques ?
Ton supérieur t’a-t-il bien expliqué tes tâches ?
As-tu eu des difficultés à comprendre ce qu’on attendait de toi ?
Est-ce que le travail correspond à tes attentes ?
Quelles sont les tâches principales que tu dois faire ?
As-tu déjà un bureau ou un espace de travail fixe ?
Ton emploi du temps est-il chargé ?
As-tu reçu une formation ou des explications pour commencer ?
As-tu eu l’occasion de participer à une réunion ?
Comment s’est passée la pause déjeuner avec les collègues ?
À ton avis, vas-tu aimer ce travail sur le long terme ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis votre voisin(e). Je connais quelqu’un qui propose des cours de musique à domicile. Vous êtes intéressé(e) et vous me posez des questions sur cette personne : ses tarifs, sa disponibilité, son expérience, etc.""",
            """« Je suis ton voisin, je connais un professeur de musique. Tu poses des questions. »
Quelle instrument enseigne cette personne ?
Est-ce qu’il ou elle donne aussi des cours pour débutants ?
Quel est son tarif pour une heure de cours ?
Propose-t-il des forfaits mensuels ?
Est-ce qu’il accepte de se déplacer chez moi ?
Est-il disponible en semaine ou seulement le week-end ?
Peut-il adapter ses horaires selon mes disponibilités ?
Quelle est son expérience dans l’enseignement de la musique ?
A-t-il déjà travaillé avec des enfants ?
Est-ce qu’il prépare aussi pour des examens ou concours de musique ?
Fournit-il les partitions ou faut-il les acheter ?
Donne-t-il des cours individuels ou en groupe ?
Combien de temps dure généralement une séance ?
Y a-t-il des frais supplémentaires (déplacement, matériel) ?
Puis-je assister à un premier cours d’essai ?""",
        ),
        sub(
            """Sujet 3""",
            """Je suis votre voisin(e). Vous souhaitez organiser une sortie à la campagne avec vos amis. Vous me demandez des conseils pour l’organisation : les activités, le transport, le lieu, etc.""",
            """« Je suis ton voisin, tu veux organiser une sortie avec des amis. »
Selon toi, quel est le meilleur endroit pour aller à la campagne ?
Est-ce loin d’ici ?
Peut-on y aller en transport en commun ?
Est-il préférable de louer une voiture ou de prendre le train ?
Y a-t-il des activités intéressantes sur place ?
Peut-on y faire un pique-nique ?
Y a-t-il un lac ou une rivière pour se baigner ?
Existe-t-il des sentiers pour faire de la randonnée ?
Est-ce que c’est un lieu calme ou très fréquenté ?
Y a-t-il des restaurants ou faut-il prévoir sa nourriture ?
Est-il possible de passer la nuit là-bas ?
Quels vêtements faut-il prévoir ?
Combien coûterait environ cette sortie par personne ?
Quelle est la meilleure période de l’année pour y aller ?
As-tu déjà fait une sortie à cet endroit ? Comment c’était ?""",
        ),
        sub(
            """Sujet 4""",
            """Je travaille dans un magasin d’alimentation. Vous êtes client(e) et vous voulez faire livrer vos courses chez vous. Vous me posez des questions sur les conditions offertes : délais, tarifs, mode de livraison, etc.""",
            """« Je suis employé d’un magasin d’alimentation, tu veux faire livrer tes courses. »
Est-ce que vous proposez la livraison à domicile ?
Quels sont vos délais de livraison ?
Est-ce que vous livrez tous les jours de la semaine ?
Livrez-vous aussi le soir ou seulement la journée ?
Quels sont les frais de livraison ?
Y a-t-il un montant minimum d’achat pour bénéficier de la livraison ?
Comment puis-je passer commande ?
Est-ce possible de commander en ligne ?
Est-ce que vous acceptez le paiement à la livraison ?
Puis-je choisir l’heure de livraison ?
Livrez-vous aussi les produits frais comme la viande et les légumes ?
Que se passe-t-il si je ne suis pas chez moi au moment de la livraison ?
Y a-t-il un service express en cas d’urgence ?
Proposez-vous un abonnement mensuel pour les livraisons ?
Quelle zone géographique couvrez-vous pour vos livraisons ?""",
        ),
        sub(
            """Sujet 5""",
            """Je travaille dans une agence immobilière. Vous voulez louer votre appartement pour les vacances afin de gagner plus d’argent. Vous me demandez des conseils sur les prix, la durée de location, le type de locataires, etc.""",
            """« Je suis agent immobilier, vous voulez louer votre appartement. »
Est-ce une bonne idée de louer mon appartement pendant les vacances ?
Quelle durée de location est la plus rentable ?
Quel type de locataires est le plus fréquent ?
Combien puis-je demander par nuit environ ?
Est-il préférable de louer à la semaine ou au mois ?
Quels critères attirent le plus les locataires ?
Dois-je meubler complètement l’appartement ?
Est-ce que je dois inclure l’électricité et l’eau dans le prix ?
Faut-il demander une caution ?
Dois-je passer par une plateforme en ligne (Airbnb, Booking) ?
Y a-t-il des obligations légales pour louer en vacances ?
Dois-je rédiger un contrat de location ?
Comment fixer un prix compétitif ?
Quels sont les risques si je loue à des étrangers ?
Est-ce que la location de vacances rapporte plus qu’une location classique ?""",
        ),
    ]),
    part("tache2", 5, [
        sub(
            """Sujet 1""",
            """Je suis un(e) ami(e). Vous cherchez un emploi. Vous allez à un entretien professionnel la semaine prochaine. Demandez-moi des conseils pour réussir votre entretien (comportement, vêtements, préparatifs, etc.).""",
            """Comment je dois m’habiller pour faire bonne impression ?
Est-ce que je dois arriver en avance ? Combien de temps avant ?
Comment me présenter au recruteur ?
Que dois-je faire si je suis trop stressé ?
Quels sont les gestes ou attitudes à éviter pendant l’entretien ?
Est-ce que je dois apporter mon CV imprimé ?
Est-ce que tu connais des questions classiques posées pendant un entretien ?
Comment répondre à la question : « Pourquoi voulez-vous ce poste ? »
Est-ce que je peux poser des questions à la fin de l’entretien ?
Est-ce que je dois parler de mes points faibles ?
Tu me conseilles de répéter l’entretien à l’avance ?
Est-ce que le langage corporel est important ?
Que faire si je ne comprends pas une question ?
Est-ce que je dois parler de mon salaire ?
Tu penses que je dois envoyer un message après l’entretien ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis votre voisin (e). Je pars en vacances. J’aimerais que vous vous occupiez de mon animal de compagnie pendant mon absence. Vous me posez des questions pour décider si vous allez accepter (dates, habitudes de l’animal, règles, etc.).""",
            """Tu pars à quelles dates exactement ?
C’est quel animal ? Un chien ? Un chat ?
Il a quel âge ?
Est-ce qu’il a des problèmes de santé ?
Est-ce qu’il prend un traitement ou des médicaments ?
À quelle fréquence dois-je le nourrir ?
Où est sa nourriture ? Et en quelle quantité ?
Est-ce qu’il sort ? Si oui, combien de fois par jour ?
Est-ce qu’il est propre à la maison ?
Est-ce que je peux le promener dans le quartier ?
Il a peur de quoi en général ?
Est-ce qu’il est sociable avec les enfants ou les autres animaux ?
Tu veux que je reste chez toi ou je peux le garder chez moi ?
Et si j’ai un problème, je peux te contacter ?
Est-ce que tu veux que je t’envoie des nouvelles pendant ton absence ?""",
        ),
        sub(
            """Sujet 3""",
            """Je travaille dans un office de tourisme. Vous voulez partir en week-end mais vous ne voulez pas dépenser beaucoup d’argent. Demandez-moi des conseils (lieux, activités, transports, etc.).""",
            """Quels sont les endroits pas chers à visiter ce week-end ?
Y a-t-il des hébergements économiques ?
Est-ce que tu connais des auberges de jeunesse dans la région ?
Est-ce qu’on peut faire du camping ?
Il y a des activités gratuites ou à petit prix ?
Est-ce qu’il y a des réductions pour les étudiants ?
Comment peut-on se déplacer sans voiture ?
Quels sont les moyens de transport les moins chers ?
Peut-on visiter des musées gratuitement ?
Est-ce qu’il y a des événements ce week-end ?
Peux-tu me recommander une randonnée ou un parc naturel ?
Est-ce qu’il y a un marché ou un village à découvrir ?
Est-ce que tu as des bons plans pour les repas ?
Combien de jours tu me conseilles pour un petit séjour ?
As-tu un site web ou une brochure avec toutes ces infos ?""",
        ),
        sub(
            """Sujet 4""",
            """Je travaille à l’accueil d’un club sportif de votre ville. Vous voulez faire du sport. Vous me posez des questions pour décider si vous allez vous inscrire (cours, tarifs, horaires, etc.).""",
            """Quels types de cours proposez-vous ?
Est-ce qu’il y a des cours pour débutants ?
Combien coûte l’abonnement ?
Est-ce qu’il y a une formule à la séance ?
Quels sont les horaires d’ouverture du club ?
Est-ce que vous êtes ouverts le week-end ?
Est-ce que je peux essayer un cours gratuitement ?
Est-ce qu’il faut réserver les cours à l’avance ?
Quels sont les horaires des cours collectifs ?
Quels équipements dois-je apporter ?
Est-ce que je peux venir avec un ami ?
Y a-t-il un coach pour m’accompagner ?
Est-ce que vous proposez des activités pour les enfants ?
Est-ce qu’il y a une salle de musculation ?
Est-ce que je peux arrêter l’abonnement à tout moment ?""",
        ),
        sub(
            """Sujet 5""",
            """Nous sommes amis. Vous voulez vous installer au Canada. J’habite dans un appartement en colocation à Toronto. Je vous propose de partager mon appartement. Vous me posez des questions pour décider si vous allez accepter (appartement, quartier, habitudes de vie, etc.).""",
            """L’appartement est situé dans quel quartier de Toronto ?
Combien coûte le loyer par mois ?
Est-ce que les charges (eau, électricité, Internet) sont incluses ?
Il y a combien de colocataires ?
Est-ce que chacun a sa propre chambre ?
Est-ce qu’il y a une bonne ambiance entre les colocataires ?
Est-ce calme ou bruyant comme quartier ?
Y a-t-il des commerces à proximité ?
Est-ce que les transports en commun sont faciles d’accès ?
Est-ce que je dois signer un contrat de location ?
Est-ce que je peux recevoir des invités chez moi ?
Quelle est ta routine quotidienne ?
Est-ce que je dois apporter des meubles ?
Est-ce que je peux cuisiner librement ?
Est-ce que tu penses que je vais m’adapter facilement à la vie à Toronto ?""",
        ),
    ]),
    part("tache2", 6, [
        sub(
            """Sujet 1""",
            """Je suis votre ami(e). Vous êtes à la recherche d’un emploi et vous avez un entretien la semaine prochaine. Vous me demandez des conseils pour bien le réussir (tenue, attitude, préparation, etc.).""",
            """Quelle tenue me conseilles-tu pour l’entretien ?
Est-ce que je dois mettre un costume/cravate ou une tenue plus simple ?
Comment dois-je me présenter en arrivant ?
Est-ce que je dois serrer la main ou attendre ?
Quels gestes ou attitudes dois-je éviter ?
Comment puis-je montrer que je suis motivé ?
Faut-il que je prépare des réponses à certaines questions ?
Quelles sont les questions les plus fréquentes en entretien ?
Est-ce que je dois poser des questions au recruteur ?
Comment dois-je parler : vite, lentement, avec assurance ?
Dois-je préparer mon CV en plusieurs exemplaires ?
Est-ce utile d’apporter mes diplômes ?
Comment gérer mon stress avant l’entretien ?
Dois-je arriver en avance ? Combien de minutes avant ?
Si je ne comprends pas une question, que dois-je faire ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis votre voisin(e). Je pars en vacances et je souhaite que vous gardiez mon animal de compagnie. Vous me posez des questions pour savoir si vous pouvez accepter (dates, habitudes, règles, etc.).""",
            """De quel animal s’agit-il ?
Quelle est sa race ou sa taille ?
Quelles sont les dates exactes de votre absence ?
Est-ce que je dois garder l’animal chez moi ou chez vous ?
À quelle heure faut-il lui donner à manger ?
Quelle nourriture mange-t-il ?
Est-ce qu’il a des allergies alimentaires ?
Combien de fois par jour dois-je le sortir ?
Est-ce qu’il est propre et habitué à l’appartement ?
Est-ce qu’il aboie/miaule beaucoup ?
A-t-il besoin de soins particuliers ou de médicaments ?
Est-ce qu’il s’entend bien avec les enfants ?
Que dois-je faire en cas de problème ou de maladie ?
Est-ce que je peux vous appeler si j’ai une question ?
Est-ce qu’il a un carnet de santé ou les vaccins à jour ?""",
        ),
        sub(
            """Sujet 3""",
            """Je travaille à l’office de tourisme. Vous voulez organiser un week-end à petit budget. Vous me demandez des recommandations (activités, destinations, transports, etc.).""",
            """Quelles sont les destinations les moins chères pour un week-end ?
Est-ce qu’il y a des villes proches accessibles en bus ou train ?
Y a-t-il des réductions pour les étudiants ou les jeunes ?
Quels sont les hébergements les plus économiques ?
Est-ce qu’il existe des auberges de jeunesse dans la région ?
Peut-on camper facilement autour de la ville ?
Quelles activités gratuites propose la ville ?
Est-ce qu’il y a des musées gratuits ?
Quels parcs ou sites naturels peut-on visiter sans payer ?
Est-ce qu’il y a des marchés ou festivals locaux ce week-end ?
Combien coûte le transport en commun pour visiter la ville ?
Est-ce qu’il y a des cartes de réduction pour les transports ?
Combien faut-il prévoir comme budget moyen ?
Pouvez-vous me donner une idée d’un programme simple pour deux jours ?
Y a-t-il des applications ou sites internet utiles pour organiser ça ?""",
        ),
        sub(
            """Sujet 4""",
            """Je travaille à l’accueil d’un club sportif de la ville. Vous souhaitez pratiquer une activité physique et vous me posez des questions pour décider si vous allez vous inscrire (horaires, cours, prix, etc.).""",
            """Quelles sont les activités proposées dans votre club ?
Quels sports sont les plus populaires ici ?
Quels sont les horaires d’ouverture du club ?
Y a-t-il des cours collectifs disponibles ?
Combien coûte l’abonnement mensuel ?
Est-ce qu’il existe une formule d’essai gratuite ?
Y a-t-il une réduction pour étudiants ou familles ?
Est-ce qu’il y a un entraîneur ou coach disponible ?
Combien de fois par semaine peut-on venir s’entraîner ?
Est-ce que les équipements sont fournis ou faut-il les acheter ?
Y a-t-il une salle de musculation dans le club ?
Proposez-vous des cours pour débutants ?
Est-ce qu’il y a une ambiance conviviale ?
Faut-il un certificat médical pour s’inscrire ?
Est-ce que je peux arrêter quand je veux ou il faut un contrat ?""",
        ),
        sub(
            """Sujet 5""",
            """Nous sommes amis. Vous voulez vous installer au Canada. Je vis en colocation à Toronto et je vous propose de partager mon appartement. Vous me posez des questions pour savoir si cette solution vous conviendrait (logement, quartier, habitudes de vie, etc.).""",
            """Combien coûte le loyer par mois ?
Est-ce que le prix inclut l’eau, l’électricité et Internet ?
Quelle est la taille de l’appartement ?
Combien de chambres y a-t-il ?
Est-ce que j’aurai ma propre chambre ?
Comment est le quartier ?
Y a-t-il des commerces proches ?
Est-ce que les transports en commun sont accessibles facilement ?
Combien de temps faut-il pour aller au centre-ville ?
Comment sont les voisins ?
Est-ce qu’il y a des règles de vie dans l’appartement ?
Est-ce que je peux inviter des amis de temps en temps ?
Est-ce qu’il y a une cuisine équipée ?
Y a-t-il une machine à laver et d’autres équipements ?
Combien de colocataires vivent déjà avec toi ?""",
        ),
    ]),
    part("tache2", 7, [
        sub(
            """Sujet 1""",
            """Je suis le (la) secrétaire de votre médecin. Il est en congés et vous me posez des questions sur son remplaçant (nom du médecin, horaires, durée, etc.).""",
            """Comment s’appelle le médecin remplaçant ?
Est-ce un homme ou une femme ?
Depuis quand il travaille ici ?
Quels sont ses horaires de consultation ?
Est-ce qu’il reçoit aussi les patients le week-end ?
Combien de temps dure une consultation ?
Faut-il prendre rendez-vous à l’avance ?
Peut-on venir sans rendez-vous ?
Est-ce que les tarifs sont les mêmes que mon médecin habituel ?
Est-ce qu’il accepte la carte d’assurance maladie ?
Est-ce qu’il a une spécialité particulière ?
Est-ce qu’il a déjà remplacé ce médecin auparavant ?
Où se trouve son cabinet exactement ?
Combien de patients il reçoit par jour environ ?
Est-ce qu’il pourra me donner des ordonnances comme mon médecin habituel ?""",
        ),
        sub(
            """Sujet 2""",
            """Je suis votre ami(e). Je viens d’être embauché(e) dans une entreprise de la région de Toronto. Vous me posez des questions sur mon nouvel emploi (collègues, locaux, avantages, activités, etc.).""",
            """Dans quelle entreprise tu travailles ?
Depuis quand as-tu commencé ce poste ?
Quel est ton rôle dans cette entreprise ?
Comment sont tes collègues ?
L’ambiance de travail est-elle agréable ?
Comment sont les locaux ?
Est-ce que ton bureau est grand ?
As-tu des avantages avec ton emploi ?
Est-ce que tu as une assurance santé ?
As-tu beaucoup d’heures de travail ?
Est-ce que tu dois travailler le week-end ?
Y a-t-il une cafétéria ou un espace de repos ?
Y a-t-il des activités organisées entre collègues ?
Comment se passe ton trajet pour aller au travail ?
Est-ce que tu es satisfait de ton nouveau poste ?""",
        ),
        sub(
            """Sujet 3""",
            """Je suis votre ami(e). J’ai un blog depuis longtemps. Vous voulez créer un blog pour votre prochain voyage au Canada et vous me demandez des conseils (rubriques, rythme de publication, types de contenus, etc.).""",
            """Depuis combien de temps tu as ton blog ?
Quelles rubriques as-tu mises dans ton blog ?
Quels types de contenus sont les plus lus ?
Est-ce que tu écris tous les jours ?
Combien d’articles publies-tu par semaine ?
Utilises-tu beaucoup de photos ?
Est-ce que tu ajoutes des vidéos dans ton blog ?
Quel style d’écriture utilises-tu ?
As-tu des conseils pour bien organiser les rubriques ?
Comment choisis-tu tes sujets ?
Est-ce que tu reçois beaucoup de commentaires ?
Quels sont les thèmes qui intéressent le plus les lecteurs ?
Est-ce que ton blog t’a permis de rencontrer des gens ?
As-tu un conseil technique pour créer un blog facilement ?
Faut-il beaucoup de temps pour tenir un blog ?""",
        ),
        sub(
            """Sujet 4""",
            """Je suis votre ami(e). Vous voulez organiser un voyage au Canada pour fêter l’anniversaire de votre conjoint(e). Vous me demandez des conseils (idées, prix, destination, etc.).""",
            """Quelle destination au Canada me conseilles-tu ?
Quelle ville est la plus romantique pour un anniversaire ?
Combien coûte un billet d’avion environ ?
Quelle est la meilleure période pour voyager ?
Quels hôtels sont les plus adaptés pour un couple ?
Est-ce qu’il y a des promotions pour les voyages au Canada ?
Que puis-je offrir comme activité spéciale ?
Est-ce qu’il y a des croisières ou excursions intéressantes ?
Quels restaurants romantiques recommandes-tu ?
Est-ce que la vie est chère au Canada ?
Combien de jours faut-il prévoir pour un voyage agréable ?
Est-ce que les transports sont pratiques sur place ?
Quels sont les lieux incontournables à visiter ?
Est-ce qu’il y a des activités spéciales pour les anniversaires ?
Est-ce que je dois réserver longtemps à l’avance ?""",
        ),
        sub(
            """Sujet 5""",
            """Vous partez vivre au Canada et vous cherchez un appartement en colocation. Je vous propose le mien. Vous me posez des questions pour savoir comment cela se déroulera (espaces, prix, colocataires, etc.).""",
            """Combien coûte le loyer par mois ?
Est-ce que les charges sont incluses dans le prix ?
Quelle est la taille de l’appartement ?
Combien de colocataires y vivent déjà ?
Est-ce que j’aurai une chambre privée ?
Est-ce que la cuisine est partagée ?
Y a-t-il une salle de bain privée ou commune ?
Est-ce que l’appartement est meublé ?
Comment est le quartier ?
Y a-t-il des commerces proches ?
Est-ce que les transports en commun sont faciles d’accès ?
Combien de temps faut-il pour aller en centre-ville ?
Est-ce que je peux inviter des amis ?
Est-ce que vous avez des règles de vie spécifiques ?
Est-ce qu’il y a une bonne ambiance entre colocataires ?] tahce 3 [ Partie 1
10 sujets""",
        ),
    ]),
]
