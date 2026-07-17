# -*- coding: utf-8 -*-
"""Contenu Tache 3 — Expression orale Novembre 2025 (texte source exact)."""
from pathlib import Path

_CORR_DIR = Path(__file__).resolve().parent.parent / "database" / "seeds" / "exp_orale" / "novembre_t3"


def sub(title, prompt, correction_file=None, icon="bx bx-message-detail"):
    correction = ""
    if correction_file:
        path = _CORR_DIR / correction_file
        if path.is_file():
            correction = path.read_text(encoding="utf-8").strip()
    return {
        "title": title,
        "prompt": prompt.strip(),
        "correction": correction,
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
            "Sujet 1",
            "Au travail, faut-il toujours suivre les consignes de son supérieur ? Êtes-vous d'accord ?",
            "p1_s1.txt",
        ),
        sub(
            "Sujet 2",
            "Pensez-vous qu'il soit essentiel d'apprendre des langues étrangères ? Pourquoi ?",
            "p1_s2.txt",
        ),
        sub(
            "Sujet 3",
            "Autoriser la sieste au travail est une bonne idée. Qu'en pensez-vous ?",
            "p1_s3.txt",
        ),
        sub(
            "Sujet 4",
            "Les outils numériques comme les réseaux sociaux ou les applications nous rendent-ils moins actifs ? Qu'en pensez-vous ?",
            "p1_s4.txt",
        ),
        sub(
            "Sujet 5",
            "D'après vous, la place réservée aux personnes en situation de handicap dans la société est-elle suffisante ? Pourquoi ?",
            "p1_s5.txt",
        ),
    ]),
    part("tache3", 2, [
        sub(
            "Sujet 1",
            "De nos jours, les gens ne s'envoient presque plus de courrier (cartes de vœux, lettres d'amour, etc.). Est-ce que c'est regrettable ?",
            "p2_s1.txt",
        ),
        sub(
            "Sujet 2",
            "Est-ce que vous pensez qu'aujourd'hui on peut vivre sans voiture ?",
            "p2_s2.txt",
        ),
        sub(
            "Sujet 3",
            "Il est plus facile de vivre seul(e) qu'en famille. Qu'en pensez-vous ?",
            "p2_s3.txt",
        ),
        sub(
            "Sujet 4",
            "Pensez-vous que faire travailler les gens jusqu'à 70 ans est une mesure acceptable ?",
            "p2_s4.txt",
        ),
        sub(
            "Sujet 5",
            "Une expérience de vie à l'étranger est toujours positive. Êtes-vous d'accord avec cette affirmation ? Pourquoi ?",
            "p2_s5.txt",
        ),
    ]),
    part("tache3", 3, [
        sub(
            "Sujet 1",
            "Avoir vécu dans un pays étranger constitue-t-il un atout pour réussir sa carrière professionnelle ?",
            None,
        ),
        sub(
            "Sujet 2",
            "Le tourisme représente-t-il une voie de développement intéressante pour tous les pays ? Pourquoi ?",
            "p3_s2.txt",
        ),
        sub(
            "Sujet 3",
            "Selon vous, est-il difficile de s'installer à l'étranger ? Pourquoi ?",
            "p3_s3.txt",
        ),
        sub(
            "Sujet 4",
            "Est-il préférable de commencer l'apprentissage des langues étrangères dès l'enfance ? Pourquoi ?",
            "p3_s4.txt",
        ),
        sub(
            "Sujet 5",
            "D'après vous, pour quelles raisons les gens aiment-ils découvrir la vie des célébrités ?",
            "p3_s5.txt",
        ),
    ]),
]
