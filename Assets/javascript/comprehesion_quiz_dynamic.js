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
const backToStartBtn = document.getElementById("back-to-start-btn");

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

function startQuiz() {
  if (!questions.length) return;
  if (resultsScreen) resultsScreen.classList.add("hidden");
  startScreen.classList.add("hidden");
  quizScreen.classList.remove("hidden");
  startTime = new Date();
  initQuiz();
}

function resetToStart() {
  clearInterval(timerInterval);
  quizStarted = false;
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

  // Fonction pour convertir les retours à la ligne en <br>
  function convertNewlinesToBr(text) {
    if (!text) return text;
    return text.replace(/\n/g, '<br>');
  }

  // Afficher ou masquer la situation
  if (question.situation) {
    situationContainer.classList.remove("hidden");
    situationText.innerHTML = convertNewlinesToBr(question.situation);
  } else {
    situationContainer.classList.add("hidden");
  }

  // Mettre Ã  jour le texte de la question
  questionText.innerHTML = convertNewlinesToBr(question.text);

  // GÃ©nÃ©rer les rÃ©ponses
  answersContainer.innerHTML = "";
  question.answers.forEach((answer) => {
    const answerElement = document.createElement("div");
    answerElement.className = "answer";

    const input = document.createElement("input");
    input.type = "radio";
    input.name = "answer";
    input.value = answer.id;
    input.id = `answer-${answer.id}`;

    // VÃ©rifier si cette rÃ©ponse a dÃ©jÃ  Ã©tÃ© sÃ©lectionnÃ©e
    if (userAnswers[question.id] === answer.id) {
      input.checked = true;
    }

    const label = document.createElement("label");
    label.htmlFor = `answer-${answer.id}`;
    label.innerHTML = convertNewlinesToBr(answer.text);

    input.addEventListener("change", () => {
      userAnswers[question.id] = answer.id;
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
      userAnswers[question.id] = answer.id;
      updateIndicators();
    });

    answerElement.appendChild(input);
    answerElement.appendChild(label);
    answersContainer.appendChild(answerElement);
  });

  // Mettre Ã  jour les boutons de navigation
  prevBtn.classList.toggle("hidden", currentQuestionIndex === 0);
  nextBtn.classList.toggle(
    "hidden",
    currentQuestionIndex === questions.length - 1
  );
  finishBtn.classList.toggle(
    "hidden",
    currentQuestionIndex !== questions.length - 1
  );

  // Mettre Ã  jour les indicateurs
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
    if (userAnswers[questionId]) {
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
  let totalPointsEarned = 0;
  questions.forEach((question) => {
    const userAnswerId = userAnswers[question.id];
    if (userAnswerId) {
      const selectedAnswer = question.answers.find(
        (answer) => answer.id === userAnswerId
      );
      if (selectedAnswer && selectedAnswer.correct) {
        correctCount++;
        totalPointsEarned += question.points;
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
  if (incorrectEl)
    incorrectEl.textContent = String(questions.length - correctCount);
  if (timeTakenEl) timeTakenEl.textContent = getTimeTaken();
  if (totalPointsEl) totalPointsEl.textContent = String(totalPointsEarned);

  const circumference = 2 * Math.PI * 15.9155;
  const dashArray = (pct / 100) * circumference;
  if (circle)
    circle.style.strokeDasharray = dashArray + " " + circumference;

  createResultsIndicators();
  createAnswersReview();

  quizScreen.classList.add("hidden");
  resultsScreen.classList.remove("hidden");
}

// CrÃ©er les indicateurs de rÃ©sultats
function createResultsIndicators() {
  resultsIndicators.innerHTML = "";
  questions.forEach((question, index) => {
    const indicator = document.createElement("div");
    indicator.className = "indicator";

    const userAnswerId = userAnswers[question.id];
    let isCorrect = false;

    if (userAnswerId) {
      const selectedAnswer = question.answers.find(
        (answer) => answer.id === userAnswerId
      );
      isCorrect = selectedAnswer && selectedAnswer.correct;
    }

    if (!userAnswerId) {
      indicator.classList.add("unanswered");
    } else if (isCorrect) {
      indicator.classList.add("correct");
    } else {
      indicator.classList.add("incorrect");
    }

    indicator.textContent = index + 1;
    resultsIndicators.appendChild(indicator);
  });
}

// CrÃ©er la revue des rÃ©ponses
function createAnswersReview() {
  answersReview.innerHTML = "";

  questions.forEach((question, index) => {
    const reviewItem = document.createElement("div");
    reviewItem.className = "review-item";

    // Ajouter la question
    const questionElement = document.createElement("div");
    questionElement.className = "review-question";
    questionElement.innerHTML = `<strong>Question ${index + 1}:</strong> ${
      convertNewlinesToBr(question.text)
    }`;
    reviewItem.appendChild(questionElement);

    // Ajouter la situation si elle existe
    if (question.situation) {
      const situationElement = document.createElement("div");
      situationElement.className = "review-situation";
      situationElement.innerHTML = `<strong>Situation:</strong> ${convertNewlinesToBr(question.situation)}`;
      reviewItem.appendChild(situationElement);
    }

    // Ajouter les rÃ©ponses
    question.answers.forEach((answer) => {
      const answerElement = document.createElement("div");
      answerElement.className = "review-answer";

      // VÃ©rifier si c'est la rÃ©ponse de l'utilisateur
      const isUserAnswer = userAnswers[question.id] === answer.id;

      // VÃ©rifier si c'est la bonne rÃ©ponse
      if (answer.correct) {
        answerElement.classList.add("correct");
        answerElement.innerHTML = `<i class='bx bx-check'></i> ${convertNewlinesToBr(answer.text)}`;
      } else if (isUserAnswer && !answer.correct) {
        answerElement.classList.add("incorrect");
        answerElement.innerHTML = `<i class='bx bx-x'></i> ${convertNewlinesToBr(answer.text)}`;
      } else {
        answerElement.innerHTML = `<i class='bx bx-circle'></i> ${convertNewlinesToBr(answer.text)}`;
      }

      if (isUserAnswer) {
        answerElement.classList.add("selected");
      }

      reviewItem.appendChild(answerElement);
    });

    answersReview.appendChild(reviewItem);
  });
}

function bindCeEvents() {
  if (ceEventsBound) return;
  ceEventsBound = true;

  startBtn.addEventListener("click", startQuiz);

  quitBtn.addEventListener("click", () => {
    clearInterval(timerInterval);
    if (
      confirm(
        "Quitter le quiz ? Votre progression sera perdue."
      )
    ) {
      quizScreen.classList.add("hidden");
      startScreen.classList.remove("hidden");
      if (resultsScreen) resultsScreen.classList.add("hidden");
      quizStarted = false;
    } else {
      startTimer();
    }
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
    if (confirm("Terminer le quiz maintenant ?")) {
      finishQuiz();
    }
  });

  restartBtn.addEventListener("click", startQuiz);

  backToStartBtn.addEventListener("click", resetToStart);
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
