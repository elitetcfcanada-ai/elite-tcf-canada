/**
 * Quiz compréhension orale — données depuis co_api.php (get_exam_quiz).
 */
(function () {
    'use strict';

    var quizData = [];
    var initialDurationSeconds = 2100;
    var currentQuestion = 0;
    var userAnswers = [];
    var timeLeft = 2100;
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

    /** Retire un éventuel préfixe A)/B./C-… : les lettres sont déjà affichées côté UI. */
    function stripAnswerLetterPrefix(htmlOrText) {
        var s = String(htmlOrText == null ? '' : htmlOrText);
        return s.replace(/^\s*(?:<[a-z][^>]*>\s*)*([A-Da-d])\s*[\)\]\.\-–—:]\s*/i, '').trim();
    }

    /** Toujours 4 propositions A–D (slots vides si besoin). */
    function ensureFourAnswers(answers) {
        var src = Array.isArray(answers) ? answers : [];
        var out = [];
        for (var i = 0; i < 4; i++) {
            var a = src[i] || {};
            out.push({
                id: a.id != null ? a.id : 'opt-' + i,
                key: a.key || String.fromCharCode(97 + i),
                text: stripAnswerLetterPrefix(a.text || ''),
                correct: !!a.correct
            });
        }
        return out;
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
            var qSpan = questionNumber.querySelector('span');
            if (qSpan) {
                qSpan.textContent = 'Question : ' + (currentQuestion + 1);
            }
        }
        if (questionPoints) {
            questionPoints.textContent =
                question.points + ' pt' + (question.points > 1 ? 's' : '');
        }
        var levelBadge = document.getElementById('question-level-badge');
        if (levelBadge) {
            var pts = Number(question.points || 0);
            var lvl = 'A1';
            if (pts >= 15) lvl = 'C2';
            else if (pts >= 12) lvl = 'C1';
            else if (pts >= 9) lvl = 'B2';
            else if (pts >= 6) lvl = 'B1';
            else if (pts >= 4) lvl = 'A2';
            levelBadge.textContent = lvl;
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

        prepareQuestionAudio(question);

        if (answersContainer) {
            answersContainer.innerHTML = '';
            var letters = ['A', 'B', 'C', 'D'];
            var four = ensureFourAnswers(question.answers);
            question.answers = four;
            for (let index = 0; index < four.length; index++) {
                const answer = four[index];
                let answerElement = document.createElement('div');
                answerElement.className = 'answer';
                if (
                    userAnswers[currentQuestion] !== null &&
                    userAnswers[currentQuestion] !== undefined &&
                    userAnswers[currentQuestion].index === index
                ) {
                    answerElement.classList.add('is-selected');
                }

                let input = document.createElement('input');
                input.type = 'radio';
                input.name = 'answer';
                input.value = String(index);
                input.id = 'answer-' + index;

                if (
                    userAnswers[currentQuestion] !== null &&
                    userAnswers[currentQuestion] !== undefined &&
                    userAnswers[currentQuestion].index === index
                ) {
                    input.checked = true;
                }

                input.addEventListener('change', function () {
                    selectAnswer(index);
                });

                let letter = document.createElement('span');
                letter.className = 'answer-letter';
                letter.textContent = letters[index];
                letter.setAttribute('aria-hidden', 'true');

                let label = document.createElement('label');
                label.htmlFor = 'answer-' + index;
                label.className = 'answer-label';
                label.innerHTML = convertNewlinesToBr(answer.text || '');

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
                answerElement.appendChild(letter);
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

        if (answersContainer) {
            answersContainer.querySelectorAll('.answer').forEach(function (el, i) {
                el.classList.toggle('is-selected', i === answerIndex);
            });
        }

        updateQuestionIndicators();
    }

    function setTtsStatus(msg) {
        var el = document.getElementById('co-tts-status');
        if (el) el.textContent = msg || '';
    }

    function formatAudioClock(sec) {
        if (window.TCF_TTS && window.TCF_TTS.formatTime) {
            return window.TCF_TTS.formatTime(sec);
        }
        sec = Math.max(0, Math.floor(sec || 0));
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function updateAudioProgressUI(state) {
        state = state || {};
        var fill = document.getElementById('co-audio-progress-fill');
        var bar = document.getElementById('co-audio-progress-bar');
        var curEl = document.getElementById('co-audio-time-current');
        var totEl = document.getElementById('co-audio-time-total');
        var ratio = Math.min(1, Math.max(0, Number(state.ratio) || 0));
        var pct = Math.round(ratio * 100);
        if (fill) fill.style.width = pct + '%';
        if (bar) bar.setAttribute('aria-valuenow', String(pct));
        if (curEl) curEl.textContent = state.formattedCurrent || formatAudioClock(state.currentTime || 0);
        if (totEl) totEl.textContent = state.formattedDuration || formatAudioClock(state.duration || 0);
    }

    function resetAudioProgressUI(durationSec) {
        updateAudioProgressUI({
            currentTime: 0,
            duration: durationSec || 0,
            ratio: 0,
            formattedCurrent: '0:00',
            formattedDuration: formatAudioClock(durationSec || 0)
        });
    }

    function stopAllAudio() {
        if (window.TCF_TTS) window.TCF_TTS.stop();
        if (questionAudio) {
            try {
                questionAudio.pause();
                questionAudio.currentTime = 0;
            } catch (e) {}
        }
        resetPlayBtnIdle();
    }

    function resetPlayBtnIdle() {
        if (!playBtn) return;
        playBtn.disabled = false;
        playBtn.setAttribute('aria-pressed', 'false');
        playBtn.setAttribute('aria-label', 'Écouter');
        playBtn.innerHTML = '<i class="bx bx-play"></i>';
    }

    function setPlayBtnPause() {
        if (!playBtn) return;
        playBtn.disabled = false;
        playBtn.setAttribute('aria-pressed', 'true');
        playBtn.setAttribute('aria-label', 'Pause');
        playBtn.innerHTML = '<i class="bx bx-pause"></i>';
    }

    function speakCurrentQuestion(auto) {
        var question = quizData[currentQuestion];
        if (!question) return;
        var text = String(question.audio_text || '').trim();
        var box = document.getElementById('co-audio-box');
        var player = document.getElementById('co-audio-player');

        if (text && window.TCF_TTS) {
            if (box) box.style.display = '';
            if (player) player.style.display = '';
            if (questionAudio) questionAudio.classList.add('hidden');

            var ttsRate = window.TCF_TTS.DEFAULT_RATE != null ? window.TCF_TTS.DEFAULT_RATE : 0.95;
            var ttsPitch = window.TCF_TTS.DEFAULT_PITCH != null ? window.TCF_TTS.DEFAULT_PITCH : 0.95;
            var est = window.TCF_TTS.estimateDurationSec
                ? window.TCF_TTS.estimateDurationSec(text, ttsRate)
                : Math.max(2, text.length / 14);
            resetAudioProgressUI(est);
            setPlayBtnPause();
            setTtsStatus(auto ? 'Lecture automatique…' : 'Lecture…');

            window.TCF_TTS.speak(text, {
                lang: 'fr-FR',
                rate: ttsRate,
                pitch: ttsPitch,
                onStart: function (st) {
                    setTtsStatus('Lecture en cours…');
                    setPlayBtnPause();
                    if (st) updateAudioProgressUI(st);
                },
                onProgress: function (st) {
                    updateAudioProgressUI(st);
                },
                onPause: function (st) {
                    setTtsStatus('En pause');
                    resetPlayBtnIdle();
                    if (st) updateAudioProgressUI(st);
                },
                onResume: function (st) {
                    setTtsStatus('Lecture en cours…');
                    setPlayBtnPause();
                    if (st) updateAudioProgressUI(st);
                },
                onSpeechEnd: function (st) {
                    if (st) updateAudioProgressUI(st);
                    setTtsStatus('Fin de lecture…');
                    resetPlayBtnIdle();
                },
                onSeek: function (st) {
                    setTtsStatus('Lecture en cours…');
                    setPlayBtnPause();
                    if (st) updateAudioProgressUI(st);
                },
                onEnd: function () {
                    setTtsStatus('Vous pouvez choisir votre réponse');
                    resetPlayBtnIdle();
                },
                onError: function () {
                    setTtsStatus('Erreur de lecture audio');
                    resetPlayBtnIdle();
                }
            }).catch(function () {
                setTtsStatus('Cliquez sur ▶ pour écouter l’audio');
                resetPlayBtnIdle();
            });
            return;
        }

        // Fallback fichier audio legacy
        if (question.audio && questionAudio) {
            if (box) box.style.display = '';
            if (player) player.style.display = '';
            questionAudio.classList.add('hidden');
            questionAudio.src = question.audio;
            questionAudio.load();
            setTtsStatus('');
            resetAudioProgressUI(0);
            if (auto) {
                questionAudio.play().catch(function () {});
            }
            return;
        }

        if (box) box.style.display = 'none';
        setTtsStatus('');
    }

    function prepareQuestionAudio(question) {
        stopAllAudio();
        var box = document.getElementById('co-audio-box');
        var player = document.getElementById('co-audio-player');
        var hasTts = !!(question && String(question.audio_text || '').trim());
        var hasFile = !!(question && question.audio);
        if (!hasTts && !hasFile) {
            if (box) box.style.display = 'none';
            return;
        }
        if (box) box.style.display = '';
        if (player) player.style.display = '';
        // Prépare l’UI puis lance l’audio (play/pause restent disponibles)
        if (hasTts && window.TCF_TTS && window.TCF_TTS.estimateDurationSec) {
            resetAudioProgressUI(
                window.TCF_TTS.estimateDurationSec(
                    String(question.audio_text),
                    window.TCF_TTS.DEFAULT_RATE != null ? window.TCF_TTS.DEFAULT_RATE : 0.95
                )
            );
        }
        setTtsStatus('Prêt — lecture de l’audio…');
        speakCurrentQuestion(true);
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
            var answers = ensureFourAnswers(question.answers);
            question.answers = answers;
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

            if (question.image) {
                var sitReview = document.createElement('div');
                sitReview.className = 'review-situation';
                sitReview.innerHTML =
                    '<div class="review-situation__title"><i class="bx bx-image" aria-hidden="true"></i><span>Situation visuelle</span></div>';
                var sitImg = document.createElement('img');
                sitImg.className = 'review-situation__img';
                sitImg.src = question.image;
                sitImg.alt = 'Situation visuelle — question ' + (index + 1);
                sitImg.loading = 'lazy';
                sitImg.decoding = 'async';
                sitReview.appendChild(sitImg);
                reviewItem.appendChild(sitReview);
            }

            if (question.audio_text || question.audio) {
                var audioReview = document.createElement('div');
                audioReview.className = 'review-audio';
                if (question.audio_text && window.TCF_TTS) {
                    var ttsBtn = document.createElement('button');
                    ttsBtn.type = 'button';
                    ttsBtn.className = 'play-btn';
                    ttsBtn.innerHTML = '<i class="bx bx-play"></i> Écouter';
                    ttsBtn.addEventListener('click', function () {
                        window.TCF_TTS.speak(String(question.audio_text), {
                            lang: 'fr-FR',
                            rate: window.TCF_TTS.DEFAULT_RATE != null ? window.TCF_TTS.DEFAULT_RATE : 0.95,
                            pitch: window.TCF_TTS.DEFAULT_PITCH != null ? window.TCF_TTS.DEFAULT_PITCH : 0.95
                        });
                    });
                    audioReview.appendChild(ttsBtn);
                } else if (question.audio) {
                    var audioElement = document.createElement('audio');
                    audioElement.src = question.audio;
                    audioElement.controls = true;
                    audioElement.className = 'audio-player';
                    audioReview.appendChild(audioElement);
                }
                reviewItem.appendChild(audioReview);
            }

            var choices = document.createElement('div');
            choices.className = 'tcf-qpro-review-choices';

            if (!answers.length) {
                choices.innerHTML =
                    '<p class="tcf-qpro-review-empty">Aucune proposition en base pour cette question.</p>';
            } else {
                answers.forEach(function (answer, answerIndex) {
                    var letter = ['A', 'B', 'C', 'D'][answerIndex] || '';
                    var display = {
                        id: answer.id,
                        key: answer.key,
                        text:
                            (letter ? '<strong>' + letter + '.</strong> ' : '') + (answer.text || ''),
                        correct: answer.correct
                    };
                    appendReviewChoice(choices, display, {
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
        stopAllAudio();

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
        stopAllAudio();
        currentQuestion = 0;
        timeLeft = initialDurationSeconds;
        userAnswers = Array(quizData.length).fill(null);
        hideCorrectionPanel();
        if (resultsScreen) resultsScreen.classList.add('hidden');
        if (quizScreen) quizScreen.classList.add('hidden');
        if (startScreen) startScreen.classList.remove('hidden');
    }

    function unlockAudioPlayback() {
        // Précharger les voix — sans speak()+cancel() (ça bloque Chrome ~plusieurs secondes)
        try {
            if (window.speechSynthesis) {
                try {
                    window.speechSynthesis.resume();
                } catch (eR) {}
                window.speechSynthesis.getVoices();
            }
        } catch (e0) {}
        try {
            if (window.TCF_TTS && window.TCF_TTS.ensureVoices) {
                window.TCF_TTS.ensureVoices();
            }
        } catch (e1) {}
    }

    function startQuiz() {
        if (!quizData.length) return;
        unlockAudioPlayback();
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
        if (playBtn) {
            playBtn.addEventListener('click', function () {
                var question = quizData[currentQuestion];
                if (!question) return;
                unlockAudioPlayback();
                var text = String(question.audio_text || '').trim();
                if (text && window.TCF_TTS) {
                    // Pause → reprendre
                    if (window.TCF_TTS.isPaused && window.TCF_TTS.isPaused()) {
                        window.TCF_TTS.resume();
                        setPlayBtnPause();
                        setTtsStatus('Lecture en cours…');
                        return;
                    }
                    // Lecture en cours (y compris silences) → pause
                    if (
                        (window.TCF_TTS.isPlaying && window.TCF_TTS.isPlaying()) ||
                        (window.TCF_TTS.isSpeaking && window.TCF_TTS.isSpeaking())
                    ) {
                        window.TCF_TTS.pause();
                        return;
                    }
                    // Nouvelle écoute
                    speakCurrentQuestion(false);
                    return;
                }
                if (questionAudio && question.audio) {
                    if (!questionAudio.paused) {
                        questionAudio.pause();
                        resetPlayBtnIdle();
                        setTtsStatus('En pause');
                        return;
                    }
                    questionAudio.play().catch(function () {
                        setTtsStatus('Cliquez à nouveau pour lancer l’audio');
                    });
                }
            });

            var replayBtn = document.getElementById('co-audio-replay-btn');
            if (replayBtn) {
                replayBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var question = quizData[currentQuestion];
                    if (!question) return;
                    var text = String(question.audio_text || '').trim();
                    if (text && window.TCF_TTS) {
                        if (window.TCF_TTS.isActive && window.TCF_TTS.isActive() && window.TCF_TTS.seekToRatio) {
                            window.TCF_TTS.seekToRatio(0);
                            setTtsStatus('Réécoute…');
                            setPlayBtnPause();
                            return;
                        }
                        speakCurrentQuestion(false);
                        return;
                    }
                    if (questionAudio && question.audio) {
                        try {
                            questionAudio.currentTime = 0;
                            questionAudio.play().catch(function () {});
                        } catch (err) {}
                    }
                });
            }

            var progressBar = document.getElementById('co-audio-progress-bar');
            function seekFromClientX(clientX) {
                if (!progressBar) return;
                var rect = progressBar.getBoundingClientRect();
                if (!rect.width) return;
                var ratio = (clientX - rect.left) / rect.width;
                ratio = Math.min(1, Math.max(0, ratio));

                var question = quizData[currentQuestion];
                if (!question) return;
                var text = String(question.audio_text || '').trim();

                if (text && window.TCF_TTS) {
                    if (window.TCF_TTS.isActive && window.TCF_TTS.isActive() && window.TCF_TTS.seekToRatio) {
                        window.TCF_TTS.seekToRatio(ratio);
                        setPlayBtnPause();
                        setTtsStatus('Lecture en cours…');
                        return;
                    }
                    // Pas encore en lecture : démarrer puis seek
                    speakCurrentQuestion(false);
                    setTimeout(function () {
                        if (window.TCF_TTS && window.TCF_TTS.seekToRatio) {
                            window.TCF_TTS.seekToRatio(ratio);
                        }
                    }, 120);
                    return;
                }
                if (questionAudio && question.audio) {
                    var d = questionAudio.duration;
                    if (isFinite(d) && d > 0) {
                        questionAudio.currentTime = ratio * d;
                        if (questionAudio.paused) {
                            questionAudio.play().catch(function () {});
                        }
                    }
                }
            }

            if (progressBar) {
                progressBar.addEventListener('click', function (e) {
                    seekFromClientX(e.clientX);
                });
                progressBar.addEventListener('keydown', function (e) {
                    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Home') return;
                    e.preventDefault();
                    var st = window.TCF_TTS && window.TCF_TTS.getState ? window.TCF_TTS.getState() : null;
                    var ratio = st ? Number(st.ratio) || 0 : 0;
                    if (e.key === 'Home') ratio = 0;
                    else if (e.key === 'ArrowLeft') ratio = Math.max(0, ratio - 0.05);
                    else ratio = Math.min(1, ratio + 0.05);
                    if (window.TCF_TTS && window.TCF_TTS.isActive && window.TCF_TTS.isActive() && window.TCF_TTS.seekToRatio) {
                        window.TCF_TTS.seekToRatio(ratio);
                        setPlayBtnPause();
                    }
                });
            }

            if (questionAudio) {
                questionAudio.addEventListener('play', function () {
                    setPlayBtnPause();
                });
                questionAudio.addEventListener('pause', function () {
                    resetPlayBtnIdle();
                });
                questionAudio.addEventListener('timeupdate', function () {
                    var d = questionAudio.duration;
                    if (!isFinite(d) || d <= 0) return;
                    updateAudioProgressUI({
                        currentTime: questionAudio.currentTime,
                        duration: d,
                        ratio: questionAudio.currentTime / d,
                        formattedCurrent: formatAudioClock(questionAudio.currentTime),
                        formattedDuration: formatAudioClock(d)
                    });
                });
                questionAudio.addEventListener('loadedmetadata', function () {
                    var d = questionAudio.duration;
                    if (isFinite(d) && d > 0) resetAudioProgressUI(d);
                });
                questionAudio.addEventListener('ended', function () {
                    setTtsStatus('Fin de l’extrait…');
                    setTimeout(function () {
                        setTtsStatus('Vous pouvez choisir votre réponse');
                    }, 3000);
                    resetPlayBtnIdle();
                    var d = questionAudio.duration;
                    if (isFinite(d) && d > 0) {
                        updateAudioProgressUI({
                            currentTime: d,
                            duration: d,
                            ratio: 1,
                            formattedCurrent: formatAudioClock(d),
                            formattedDuration: formatAudioClock(d)
                        });
                    }
                });
            }
        }
    }

    function initCoQuizRuntime() {
        quizData = window.__coQuizData || [];
        initialDurationSeconds = Math.max(60, Number(window.__coInitialDuration || 2100));
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
                window.__coInitialDuration = Number(exam.duration_seconds || 2100);

                if (titleEl) titleEl.textContent = exam.title || 'Compréhension Orale';
                if (desc) {
                    var t = (exam.subtitle || '').trim();
                    desc.textContent = t || 'Écoutez les extraits et répondez aux questions.';
                }
                var metaQ = $('quiz-meta-questions');
                var metaD = $('quiz-meta-duration');
                var durSec = Math.max(60, Number(exam.duration_seconds || 2100));
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
