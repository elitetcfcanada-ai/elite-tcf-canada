let questions = [];
let examMeta = null;
let initialDurationSeconds = 3600;
let currentQuestionIndex = 0;
let userAnswers = {};
let timeLeft = 3600;
let timerInterval;
let quizStarted = false;
let ceEventsBound = false;
let startTime = null;

/** Disponible partout (correction résultats incluse) — ne pas imbriquer. */
function convertNewlinesToBr(text) {
  if (text == null || text === "") return "";
  var t = String(text);
  if (/&lt;\/?[a-z]/i.test(t) && !/<[a-z]/i.test(t)) {
    var ta = document.createElement("textarea");
    ta.innerHTML = t;
    t = ta.value;
  }
  if (/<[a-z][\s\S]*>/i.test(t)) return t;
  return t.replace(/\n/g, "<br>");
}

function answerIdKey(id) {
  return String(id == null ? "" : id);
}

/** Réponse utilisateur : { id, index } (index = source de vérité). */
function getUserAnswerMeta(questionId) {
  var raw = userAnswers[questionId];
  if (raw == null || raw === "") {
    return { id: "", index: -1 };
  }
  if (typeof raw === "object") {
    return {
      id: answerIdKey(raw.id),
      index: typeof raw.index === "number" ? raw.index : -1,
    };
  }
  return { id: answerIdKey(raw), index: -1 };
}

function setUserAnswer(questionId, answerId, answerIndex) {
  userAnswers[questionId] = {
    id: answerIdKey(answerId),
    index: typeof answerIndex === "number" ? answerIndex : -1,
  };
}

function hasUserAnswer(questionId) {
  var meta = getUserAnswerMeta(questionId);
  return meta.index >= 0 || !!meta.id;
}

function getSelectedAnswer(question) {
  var meta = getUserAnswerMeta(question.id);
  var answers = question.answers || [];
  if (meta.index >= 0 && answers[meta.index]) {
    return { answer: answers[meta.index], index: meta.index };
  }
  if (meta.id) {
    for (var i = 0; i < answers.length; i++) {
      if (answerIdKey(answers[i].id) === meta.id) {
        return { answer: answers[i], index: i };
      }
    }
  }
  return null;
}

// Elements DOM
const startScreen = document.getElementById("start-screen");
const quizScreen = document.getElementById("quiz-screen");
const resultsScreen = document.getElementById("results-screen");

const startBtn = document.getElementById("start-btn");
const quitBtn = document.getElementById("quit-btn");
const prevBtn = document.getElementById("prev-btn");
const nextBtn = document.getElementById("next-btn");
const finishBtn = document.getElementById("finish-btn");
const restartBtn = document.getElementById("restart-btn");
const showCorrectionBtn = document.getElementById("show-correction-btn");
const correctionPanel = document.getElementById("correction-panel");
const correctionNav = document.getElementById("correction-nav");
const correctionPrevBtn = document.getElementById("correction-prev-btn");
const correctionNextBtn = document.getElementById("correction-next-btn");
const correctionRestartBtn = document.getElementById("correction-restart-btn");
const correctionPos = document.getElementById("correction-pos");

const timerDisplay = document.getElementById("timer-display");
const timeProgress = document.getElementById("time-progress");
const questionNumber = document.getElementById("question-number");
const questionPoints = document.getElementById("question-points");
const questionText = document.getElementById("question-text");
const situationContainer = document.getElementById("situation-container");
const situationText = document.getElementById("situation-text");
const answersContainer = document.getElementById("answers-container");
const questionIndicators = document.getElementById("question-indicators");
const resultsIndicators = document.getElementById("results-indicators");
const answersReview = document.getElementById("answers-review");

let focusedReviewIndex = 0;

function setCorrectionBtnLabel(open) {
  if (!showCorrectionBtn) return;
  if (open) {
    showCorrectionBtn.innerHTML =
      "<i class='bx bx-hide'></i>" +
      "<span class='btn-txt btn-txt--full'>Masquer la correction</span>" +
      "<span class='btn-txt btn-txt--short'>Masquer</span>";
    showCorrectionBtn.setAttribute("aria-expanded", "true");
  } else {
    showCorrectionBtn.innerHTML =
      "<i class='bx bx-check-shield'></i>" +
      "<span class='btn-txt btn-txt--full'>Voir la correction</span>" +
      "<span class='btn-txt btn-txt--short'>Correction</span>";
    showCorrectionBtn.setAttribute("aria-expanded", "false");
  }
}

