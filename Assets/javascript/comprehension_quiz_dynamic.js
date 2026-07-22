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
    var showCorrectionBtn;
    var correctionPanel;
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
    var correctionNav;
    var correctionPrevBtn;
    var correctionNextBtn;
    var correctionRestartBtn;
    var correctionPos;

    function $(id) {
        return document.getElementById(id);
    }

    /** Disponible pour la correction résultats — ne pas imbriquer dans loadQuestion. */
    function convertNewlinesToBr(text) {
        if (text == null || text === '') return '';
        var t = String(text);
        if (/&lt;\/?[a-z]/i.test(t) && !/<[a-z]/i.test(t)) {
            var ta = document.createElement('textarea');
            ta.innerHTML = t;
            t = ta.value;
        }
        if (/<[a-z][\s\S]*>/i.test(t)) return t;
        return t.replace(/\n/g, '<br>');
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
        showCorrectionBtn = $('show-correction-btn');
        correctionPanel = $('correction-panel');
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
        correctionNav = $('correction-nav');
        correctionPrevBtn = $('correction-prev-btn');
        correctionNextBtn = $('correction-next-btn');
        correctionRestartBtn = $('correction-restart-btn');
        correctionPos = $('correction-pos');
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

        var isLast = currentQuestion >= quizData.length - 1;
        if (prevBtn) {
            prevBtn.classList.remove('hidden');
            prevBtn.disabled = currentQuestion === 0;
        }
        if (nextBtn) {
            nextBtn.classList.toggle('hidden', isLast);
            nextBtn.disabled = isLast;
        }
        // Terminer uniquement à la dernière question
        if (finishBtn) finishBtn.classList.toggle('hidden', !isLast);

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

    var focusedReviewIndex = 0;

    function setCorrectionBtnLabel(open) {
        if (!showCorrectionBtn) return;
        if (open) {
            showCorrectionBtn.innerHTML =
                '<i class="bx bx-hide"></i>' +
                '<span class="btn-txt btn-txt--full">Masquer la correction</span>' +
                '<span class="btn-txt btn-txt--short">Masquer</span>';
            showCorrectionBtn.setAttribute('aria-expanded', 'true');
        } else {
            showCorrectionBtn.innerHTML =
                '<i class="bx bx-check-shield"></i>' +
                '<span class="btn-txt btn-txt--full">Voir la correction</span>' +
                '<span class="btn-txt btn-txt--short">Correction</span>';
            showCorrectionBtn.setAttribute('aria-expanded', 'false');
        }
    }

    function hideCorrectionPanel() {
        if (correctionPanel) {
            correctionPanel.classList.add('hidden');
            correctionPanel.classList.remove('is-open');
            correctionPanel.setAttribute('hidden', '');
            correctionPanel.setAttribute('aria-hidden', 'true');
        }
        if (correctionNav) correctionNav.hidden = true;
        setCorrectionBtnLabel(false);
    }

    function updateCorrectionNav() {
        var total = quizData.length;
        var isLast = focusedReviewIndex >= total - 1;
        var isFirst = focusedReviewIndex <= 0;

        if (correctionPos) {
            correctionPos.textContent =
                total > 0 ? focusedReviewIndex + 1 + ' / ' + total : '0 / 0';
        }
        if (correctionPrevBtn) correctionPrevBtn.disabled = isFirst;
        if (correctionNextBtn) correctionNextBtn.hidden = isLast || total === 0;
        if (correctionRestartBtn) correctionRestartBtn.hidden = !isLast || total === 0;
        if (correctionNav) {
            var panelOpen =
                correctionPanel &&
                correctionPanel.classList.contains('is-open') &&
                !correctionPanel.hasAttribute('hidden');
            correctionNav.hidden = !panelOpen || total === 0;
        }
    }

    function focusReviewQuestion(index) {
        if (!quizData.length) return;
        focusedReviewIndex = Math.max(0, Math.min(index, quizData.length - 1));

        if (answersReview) {
            answersReview.querySelectorAll('.tcf-qpro-review-card').forEach(function (card, i) {
                card.classList.toggle('is-focused', i === focusedReviewIndex);
            });
        }
        if (resultsIndicators) {
            resultsIndicators.querySelectorAll('.indicator').forEach(function (ind, i) {
                ind.classList.toggle('is-active', i === focusedReviewIndex);
                ind.setAttribute('aria-current', i === focusedReviewIndex ? 'true' : 'false');
            });
        }

        updateCorrectionNav();

        var card = document.getElementById('co-review-q-' + (focusedReviewIndex + 1));
        if (card && correctionPanel && correctionPanel.classList.contains('is-open')) {
            window.requestAnimationFrame(function () {
                try {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (e) {
                    /* ignore */
                }
            });
        }
    }

    function appendReviewChoice(container, answer, opts) {
        var isUser = !!(opts && opts.isUser);
        var isCorrect = !!(opts && opts.isCorrect);
        var rowClass = 'review-answer';
        if (isCorrect) rowClass += ' correct';
        else if (isUser) rowClass += ' incorrect selected';
        else rowClass += ' is-other';

        var answerElement = document.createElement('div');
        answerElement.className = rowClass;

        var icon = document.createElement('i');
        if (isCorrect) icon.className = 'bx bx-check';
        else if (isUser) icon.className = 'bx bx-x';
        else icon.className = 'bx bx-circle';
        answerElement.appendChild(icon);

        var textSpan = document.createElement('span');
        textSpan.innerHTML = convertNewlinesToBr(answer.text);
        answerElement.appendChild(textSpan);

        /* Badge uniquement pour la bonne réponse — pas de « Votre choix » */
        if (isCorrect) {
            var meta = document.createElement('div');
            meta.className = 'review-answer-meta';
            var good = document.createElement('span');
            good.className = 'review-badge good';
            good.textContent = 'Bonne réponse';
            meta.appendChild(good);
            answerElement.appendChild(meta);
        }

        container.appendChild(answerElement);
    }

    function createAnswersReview() {
        if (!answersReview) return;
        answersReview.innerHTML = '';
        if (!quizData.length) {
            answersReview.innerHTML =
                '<p class="tcf-qpro-review-empty">Aucune question à corriger.</p>';
            return;
        }
        quizData.forEach(function (question, index) {
            var reviewItem = document.createElement('article');
            reviewItem.className = 'review-item tcf-qpro-review-card';
            reviewItem.id = 'co-review-q-' + (index + 1);

            var ua = userAnswers[index];
            var selectedIdx = ua && typeof ua.index === 'number' ? ua.index : -1;
            var answers = question.answers || [];
            var qOk = !!(ua && ua.correct);
            var statusClass =
                ua === null || ua === undefined
                    ? 'is-unanswered'
                    : qOk
                      ? 'is-correct'
                      : 'is-wrong';

            var head = document.createElement('div');
            head.className = 'tcf-qpro-review-card__head ' + statusClass;
            head.innerHTML =
                '<span class="tcf-qpro-review-card__num">Question ' +
                (index + 1) +
                '</span>' +
                '<span class="tcf-qpro-review-card__status">' +
                (ua === null || ua === undefined
                    ? 'Sans réponse'
                    : qOk
                      ? 'Correct'
                      : 'Incorrect') +
                '</span>';
            reviewItem.appendChild(head);

            var questionTextEl = document.createElement('div');
            questionTextEl.className = 'review-question';
            questionTextEl.innerHTML = convertNewlinesToBr(question.question || '');
            reviewItem.appendChild(questionTextEl);

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

            var choices = document.createElement('div');
            choices.className = 'tcf-qpro-review-choices';

            if (!answers.length) {
                choices.innerHTML =
                    '<p class="tcf-qpro-review-empty">Aucune proposition en base pour cette question.</p>';
            } else {
                answers.forEach(function (answer, answerIndex) {
                    appendReviewChoice(choices, answer, {
                        isUser: selectedIdx === answerIndex,
                        isCorrect: !!answer.correct,
                    });
                });
            }

            reviewItem.appendChild(choices);
            answersReview.appendChild(reviewItem);
        });

        focusReviewQuestion(focusedReviewIndex);
    }

    function showCorrectionPanel(focusIndex) {
        if (!correctionPanel) return;
        try {
            initResultsIndicators();
            createAnswersReview();
        } catch (err) {
            console.error('CO correction build error', err);
        }
        correctionPanel.classList.remove('hidden');
        correctionPanel.classList.add('is-open');
        correctionPanel.removeAttribute('hidden');
        correctionPanel.setAttribute('aria-hidden', 'false');
        setCorrectionBtnLabel(true);

        var idx =
            typeof focusIndex === 'number' && !isNaN(focusIndex)
                ? focusIndex
                : focusedReviewIndex;
        focusReviewQuestion(idx);

        window.requestAnimationFrame(function () {
            try {
                if (resultsIndicators) {
                    resultsIndicators.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } catch (e) {
                /* ignore */
            }
        });
    }

    function toggleCorrectionPanel() {
        if (!correctionPanel) return;
        var isHidden =
            correctionPanel.classList.contains('hidden') ||
            correctionPanel.hasAttribute('hidden') ||
            !correctionPanel.classList.contains('is-open');
        if (isHidden) showCorrectionPanel();
        else hideCorrectionPanel();
    }

    function finishQuiz() {
        clearInterval(timerInterval);

        var correctCount = userAnswers.filter(function (a) {
            return a !== null && a.correct;
        }).length;
        var wrongCount = userAnswers.filter(function (a) {
            return a !== null && !a.correct;
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
        if (incorrectAnswers) incorrectAnswers.textContent = String(wrongCount);
        if (timeTaken) timeTaken.textContent = getTimeTaken();
        if (totalPoints) totalPoints.textContent = String(totalPointsEarned);

        var circumference = 2 * Math.PI * 15.9155;
        var dashArray = (pct / 100) * circumference;
        if (scoreCircle) scoreCircle.style.strokeDasharray = dashArray + ' ' + circumference;

        try {
            createAnswersReview();
            initResultsIndicators();
        } catch (err) {
            console.error('CO results review error', err);
        }

        hideCorrectionPanel();

        if (startScreen) startScreen.classList.add('hidden');
        if (quizScreen) quizScreen.classList.add('hidden');
        if (resultsScreen) {
            resultsScreen.classList.remove('hidden');
            try {
                resultsScreen.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (e) {
                window.scrollTo(0, 0);
            }
        }
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
            indicator.setAttribute('role', 'button');
            indicator.setAttribute('tabindex', '0');
            indicator.title = 'Voir la question ' + (index + 1);
            indicator.addEventListener('click', function () {
                showCorrectionPanel(index);
            });
            indicator.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    showCorrectionPanel(index);
                }
            });
            resultsIndicators.appendChild(indicator);
        });
    }

    function resetToStart() {
        clearInterval(timerInterval);
        currentQuestion = 0;
        timeLeft = initialDurationSeconds;
        userAnswers = Array(quizData.length).fill(null);
        hideCorrectionPanel();
        if (resultsScreen) resultsScreen.classList.add('hidden');
        if (quizScreen) quizScreen.classList.add('hidden');
        if (startScreen) startScreen.classList.remove('hidden');
    }

    function startQuiz() {
        if (!quizData.length) return;
        hideCorrectionPanel();
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
        function askConfirm(opts) {
            if (typeof window.tcfQuizConfirm === 'function') {
                return window.tcfQuizConfirm(opts);
            }
            return Promise.resolve(window.confirm(opts.message || 'Confirmer ?'));
        }
        if (quitBtn)
            quitBtn.addEventListener('click', function () {
                clearInterval(timerInterval);
                askConfirm({
                    title: 'Terminer l’épreuve ?',
                    message: 'Voulez-vous terminer maintenant et afficher votre score ?',
                    confirmLabel: 'Oui, terminer',
                    cancelLabel: 'Continuer',
                }).then(function (ok) {
                    if (ok) {
                        finishQuiz();
                        return;
                    }
                    askConfirm({
                        title: 'Quitter sans résultats ?',
                        message:
                            'Votre progression sera perdue. Confirmez pour quitter sans voir vos résultats.',
                        confirmLabel: 'Quitter',
                        cancelLabel: 'Reprendre',
                    }).then(function (leave) {
                        if (leave) resetToStart();
                        else startTimer();
                    });
                });
            });
        if (prevBtn)
            prevBtn.addEventListener('click', function () {
                navigateToQuestion(currentQuestion - 1);
            });
        if (nextBtn)
            nextBtn.addEventListener('click', function () {
                navigateToQuestion(currentQuestion + 1);
            });
        if (finishBtn)
            finishBtn.addEventListener('click', function () {
                askConfirm({
                    title: 'Terminer l’épreuve ?',
                    message:
                        'Afficher votre score ? Vous pourrez ensuite choisir de voir la correction.',
                    confirmLabel: 'Oui, terminer',
                    cancelLabel: 'Continuer',
                }).then(function (ok) {
                    if (ok) finishQuiz();
                });
            });
        if (restartBtn) restartBtn.addEventListener('click', startQuiz);
        if (showCorrectionBtn)
            showCorrectionBtn.addEventListener('click', toggleCorrectionPanel);
        if (correctionPrevBtn) {
            correctionPrevBtn.addEventListener('click', function () {
                if (focusedReviewIndex > 0) focusReviewQuestion(focusedReviewIndex - 1);
            });
        }
        if (correctionNextBtn) {
            correctionNextBtn.addEventListener('click', function () {
                if (focusedReviewIndex < quizData.length - 1) {
                    focusReviewQuestion(focusedReviewIndex + 1);
                }
            });
        }
        if (correctionRestartBtn) {
            correctionRestartBtn.addEventListener('click', function () {
                startQuiz();
            });
        }
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
                    if (j && j.locked) {
                        if (j.reason === 'login') {
                            var loginBase = window.TCF_LOGIN_URL || 'login.php';
                            var next = encodeURIComponent('comprehension_orale_quiz.php?exam_id=' + encodeURIComponent(String(examId || '')));
                            window.location.href = loginBase + (loginBase.indexOf('?') >= 0 ? '&' : '?') + 'next=' + next;
                            return;
                        }
                        if (j.reason === 'subscription') {
                            window.location.href = window.TCF_ABO_URL || 'abonnement.php';
                            return;
                        }
                    }
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
                var metaQ = $('quiz-meta-questions');
                var metaD = $('quiz-meta-duration');
                var durSec = Math.max(60, Number(exam.duration_seconds || 1800));
                if (metaQ) metaQ.textContent = String(questions.length) + ' questions';
                if (metaD) metaD.textContent = Math.max(1, Math.round(durSec / 60)) + ' min';

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
