(function () {
  'use strict';

  var cfg = window.EO_SIM_CFG || {};
  var api = cfg.api || 'eo_api.php';
  var loggedIn = !!cfg.loggedIn;
  var loginHref = cfg.loginHref || 'login.php';

  var mediaRecorder = null;
  var mediaStream = null;
  var audioCtx = null;
  var analyser = null;
  var animFrame = 0;
  var chunks = [];
  var blob = null;
  var blobUrl = null;
  var startedAt = 0;
  var tickTimer = null;
  var recognition = null;
  var liveTranscript = '';
  var phase = 'idle'; // idle | rec | done | loading

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function fmtTime(sec) {
    sec = Math.max(0, Math.floor(sec || 0));
    var m = Math.floor(sec / 60);
    var s = sec % 60;
    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
  }

  function hostFromToggle(btn) {
    return btn.closest('.eo-subject-card, .eo-t1-card, .eo-task-panel') || btn.parentElement;
  }

  function panelHtml(maxSec) {
    var bars = '';
    for (var i = 0; i < 32; i++) {
      bars += '<i style="--h:8%"></i>';
    }
    return (
      '<div class="eo-sim-inner" data-phase="idle">' +
      '  <div class="eo-sim-stage">' +
      '    <div class="eo-sim-timer-wrap">' +
      '      <span class="eo-sim-timer" data-max="' + esc(String(maxSec)) + '">00:00</span>' +
      '      <span class="eo-sim-timer-max">/ ' + esc(fmtTime(maxSec)) + '</span>' +
      '    </div>' +
      '    <div class="eo-sim-wave" aria-hidden="true">' + bars + '</div>' +
      '    <button type="button" class="eo-sim-mic" aria-label="Démarrer l’enregistrement">' +
      '      <span class="eo-sim-mic-ring"></span>' +
      '      <span class="eo-sim-mic-ring eo-sim-mic-ring--delay"></span>' +
      '      <i class="bx bx-microphone"></i>' +
      '    </button>' +
      '    <p class="eo-sim-caption">Appuyez pour parler · durée libre</p>' +
      '  </div>' +
      '  <div class="eo-sim-toolbar">' +
      '    <button type="button" class="eo-sim-tool eo-sim-stop" disabled title="Arrêter"><i class="bx bx-stop"></i></button>' +
      '    <button type="button" class="eo-sim-tool eo-sim-replay" disabled title="Réécouter"><i class="bx bx-play"></i></button>' +
      '    <button type="button" class="eo-sim-tool eo-sim-reset" disabled title="Reprendre"><i class="bx bx-revision"></i></button>' +
      '    <button type="button" class="eo-sim-submit eo-sim-correct" disabled>Terminer</button>' +
      '  </div>' +
      '  <audio class="eo-sim-audio" controls hidden></audio>' +
      '  <textarea class="eo-sim-transcript" hidden aria-hidden="true"></textarea>' +
      '  <div class="eo-sim-result" hidden></div>' +
      '</div>'
    );
  }

  function setPhase(panel, next, caption) {
    phase = next;
    var inner = panel.querySelector('.eo-sim-inner');
    if (inner) inner.setAttribute('data-phase', next);
    var cap = panel.querySelector('.eo-sim-caption');
    if (cap && caption != null) cap.textContent = caption;
    var mic = panel.querySelector('.eo-sim-mic');
    if (mic) {
      mic.classList.toggle('is-rec', next === 'rec');
      mic.setAttribute(
        'aria-label',
        next === 'rec' ? 'Arrêter l’enregistrement' : 'Démarrer l’enregistrement'
      );
      var icon = mic.querySelector('i');
      if (icon) {
        icon.className = next === 'rec' ? 'bx bx-stop' : 'bx bx-microphone';
      }
    }
  }

  function updateTimer(panel) {
    var el = panel.querySelector('.eo-sim-timer');
    if (!el) return;
    var max = parseInt(el.getAttribute('data-max') || '120', 10) || 120;
    var elapsed = startedAt ? Math.floor((Date.now() - startedAt) / 1000) : 0;
    el.textContent = fmtTime(elapsed);
    el.classList.toggle('is-warn', elapsed >= max - 15 && phase === 'rec');
    if (elapsed >= max && mediaRecorder && mediaRecorder.state === 'recording') {
      stopRecording(panel);
    }
  }

  function stopSpeechRec() {
    try {
      if (recognition) recognition.stop();
    } catch (e) {}
    recognition = null;
  }

  function startSpeechRec(panel) {
    stopSpeechRec();
    liveTranscript = '';
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) return;
    try {
      recognition = new SR();
      recognition.lang = 'fr-FR';
      recognition.continuous = true;
      recognition.interimResults = true;
      recognition.onresult = function (ev) {
        var finalText = '';
        var interim = '';
        for (var i = 0; i < ev.results.length; i++) {
          var r = ev.results[i];
          if (r.isFinal) finalText += r[0].transcript + ' ';
          else interim += r[0].transcript;
        }
        liveTranscript = (finalText + interim).trim();
        var ta = panel.querySelector('.eo-sim-transcript');
        if (ta) ta.value = liveTranscript;
      };
      recognition.onerror = function () {};
      recognition.start();
    } catch (e) {
      recognition = null;
    }
  }

  function stopWave() {
    if (animFrame) {
      cancelAnimationFrame(animFrame);
      animFrame = 0;
    }
    if (audioCtx) {
      try {
        audioCtx.close();
      } catch (e) {}
    }
    audioCtx = null;
    analyser = null;
  }

  function drawWave(panel) {
    var wave = panel.querySelector('.eo-sim-wave');
    if (!wave || !analyser) return;
    var bars = wave.querySelectorAll('i');
    var data = new Uint8Array(analyser.frequencyBinCount);
    function frame() {
      animFrame = requestAnimationFrame(frame);
      analyser.getByteFrequencyData(data);
      var n = bars.length;
      var step = Math.max(1, Math.floor(data.length / n));
      for (var i = 0; i < n; i++) {
        var v = data[i * step] || 0;
        // centre plus sensible (voix)
        var midBoost = 1 + (1 - Math.abs(i - n / 2) / (n / 2)) * 0.35;
        var h = Math.max(8, Math.min(100, (v / 255) * 100 * midBoost));
        bars[i].style.setProperty('--h', h.toFixed(1) + '%');
      }
    }
    frame();
  }

  function idleWave(panel) {
    var wave = panel.querySelector('.eo-sim-wave');
    if (!wave) return;
    wave.querySelectorAll('i').forEach(function (bar, i) {
      var h = 10 + Math.abs(Math.sin(i * 0.45)) * 12;
      bar.style.setProperty('--h', h.toFixed(1) + '%');
    });
  }

  function startWaveFromStream(panel, stream) {
    stopWave();
    try {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      var src = audioCtx.createMediaStreamSource(stream);
      analyser = audioCtx.createAnalyser();
      analyser.fftSize = 256;
      analyser.smoothingTimeConstant = 0.72;
      src.connect(analyser);
      drawWave(panel);
    } catch (e) {
      idleWave(panel);
    }
  }

  function cleanupStream() {
    if (mediaStream) {
      mediaStream.getTracks().forEach(function (t) {
        try {
          t.stop();
        } catch (e) {}
      });
    }
    mediaStream = null;
  }

  function pickMime() {
    var candidates = [
      'audio/webm;codecs=opus',
      'audio/webm',
      'audio/ogg;codecs=opus',
      'audio/mp4',
    ];
    if (!window.MediaRecorder) return '';
    for (var i = 0; i < candidates.length; i++) {
      if (MediaRecorder.isTypeSupported(candidates[i])) return candidates[i];
    }
    return '';
  }

  function setTools(panel, opts) {
    var stop = panel.querySelector('.eo-sim-stop');
    var replay = panel.querySelector('.eo-sim-replay');
    var reset = panel.querySelector('.eo-sim-reset');
    var submit = panel.querySelector('.eo-sim-correct');
    if (stop) stop.disabled = !opts.stop;
    if (replay) replay.disabled = !opts.replay;
    if (reset) reset.disabled = !opts.reset;
    if (submit) {
      submit.disabled = !opts.submit;
      if (opts.submitLabel) submit.textContent = opts.submitLabel;
    }
  }

  function resetBlob(panel) {
    blob = null;
    if (blobUrl) {
      try {
        URL.revokeObjectURL(blobUrl);
      } catch (e) {}
    }
    blobUrl = null;
    var audio = panel.querySelector('.eo-sim-audio');
    if (audio) {
      audio.pause();
      audio.removeAttribute('src');
      audio.hidden = true;
    }
  }

  function afterStop(panel) {
    clearInterval(tickTimer);
    tickTimer = null;
    stopSpeechRec();
    stopWave();
    cleanupStream();
    mediaRecorder = null;

    if (!chunks.length) {
      setPhase(panel, 'idle', 'Micro silencieux — réessayez');
      setTools(panel, { stop: false, replay: false, reset: false, submit: false, submitLabel: 'Terminer' });
      idleWave(panel);
      return;
    }
    var mime = chunks[0].type || 'audio/webm';
    blob = new Blob(chunks, { type: mime });
    chunks = [];
    blobUrl = URL.createObjectURL(blob);
    var audio = panel.querySelector('.eo-sim-audio');
    if (audio) {
      audio.src = blobUrl;
      audio.hidden = false;
    }
    var elapsed = startedAt ? Math.floor((Date.now() - startedAt) / 1000) : 0;
    setPhase(panel, 'done', 'Enregistrement prêt · ' + fmtTime(elapsed));
    setTools(panel, {
      stop: false,
      replay: true,
      reset: true,
      submit: true,
      submitLabel: 'Terminer',
    });
    idleWave(panel);
  }

  function stopRecording(panel) {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      try {
        mediaRecorder.stop();
      } catch (e) {
        afterStop(panel);
      }
    } else {
      afterStop(panel);
    }
  }

  function startRecording(panel) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setPhase(panel, 'idle', 'Micro non disponible');
      return;
    }
    resetBlob(panel);
    var result = panel.querySelector('.eo-sim-result');
    if (result) {
      result.hidden = true;
      result.innerHTML = '';
    }
    setPhase(panel, 'idle', 'Autorisation du micro…');
    navigator.mediaDevices
      .getUserMedia({
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: true,
        },
      })
      .then(function (stream) {
        mediaStream = stream;
        chunks = [];
        var mime = pickMime();
        try {
          mediaRecorder = mime
            ? new MediaRecorder(stream, { mimeType: mime })
            : new MediaRecorder(stream);
        } catch (e) {
          mediaRecorder = new MediaRecorder(stream);
        }
        mediaRecorder.ondataavailable = function (ev) {
          if (ev.data && ev.data.size > 0) chunks.push(ev.data);
        };
        mediaRecorder.onstop = function () {
          afterStop(panel);
        };
        mediaRecorder.start(200);
        startedAt = Date.now();
        startSpeechRec(panel);
        startWaveFromStream(panel, stream);
        setPhase(panel, 'rec', 'Parlez maintenant');
        setTools(panel, {
          stop: true,
          replay: false,
          reset: false,
          submit: false,
          submitLabel: 'Terminer',
        });
        clearInterval(tickTimer);
        tickTimer = setInterval(function () {
          updateTimer(panel);
        }, 200);
        updateTimer(panel);
      })
      .catch(function () {
        setPhase(panel, 'idle', 'Micro refusé');
        idleWave(panel);
      });
  }

  function scoreBars(details) {
    var labels = {
      linguistique: 'Linguistique',
      pragmatique: 'Pragmatique',
      sociolinguistique: 'Sociolinguistique',
      phonetique: 'Phonétique',
    };
    var html = '<div class="eo-sim-scores">';
    Object.keys(labels).forEach(function (k) {
      var v = Math.max(0, Math.min(5, parseInt((details && details[k]) || 0, 10) || 0));
      var pct = (v / 5) * 100;
      html +=
        '<div class="eo-sim-score-row">' +
        '<span>' +
        esc(labels[k]) +
        '</span>' +
        '<div class="eo-sim-score-track"><i style="width:' +
        pct +
        '%"></i></div>' +
        '<strong>' +
        v +
        '/5</strong>' +
        '</div>';
    });
    html += '</div>';
    return html;
  }

  function playCandidateAudio(panel) {
    return new Promise(function (resolve) {
      var audio = panel.querySelector('.eo-sim-audio');
      if (!audio || !audio.src) {
        resolve(false);
        return;
      }
      var done = false;
      var safety = null;
      function finish() {
        if (done) return;
        done = true;
        if (safety) clearTimeout(safety);
        audio.removeEventListener('ended', finish);
        audio.removeEventListener('error', finish);
        resolve(true);
      }
      try {
        audio.pause();
      } catch (e) {}
      audio.hidden = false;
      audio.currentTime = 0;
      audio.addEventListener('ended', finish);
      audio.addEventListener('error', finish);
      function armSafety() {
        if (safety) clearTimeout(safety);
        var ms = 90000;
        if (isFinite(audio.duration) && audio.duration > 0) {
          ms = Math.min(200000, audio.duration * 1000 + 2000);
        }
        safety = setTimeout(finish, ms);
      }
      armSafety();
      var p = audio.play();
      if (p && typeof p.then === 'function') {
        p.then(function () {
          armSafety();
        }).catch(function () {
          finish();
        });
      }
    });
  }

  function pickFrVoice() {
    try {
      var voices = window.speechSynthesis.getVoices() || [];
      var fr = voices.filter(function (v) {
        return /^fr/i.test(v.lang || '');
      });
      var score = function (v) {
        var n = (v.name || '') + ' ' + (v.lang || '');
        var s = 0;
        if (/natural|neural|online|premium|enhanced|google/i.test(n)) s += 8;
        if (/microsoft|hortense|julie|paul|denise|brigitte|thomas/i.test(n)) s += 5;
        if (/fr-ca|canada/i.test(n)) s += 4;
        if (/fr-fr|france/i.test(n)) s += 3;
        if (v.localService === false) s += 2;
        return s;
      };
      fr.sort(function (a, b) {
        return score(b) - score(a);
      });
      return fr[0] || null;
    } catch (e) {
      return null;
    }
  }

  function splitSpeakChunks(text) {
    var raw = String(text || '').replace(/\s+/g, ' ').trim();
    if (!raw) return [];
    var parts = raw.match(/[^.!?…]+[.!?…]*/g) || [raw];
    var chunks = [];
    var buf = '';
    parts.forEach(function (p) {
      p = String(p || '').trim();
      if (!p) return;
      if ((buf + ' ' + p).trim().length > 220 && buf) {
        chunks.push(buf.trim());
        buf = p;
      } else {
        buf = (buf ? buf + ' ' : '') + p;
      }
    });
    if (buf.trim()) chunks.push(buf.trim());
    return chunks.length ? chunks : [raw];
  }

  function speakText(text, btn) {
    return new Promise(function (resolve) {
      if (!text || !window.speechSynthesis) {
        resolve(false);
        return;
      }
      stopTts();
      var chunks = splitSpeakChunks(text);
      var voice = pickFrVoice();
      if (btn) btn.classList.add('is-speaking');

      function speakNext(i) {
        if (i >= chunks.length) {
          if (btn) btn.classList.remove('is-speaking');
          resolve(true);
          return;
        }
        var u = new SpeechSynthesisUtterance(chunks[i]);
        u.lang = voice && voice.lang ? voice.lang : 'fr-FR';
        u.rate = 0.92;
        u.pitch = 1.02;
        u.volume = 1;
        if (voice) u.voice = voice;
        u.onend = function () {
          speakNext(i + 1);
        };
        u.onerror = function () {
          if (btn) btn.classList.remove('is-speaking');
          resolve(false);
        };
        window.speechSynthesis.speak(u);
      }

      function start() {
        speakNext(0);
      }

      if (window.speechSynthesis.getVoices().length === 0) {
        window.speechSynthesis.onvoiceschanged = function () {
          voice = pickFrVoice() || voice;
          start();
        };
        setTimeout(start, 280);
      } else {
        start();
      }
    });
  }

  function buildCoachScript(fb) {
    if (fb.advice_spoken && String(fb.advice_spoken).trim()) {
      return String(fb.advice_spoken).trim();
    }
    var parts = [];
    parts.push('Voici votre correction. Niveau estimé : ' + (fb.cefr_level || '') + '.');
    var reforms = Array.isArray(fb.reformulations) ? fb.reformulations : [];
    reforms.slice(0, 4).forEach(function (r) {
      if (r.you_said) parts.push('Vous avez dit : ' + r.you_said + '.');
      if (r.say_instead) parts.push('Il fallait plutôt : ' + r.say_instead + '.');
    });
    var missing = Array.isArray(fb.missing_points) ? fb.missing_points : [];
    missing.slice(0, 3).forEach(function (m) {
      parts.push('À ajouter : ' + (m.title || '') + (m.detail ? '. ' + m.detail : ''));
    });
    parts.push(
      'Attention aussi au rythme : évitez les trop longs silences et les euh répétés. Respirez, puis enchaînez clairement.'
    );
    if (fb.better_answer) {
      parts.push('Voici une meilleure version possible. ' + fb.better_answer);
    }
    parts.push('Le temps total n’est pas une obligation : visez la clarté, les arguments et les exemples.');
    return parts.join(' ');
  }

  function startCorrectionVoice(panel, fb) {
    var btn = panel.querySelector('.eo-sim-speak-btn');
    var caption = panel.querySelector('.eo-sim-caption');
    var script = buildCoachScript(fb);
    stopTts();
    // Stopper l’audio candidat s’il tourne — la correction est vocale coach, pas relecture complète
    var audio = panel.querySelector('.eo-sim-audio');
    if (audio) {
      try {
        audio.pause();
      } catch (e) {}
    }
    if (caption) caption.textContent = 'Correction orale…';
    speakText(script, btn).then(function () {
      if (!fb.better_answer || !String(fb.better_answer).trim()) {
        if (caption) caption.textContent = 'Correction prête';
        return;
      }
      // Si le script n’incluait pas déjà le modèle, enchaîner la version attendue
      if (script.indexOf(String(fb.better_answer).slice(0, 40)) >= 0) {
        if (caption) caption.textContent = 'Correction prête';
        return;
      }
      if (caption) caption.textContent = 'Version attendue…';
      return speakText('Voici ce qu’il fallait dire. ' + fb.better_answer, btn).then(function () {
        if (caption) caption.textContent = 'Correction prête';
      });
    });
  }

  function renderFeedback(panel, fb, autoSpeak) {
    var box = panel.querySelector('.eo-sim-result');
    if (!box || !fb) return;
    var coach = buildCoachScript(fb);
    var reforms = Array.isArray(fb.reformulations) ? fb.reformulations : [];
    var missing = Array.isArray(fb.missing_points) ? fb.missing_points : [];

    var detailsHtml = '';
    if (reforms.length || missing.length || fb.better_answer) {
      detailsHtml = '<details class="eo-sim-details"><summary>Voir le détail écrit</summary>';
      if (reforms.length) {
        detailsHtml += '<h4>Reformulations</h4><div class="eo-sim-reforms">';
        reforms.forEach(function (r) {
          detailsHtml +=
            '<div class="eo-sim-reform">' +
            (r.you_said
              ? '<p class="eo-sim-reform-you"><span>Vous</span> «&nbsp;' + esc(r.you_said) + '&nbsp;»</p>'
              : '') +
            (r.say_instead
              ? '<p class="eo-sim-reform-better"><span>Plutôt</span> «&nbsp;' +
                esc(r.say_instead) +
                '&nbsp;»</p>'
              : '') +
            '</div>';
        });
        detailsHtml += '</div>';
      }
      if (missing.length) {
        detailsHtml += '<h4>À ajouter</h4><ul class="eo-sim-missing">';
        missing.forEach(function (m) {
          detailsHtml +=
            '<li><strong>' +
            esc(m.title || '') +
            '</strong>' +
            (m.detail ? ' — ' + esc(m.detail) : '') +
            '</li>';
        });
        detailsHtml += '</ul>';
      }
      if (fb.better_answer) {
        detailsHtml +=
          '<h4>Version attendue</h4><div class="eo-sim-model">' + esc(fb.better_answer) + '</div>';
      }
      detailsHtml += '</details>';
    }

    box.innerHTML =
      '<div class="eo-sim-fb eo-sim-fb--voice">' +
      '  <div class="eo-sim-fb-top">' +
      '    <span class="eo-sim-level">' +
      esc(fb.cefr_level || '—') +
      '</span>' +
      '    <div class="eo-sim-fb-score">' +
      '      <strong>' +
      esc(String(fb.score_global != null ? fb.score_global : '—')) +
      '/20</strong>' +
      '      <span>Note estimée</span>' +
      '    </div>' +
      '  </div>' +
      scoreBars(fb.score_details || {}) +
      (fb.remarks ? '<p class="eo-sim-remarks">' + esc(fb.remarks) + '</p>' : '') +
      '  <button type="button" class="eo-sim-speak-btn eo-sim-speak-btn--main" data-speak="' +
      esc(coach) +
      '"><i class="bx bx-volume-full"></i> Réécouter la correction</button>' +
      detailsHtml +
      '</div>';
    box.hidden = false;
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    panel._eoLastFb = fb;
    if (autoSpeak) {
      startCorrectionVoice(panel, fb);
    }
  }

  function stopTts() {
    try {
      if (window.speechSynthesis) window.speechSynthesis.cancel();
    } catch (e) {}
  }

  function speakAdvice(text, btn) {
    speakText(text, btn);
  }

  function correctOral(btnToggle, panel) {
    if (!loggedIn) {
      window.location.href = loginHref;
      return;
    }
    var ta = panel.querySelector('.eo-sim-transcript');
    var transcript = ta ? String(ta.value || '').trim() : '';
    if (!blob && transcript.length < 12) {
      setPhase(panel, 'done', 'Aucun enregistrement');
      return;
    }
    var elapsed = startedAt ? Math.floor((Date.now() - startedAt) / 1000) : 0;
    var fd = new FormData();
    fd.append('action', 'ai_correct_oral');
    fd.append('task_key', btnToggle.getAttribute('data-task-key') || 'tache2');
    fd.append('exam_id', btnToggle.getAttribute('data-exam-id') || '0');
    fd.append('subject_id', btnToggle.getAttribute('data-subject-id') || '0');
    fd.append('subject_title', btnToggle.getAttribute('data-subject-title') || '');
    fd.append('subject_prompt', btnToggle.getAttribute('data-subject-prompt') || '');
    fd.append('role_label', btnToggle.getAttribute('data-role-label') || '');
    fd.append('transcript', transcript);
    fd.append('duration_sec', String(elapsed));
    if (blob) {
      var ext = (blob.type || '').indexOf('mp4') >= 0 ? 'm4a' : 'webm';
      fd.append('audio', blob, 'oral.' + ext);
    }

    stopTts();
    setPhase(panel, 'loading', 'Correction en cours…');
    setTools(panel, {
      stop: false,
      replay: !!blob,
      reset: false,
      submit: false,
      submitLabel: '…',
    });

    fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, j: j };
        });
      })
      .then(function (res) {
        if (res.j && res.j.reason === 'login') {
          window.location.href = loginHref;
          return;
        }
        if (!res.j || !res.j.success) {
          setPhase(panel, 'done', (res.j && res.j.message) || 'Échec de la correction');
          setTools(panel, {
            stop: false,
            replay: !!blob,
            reset: true,
            submit: true,
            submitLabel: 'Terminer',
          });
          return;
        }
        var fb = res.j.feedback || {};
        renderFeedback(panel, fb, true);
        setPhase(panel, 'done', 'Correction orale…');
        setTools(panel, {
          stop: false,
          replay: !!blob,
          reset: true,
          submit: true,
          submitLabel: 'Terminer',
        });
        if (ta && fb.transcript && !ta.value.trim()) {
          ta.value = fb.transcript;
        }
      })
      .catch(function () {
        setPhase(panel, 'done', 'Erreur réseau');
        setTools(panel, {
          stop: false,
          replay: !!blob,
          reset: true,
          submit: true,
          submitLabel: 'Terminer',
        });
      });
  }

  function openPanel(toggle) {
    var host = hostFromToggle(toggle);
    var panel = host.querySelector('.eo-sim-panel');
    if (!panel) return;

    if (toggle.classList.contains('is-disabled') || toggle.getAttribute('aria-disabled') === 'true') {
      window.location.href = loginHref;
      return;
    }

    var open = toggle.getAttribute('aria-expanded') === 'true';
    document.querySelectorAll('.eo-sim-toggle[aria-expanded="true"]').forEach(function (b) {
      if (b === toggle) return;
      b.setAttribute('aria-expanded', 'false');
      b.classList.remove('is-active');
      var h = hostFromToggle(b);
      var p = h && h.querySelector('.eo-sim-panel');
      if (p) {
        if (mediaRecorder && mediaRecorder.state === 'recording' && p.contains(document.activeElement)) {
          stopRecording(p);
        }
        p.hidden = true;
      }
    });

    open = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.classList.toggle('is-active', open);
    panel.hidden = !open;

    if (open) {
      if (!panel.querySelector('.eo-sim-inner')) {
        var maxSec = parseInt(toggle.getAttribute('data-max-sec') || '210', 10) || 210;
        panel.innerHTML = panelHtml(maxSec);
        idleWave(panel);
      }
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      if (mediaRecorder && mediaRecorder.state === 'recording') stopRecording(panel);
      stopTts();
      stopWave();
    }
  }

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('.eo-sim-toggle');
    if (toggle) {
      e.preventDefault();
      openPanel(toggle);
      return;
    }

    var panel = e.target.closest('.eo-sim-panel');
    if (!panel) return;

    if (e.target.closest('.eo-sim-mic')) {
      if (phase === 'rec') stopRecording(panel);
      else if (phase !== 'loading') startRecording(panel);
      return;
    }
    if (e.target.closest('.eo-sim-stop')) {
      stopRecording(panel);
      return;
    }
    if (e.target.closest('.eo-sim-replay')) {
      var audio = panel.querySelector('.eo-sim-audio');
      if (audio && audio.src) {
        audio.hidden = false;
        audio.currentTime = 0;
        audio.play().catch(function () {});
      }
      return;
    }
    if (e.target.closest('.eo-sim-reset')) {
      if (mediaRecorder && mediaRecorder.state === 'recording') stopRecording(panel);
      resetBlob(panel);
      var ta = panel.querySelector('.eo-sim-transcript');
      if (ta) ta.value = '';
      var result = panel.querySelector('.eo-sim-result');
      if (result) {
        result.hidden = true;
        result.innerHTML = '';
      }
      startedAt = 0;
      var timer = panel.querySelector('.eo-sim-timer');
      if (timer) {
        timer.textContent = '00:00';
        timer.classList.remove('is-warn');
      }
      setPhase(panel, 'idle', 'Appuyez pour parler · durée libre');
      setTools(panel, {
        stop: false,
        replay: false,
        reset: false,
        submit: false,
        submitLabel: 'Terminer',
      });
      idleWave(panel);
      stopTts();
      return;
    }
    if (e.target.closest('.eo-sim-correct')) {
      var host = panel.closest('.eo-subject-card, .eo-t1-card, .eo-task-panel') || panel.parentElement;
      var btn = host.querySelector('.eo-sim-toggle');
      if (btn) correctOral(btn, panel);
      return;
    }
    var speakBtn = e.target.closest('.eo-sim-speak-btn');
    if (speakBtn) {
      var fb = panel._eoLastFb;
      if (fb) {
        startCorrectionVoice(panel, fb);
      } else {
        speakAdvice(speakBtn.getAttribute('data-speak') || '', speakBtn);
      }
    }
  });
})();