function hideCorrectionPanel() {
  if (correctionPanel) {
    correctionPanel.classList.add("hidden");
    correctionPanel.classList.remove("is-open");
    correctionPanel.setAttribute("hidden", "");
    correctionPanel.setAttribute("aria-hidden", "true");
  }
  if (correctionNav) {
    correctionNav.hidden = true;
  }
  setCorrectionBtnLabel(false);
}

function updateCorrectionNav() {
  var total = questions.length;
  var isLast = focusedReviewIndex >= total - 1;
  var isFirst = focusedReviewIndex <= 0;

  if (correctionPos) {
    correctionPos.textContent =
      total > 0 ? focusedReviewIndex + 1 + " / " + total : "0 / 0";
  }
  if (correctionPrevBtn) {
    correctionPrevBtn.disabled = isFirst;
  }
  if (correctionNextBtn) {
    correctionNextBtn.hidden = isLast || total === 0;
  }
  if (correctionRestartBtn) {
    correctionRestartBtn.hidden = !isLast || total === 0;
  }
  if (correctionNav) {
    var panelOpen =
      correctionPanel &&
      correctionPanel.classList.contains("is-open") &&
      !correctionPanel.hasAttribute("hidden");
    correctionNav.hidden = !panelOpen || total === 0;
  }
}

function focusReviewQuestion(index) {
  if (!questions.length) return;
  focusedReviewIndex = Math.max(0, Math.min(index, questions.length - 1));

  if (answersReview) {
    answersReview.querySelectorAll(".tcf-qpro-review-card").forEach(function (card, i) {
      card.classList.toggle("is-focused", i === focusedReviewIndex);
    });
  }
  if (resultsIndicators) {
    resultsIndicators.querySelectorAll(".indicator").forEach(function (ind, i) {
      ind.classList.toggle("is-active", i === focusedReviewIndex);
      ind.setAttribute("aria-current", i === focusedReviewIndex ? "true" : "false");
    });
  }

  updateCorrectionNav();

  var card = document.getElementById("ce-review-q-" + (focusedReviewIndex + 1));
  if (card && correctionPanel && correctionPanel.classList.contains("is-open")) {
    window.requestAnimationFrame(function () {
      try {
        card.scrollIntoView({ behavior: "smooth", block: "nearest" });
      } catch (e) {
        /* ignore */
      }
    });
  }
}

function appendReviewChoice(container, answer, opts) {
  var isUser = !!(opts && opts.isUser);
  var isCorrect = !!(opts && opts.isCorrect);
  var rowClass = "review-answer";
  if (isCorrect) rowClass += " correct";
  else if (isUser) rowClass += " incorrect selected";
  else rowClass += " is-other";

  var answerElement = document.createElement("div");
  answerElement.className = rowClass;

  var icon = document.createElement("i");
  if (isCorrect) icon.className = "bx bx-check";
  else if (isUser) icon.className = "bx bx-x";
  else icon.className = "bx bx-circle";
  answerElement.appendChild(icon);

  var textSpan = document.createElement("span");
  textSpan.innerHTML = convertNewlinesToBr(answer.text);
  answerElement.appendChild(textSpan);

  /* Badge uniquement pour la bonne réponse — pas de « Votre choix » */
  if (isCorrect) {
    var meta = document.createElement("div");
    meta.className = "review-answer-meta";
    var good = document.createElement("span");
    good.className = "review-badge good";
    good.textContent = "Bonne réponse";
    meta.appendChild(good);
    answerElement.appendChild(meta);
  }

  container.appendChild(answerElement);
}

function showCorrectionPanel(focusIndex) {
  if (!correctionPanel) return;
  try {
    createResultsIndicators();
    createAnswersReview();
  } catch (err) {
    console.error("CE correction build error", err);
  }
  correctionPanel.classList.remove("hidden");
  correctionPanel.classList.add("is-open");
  correctionPanel.removeAttribute("hidden");
  correctionPanel.setAttribute("aria-hidden", "false");
  setCorrectionBtnLabel(true);

  var idx =
    typeof focusIndex === "number" && !isNaN(focusIndex)
      ? focusIndex
      : focusedReviewIndex;
  focusReviewQuestion(idx);

  window.requestAnimationFrame(function () {
    try {
      if (resultsIndicators) {
        resultsIndicators.scrollIntoView({ behavior: "smooth", block: "nearest" });
      }
    } catch (e) {
      /* ignore */
    }
  });
}

