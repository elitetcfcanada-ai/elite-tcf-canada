# -*- coding: utf-8 -*-
"""Génère database/seeds/exp_orale/aout_2025.json pour seed_eo_aout_2025.php"""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "database" / "seeds" / "exp_orale" / "aout_2025.json"

SUBTITLE = (
    "Découvrez les nouveaux sujets de l'expression orale qui se répètent. "
    "Pratiquez sur ces thèmes afin d'obtenir de bonnes notes."
)


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
            "Association de défense des animaux",
            "Je fais partie d'une association de défense des animaux. Je vous propose d'en faire partie. Vous me posez des questions pour connaitre mon association (actions, services, public, etc.)",
            """Quel est le nom de votre association et depuis quand existe-t-elle ?
Quelles sont les principales actions que votre association mène pour défendre les animaux ?
Votre association offre-t-elle des services de sauvetage ou d'adoption pour les animaux en détresse ?
Comment peut-on devenir membre de votre association ?
Organisez-vous des événements ouverts au public pour sensibiliser à la cause animale ?
Avez-vous des partenariats avec des entreprises ou d'autres organisations ?
Quelles sont les conditions pour être bénévole chez vous ?
Y a-t-il une cotisation à payer pour faire partie de l'association ?
Comment votre association est-elle financée ?
Quel est l'impact de votre association sur la communauté locale ?""",
            "bx bx-heart",
        ),
        sub(
            "Film au cinéma",
            "Je suis un(e) collègue de travail. Je viens de voir un film au cinéma. Vous me posez des questions sur ce film pour décider si vous irez le voir (thème, acteurs principaux, horaires des séances, etc.)",
            """Quel est le titre du film et qui en est le réalisateur ?
Quel est le thème principal du film ?
Qui sont les acteurs principaux et comment était leur performance ?
Où le film a-t-il été tourné ?
Quelles sont les séances disponibles pour aller voir ce film ?
Le film est-il adapté d'un livre ou d'une histoire vraie ?
Comment qualifieriez-vous la bande sonore ? Est-elle notable ?
Y a-t-il des éléments particuliers, comme des effets spéciaux, qui se démarquent dans ce film ?
Quel public ciblez-vous pour ce film ?
Quelle note donneriez-vous au film sur une échelle de 1 à 10 ?""",
            "bx bx-movie",
        ),
        sub(
            "Parcours professionnel au Québec",
            "Je suis votre nouveau/nouvelle collègue. J'ai vécu au Québec, je vis et travaille actuellement dans votre pays. Posez-moi des questions sur mon parcours professionnel (études, premier emploi, expériences suivantes, expérience de l'expatriation, intérêt, difficultés, etc.).",
            """Quelles études avez-vous suivies pour préparer votre carrière ?
Quel a été votre premier emploi et dans quel domaine ?
Pouvez-vous décrire l'évolution de votre parcours professionnel jusqu'à présent ?
Quelles ont été vos principales expériences de travail au Québec ?
Quelles raisons vous ont poussé à vous expatrier ?
Quels sont les défis majeurs que vous avez rencontrés en travaillant à l'étranger ?
Comment votre expérience internationale a-t-elle influencé votre carrière ?
Y a-t-il des compétences spécifiques que vous avez développées grâce à votre expatriation ?
Avez-vous des conseils pour quelqu'un envisageant de travailler à l'étranger ?
Quels sont vos projets professionnels futurs ?""",
            "bx bx-briefcase",
        ),
        sub(
            "Recherche de logement au Québec",
            "Je travaille dans une agence de location. Vous venez d'arriver au Québec et vous cherchez un logement. Vous me posez des questions pour trouver un appartement (démarches, quartier, type de logement, etc.)",
            """Quelles sont les démarches à suivre pour louer un appartement via votre agence ?
Quels types de logements avez-vous disponibles ?
Dans quels quartiers se trouvent ces logements ?
Y a-t-il des frais d'agence ou autres frais supplémentaires à prévoir ?
Quelles sont les conditions requises pour louer un appartement ?
Proposez-vous des logements meublés ou non meublés ?
Quelle est la durée minimum de location chez vous ?
Y a-t-il des logements particulièrement adaptés aux familles ?
Quelles garanties doivent fournir les locataires ?
Comment s'effectue le processus de sélection des locataires ?""",
            "bx bx-home",
        ),
        sub(
            "Inscription à la bibliothèque",
            "Je travaille dans une bibliothèque. Vous aimeriez vous inscrire. Vous m'interrogez sur le fonctionnement de la bibliothèque (conditions d'inscription, programmation culturelle, horaires, etc.)",
            """Quelles sont les conditions pour s'inscrire à la bibliothèque ?
Quels types de documents puis-je emprunter ?
Quelles sont vos heures d'ouverture ?
Organisez-vous des événements ou des activités culturelles ?
La bibliothèque propose-t-elle des ressources en ligne ?
Y a-t-il des limitations sur le nombre de documents qu'on peut emprunter ?
Combien de temps peut-on garder les documents empruntés ?
La bibliothèque offre-t-elle des services spéciaux pour les enfants ou les personnes âgées ?
Comment puis-je accéder aux archives ou aux collections spéciales ?
Y a-t-il des frais associés à certains services spécifiques de la bibliothèque ?""",
            "bx bx-book",
        ),
    ]),
    part("tache2", 2, [
        sub(
            "Sortie à petit prix",
            "Je suis votre ami(e). Vous venez d'arriver dans ma ville. Vous me posez des questions pour organiser une sortie à petit prix (lieux, activités, transports, etc.).",
            """Quels sont les lieux gratuits ou peu chers à visiter dans ta ville ?
Y a-t-il des musées ou monuments avec des tarifs réduits ?
Est-ce qu'il existe des parcs ou des espaces verts agréables pour se promener ?
Quelles activités culturelles peut-on faire sans trop dépenser ?
Y a-t-il des événements gratuits en ce moment, comme des concerts ou des festivals ?
Quels moyens de transport coûtent le moins cher ici ?
Est-ce qu'il y a des cartes spéciales ou des abonnements pour les visiteurs ?
Peux-tu me recommander un restaurant ou un café bon marché ?
Est-ce qu'il existe des marchés locaux où je peux goûter des spécialités à petit prix ?
Où est-ce que les habitants sortent d'habitude pour se divertir sans trop payer ?
Est-il possible de visiter les alentours de la ville pour pas trop cher ?
Est-ce que tu pourrais m'accompagner pour m'aider à découvrir ces endroits économiques ?""",
            "bx bx-map",
        ),
        sub(
            "Maison de vacances près de la mer",
            "Je suis votre voisin(e). Je possède une petite maison près de la mer que je loue pour les vacances. Vous êtes intéressé(e). Vous me posez des questions pour savoir si vous allez la louer (équipement, cadre, prix, etc.).",
            """Quelle est la superficie de la maison ?
Combien de chambres et de lits y a-t-il ?
Est-ce que la maison est bien équipée (cuisine, salle de bain, électroménager) ?
Y a-t-il une connexion Internet ou la télévision ?
Est-ce que la maison est proche de la plage ?
Quels types d'activités peut-on faire dans les environs ?
La maison est-elle adaptée pour une famille avec enfants ?
Est-ce qu'il y a un espace extérieur, comme un jardin ou une terrasse ?
Quels sont les tarifs de la location à la semaine ?
Est-ce que les charges (eau, électricité) sont incluses dans le prix ?
Quels sont les moyens de transport pour arriver facilement jusqu'à la maison ?""",
            "bx bx-home-heart",
        ),
        sub(
            "Livraison d'un meuble",
            "Je travaille dans un magasin de meubles. Vous voulez faire livrer un meuble. Vous me posez des questions sur les conditions de livraison (tarifs, délais, mode de transport, etc.).",
            """Est-ce que vous proposez un service de livraison à domicile ?
Quels sont les tarifs pour la livraison ?
Est-ce que le prix dépend de la distance ?
Quel est le délai moyen pour recevoir un meuble livré ?
Puis-je choisir la date et l'heure de la livraison ?
Quels moyens de transport utilisez-vous pour la livraison ?
Est-ce que les livreurs installent le meuble à l'intérieur de la maison ?
Y a-t-il un service de montage inclus ou faut-il le payer en plus ?
Est-ce que je peux suivre la livraison en ligne ou par téléphone ?
Que se passe-t-il si je ne suis pas chez moi le jour de la livraison ?
Offrez-vous la possibilité de livrer à l'étage si l'appartement n'a pas d'ascenseur ?
La livraison est-elle gratuite à partir d'un certain montant d'achat ?""",
            "bx bx-package",
        ),
        sub(
            "Passion pour la montagne",
            "Nous sommes dans une salle d'attente. Nous ne nous connaissons pas. Le train est en retard et nous discutons. Je vous dis que je suis passionné(e) par la montagne. Vous me posez des questions sur cette passion (endroits, activités, matériel, etc.).",
            """Depuis combien de temps aimez-vous la montagne ?
Quelle est votre région ou montagne préférée ?
Quelles activités pratiquez-vous à la montagne ?
Préférez-vous l'été ou l'hiver pour aller en montagne ?
Est-ce que vous faites de la randonnée régulièrement ?
Connaissez-vous de bons itinéraires pour les débutants ?
Pratiquez-vous aussi des sports d'hiver comme le ski ou le snowboard ?
Est-ce que vous voyagez loin pour aller en montagne ?
Avec qui partez-vous habituellement, seul ou en groupe ?
Quels équipements faut-il absolument avoir pour bien profiter de la montagne ?
Est-ce que vous participez à des clubs ou des associations liés à cette passion ?
Quelle est votre plus belle expérience en montagne ?""",
            "bx bx-landscape",
        ),
        sub(
            "Retour de voyage",
            "Nous sommes à une soirée et nous faisons connaissance. Je reviens d'un voyage. Vous me posez des questions sur ce séjour (durée, lieux, impressions, etc.).",
            """Où êtes-vous parti en voyage ?
Combien de temps êtes-vous resté là-bas ?
Qu'est-ce qui vous a le plus marqué pendant ce séjour ?
Avez-vous visité plusieurs villes ou une seule région ?
Quelles activités avez-vous faites sur place ?
Est-ce que vous avez rencontré des habitants intéressants ?
Quelle a été votre expérience culinaire pendant ce voyage ?
Y a-t-il un endroit que vous recommandez absolument ?
Qu'est-ce qui vous a surpris dans ce pays ou cette région ?
Avez-vous rencontré des difficultés, par exemple avec la langue ou les transports ?
Est-ce que vous aimeriez y retourner un jour ?
Quel souvenir avez-vous rapporté de ce voyage ?""",
            "bx bx-world",
        ),
    ]),
    part("tache2", 3, [
        sub(
            "Voyage au Canada",
            "Je suis votre collègue et j'ai récemment voyagé au Canada. Vous êtes intéressé(e) par mon expérience et vous me posez des questions sur ce séjour (activités, moyens de transport, loisirs, etc.).",
            """Tu es resté(e) combien de temps au Canada ?
Tu es allé(e) dans quelles villes exactement ?
Quelles activités as-tu faites là-bas ?
Tu as visité des lieux touristiques ?
Tu as rencontré des Canadiens sympas ?
Comment est l'ambiance là-bas ?
Tu as voyagé seul(e) ou accompagné(e) ?
Quel moyen de transport as-tu utilisé pour te déplacer sur place ?
C'était facile de se repérer ?
Tu as testé la cuisine locale ?
Qu'est-ce qui t'a le plus marqué durant ton séjour ?
Est-ce que tu recommanderais ce voyage ?
Tu penses y retourner un jour ?""",
            "bx bx-flag",
        ),
        sub(
            "Location en bord de mer",
            "Je suis votre voisin(e) et je propose une maison à louer en bord de mer. Vous êtes intéressé(e) et vous me posez des questions sur cette location.",
            """La maison se trouve dans quelle ville exactement ?
Elle est située à combien de mètres de la plage ?
Combien de chambres y a-t-il ?
Est-ce qu'il y a une cuisine équipée ?
Y a-t-il une terrasse ou un jardin ?
Est-ce qu'il y a une place de parking ?
Quels sont les équipements inclus (Wi-Fi, TV, linge…) ?
Peut-on venir avec des enfants ?
Les animaux sont-ils acceptés ?
Quel est le prix par nuit ou par semaine ?
Y a-t-il un supermarché ou des restaurants à proximité ?
Est-ce qu'il faut verser une caution ?
Comment se passe la réservation ?""",
            "bx bx-home",
        ),
        sub(
            "Surveillance d'appartement",
            "Je suis votre ami(e). Je pars bientôt en vacances et je souhaite que vous surveilliez mon appartement pendant mon absence. Vous me posez des questions (dates, choses à faire, consignes, etc.).",
            """Tu pars à partir de quelle date exactement ?
Tu reviens quand ?
Tu veux que je passe tous les jours ou un jour sur deux ?
Est-ce que je dois arroser les plantes ?
Tu as des animaux à nourrir ?
Tu veux que je relève ton courrier ?
Est-ce que quelqu'un d'autre a les clés ?
Est-ce qu'il y a une alarme ou un code à connaître ?
Je peux ouvrir les fenêtres un peu si besoin ?
Tu veux que je vérifie le frigo ou les prises ?
Est-ce que je peux rester un peu dans l'appart ou juste passer ?
Tu me laisseras les clés où ?
Tu veux que je t'envoie des nouvelles pendant ton absence ?""",
            "bx bx-key",
        ),
        sub(
            "Passion montagne en gare",
            "Nous venons de faire connaissance dans une gare. Vous engagez la conversation avec moi pour en savoir plus sur ma passion pour la montagne, et vous me posez des questions à ce sujet.",
            """Depuis combien de temps tu fais de la montagne ?
Tu pratiques quel type d'activité : randonnée, escalade, ski ?
Tu y vas souvent ?
Tu pars seul(e) ou avec un groupe ?
C'est quelle région ta préférée ?
Tu dors en refuge ou tu fais du camping ?
Tu as déjà fait des sommets difficiles ?
Tu as un équipement spécial à chaque fois ?
Tu préfères les montagnes d'été ou d'hiver ?
Est-ce que tu prends des photos là-haut ?
Tu as déjà eu des moments dangereux ?
Qu'est-ce que tu aimes le plus dans la montagne ?
Tu conseilles ça à quelqu'un qui débute ?""",
            "bx bx-landscape",
        ),
        sub(
            "Commande et livraison de meuble",
            "Je travaille pour une entreprise de livraison. Vous voulez commander un meuble et vous me posez des questions pour connaître les détails (prix, délai de livraison, conditions, etc.).",
            """Combien coûte ce meuble exactement ?
Quelles sont les dimensions ?
Est-ce que le montage est inclus ?
La livraison est-elle gratuite ?
En combien de jours je peux le recevoir ?
Est-ce que je peux choisir la date de livraison ?
Vous livrez aussi le week-end ?
Est-ce qu'on m'appelle avant de venir ?
Y a-t-il des frais supplémentaires pour l'étage ?
Je peux payer à la livraison ?
Est-ce que je peux retourner le meuble s'il ne me convient pas ?
Est-ce qu'il y a une garantie ?
Vous proposez d'autres modèles dans la même gamme ?""",
            "bx bx-truck",
        ),
    ]),
    part("tache2", 4, [
        sub(
            "Nouveau à l'entreprise",
            "Je suis un(e) de vos collègues. Vous êtes arrivé(e) récemment dans l'entreprise et vous souhaitez en savoir plus. Vous me posez des questions sur le fonctionnement de l'entreprise (organisation, ambiance, cantine, etc.).",
            """À quelle heure commence le travail ici en général ?
Est-ce qu'on peut faire du télétravail parfois ?
Comment est l'ambiance avec les collègues ?
Est-ce que l'équipe est grande ?
Y a-t-il une hiérarchie très stricte ?
Est-ce qu'il y a une pause café dans la matinée ?
La cantine est bonne ?
Il y a des restaurants autour pour le déjeuner ?
Est-ce que les horaires sont flexibles ?
Où est-ce qu'on peut trouver le planning des réunions ?
Est-ce qu'il y a une formation prévue pour les nouveaux ?
Y a-t-il un responsable RH à qui je peux parler ?
Tu aurais un conseil à me donner pour bien m'intégrer ?""",
            "bx bx-building",
        ),
        sub(
            "Première fête au Québec",
            "Je suis votre ami(e) et je vous invite à une fête. Vous venez de vous installer au Québec et c'est votre première fête ici. Vous me posez des questions sur cet événement (heure, cadeau à apporter, tenue, etc.).",
            """À quelle heure commence la fête ?
Est-ce qu'il faut arriver à l'heure pile ou un peu plus tard ?
Est-ce que je dois apporter un cadeau ?
Quel genre de cadeau est le plus apprécié ici ?
Est-ce qu'on dîne sur place ou c'est juste un apéritif ?
Est-ce que je peux venir avec quelqu'un ?
Est-ce qu'il faut s'habiller de manière spéciale ?
Est-ce qu'il y aura de la musique ?
On danse pendant la fête ?
Est-ce qu'il y aura des gens que je connais ?
Est-ce que je peux aider à préparer quelque chose ?
Combien de personnes seront présentes environ ?
Est-ce que la fête va durer tard ?""",
            "bx bx-party",
        ),
        sub(
            "Club de sport du quartier",
            "Je travaille à l'accueil du club de sport de votre quartier. Vous voulez commencer une activité sportive mais vous hésitez. Vous me posez des questions sur les sports proposés (horaires, prix, matériel, etc.).",
            """Quels types de sports proposez-vous ici ?
Est-ce qu'il y a des cours collectifs ?
Est-ce que vous avez une salle de musculation ?
Y a-t-il une piscine dans le club ?
Quels sont les horaires d'ouverture ?
Peut-on venir quand on veut ou faut-il réserver ?
Est-ce qu'il faut un certificat médical ?
Quels sont les tarifs d'abonnement ?
Est-ce qu'il y a des réductions pour les étudiants ou les familles ?
Est-ce que vous prêtez du matériel ou faut-il tout acheter ?
Peut-on faire un essai gratuit avant de s'inscrire ?
Les coachs sont-ils diplômés ?
Est-ce que vous proposez des cours pour débutants ?""",
            "bx bx-dumbbell",
        ),
        sub(
            "Visite de musée",
            "Je travaille à l'accueil d'un musée. Vous souhaitez visiter ce musée avec des amis. Vous me posez des questions pour organiser votre visite (heures d'ouverture, prix, visites guidées, etc.).",
            """Quelles sont les heures d'ouverture du musée ?
Est-ce que le musée est ouvert le week-end ?
Combien coûte l'entrée ?
Est-ce qu'il y a un tarif réduit pour les étudiants ?
Y a-t-il une visite guidée ?
À quelle heure commencent les visites guidées ?
Est-ce qu'il faut réserver à l'avance ?
Combien de temps dure la visite en général ?
Est-ce que le musée est accessible aux personnes à mobilité réduite ?
Peut-on prendre des photos à l'intérieur ?
Y a-t-il un vestiaire pour déposer les sacs ?
Est-ce qu'il y a un café ou un espace pour manger ?
Le musée propose-t-il des expositions temporaires en ce moment ?""",
            "bx bx-landmark",
        ),
        sub(
            "Week-end en ville",
            "Nous sommes collègues. Vous souhaitez partir en week-end et je vous conseille de visiter une ville que je connais bien. Vous me posez des questions sur cette ville (hébergements, restaurants, lieux à visiter, etc.).",
            """Comment peut-on se rendre facilement dans cette ville ?
Tu me conseilles d'y aller en train ou en voiture ?
Combien de temps faut-il pour y aller ?
Est-ce qu'on peut trouver un hébergement facilement ?
Tu connais un bon hôtel ou un Airbnb à recommander ?
Quels sont les endroits incontournables à visiter ?
Y a-t-il des musées ou des monuments intéressants ?
Est-ce qu'il y a un marché ou des boutiques sympas ?
On peut faire des activités en plein air là-bas ?
Y a-t-il de bons restaurants ?
Tu as une spécialité locale à me conseiller ?
C'est une ville animée le soir ou plutôt tranquille ?
Tu y es déjà allé(e) plusieurs fois ?""",
            "bx bx-map-alt",
        ),
    ]),
]

# Tache 3 corrections loaded from companion module
from eo_aout_2025_tache3_content import TACHE3  # noqa: E402

payload = {
    "slug": "eo-expression-orale-aout-2025",
    "title": "Expression Orale Août 2025",
    "subtitle": SUBTITLE,
    "parts": TACHE2 + TACHE3,
}

OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
n_sub = sum(len(p["subjects"]) for p in payload["parts"])
print(f"OK - {len(payload['parts'])} parties, {n_sub} sujets -> {OUT}")
