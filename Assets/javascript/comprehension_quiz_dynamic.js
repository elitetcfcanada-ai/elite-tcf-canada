/**
 * Quiz compréhension orale — données depuis co_api.php (get_exam_quiz).
 */
(function () {
    'use strict';

    var quizData = [];
    var initialDurationSeconds = 1800;
    var currentQuestion = 0;
    var userAnswers = [];
    var timeLeft = 1800;
    var timerInterval = null;
    var startTime = null;
    var eventsBound = false;

    var startScreen;
    var quizScreen;
    var resultsScreen;
    var startBtn;
    var quitBtn;
    var prevBtn;
    var nextBtn;
    var finishBtn;
    var restartBtn;
    var backToStartBtn;
    var questionNumber;
    var questionPoints;
    var coQuestionText;
    var situationImage;
    var questionAudio;
    var playBtn;
    var answersContainer;
    var questionIndicators;
    var timerDisplay;
    var timeProgress;
    var percentageText;
    var levelText;
    var correctAnswers;
    var incorrectAnswers;
    var timeTaken;
    var totalPoints;
    var answersReview;
    var resultsIndicators;
    var scoreCircle;

    function $(id) {
        return document.getElementById(id);
    }

    function initRefs() {
        startScreen = $('start-screen');
        quizScreen = $('quiz-screen');
        resultsScreen = $('results-screen');
        startBtn = $('start-btn');
        quitBtn = $('quit-btn');
        prevBtn = $('prev-btn');
        nextBtn = $('next-btn');
        finishBtn = $('finish-btn');
        restartBtn = $('restart-btn');
        backToStartBtn = $('back-to-start-btn');
        questionNumber = $('question-number');
        questionPoints = $('question-points');
        coQuestionText = $('co-question-text');
        situationImage = $('situation-image');
        questionAudio = $('question-audio');
        playBtn = $('play-btn');
        answersContainer = $('answers-container');
        questionIndicators = $('question-indicators');
        timerDisplay = $('timer-display');
        timeProgress = $('time-progress');
        percentageText = $('percentage-text');
        levelText = $('level-text');
        correctAnswers = $('correct-answers');
        incorrectAnswers = $('incorrect-answers');
        timeTaken = $('time-taken');
        totalPoints = $('total-points');
        answersReview = $('answers-review');
        resultsIndicators = $('results-indicators');
        scoreCircle = $('score-circle');
    }

    function updateTimerDisplay() {
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;
        if (timerDisplay) {
            timerDisplay.textContent =
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        }
        var pct = initialDurationSeconds > 0 ? (timeLeft / initialDurationSeconds) * 100 : 0;
        if (timeProgress) timeProgress.style.width = pct + '%';
    }

    function startTimer() {
        updateTimerDisplay();
        clearInterval(timerInterval);
        timerInterval = setInterval(function () {
            timeLeft--;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                finishQuiz();
            }
        }, 1000);
    }

    function initQuestionIndicators() {
        if (!questionIndicators) return;
        questionIndicators.innerHTML = '';
        quizData.forEach(function (_, index) {
            var indicator = document.createElement('div');
            indicator.className = 'indicator ' + (index === 0 ? 'current' : 'unanswered');
            indicator.textContent = String(index + 1);
            indicator.addEventListener('click', function () {
                navigateToQuestion(index);
            });
            questionIndicators.appendChild(indicator);
        });
    }

    function updateQuestionIndicators() {
        var indicators = document.querySelectorAll('#question-indicators .indicator');
        indicators.forEach(function (indicator, index) {
            indicator.className = 'indicator';
            if (index === currentQuestion) {
                indicator.classList.add('current');
            } else if (userAnswers[index] !== null) {
                indicator.classList.add('answered');
            } else {
                indicator.classList.add('unanswered');
            }
        });
    }

    function loadQuestion() {
        var question = quizData[currentQuestion];
        if (!question) return;

        if (questionNumber) {
            questionNumber.querySelector('span').textContent =
                'Question ' + (currentQuestion + 1) + '/' + quizData.length;
        }
        if (questionPoints) {
            questionPoints.textContent =
                question.points + ' point' + (question.points > 1 ? 's' : '');
        }
        // Fonction pour convertir les retours à la ligne en <br>
        function convertNewlinesToBr(text) {
            if (!text) return text;
            return text.replace(/\n/g, '<br>');
        }

        if (coQuestionText) {
            coQuestionText.innerHTML = convertNewlinesToBr(question.question || '');
        }

        if (situationImage) {
            if (question.image) {
                situationImage.src = question.image;
                situationImage.alt = 'Situation ' + (currentQuestion + 1);
                situationImage.style.display = '';
                situationImage.closest('.situation-container').style.display = '';
            } else {
                situationImage.removeAttribute('src');
                situationImage.style.display = 'none';
                var sit = situationImage.closest('.situation-container');
                if (sit) sit.style.display = 'none';
            }
        }

        if (questionAudio) {
            questionAudio.src = question.audio || '';
            questionAudio.load();
        }

        if (answersContainer) {
            answersContainer.innerHTML = '';
            for (let index = 0; index < question.answers.length; index++) {
                const answer = question.answers[index];
                let answerElement = document.createElement('div');
                answerElement.className = 'answer';

                let input = document.createElement('input');
                input.type = 'radio';
                input.name = 'answer';
                input.value = String(index);
                input.id = 'answer-' + index;

                if (
                    userAnswers[currentQuestion] !== null &&
                    userAnswers[currentQuestion].index === index
                ) {
                    input.checked = true;
                }

                input.addEventListener('change', function () {
                    selectAnswer(index);
                });

                let label = document.createElement('label');
                label.htmlFor = 'answer-' + index;
                label.innerHTML = convertNewlinesToBr(answer.text);

                answerElement.addEventListener('click', function (e) {
                    if (e.target === input) {
                        return;
                    }
                    if (label.contains(e.target)) {
                        return;
                    }
                    input.checked = true;
                    selectAnswer(index);
                });

                answerElement.appendChild(input);
                answerElement.appendChild(label);
                answersContainer.appendChild(answerElement);
            }
        }

        if (prevBtn) prevBtn.disabled = currentQuestion === 0;
        if (nextBtn) nextBtn.disabled = currentQuestion === quizData.length - 1;

        if (currentQuestion === quizData.length - 1) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (finishBtn) finishBtn.classList.remove('hidden');
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (finishBtn) finishBtn.classList.add('hidden');
        }

        updateQuestionIndicators();
    }

    function selectAnswer(answerIndex) {
        var question = quizData[currentQuestion];
        var isCorrect = question.answers[answerIndex].correct;

        userAnswers[currentQuestion] = {
            index: answerIndex,
            correct: isCorrect,
            text: question.answers[answerIndex].text
        };

        updateQuestionIndicators();
    }

    function navigateToQuestion(index) {
        if (index >= 0 && index < quizData.length) {
            currentQuestion = index;
            loadQuestion();
        }
    }

    function getTimeTaken() {
        var endTime = new Date();
        var timeDiff = (endTime - startTime) / 1000;
        var minutes = Math.floor(timeDiff / 60);
        var seconds = Math.floor(timeDiff % 60);
        return minutes + ':' + seconds.toString().padStart(2, '0');
    }

    function finishQuiz() {
        clearInterval(timerInterval);

        var correctCount = userAnswers.filter(function (a) {
            return a !== null && a.correct;
        }).length;

        var totalPointsEarned = userAnswers.reduce(function (total, answer, index) {
            if (answer !== null && answer.correct) {
                return total + quizData[index].points;
            }
            return total;
        }, 0);

        var pct = quizData.length ? Math.round((correctCount / quizData.length) * 100) : 0;

        var level = 'Niveau A1';
        if (pct >= 90) level = 'Niveau C2';
        else if (pct >= 80) level = 'Niveau C1';
        else if (pct >= 70) level = 'Niveau B2';
        else if (pct >= 60) level = 'Niveau B1';
        else if (pct >= 50) level = 'Niveau A2';

        if (percentageText) percentageText.textContent = pct + '%';
        if (levelText) levelText.textContent = level;
        if (correctAnswers) correctAnswers.textContent = String(correctCount);
        if (incorrectAnswers) incorrectAnswers.textContent = String(quizData.length - correctCount);
        if (timeTaken) timeTaken.textContent = getTimeTaken();
        if (totalPoints) totalPoints.textContent = String(totalPointsEarned);

        var circumference = 2 * Math.PI * 15.9155;
        var dashArray = (pct / 100) * circumference;
        if (scoreCircle) scoreCircle.style.strokeDasharray = dashArray + ' ' + circumference;

        if (answersReview) {
            answersReview.innerHTML = '';
            quizData.forEach(function (question, index) {
                var reviewItem = document.createElement('div');
                reviewItem.className = 'review-item';

                var questionText = document.createElement('div');
                questionText.className = 'review-question';
                questionText.innerHTML = index + 1 + '. ' + convertNewlinesToBr(question.question || '');

                reviewItem.appendChild(questionText);

                if (question.audio) {
                    var audioReview = document.createElement('div');
                    audioReview.className = 'review-audio';
                    var audioElement = document.createElement('audio');
                    audioElement.src = question.audio;
                    audioElement.controls = true;
                    audioElement.className = 'audio-player';
                    audioReview.appendChild(audioElement);
                    reviewItem.appendChild(audioReview);
                }

                question.answers.forEach(function (answer, answerIndex) {
                    var answerElement = document.createElement('div');
                    var answerClass = 'review-answer';
                    if (answer.correct) answerClass += ' correct';
                    else if (
                        userAnswers[index] !== null &&
                        userAnswers[index].index === answerIndex &&
                        !answer.correct
                    ) {
                        answerClass += ' incorrect';
                    }
                    if (userAnswers[index] !== null && userAnswers[index].index === answerIndex) {
                        answerClass += ' selected';
                    }
                    answerElement.className = answerClass;

                    var icon = document.createElement('i');
                    if (answer.correct) {
                        icon.className = 'bx bx-check';
                        answerElement.appendChild(icon);
                    } else if (
                        userAnswers[index] !== null &&
                        userAnswers[index].index === answerIndex &&
                        !answer.correct
                    ) {
                        icon.className = 'bx bx-x';
                        answerElement.appendChild(icon);
                    } else {
                        var emptyIcon = document.createElement('span');
                        emptyIcon.style.width = '20px';
                        emptyIcon.style.marginRight = 'var(--space-xs)';
                        answerElement.appendChild(emptyIcon);
                    }

                    answerElement.innerHTML += convertNewlinesToBr(answer.text);
                    reviewItem.appendChild(answerElement);
                });

                answersReview.appendChild(reviewItem);
            });
        }

        initResultsIndicators();

        if (quizScreen) quizScreen.classList.add('hidden');
        if (resultsScreen) resultsScreen.classList.remove('hidden');
    }

    function initResultsIndicators() {
        if (!resultsIndicators) return;
        resultsIndicators.innerHTML = '';
        quizData.forEach(function (_, index) {
            var indicator = document.createElement('div');
            var indicatorClass = 'indicator';
            if (userAnswers[index] === null) {
                indicatorClass += ' unanswered';
            } else if (userAnswers[index].correct) {
                indicatorClass += ' correct';
            } else {
                indicatorClass += ' incorrect';
            }
            indicator.className = indicatorClass;
            indicator.textContent = String(index + 1);
            resultsIndicators.appendChild(indicator);
        });
    }

    function resetToStart() {
        clearInterval(timerInterval);
        currentQuestion = 0;
        timeLeft = initialDurationSeconds;
        userAnswers = Array(quizData.length).fill(null);
        if (resultsScreen) resultsScreen.classList.add('hidden');
        if (quizScreen) quizScreen.classList.add('hidden');
        if (startScreen) startScreen.classList.remove('hidden');
    }

    function startQuiz() {
        if (!quizData.length) return;
        if (resultsScreen) resultsScreen.classList.add('hidden');
        if (startScreen) startScreen.classList.add('hidden');
        if (quizScreen) quizScreen.classList.remove('hidden');
        startTime = new Date();
        currentQuestion = 0;
        userAnswers = Array(quizData.length).fill(null);
        timeLeft = initialDurationSeconds;
        initQuestionIndicators();
        loadQuestion();
        startTimer();
    }

    function bindEventsOnce() {
        if (eventsBound) return;
        eventsBound = true;
        if (startBtn) startBtn.addEventListener('click', startQuiz);
        if (quitBtn) quitBtn.addEventListener('click', resetToStart);
        if (prevBtn)
            prevBtn.addEventListener('click', function () {
                navigateToQuestion(currentQuestion - 1);
            });
        if (nextBtn)
            nextBtn.addEventListener('click', function () {
                navigateToQuestion(currentQuestion + 1);
            });
        if (finishBtn) finishBtn.addEventListener('click', finishQuiz);
        if (restartBtn) restartBtn.addEventListener('click', startQuiz);
        if (backToStartBtn) backToStartBtn.addEventListener('click', resetToStart);
        if (playBtn && questionAudio) {
            playBtn.addEventListener('click', function () {
                questionAudio.play();
            });
            questionAudio.addEventListener('play', function () {
                playBtn.innerHTML = '<i class="bx bx-pause"></i> Pause';
            });
            questionAudio.addEventListener('pause', function () {
                playBtn.innerHTML = '<i class="bx bx-play"></i> Lancer l’audio';
            });
        }
    }

    function initCoQuizRuntime() {
        quizData = window.__coQuizData || [];
        initialDurationSeconds = Math.max(60, Number(window.__coInitialDuration || 1800));
        timeLeft = initialDurationSeconds;
        userAnswers = Array(quizData.length).fill(null);
        bindEventsOnce();
    }

    function coBoot() {
        initRefs();
        var api = window.TCF_CO_API;
        var params = new URLSearchParams(window.location.search);
        var examId = params.get('exam_id');
        var desc = $('quiz-description');
        var titleEl = $('quiz-title');
        var btn = $('start-btn');

        if (!api) {
            if (desc) desc.textContent = 'Configuration API manquante.';
            return;
        }
        if (!examId) {
            if (desc) desc.textContent = 'Paramètre exam_id manquant dans l’URL.';
            return;
        }

        var fd = new FormData();
        fd.append('action', 'get_exam_quiz');
        fd.append('exam_id', examId);

        fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.success) {
                    var msg = (j && j.message) ? j.message : 'Épreuve indisponible.';
                    if (desc) desc.textContent = msg;
                    return;
                }
                var exam = j.data.exam;
                var questions = j.data.questions || [];
                window.__coQuizData = questions;
                window.__coExamMeta = exam;
                window.__coInitialDuration = Number(exam.duration_seconds || 1800);

                if (titleEl) titleEl.textContent = exam.title || 'Compréhension Orale';
                if (desc) {
                    var t = (exam.subtitle || '').trim();
                    desc.textContent = t || 'Écoutez les extraits et répondez aux questions.';
                }

                initCoQuizRuntime();
                if (btn) btn.disabled = questions.length === 0;
            })
            .catch(function () {
                if (desc) desc.textContent = 'Erreur réseau.';
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', coBoot);
    } else {
        coBoot();
    }
})();