function toggleCorrectionPanel() {
  if (!correctionPanel) return;
  var isHidden =
    correctionPanel.classList.contains("hidden") ||
    correctionPanel.hasAttribute("hidden") ||
    !correctionPanel.classList.contains("is-open");
  if (isHidden) showCorrectionPanel();
  else hideCorrectionPanel();
}

function startQuiz() {
  if (!questions.length) return;
  hideCorrectionPanel();
  if (resultsScreen) resultsScreen.classList.add("hidden");
  startScreen.classList.add("hidden");
  quizScreen.classList.remove("hidden");
  startTime = new Date();
  initQuiz();
}

function resetToStart() {
  clearInterval(timerInterval);
  quizStarted = false;
  hideCorrectionPanel();
  if (resultsScreen) resultsScreen.classList.add("hidden");
  quizScreen.classList.add("hidden");
  startScreen.classList.remove("hidden");
}

// Initialisation du quiz (nouvelle tentative ou après « Commencer »)
function initQuiz() {
  currentQuestionIndex = 0;
  userAnswers = {};
  timeLeft = initialDurationSeconds;
  quizStarted = true;

  updateTimerDisplay();
  updateQuestion();
  createIndicators();

  startTimer();
}

// Mettre Ã  jour l'affichage du timer
function updateTimerDisplay() {
  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  timerDisplay.textContent = `${minutes.toString().padStart(2, "0")}:${seconds
    .toString()
    .padStart(2, "0")}`;

  // Mettre Ã  jour la barre de progression
  const progressPercentage =
    initialDurationSeconds > 0 ? (timeLeft / initialDurationSeconds) * 100 : 0;
  timeProgress.style.width = `${progressPercentage}%`;
}

// DÃ©marrer le timer
function startTimer() {
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    timeLeft--;
    updateTimerDisplay();

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      finishQuiz();
    }
  }, 1000);
}

// Mettre Ã  jour l'affichage de la question actuelle
function updateQuestion() {
  const question = questions[currentQuestionIndex];

  // Mettre Ã  jour le numÃ©ro de question et les points
  questionNumber.innerHTML = `<span>Question ${currentQuestionIndex + 1}/${
    questions.length
  }</span><span class="question-points">${question.points} point${
    question.points > 1 ? "s" : ""
  }</span>`;

  // Afficher ou masquer la situation
  if (question.situation) {
    situationContainer.classList.remove("hidden");
    situationText.innerHTML = convertNewlinesToBr(question.situation);
  } else {
    situationContainer.classList.add("hidden");
  }

  // Mettre Ã  jour le texte de la question
  questionText.innerHTML = convertNewlinesToBr(question.text);

  // Générer les réponses
  answersContainer.innerHTML = "";
  const userMeta = getUserAnswerMeta(question.id);
  (question.answers || []).forEach((answer, answerIndex) => {
    const answerElement = document.createElement("div");
    answerElement.className = "answer";
    const aid = answerIdKey(answer.id) || String(answerIndex);
    const inputId = "ce-answer-" + question.id + "-" + answerIndex;

    const input = document.createElement("input");
    input.type = "radio";
    input.name = "answer";
    input.value = aid;
    input.id = inputId;
    input.dataset.answerIndex = String(answerIndex);

    if (
      userMeta.index === answerIndex ||
      (userMeta.index < 0 && userMeta.id && userMeta.id === aid)
    ) {
      input.checked = true;
    }

    const label = document.createElement("label");
    label.htmlFor = inputId;
    label.innerHTML = convertNewlinesToBr(answer.text);

    input.addEventListener("change", () => {
      setUserAnswer(question.id, aid, answerIndex);
      updateIndicators();
    });

    answerElement.addEventListener("click", (e) => {
      if (e.target === input) {
        return;
      }
      if (label.contains(e.target)) {
        return;
      }
      input.checked = true;
      setUserAnswer(question.id, aid, answerIndex);
      updateIndicators();
    });

    answerElement.appendChild(input);
    answerElement.appendChild(label);
    answersContainer.appendChild(answerElement);
  });

  var isLast = currentQuestionIndex >= questions.length - 1;
  if (prevBtn) {
    prevBtn.classList.remove("hidden");
    prevBtn.disabled = currentQuestionIndex === 0;
  }
  if (nextBtn) {
    nextBtn.classList.toggle("hidden", isLast);
    nextBtn.disabled = isLast;
  }
  // Terminer uniquement à la dernière question
  if (finishBtn) finishBtn.classList.toggle("hidden", !isLast);

  // Mettre à jour les indicateurs
  updateIndicators();
}

