/**
 * Lecture vocale fluide (Web Speech API).
 * - Voix FR homme privilégiée (natural / neural si dispo)
 * - Pauses courtes (ponctuation), progression monotone (inclut les silences)
 * - Sans retour arrière ni blocage de la barre
 */
(function (global) {
    'use strict';

    /** Silence après la lecture (avant onEnd) — court */
    var POST_READ_MS = 700;
    /** ~14 caractères/s à débit oral FR (rate ~0.95–1) */
    var CHARS_PER_SEC = 14;
    var DEFAULT_RATE = 0.95;
    var DEFAULT_PITCH = 0.95;

    var postTimer = null;
    var progressTimer = null;
    var chunkPauseTimer = null;
    var keepAliveTimer = null;
    var voicesReady = false;
    var active = false;
    var speaking = false;
    var paused = false;
    var speakGeneration = 0;
    var chunkRunId = 0;

    var session = null;

    function cancelTimers() {
        if (postTimer) {
            clearTimeout(postTimer);
            postTimer = null;
        }
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
        if (chunkPauseTimer) {
            clearTimeout(chunkPauseTimer);
            chunkPauseTimer = null;
        }
        if (keepAliveTimer) {
            clearInterval(keepAliveTimer);
            keepAliveTimer = null;
        }
    }

    function setIdle() {
        active = false;
        speaking = false;
        paused = false;
        session = null;
    }

    function loadVoices() {
        try {
            return global.speechSynthesis ? global.speechSynthesis.getVoices() || [] : [];
        } catch (e) {
            return [];
        }
    }

    var cachedVoice = null;
    var cachedVoiceLang = '';

    function ensureVoices(cb) {
        var voices = loadVoices();
        if (voices.length || !global.speechSynthesis) {
            voicesReady = true;
            if (cb) cb(voices);
            return;
        }
        var done = false;
        function finish() {
            if (done) return;
            done = true;
            voicesReady = true;
            if (cb) cb(loadVoices());
        }
        try {
            global.speechSynthesis.addEventListener('voiceschanged', finish, { once: true });
        } catch (e) {
            global.speechSynthesis.onvoiceschanged = finish;
        }
        // Ne pas attendre longtemps : parler tout de suite même sans liste complète
        setTimeout(finish, 120);
    }

    function scoreVoice(v, preferLang) {
        var name = String(v.name || '').toLowerCase();
        var lang = String(v.lang || '').toLowerCase().replace('_', '-');
        var pref = String(preferLang || 'fr-FR').toLowerCase().replace('_', '-');
        var score = 0;

        if (lang === pref) score += 60;
        else if (lang.indexOf(pref.slice(0, 2)) === 0) score += 35;
        else if (lang.indexOf('fr') === 0) score += 25;

        // Voix locales = démarrage immédiat ; online/cloud = latence 5–10 s
        if (v.localService === true) score += 55;
        else if (v.localService === false) score -= 60;
        if (/online|cloud|remote|network/.test(name)) score -= 50;

        // Qualité parmi les locales
        if (/neural|natural|premium|enhanced|desktop/.test(name) && v.localService !== false) {
            score += 20;
        }
        if (/google|microsoft|apple|siri/.test(name)) score += 8;

        var maleHints = [
            'male', 'homme', 'man', 'thomas', 'paul', 'jacques', 'henri', 'nicolas',
            'julien', 'gabriel', 'claude', 'daniel', 'george', 'jean', 'luc', 'marc',
            'pierre', 'remy', 'rémy', 'herve', 'hervé', 'david', 'mark', 'richard',
            'james', 'fred', 'andrew', 'guy', 'jerome', 'jérôme', 'louis'
        ];
        // Hortense = voix FR Windows locale fréquente (démarrage rapide)
        var femaleHints = [
            'female', 'femme', 'woman', 'zira', 'julie', 'amélie', 'amelie', 'denise',
            'marie', 'claire', 'audrey', 'aurelie', 'aurélie', 'susan',
            'linda', 'helena', 'pauline', 'brigitte'
        ];
        for (var i = 0; i < maleHints.length; i++) {
            if (name.indexOf(maleHints[i]) !== -1) score += 28;
        }
        if (name.indexOf('hortense') !== -1) score += 18;
        for (var j = 0; j < femaleHints.length; j++) {
            if (name.indexOf(femaleHints[j]) !== -1) score -= 20;
        }
        if (/compact|eloquence|espeak|festival/.test(name)) score -= 15;

        return score;
    }

    function pickVoice(preferLang) {
        var lang = preferLang || 'fr-FR';
        if (cachedVoice && cachedVoiceLang === lang) {
            // Vérifier que la voix est encore dans la liste
            var still = loadVoices();
            for (var c = 0; c < still.length; c++) {
                if (still[c] === cachedVoice || (still[c].name === cachedVoice.name && still[c].lang === cachedVoice.lang)) {
                    return still[c];
                }
            }
        }
        var voices = loadVoices();
        if (!voices.length) return null;
        var best = null;
        var bestScore = -1e9;
        for (var i = 0; i < voices.length; i++) {
            var s = scoreVoice(voices[i], lang);
            if (s > bestScore) {
                bestScore = s;
                best = voices[i];
            }
        }
        cachedVoice = best;
        cachedVoiceLang = lang;
        return best;
    }

    function detectLang(text) {
        var t = String(text || '');
        if (/[\u0600-\u06FF]/.test(t)) return 'ar-SA';
        if (/[\u4e00-\u9fff]/.test(t)) return 'zh-CN';
        if (/[äöüß]/i.test(t) && !/[éèàùç]/i.test(t)) return 'de-DE';
        if (/\b(the|and|you|with)\b/i.test(t) && !/[éèàùç]/i.test(t)) return 'en-US';
        if (/[ñ¿¡]/i.test(t)) return 'es-ES';
        return 'fr-FR';
    }

    function normalizeText(text) {
        return String(text || '')
            .replace(/\r\n/g, '\n')
            .replace(/[ \t]+/g, ' ')
            .replace(/\n{3,}/g, '\n\n')
            .replace(/\s+([,.;:!?…])/g, '$1')
            .replace(/([,.;:!?…])([^\s\n])/g, '$1 $2')
            .trim();
    }

    /**
     * Découpe minimale : la voix native gère mieux la ponctuation en continu.
     * On ne découpe que les longs textes (paragraphes) pour éviter les coupures Chrome.
     * @returns {{text:string, pauseAfter:number}[]}
     */
    function splitChunks(text) {
        var raw = normalizeText(text);
        if (!raw) return [];

        // Texte court / moyen : une seule utterance = pauses naturelles du moteur
        if (raw.length <= 900) {
            return [{ text: raw, pauseAfter: 0 }];
        }

        var paras = raw.split(/\n+/);
        var out = [];
        for (var i = 0; i < paras.length; i++) {
            var p = paras[i].trim();
            if (!p) continue;
            if (p.length <= 900) {
                out.push({ text: p, pauseAfter: i < paras.length - 1 ? 50 : 0 });
                continue;
            }
            // Très long paragraphe : phrases, silences ultra-courts
            var re = /([^.!?…]+[.!?…]+)|([^.!?…]+$)/g;
            var m;
            var bits = [];
            while ((m = re.exec(p)) !== null) {
                var piece = (m[1] || m[2] || '').trim();
                if (piece) bits.push(piece);
            }
            if (!bits.length) bits = [p];
            for (var k = 0; k < bits.length; k++) {
                out.push({
                    text: bits[k],
                    pauseAfter: k < bits.length - 1 ? 45 : (i < paras.length - 1 ? 50 : 0)
                });
            }
        }
        return out.length ? out : [{ text: raw, pauseAfter: 0 }];
    }

    function chunkSpeakSec(chunk, rate) {
        var r = rate != null ? rate : DEFAULT_RATE;
        if (r < 0.5) r = 0.5;
        return Math.max(0.25, (chunk.text || '').length / (CHARS_PER_SEC * r));
    }

    function estimateDurationSec(text, rate) {
        var r = rate != null ? rate : DEFAULT_RATE;
        if (r < 0.5) r = 0.5;
        var chunks = splitChunks(text);
        var total = 0;
        for (var i = 0; i < chunks.length; i++) {
            total += chunkSpeakSec(chunks[i], r);
            total += (chunks[i].pauseAfter || 0) / 1000;
        }
        if (!total) {
            var n = normalizeText(text).length || 1;
            total = Math.max(1, n / (CHARS_PER_SEC * r));
        }
        return Math.max(1, total);
    }

    function formatTime(sec) {
        sec = Math.max(0, Math.floor(sec || 0));
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    /** Progression monotone : avance pendant parole + silence, jamais en arrière. */
    function emitProgress() {
        if (!session) return;
        var now = Date.now();
        var current = session.lastProgress || 0;

        if (!paused && session.phaseStartedAt) {
            var local = (now - session.phaseStartedAt) / 1000;
            if (session.phaseKind === 'pause' && session.phaseDuration > 0) {
                local = Math.min(session.phaseDuration, local);
            }
            // Pendant la parole : pas de plafond dur (évite blocage si l’estim. est trop courte)
            current = (session.progressAnchor || 0) + Math.max(0, local);
        } else if (paused) {
            current = session.lastProgress || session.progressAnchor || 0;
        }

        // Monotone : jamais en arrière
        current = Math.max(session.lastProgress || 0, current);

        // Si on dépasse l’estim. initiale, on allonge la durée pour garder une barre fluide
        if (session.duration > 0 && current > session.duration - 0.05) {
            var remainChunks = 0;
            if (session.chunks && session.index < session.chunks.length) {
                for (var i = session.index; i < session.chunks.length; i++) {
                    remainChunks += chunkSpeakSec(session.chunks[i], session.rate);
                    remainChunks += (session.chunks[i].pauseAfter || 0) / 1000;
                }
            }
            session.duration = Math.max(session.duration, current + Math.max(0.4, remainChunks));
        }

        session.lastProgress = current;
        session.currentTime = Math.min(current, session.duration || current);

        var ratio = session.duration > 0 ? session.currentTime / session.duration : 0;
        if (session.opts.onProgress) {
            session.opts.onProgress({
                currentTime: session.currentTime,
                duration: session.duration,
                ratio: Math.min(1, Math.max(0, ratio)),
                paused: paused,
                speaking: speaking,
                formattedCurrent: formatTime(session.currentTime),
                formattedDuration: formatTime(session.duration)
            });
        }
    }

    function startProgressLoop() {
        if (progressTimer) clearInterval(progressTimer);
        progressTimer = setInterval(function () {
            if (!session || paused) return;
            emitProgress();
        }, 80);
    }

    /** Chrome coupe parfois la synthèse ~15 s — petit resume périodique. */
    function startKeepAlive() {
        if (keepAliveTimer) clearInterval(keepAliveTimer);
        keepAliveTimer = setInterval(function () {
            if (!active || paused || !speaking) return;
            try {
                if (global.speechSynthesis && global.speechSynthesis.speaking) {
                    global.speechSynthesis.resume();
                }
            } catch (e) {}
        }, 9000);
    }

    function beginPhase(anchorSec, durationSec, kind) {
        if (!session) return;
        session.progressAnchor = Math.max(session.lastProgress || 0, Math.max(0, anchorSec || 0));
        session.phaseDuration = Math.max(0, durationSec || 0);
        session.phaseKind = kind || 'speak';
        session.phaseStartedAt = Date.now();
        session.currentChunkEst = durationSec || 0;
    }

    function stopEngineHard() {
        speakGeneration += 1;
        chunkRunId += 1;
        cancelTimers();
        try {
            if (global.speechSynthesis) {
                // Chrome : resume avant cancel évite un long « gel » avant la prochaine lecture
                try {
                    global.speechSynthesis.resume();
                } catch (e0) {}
                if (global.speechSynthesis.speaking || global.speechSynthesis.pending) {
                    global.speechSynthesis.cancel();
                }
            }
        } catch (e) {}
        setIdle();
    }

    function stop() {
        stopEngineHard();
    }

    function isActive() {
        return !!(active || speaking || paused);
    }

    /** Lecture en cours (parole ou silence entre phrases) — pas la pause post-fin. */
    function isPlaying() {
        return !!(session && active && !paused && session.live);
    }

    function isSpeaking() {
        return speaking && !paused;
    }

    function isPaused() {
        return !!(paused && session && active);
    }

    function getState() {
        if (!session) {
            return {
                currentTime: 0,
                duration: 0,
                ratio: 0,
                paused: false,
                speaking: false,
                formattedCurrent: '0:00',
                formattedDuration: '0:00'
            };
        }
        return {
            currentTime: session.currentTime || 0,
            duration: session.duration || 0,
            ratio: session.duration ? (session.currentTime || 0) / session.duration : 0,
            paused: paused,
            speaking: speaking,
            formattedCurrent: formatTime(session.currentTime || 0),
            formattedDuration: formatTime(session.duration || 0)
        };
    }

    function pause() {
        if (!active || paused || !session) return false;
        if (!session.live) return false;
        paused = true;
        speaking = false;
        emitProgress();
        session.lastProgress = session.currentTime || session.lastProgress || 0;
        session.phaseStartedAt = 0;

        // Invalider l’utterance / timer en cours sans détruire la session
        chunkRunId += 1;
        if (chunkPauseTimer) {
            clearTimeout(chunkPauseTimer);
            chunkPauseTimer = null;
        }
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
        if (keepAliveTimer) {
            clearInterval(keepAliveTimer);
            keepAliveTimer = null;
        }

        try {
            if (global.speechSynthesis) {
                try {
                    global.speechSynthesis.pause();
                } catch (e0) {}
                try {
                    global.speechSynthesis.cancel();
                } catch (e1) {}
            }
        } catch (e) {}

        emitProgress();
        if (session.opts.onPause) session.opts.onPause(getState());
        return true;
    }

    function resume() {
        if (!active || !paused || !session) return false;
        if (!session.live) {
            paused = false;
            return false;
        }
        paused = false;
        try {
            if (global.speechSynthesis) global.speechSynthesis.resume();
        } catch (e) {}
        startProgressLoop();
        startKeepAlive();
        if (session.opts.onResume) session.opts.onResume(getState());
        speakNextChunk();
        return true;
    }

    function softInterrupt() {
        chunkRunId += 1;
        if (chunkPauseTimer) {
            clearTimeout(chunkPauseTimer);
            chunkPauseTimer = null;
        }
        if (postTimer) {
            clearTimeout(postTimer);
            postTimer = null;
        }
        speaking = false;
        try {
            if (global.speechSynthesis) {
                try {
                    global.speechSynthesis.resume();
                } catch (e0) {}
                global.speechSynthesis.cancel();
            }
        } catch (e) {}
    }

    function cumulativeSecBefore(index) {
        if (!session) return 0;
        var rate = session.rate || DEFAULT_RATE;
        var sec = 0;
        for (var i = 0; i < index && i < session.chunks.length; i++) {
            var c = session.chunks[i];
            sec += chunkSpeakSec(c, rate);
            sec += (c.pauseAfter || 0) / 1000;
        }
        return sec;
    }

    function seekToRatio(ratio) {
        if (!session || !session.chunks || !session.chunks.length) return false;
        ratio = Math.min(1, Math.max(0, Number(ratio) || 0));
        var targetTime = ratio * (session.duration || 1);
        var rate = session.rate || DEFAULT_RATE;
        var acc = 0;
        var targetIdx = 0;
        for (var i = 0; i < session.chunks.length; i++) {
            var c = session.chunks[i];
            var chunkDur = chunkSpeakSec(c, rate) + (c.pauseAfter || 0) / 1000;
            if (acc + chunkDur >= targetTime) {
                targetIdx = i;
                break;
            }
            acc += chunkDur;
            targetIdx = i + 1;
        }
        if (targetIdx >= session.chunks.length) {
            targetIdx = Math.max(0, session.chunks.length - 1);
        }

        softInterrupt();
        paused = false;
        active = true;
        session.live = true;
        session.index = targetIdx;
        session.completedSec = cumulativeSecBefore(targetIdx);
        session.lastProgress = session.completedSec;
        session.currentTime = session.completedSec;
        beginPhase(session.completedSec, 0, 'speak');
        emitProgress();
        if (session.opts.onSeek) session.opts.onSeek(getState());

        var runAfter = chunkRunId;
        setTimeout(function () {
            if (runAfter !== chunkRunId || !session) return;
            if (session.gen !== speakGeneration) return;
            startProgressLoop();
            startKeepAlive();
            speakNextChunk();
        }, 40);
        return true;
    }

    function speakUtterance(chunkText, lang, rate, pitch, voice, gen, runId) {
        return new Promise(function (resolve, reject) {
            var u = new SpeechSynthesisUtterance(chunkText);
            u.lang = lang;
            u.rate = rate;
            u.pitch = pitch;
            u.volume = 1;
            if (voice) {
                u.voice = voice;
                if (voice.lang) u.lang = voice.lang;
            }

            var settled = false;
            function done(ok, err) {
                if (settled) return;
                settled = true;
                if (ok) resolve();
                else reject(err || new Error('TTS error'));
            }

            u.onstart = function () {
                if (gen !== speakGeneration || runId !== chunkRunId) return;
                speaking = true;
                active = true;
            };
            u.onend = function () {
                if (gen !== speakGeneration || runId !== chunkRunId) {
                    done(true);
                    return;
                }
                speaking = false;
                done(true);
            };
            u.onerror = function (ev) {
                if (gen !== speakGeneration || runId !== chunkRunId) {
                    done(true);
                    return;
                }
                speaking = false;
                var errName = (ev && ev.error) || '';
                if (errName === 'interrupted' || errName === 'canceled' || errName === 'cancelled') {
                    done(true);
                    return;
                }
                done(false, ev);
            };

            try {
                global.speechSynthesis.speak(u);
            } catch (e) {
                done(false, e);
            }
        });
    }

    function speakNextChunk() {
        if (!session || paused) return;
        var gen = session.gen;
        if (gen !== speakGeneration) return;
        var runId = chunkRunId;

        var idx = session.index;
        var chunks = session.chunks;

        if (idx >= chunks.length) {
            finishSpeechSuccess();
            return;
        }

        var chunk = chunks[idx];
        var rate = session.rate;
        var est = chunkSpeakSec(chunk, rate);
        var pauseMs = chunk.pauseAfter || 0;
        var pauseSec = pauseMs / 1000;

        beginPhase(session.completedSec || 0, est, 'speak');

        if (session.index === 0 && session.opts.onStart && !session.startedOnce) {
            session.startedOnce = true;
            session.opts.onStart(getState());
        }

        speakUtterance(chunk.text, session.lang, session.rate, session.pitch, session.voice, gen, runId)
            .then(function () {
                if (gen !== speakGeneration || runId !== chunkRunId || !session || paused) return;

                // Ancre sur la progression réelle (horloge) — pas de saut ni retour
                session.completedSec = Math.max(session.completedSec || 0, session.lastProgress || 0);
                emitProgress();

                session.index += 1;

                if (session.index >= chunks.length) {
                    beginPhase(session.completedSec, 0, 'speak');
                    emitProgress();
                    finishSpeechSuccess();
                    return;
                }

                if (pauseMs > 0) {
                    beginPhase(session.completedSec, pauseSec, 'pause');
                    chunkPauseTimer = setTimeout(function () {
                        chunkPauseTimer = null;
                        if (gen !== speakGeneration || runId !== chunkRunId || !session || paused) return;
                        session.completedSec = Math.max(
                            session.lastProgress || 0,
                            session.completedSec || 0
                        );
                        session.lastProgress = Math.max(session.lastProgress || 0, session.completedSec);
                        emitProgress();
                        speakNextChunk();
                    }, pauseMs);
                } else {
                    speakNextChunk();
                }
            })
            .catch(function (err) {
                if (gen !== speakGeneration || runId !== chunkRunId) return;
                var opts = session && session.opts;
                var rej = session && session._reject;
                cancelTimers();
                setIdle();
                if (opts && opts.onError) opts.onError(err);
                if (rej) rej(err);
            });
    }

    function finishSpeechSuccess() {
        if (!session) return;
        var opts = session.opts;
        var gen = session.gen;
        var resolve = session._resolve;

        session.currentTime = session.duration;
        session.lastProgress = session.duration;
        session.live = false;
        beginPhase(session.duration, 0, 'speak');
        emitProgress();
        speaking = false;

        if (opts.onSpeechEnd) opts.onSpeechEnd(getState());

        var wait = opts.postReadMs != null ? opts.postReadMs : POST_READ_MS;
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
        if (keepAliveTimer) {
            clearInterval(keepAliveTimer);
            keepAliveTimer = null;
        }
        postTimer = setTimeout(function () {
            postTimer = null;
            if (gen !== speakGeneration) return;
            setIdle();
            if (opts.onEnd) opts.onEnd();
            if (resolve) resolve();
        }, Math.max(0, wait));
    }

    /**
     * @param {string} text
     * @param {object} opts
     */
    function speak(text, opts) {
        opts = opts || {};
        var raw = normalizeText(text);
        if (!raw) {
            if (opts.onError) opts.onError(new Error('Texte vide'));
            return Promise.reject(new Error('Texte vide'));
        }
        if (!global.speechSynthesis || typeof global.SpeechSynthesisUtterance === 'undefined') {
            var err = new Error('Lecture audio non supportée par ce navigateur.');
            if (opts.onError) opts.onError(err);
            return Promise.reject(err);
        }

        stopEngineHard();
        var gen = speakGeneration;
        active = true;
        speaking = false;
        paused = false;

        var rate = opts.rate != null ? opts.rate : DEFAULT_RATE;
        var pitch = opts.pitch != null ? opts.pitch : DEFAULT_PITCH;
        var chunks = splitChunks(raw);
        var duration = estimateDurationSec(raw, rate);
        var lang = opts.lang || detectLang(raw);

        return new Promise(function (resolve, reject) {
            function startSession() {
                if (gen !== speakGeneration) {
                    reject(new Error('Annulé'));
                    return;
                }
                try {
                    if (global.speechSynthesis) global.speechSynthesis.resume();
                } catch (eR) {}

                var voice = pickVoice(lang);
                // Éviter les voix online (5–10 s de latence) si une locale existe
                if (voice && voice.localService === false) {
                    var locals = loadVoices();
                    var localFr = null;
                    var localFrScore = -1e9;
                    for (var li = 0; li < locals.length; li++) {
                        var lv = locals[li];
                        if (lv.localService === false) continue;
                        var ls = scoreVoice(lv, lang);
                        if (ls > localFrScore) {
                            localFrScore = ls;
                            localFr = lv;
                        }
                    }
                    if (localFr) voice = localFr;
                }

                session = {
                    gen: gen,
                    opts: opts,
                    chunks: chunks,
                    index: 0,
                    lang: lang,
                    rate: rate,
                    pitch: pitch,
                    voice: voice,
                    duration: duration,
                    currentTime: 0,
                    completedSec: 0,
                    lastProgress: 0,
                    progressAnchor: 0,
                    phaseStartedAt: 0,
                    phaseDuration: 0,
                    phaseKind: 'speak',
                    currentChunkEst: 0,
                    startedOnce: false,
                    live: true,
                    _resolve: resolve,
                    _reject: reject
                };

                emitProgress();
                startProgressLoop();
                startKeepAlive();
                speakNextChunk();
            }

            if (loadVoices().length) {
                startSession();
            } else {
                ensureVoices(function () {
                    startSession();
                });
            }
        });
    }

    if (global.speechSynthesis) {
        ensureVoices();
    }

    global.TCF_TTS = {
        POST_READ_MS: POST_READ_MS,
        DEFAULT_RATE: DEFAULT_RATE,
        DEFAULT_PITCH: DEFAULT_PITCH,
        speak: speak,
        stop: stop,
        pause: pause,
        resume: resume,
        seekToRatio: seekToRatio,
        isActive: isActive,
        isPlaying: isPlaying,
        isSpeaking: isSpeaking,
        isPaused: isPaused,
        getState: getState,
        estimateDurationSec: estimateDurationSec,
        formatTime: formatTime,
        splitChunks: splitChunks,
        pickVoice: pickVoice,
        detectLang: detectLang,
        ensureVoices: ensureVoices
    };
})(typeof window !== 'undefined' ? window : this);
