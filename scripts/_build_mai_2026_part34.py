#!/usr/bin/env python3
"""Generate mai_2026_part3.php and mai_2026_part4.php from structured combo data."""

from __future__ import annotations

import json
import os
import re

ROOT = os.path.join(os.path.dirname(__file__), "..", "database", "seeds", "exp_ecrite")


def php_str(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def emit_combo(combo: dict, indent: int = 4) -> str:
    sp = " " * indent
    lines = [
        f"{sp}[",
        f"{sp}    'combo' => {combo['combo']},",
        f"{sp}    'tasks' => [",
    ]
    for task in combo["tasks"]:
        lines.append(f"{sp}        [")
        lines.append(f"{sp}            'task' => {task['task']},")
        lines.append(f"{sp}            'prompt' => {php_str(task['prompt'])},")
        lines.append(f"{sp}            'correction' => {php_str(task['correction'])},")
        if "documents" in task:
            lines.append(f"{sp}            'documents' => [")
            for doc in task["documents"]:
                lines.append(f"{sp}                [")
                lines.append(f"{sp}                    'title' => {php_str(doc['title'])},")
                lines.append(f"{sp}                    'content' => {php_str(doc['content'])},")
                lines.append(f"{sp}                ],")
            lines.append(f"{sp}            ],")
        lines.append(f"{sp}        ],")
    lines.append(f"{sp}    ],")
    lines.append(f"{sp}],")
    return "\n".join(lines)


def write_part(filename: str, start: int, end: int, combos: list) -> int:
    header = f"""<?php
declare(strict_types=1);

/** Combinaisons {start} à {end} — Mai 2026 */
return [
"""
    body = "\n".join(emit_combo(c) for c in combos)
    footer = "];\n"
    path = os.path.join(ROOT, filename)
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        f.write(header + body + footer)
    return len(combos)


# Load reusable snippets from existing PHP via regex would be fragile; embed data below.
COMBOS_PART3 = json.loads(r"""
[
  {
    "combo": 25,
    "tasks": [
      {"task": 1, "prompt": "Écrivez un message dans le journal de votre université pour rechercher un partenaire avec qui faire du sport.", "correction": "Bonjour,\nJe suis étudiant à l'université et je cherche un partenaire pour faire du sport régulièrement. J'aimerais pratiquer surtout le jogging, la musculation ou le fitness, deux à trois fois par semaine, après les cours ou le week-end. Je suis motivé, débutant à intermédiaire, et je cherche quelqu'un de sérieux pour rester motivé ensemble. Si vous êtes intéressé(e), n'hésitez pas à me contacter pour en discuter et organiser les séances.\n\nMerci et à bientôt."},
      {"task": 2, "prompt": "Vous avez passé des vacances dans une belle région de votre pays. Vous écrivez un message à vos amis dans lequel vous décrivez votre expérience, vous expliquez pourquoi vous avez beaucoup aimé ce séjour.", "correction": "Salut les amis,\nJ'espère que vous allez bien. Je voulais vous raconter mes dernières vacances dans une très belle région de mon pays. J'ai passé quelques jours dans un endroit calme, entouré de nature, avec des paysages magnifiques. Il y avait des montagnes, des petits villages traditionnels et une ambiance très chaleureuse. Chaque jour, je faisais des promenades, je prenais des photos et je profitais de l'air frais.\nCe que j'ai le plus aimé, c'est la tranquillité et la beauté du lieu. J'ai aussi découvert des spécialités locales délicieuses et rencontré des habitants très accueillants. Ce séjour m'a vraiment permis de me reposer, de changer d'air et d'oublier le stress. Franchement, c'était une expérience incroyable que je recommande à tout le monde.\nÀ bientôt,\nAyoub"},
      {"task": 3, "prompt": "Les animaux de compagnie pour les enfants, pour ou contre ?", "correction": "Animaux de compagnie pour enfants : cadeau éducatif ou responsabilité lourde ?\n\nDans le débat sur les animaux de compagnie pour enfants, les avis sont partagés. Le Document 1 met en avant les bienfaits affectifs et éducatifs pour l'enfant, comme la confiance et l'autonomie. À l'inverse, le Document 2 insiste sur les responsabilités, les coûts et l'engagement à long terme que cela implique.\n\nÀ mon sens, offrir un animal de compagnie à un enfant peut être une expérience très positive, à condition d'être bien encadrée. Comme le souligne le Document 1, l'animal aide l'enfant à lutter contre la solitude et à développer le sens des responsabilités, tout en lui apprenant le respect du vivant. Cependant, le Document 2 rappelle à juste titre qu'un animal n'est pas un simple jouet. Il demande du temps, de l'argent et une implication quotidienne des parents. Avant d'adopter, il est donc essentiel de réfléchir sérieusement et de s'assurer que toute la famille est prête à s'engager sur le long terme.", "documents": [{"title": "Document 1", "content": "Offrir un animal de compagnie à un enfant présente de nombreux avantages, comme le soulignent beaucoup de psychologues. Pour des enfants qui n'ont pas de frères et/ou de soeurs, l'animal est un compagnon qui leur évitera la solitude. Grâce à lui, un enfant prendra confiance en lui et il apprendra vite qu'un animal est un être vivant qui a besoin d'attention et de respect. En sa présence, l'enfant se sentira en sécurité et pourra agir de manière autonome, sans l'aide de ses parents."}, {"title": "Document 2", "content": "Beaucoup d'enfants demandent, un jour ou l'autre, un animal à leurs parents, le plus souvent un chien ou un chat. Mais même si vous avez envie de faire plaisir à votre enfant, il vaut mieux réfléchir sérieusement avant d'acheter un animal domestique. L'animal devient un nouveau membre de la famille et représente un engagement sur de nombreuses années. Or, avoir un animal coûte souvent très cher, et c'est une grande responsabilité. On ne peut pas le traiter comme un jouet que l'on met à la poubelle quand l'enfant s'en désintéresse."}]}
    ]
  },
  {
    "combo": 26,
    "tasks": [
      {"task": 1, "prompt": "Écrivez un message pour inviter vos amis à une fête de fin d'année.", "correction": "Objet : Invitation à la fête de fin d'année\n\nSalut les amis,\n\nJ'espère que vous allez bien. Je vous invite à ma fête de fin d'année le samedi 28 décembre à partir de 19 h, chez moi, au 15 rue des Lilas à Lyon. Ce sera une soirée simple et sympa pour passer un bon moment ensemble avant la nouvelle année. Au programme : musique, jeux, dîner, boissons et quelques surprises. Chacun peut apporter un petit plat, un dessert ou une boisson à partager. Vous pouvez aussi venir avec votre bonne humeur et vos idées pour animer la soirée. Merci de me confirmer votre présence avant le 24 décembre pour mieux organiser la fête.\n\nÀ bientôt,\n\nAYOUB"},
      {"task": 2, "prompt": "Vous avez passé des vacances au Canada par le biais d'une agence de voyage. Écrivez un commentaire pour raconter votre expérience que vous avez vécue durant ce voyage.", "correction": "Mes vacances au Canada avec une agence de voyage\n\nBonjour à tous,\n\nL'été dernier, j'ai passé dix jours au Canada avec ma sœur grâce à une agence de voyage. Nous avons commencé notre séjour à Montréal, où l'ambiance était très chaleureuse et les rues étaient pleines de musique. Le programme était bien organisé, ce qui nous a permis de visiter le Vieux-Montréal, le parc du Mont-Royal et plusieurs petits cafés très agréables.\n\nEnsuite, nous sommes allés à Québec. J'ai adoré cette ville parce qu'elle était calme, propre et vraiment magnifique. Nous avons marché dans les rues historiques, pris beaucoup de photos et goûté des spécialités locales. Le meilleur moment a été notre excursion aux chutes Montmorency. Le paysage était impressionnant, et j'ai ressenti beaucoup de joie et d'émerveillement.\n\nDans l'ensemble, j'ai vécu une expérience inoubliable. L'agence a bien préparé le voyage, même si les journées étaient parfois un peu trop chargées. Je recommande vraiment cette expérience.\n\nÀ bientôt,\n\nAYOUB"},
      {"task": 3, "prompt": "Limitation des voitures dans les centres-villes.", "correction": "La place de la voiture en centre-ville\n\nLa limitation des voitures dans les centres-villes suscite un vif débat. Le premier document souligne les effets positifs de cette mesure, comme la baisse des accidents, de la pollution et de la dépendance au pétrole. Cependant, le second rappelle qu'une telle transition exige des infrastructures adaptées et des exceptions pour certains professionnels.\n\nÀ mon avis, limiter les voitures en centre-ville est une bonne décision, à condition de bien préparer ce changement. D'abord, cette mesure améliore la santé publique, car un air moins pollué réduit les problèmes respiratoires. Ensuite, elle rend la ville plus agréable et plus sûre pour les piétons et les cyclistes. De plus, elle encourage l'utilisation des transports en commun, ce qui peut diminuer les embouteillages. Par exemple, dans certaines villes européennes, des rues devenues piétonnes attirent davantage de familles et de commerces. Enfin, cette politique ne peut réussir que si les autorités créent des parkings relais, renforcent les bus et prévoient des accès pour les services essentiels.", "documents": [{"title": "Document 1", "content": "Avec des taux de pollution alarmants constatés dans plusieurs endroits dans le monde, plusieurs villes ont réussi leur pari d'interdire la circulation des voitures en zone urbaine. La capitale de Norvège, Oslo, a récemment opté pour cette solution et s'en félicite, estimant que c'est une décision bénéfique pour tout le monde. Après un certain temps, les accidents diminueront, la dépendance au pétrole baissera et la qualité d'air sera meilleure !"}, {"title": "Document 2", "content": "Beaucoup de villes se lancent dans des projets d'interdiction de voitures en zone urbaine sans mettre en place les outils et les infrastructures nécessaires pour réussir cette transition. Certes, en diminuant les voitures, on aura moins pollué, mais en contrepartie, il faut prévoir, entre autres, de gigantesques parkings pour garer les voitures, opter davantage pour le transport en commun (métros et bus) et prévoir des autorisations de circulation pour certains corps de métier (comme la police, les urgentistes, les livreurs, etc.)."}]}
    ]
  }
]
""")

# Append remaining combos via exec of a larger data block in the script file itself
# For maintainability, load from external JSON if present
json_path = os.path.join(os.path.dirname(__file__), "_mai_combo_25_48.json")
if os.path.isfile(json_path):
    with open(json_path, encoding="utf-8") as jf:
        all_combos = json.load(jf)
else:
    all_combos = COMBOS_PART3  # fallback partial

part3 = [c for c in all_combos if 25 <= c["combo"] <= 36]
part4 = [c for c in all_combos if 37 <= c["combo"] <= 48]

if part3:
    n3 = write_part("mai_2026_part3.php", 25, 36, part3)
    print(f"mai_2026_part3.php: {n3} combos")
if part4:
    n4 = write_part("mai_2026_part4.php", 37, 48, part4)
    print(f"mai_2026_part4.php: {n4} combos")