// CrÃ©er les indicateurs de questions
function createIndicators() {
  questionIndicators.innerHTML = "";
  questions.forEach((question, index) => {
    const indicator = document.createElement("div");
    indicator.className = "indicator unanswered";
    if (index === currentQuestionIndex) {
      indicator.classList.add("current");
    }
    indicator.textContent = index + 1;
    indicator.addEventListener("click", () => {
      if (quizStarted) {
        currentQuestionIndex = index;
        updateQuestion();
      }
    });
    questionIndicators.appendChild(indicator);
  });
}

// Mettre Ã  jour les indicateurs de questions
function updateIndicators() {
  const indicators = questionIndicators.querySelectorAll(".indicator");
  indicators.forEach((indicator, index) => {
    indicator.classList.remove("current", "unanswered", "answered");

    if (index === currentQuestionIndex) {
      indicator.classList.add("current");
    }

    const questionId = questions[index].id;
    if (hasUserAnswer(questionId)) {
      indicator.classList.add("answered");
    } else {
      indicator.classList.add("unanswered");
    }
  });
}

function getTimeTaken() {
  if (!startTime) return "0:00";
  const endTime = new Date();
  const timeDiff = (endTime - startTime) / 1000;
  const minutes = Math.floor(timeDiff / 60);
  const seconds = Math.floor(timeDiff % 60);
  return (
    minutes + ":" + seconds.toString().padStart(2, "0")
  );
}

// Terminer le quiz et afficher les résultats
function finishQuiz() {
  clearInterval(timerInterval);
  quizStarted = false;
  showResults();
}

function showResults() {
  let correctCount = 0;
  let wrongCount = 0;
  let totalPointsEarned = 0;
  questions.forEach((question) => {
    const picked = getSelectedAnswer(question);
    if (picked && picked.answer) {
      if (picked.answer.correct) {
        correctCount++;
        totalPointsEarned += question.points || 0;
      } else {
        wrongCount++;
      }
    }
  });

  const pct = questions.length
    ? Math.round((correctCount / questions.length) * 100)
    : 0;

  let levelLabel = "Niveau A1";
  if (pct >= 90) levelLabel = "Niveau C2";
  else if (pct >= 80) levelLabel = "Niveau C1";
  else if (pct >= 70) levelLabel = "Niveau B2";
  else if (pct >= 60) levelLabel = "Niveau B1";
  else if (pct >= 50) levelLabel = "Niveau A2";

  const pctEl = document.getElementById("percentage-text");
  const levelEl = document.getElementById("level-text");
  const correctEl = document.getElementById("correct-answers");
  const incorrectEl = document.getElementById("incorrect-answers");
  const timeTakenEl = document.getElementById("time-taken");
  const totalPointsEl = document.getElementById("total-points");
  const circle = document.getElementById("score-circle");

  if (pctEl) pctEl.textContent = pct + "%";
  if (levelEl) levelEl.textContent = levelLabel;
  if (correctEl) correctEl.textContent = String(correctCount);
  if (incorrectEl) incorrectEl.textContent = String(wrongCount);
  if (timeTakenEl) timeTakenEl.textContent = getTimeTaken();
  if (totalPointsEl) totalPointsEl.textContent = String(totalPointsEarned);

  const circumference = 2 * Math.PI * 15.9155;
  const dashArray = (pct / 100) * circumference;
  if (circle)
    circle.style.strokeDasharray = dashArray + " " + circumference;

  try {
    createResultsIndicators();
    createAnswersReview();
  } catch (err) {
    console.error("CE results review error", err);
  }

  hideCorrectionPanel();

  if (startScreen) startScreen.classList.add("hidden");
  if (quizScreen) quizScreen.classList.add("hidden");
  if (resultsScreen) {
    resultsScreen.classList.remove("hidden");
    try {
      resultsScreen.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch (e) {
      window.scrollTo(0, 0);
    }
  }
}

// CrÃ©er les indicateurs de rÃ©sultats
function createResultsIndicators() {
  if (!resultsIndicators) return;
  resultsIndicators.innerHTML = "";
  questions.forEach((question, index) => {
    const indicator = document.createElement("div");
    indicator.className = "indicator";

    const picked = getSelectedAnswer(question);
    const isCorrect = !!(picked && picked.answer && picked.answer.correct);

    if (!picked) {
      indicator.classList.add("unanswered");
    } else if (isCorrect) {
      indicator.classList.add("correct");
    } else {
      indicator.classList.add("incorrect");
    }

    indicator.textContent = index + 1;
    indicator.setAttribute("role", "button");
    indicator.setAttribute("tabindex", "0");
    indicator.title = "Voir la question " + (index + 1);
    indicator.addEventListener("click", function () {
      showCorrectionPanel(index);
    });
    indicator.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        showCorrectionPanel(index);
      }
    });
    resultsIndicators.appendChild(indicator);
  });
}

// Créer la revue des réponses
function createAnswersReview() {
  if (!answersReview) return;
  answersReview.innerHTML = "";

  if (!questions.length) {
    answersReview.innerHTML =
      '<p class="tcf-qpro-review-empty">Aucune question à corriger.</p>';
    return;
  }

  questions.forEach((question, index) => {
    const reviewItem = document.createElement("article");
    reviewItem.className = "review-item tcf-qpro-review-card";
    reviewItem.id = "ce-review-q-" + (index + 1);

    const answers = question.answers || [];
    const picked = getSelectedAnswer(question);
    const selectedIndex = picked ? picked.index : -1;
    const qOk = !!(picked && picked.answer && picked.answer.correct);
    const statusClass = !picked
      ? "is-unanswered"
      : qOk
        ? "is-correct"
        : "is-wrong";

    const head = document.createElement("div");
    head.className = "tcf-qpro-review-card__head " + statusClass;
    head.innerHTML =
      '<span class="tcf-qpro-review-card__num">Question ' +
      (index + 1) +
      "</span>" +
      '<span class="tcf-qpro-review-card__status">' +
      (!picked
        ? "Sans réponse"
        : qOk
          ? "Correct"
          : "Incorrect") +
      "</span>";
    reviewItem.appendChild(head);

    const questionElement = document.createElement("div");
    questionElement.className = "review-question";
    questionElement.innerHTML = convertNewlinesToBr(question.text);
    reviewItem.appendChild(questionElement);

    if (question.situation) {
      const situationElement = document.createElement("div");
      situationElement.className = "review-situation";
      situationElement.innerHTML =
        "<strong>Situation</strong> " +
        convertNewlinesToBr(question.situation);
      reviewItem.appendChild(situationElement);
    }

    const choices = document.createElement("div");
    choices.className = "tcf-qpro-review-choices";

    if (!answers.length) {
      choices.innerHTML =
        '<p class="tcf-qpro-review-empty">Aucune proposition en base pour cette question.</p>';
    } else {
      answers.forEach(function (answer, answerIndex) {
        appendReviewChoice(choices, answer, {
          isUser: selectedIndex === answerIndex,
          isCorrect: !!answer.correct,
        });
      });
    }

    reviewItem.appendChild(choices);
    answersReview.appendChild(reviewItem);
  });

  focusReviewQuestion(focusedReviewIndex);
}

function bindCeEvents() {
  if (ceEventsBound) return;
  ceEventsBound = true;

  startBtn.addEventListener("click", startQuiz);

  quitBtn.addEventListener("click", () => {
    clearInterval(timerInterval);
    var ask =
      typeof window.tcfQuizConfirm === "function"
        ? window.tcfQuizConfirm
        : function (o) {
            return Promise.resolve(window.confirm(o.message));
          };
    ask({
      title: "Terminer l’épreuve ?",
      message: "Voulez-vous terminer maintenant et afficher votre score ?",
      confirmLabel: "Oui, terminer",
      cancelLabel: "Continuer",
    }).then(function (ok) {
      if (ok) {
        finishQuiz();
        return;
      }
      ask({
        title: "Quitter sans résultats ?",
        message:
          "Votre progression sera perdue. Confirmez pour quitter sans voir vos résultats.",
        confirmLabel: "Quitter",
        cancelLabel: "Reprendre",
      }).then(function (leave) {
        if (leave) {
          quizScreen.classList.add("hidden");
          startScreen.classList.remove("hidden");
          if (resultsScreen) resultsScreen.classList.add("hidden");
          hideCorrectionPanel();
          quizStarted = false;
        } else {
          startTimer();
        }
      });
    });
  });

  prevBtn.addEventListener("click", () => {
    if (currentQuestionIndex > 0) {
      currentQuestionIndex--;
      updateQuestion();
    }
  });

  nextBtn.addEventListener("click", () => {
    if (currentQuestionIndex < questions.length - 1) {
      currentQuestionIndex++;
      updateQuestion();
    }
  });

  finishBtn.addEventListener("click", () => {
    var ask =
      typeof window.tcfQuizConfirm === "function"
        ? window.tcfQuizConfirm
        : function (o) {
            return Promise.resolve(window.confirm(o.message));
          };
    ask({
      title: "Terminer l’épreuve ?",
      message:
        "Afficher votre score ? Vous pourrez ensuite choisir de voir la correction.",
      confirmLabel: "Oui, terminer",
      cancelLabel: "Continuer",
    }).then(function (ok) {
      if (ok) finishQuiz();
    });
  });

  if (restartBtn) restartBtn.addEventListener("click", startQuiz);

  if (correctionPrevBtn) {
    correctionPrevBtn.addEventListener("click", function () {
      if (focusedReviewIndex > 0) focusReviewQuestion(focusedReviewIndex - 1);
    });
  }
  if (correctionNextBtn) {
    correctionNextBtn.addEventListener("click", function () {
      if (focusedReviewIndex < questions.length - 1) {
        focusReviewQuestion(focusedReviewIndex + 1);
      }
    });
  }
  if (correctionRestartBtn) {
    correctionRestartBtn.addEventListener("click", function () {
      startQuiz();
    });
  }
  if (showCorrectionBtn)
    showCorrectionBtn.addEventListener("click", toggleCorrectionPanel);
}

function showCeLoadError(msg) {
  var el = document.getElementById("ce-load-error");
  if (!el) {
    el = document.createElement("div");
    el.id = "ce-load-error";
    el.style.cssText =
      "margin:1rem;padding:1rem;border-radius:12px;background:#fff3f3;border:1px solid rgba(211,13,13,.35);text-align:center;";
    var main = document.querySelector(".app-main");
    if (main) main.insertBefore(el, main.firstChild);
    else document.body.insertBefore(el, document.body.firstChild);
  }
  el.textContent = msg || "Erreur de chargement.";
}

async function ceBoot() {
  var api =
    typeof window.TCF_CE_API === "string" && window.TCF_CE_API
      ? window.TCF_CE_API
      : "../../ce_api.php";
  var id = new URLSearchParams(location.search).get("exam_id");
  if (!id) {
    showCeLoadError("Paramètre exam_id manquant dans l’URL.");
    if (startBtn) startBtn.disabled = true;
    return;
  }
  if (startBtn) startBtn.disabled = true;
  try {
    var fd = new FormData();
    fd.append("action", "get_exam_quiz");
    fd.append("exam_id", id);
    var r = await fetch(api, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
    });
    var j = await r.json();
    if (!j || !j.success) {
      var m =
        (j && j.message) ||
        "Épreuve indisponible.";
      if (j && j.locked) {
        if (j.reason === "login") {
          var loginBase = window.TCF_LOGIN_URL || "login.php";
          var next = encodeURIComponent(
            "comprehesion_ecrite_quiz.php?exam_id=" + encodeURIComponent(String(id || ""))
          );
          window.location.href =
            loginBase + (loginBase.indexOf("?") >= 0 ? "&" : "?") + "next=" + next;
          return;
        }
        if (j.reason === "subscription") {
          window.location.href = window.TCF_ABO_URL || "abonnement.php";
          return;
        }
        m +=
          " Connectez-vous ou souscrivez à une offre premium si nécessaire.";
      }
      showCeLoadError(m);
      return;
    }
    examMeta = j.data.exam;
    questions = j.data.questions || [];
    initialDurationSeconds =
      examMeta && examMeta.duration_seconds
        ? parseInt(examMeta.duration_seconds, 10)
        : 3600;
    if (initialDurationSeconds < 60) initialDurationSeconds = 3600;
    timeLeft = initialDurationSeconds;

    var qt = document.getElementById("quiz-title");
    var qd = document.getElementById("quiz-description");
    if (qt && examMeta && examMeta.title) qt.textContent = examMeta.title;
    if (qd && examMeta) {
      var sub = (examMeta.subtitle || "").trim();
      qd.textContent = sub || "Lisez les textes et répondez aux questions.";
    }
    var metaQ = document.getElementById("quiz-meta-questions");
    var metaD = document.getElementById("quiz-meta-duration");
    if (metaQ) metaQ.textContent = String(questions.length) + " questions";
    if (metaD) {
      var mins = Math.max(1, Math.round(initialDurationSeconds / 60));
      metaD.textContent = mins + " min";
    }

    updateTimerDisplay();
    bindCeEvents();
    if (startBtn) startBtn.disabled = false;
  } catch (e) {
    showCeLoadError("Erreur réseau ou serveur.");
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", ceBoot);
} else {
  ceBoot();
}
