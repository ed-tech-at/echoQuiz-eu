var adminView, alertBtn, autoReload, beamer, drawChart, echoPhaseStart, echoPhaseVisible, getInCheckbox, getRatings, handleEq, langText, lastKnownEtag, like, loadText, ownAId, parseStars, questionId, roomId, roomLang, session_id, shortHost, shortUrl, startNchan, tnId, tolike, updateAnswerBg, updateBadges, updateOwnAgrade;

lastKnownEtag = 0;

session_id = "";

tnId = 0;

roomId = 0;

roomLang = "de";

questionId = 0;

ownAId = 0;

beamer = 0;

shortUrl = "";

shortHost = "";

autoReload = 2; // 0.. of, 1.. ajax, 2.. websocket

adminView = 0;

echoPhaseVisible = 0;

langText = {
  de: {
    "question": "Frage",
    "questionPreparing": "Quizfrage wird vorbereitet.",
    "joinNow": "Steigen Sie ein:",
    "theEchoQuizApproach": "Der echoQuiz Ansatz:<br>Ihre Antworten werden zufällig an einige andere Teilnehmer:innen weitergegeben, und diese können Ihre Antworten bewerten.",
    "finalQuestion": "Abschlussfragen zum echoQuiz",
    "thankYou": "Vielen Dank für Ihre Teilnahme!"
  },
  en: {
    "question": "Question",
    "questionPreparing": "Quiz question is being prepared.",
    "joinNow": "Join now:",
    "theEchoQuizApproach": "The echoQuiz approach:<br>Your answers are randomly shared with some other participants, and they can rate your answers.",
    "finalQuestion": "Final questions for the echoQuiz",
    "thankYou": "Thank you for your participation!"
  }
};

// #{langText[roomLang].questionPreparing}
loadText = function() {
  return $.ajax("/api_gpt?getText=1", {
    type: "POST",
    data: {},
    headers: {
      "If-None-Match": lastKnownEtag
    },
    error: function(jqXHR, textStatus, errorThrown) {
      $('body').append(`AJAX Error: ${textStatus}`);
      if (autoReload === 1) {
        return setTimeout(function() {
          return loadText();
        }, 5000);
      }
    },
    success: function(data, textStatus, jqXHR) {
      if (autoReload === 1) {
        setTimeout(function() {
          return loadText();
        }, 500);
      }
      if (jqXHR.status === 200) {
        if (lastKnownEtag === jqXHR.getResponseHeader('etagb')) {
          return;
        }
        lastKnownEtag = jqXHR.getResponseHeader('etagb');
        $('#text').replaceWith(data);
        return htmx.process(document.body);
      } else if (jqXHR.status === 304) {

      }
    }
  });
};

// console.log 'Cached data unchanged'
getInCheckbox = function(sender) {
  if ($("#agb").is(":checked")) {
    $("#getin").addClass("getin");
    return $("#getin").removeClass("disable");
  } else {
    $("#getin").addClass("disable");
    return $("#getin").removeClass("getin");
  }
};

drawChart = function(data) {
  var ctx, myChart;
  $('#chart').empty().append("<canvas id='myChart' width='400' height='400'></canvas>");
  ctx = document.getElementById('myChart').getContext('2d');
  return myChart = new Chart(ctx, data);
};

like = function(value, rId, element) {
  var parent;
  parent = element.parentNode.parentNode;
  if (value === 1) {
    if (parent.classList.contains('like')) {
      value = 0;
    }
    parent.classList.toggle('like');
    parent.classList.remove('openend');
    parent.classList.remove('dislike');
  } else if (value === 2) {
    if (parent.classList.contains('openend')) {
      value = 0;
    }
    parent.classList.remove('like');
    parent.classList.toggle('openend');
    parent.classList.remove('dislike');
  } else if (value === 3) {
    if (parent.classList.contains('dislike')) {
      value = 0;
    }
    parent.classList.remove('like');
    parent.classList.remove('openend');
    parent.classList.toggle('dislike');
  }
  return $.ajax({
    url: "/api/setRating",
    type: "POST",
    data: {
      rating: value,
      ratingId: rId
    },
    success: function(response) {
      var msg;
      // console.log "Rating set successfully"
      msg = JSON.parse(response);
      return handleEq(msg);
    },
    error: function(jqXHR, textStatus, errorThrown) {
      return console.error(`AJAX Error: ${textStatus}`);
    }
  });
};

tolike = function(value, element) {
  var parent;
  parent = element.parentNode.parentNode;
  if (value === 1) {
    parent.classList.add('tolike');
    parent.classList.remove('toopenend');
    parent.classList.remove('todislike');
  } else if (value === 0) {
    parent.classList.remove('tolike');
    parent.classList.remove('toopenend');
    parent.classList.remove('todislike');
  } else if (value === 2) {
    parent.classList.remove('tolike');
    parent.classList.add('toopenend');
    parent.classList.remove('todislike');
  } else if (value === 3) {
    parent.classList.remove('tolike');
    parent.classList.remove('toopenend');
    parent.classList.add('todislike');
  }
};

updateAnswerBg = function(target) {
  var doneRatings, pct1, pct2, pctDone;
  if (adminView) {
    target.find(".userwaiting mark").text(target.data('r0'));
    target.find(".likebtn mark").text(target.data('r1'));
    target.find(".openendbtn mark").text(target.data('r2'));
    target.find(".dislikebtn mark").text(target.data('r3'));
  }
  doneRatings = target.data('r1') + target.data('r2') + target.data('r3');
  pctDone = (1 - (target.data('r0') / (target.data('r0') + doneRatings))) * 100;
  target.css('--pctDone', `${pctDone.toFixed(2)}%`);
  pct1 = (target.data('r1') / doneRatings) * 100;
  target.css('--pct1', `${pct1.toFixed(2)}%`);
  pct2 = ((target.data('r2') + target.data('r1')) / doneRatings) * 100;
  return target.css('--pct2', `${pct2.toFixed(2)}%`);
};

alertBtn = function(ratingId, sender) {
  var alertHtml, parent;
  parent = $(sender).closest('article');
  if (parent.find('.alertForm').length) {
    parent.find('.alertForm').remove();
    parent.removeClass('alertFormOpen');
    return;
  }
  parent.addClass('alertFormOpen');
  alertHtml = `<form class='formjs alertForm' action='/api/answerAlert'>\n<h4>Möchten Sie diese Antwort an das Moderationsteam melden?</h4>\n<label for='alert_beleidigend'>\n  <input type='checkbox' id='alert_beleidigend' name='beleidigend' required>\n  Dieser Inhalt ist beleidigend.\n</label>\n<label for='alert_user_text'>Meldegrund*</label>\n<input type='hidden' name='ratingId' value='${ratingId}'>\n<input type='text' id='alert_user_text' name='alert_user_text' required>\n<label for='alert_sender_user_email'>Optional: Meine E-Mail für Rückmeldungen</label>\n<input type='alert_sender_user_email' id='alert_sender_user_email' name='alert_sender_user_email'>\n<button class='sendAlert btn'>Senden</button>\n</form>`;
  return parent.append(alertHtml);
};

updateBadges = function() {
  return $.ajax("/api/getBadges", {
    type: "POST",
    data: {
      tnId: tnId
    },
    success: function(response) {
      var jsonResponse;
      // console.log "Request successful"
      // console.log response
      jsonResponse = JSON.parse(response);
      return handleEq(jsonResponse);
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

parseStars = function() {
  return document.querySelectorAll('.stars').forEach(function(stars) {
    stars.addEventListener('mouseover', function(e) {
      var index;
      if (e.target.tagName === 'I') {
        index = Array.from(stars.children).indexOf(e.target);
        return stars.querySelectorAll('i').forEach(function(star, i) {
          star.classList.toggle('fas', i <= index);
          return star.classList.toggle('far', i > index);
        });
      }
    });
    stars.addEventListener('mouseout', function() {
      return stars.querySelectorAll('i').forEach(function(star) {
        if (!star.classList.contains('selected')) {
          star.classList.add('far');
          return star.classList.remove('fas');
        }
      });
    });
    return stars.addEventListener('click', function(e) {
      var index;
      if (e.target.tagName === 'I') {
        index = Array.from(stars.children).indexOf(e.target);
        // console.log index
        // console.log e.target.parentElement.parentElement.dataset.fq
        $.ajax({
          url: "/api/setFeedback",
          type: "POST",
          data: {
            stars: index + 1,
            fqk: e.target.parentElement.parentElement.dataset.fqk,
            tnId: tnId,
            roomId: roomId
          },
          success: function(response) {
            // msg = JSON.parse response
            // handleEq(msg)
            return updateBadges();
          },
          error: function(jqXHR, textStatus, errorThrown) {
            return console.error(`AJAX Error: ${textStatus}`);
          }
        });
        return stars.querySelectorAll('i').forEach(function(star, i) {
          star.classList.toggle('selected', i <= index);
          star.classList.toggle('fas', i <= index);
          return star.classList.toggle('far', i > index);
        });
      }
    });
  });
};

echoPhaseStart = function() {
  // $("mark#eye").fadeOut()
  // $("mark#eye").removeClass("echoPhase") 
  if (echoPhaseVisible) {
    return;
  }
  echoPhaseVisible = 1;
  $(".eqlogo").removeClass("quiz").addClass("echo");
  $("mark#q").addClass("echoPhase");
  
  // $("#submitAnswer").addClass('opacity0')

  // $("#inputs").addClass('disabled')
  $("#inputs").fadeOut();
  return updateOwnAgrade();
};

updateOwnAgrade = function() {
  $("mark#ownAgrade").html("");
  $("mark#ownAgrade").addClass("opacity0");
  $("mark#ownAgrade").show();
  return $.ajax("/api/getOwnAgrade", {
    type: "POST",
    data: {
      tnId: tnId,
      questionId: questionId,
      roomId: roomId
    },
    success: function(response) {
      // console.log "Request successful"
      // console.log response
      // msg = JSON.parse message
      $("mark#ownAgrade").html(response);
      updateAnswerBg($("#ownGrade"));
      return setTimeout(function() {
        return $("mark#ownAgrade").removeClass("opacity0");
      }, 400);
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

startNchan = function(wsshost) {
  var opt, sub;
  // NchanSubscriber = require("nchan")
  opt = {
    subscriber: 'websocket',
    reconnect: void 0,
    shared: true
  };
  sub = new NchanSubscriber(wsshost + "/sub_id/?id=eq!" + roomId, opt);
  // console.log sub
  sub.on('message', function(message, message_metadata) {
    var msg;
    // message is a string
    // message_metadata is a hash that may contain 'id' and 'content-type'
    // console.log message
    // console.log message_metadata
    msg = JSON.parse(message);
    // console.log msg
    // if msg.a == "q"
    // return handleEq(msg)
    return handleEq(msg);
    if (msg.a === "p") {
      loadText();
    }
    if (msg.a === "reset") {
      if (msg.session_id === session_id) {
        htmx.ajax('GET', '/api_gpt?resetUsername=2', '#main');
      }
    }
    if (msg.a === "chart") {
      $.ajax("/chart-config.json", {
        type: "GET",
        data: {},
        error: function(jqXHR, textStatus, errorThrown) {
          return $('body').append(`AJAX Error: ${textStatus}`);
        },
        success: function(data, textStatus, jqXHR) {
          return drawChart(data);
        }
      });
    }
    if (msg.a === "stop") {
      $.ajax("/", {
        type: "GET",
        data: {},
        error: function(jqXHR, textStatus, errorThrown) {
          return $('body').append(`AJAX Error: ${textStatus}`);
        },
        success: function(data, textStatus, jqXHR) {
          return $('body').html(data);
        }
      });
      sub.stop();
      return;
    }
  });
  sub.on("connect", function(evt) {});
  // console.log sub
  // console.log evt
  // loadText()
  sub.on("error", function(evt, error_description) {
    console.log("error");
    console.log(sub);
    console.log(evt);
    return console.log(error_description);
  });
  return sub.start();
};

handleEq = function(eqData) {
  var answerHtml, classString, j, len, oldRating, questionHtml, rating, ref, target;
  // console.log eqData
  if (eqData.qId != null) {
    questionId = eqData.qId;
  }
  if (eqData.q != null) {
    // new active quesiton
    ownAId = 0;
    if (adminView) {
      handleAdminEq(eqData);
    } else {
      // console.log ""
      $("#rating").empty();
      $("mark#ownAgrade").html("");
      questionHtml = `<div class='question'>\n<div class='questionText expletus'>${eqData.q}</div>\n</div>`;
      $("mark#q").html(questionHtml);
      $("mark#qInput").hide();
      $("mark#qInput").html(`<form action='/api/answer' class='formjs' id='inputs' eq-loader='.getin'>\n  <input name='answer_text' id='answer_text' class='large' value='' required='1'  maxlength='240' autocomplete='off' >\n  <input name='tnId' value='${tnId}' type='hidden'>\n  <input name='roomId' value='${roomId}' type='hidden'>\n  <input name='questionId' value='${questionId}' type='hidden'>\n  <button class='getin btn' id='submitAnswer'><i class='fa fa-paper-plane' aria-hidden='true'></i></button>\n</form>`);
      $("mark#qInput").fadeIn();
      updateBadges();
    }
  }
  if (eqData.aS != null) {
    // answer Submitted
    // $(".getin").html "<i class='far fa-check-square'></i>"
    // $("#answer_text").prop('disabled', true)
    // $("#answer_text").val(eqData.aS)
    // $("#inputs").addClass('disabled')
    // $("#inputs").addClass('hasAnswer')
    $("#inputs").fadeOut();
    $("mark#ownAgrade").hide();
    if (eqData.ownAId != null) {
      ownAId = eqData.ownAId;
    }
    $("mark#ownAgrade").html(`<div class='answers gradient'><article class='vis force forceWhite aId-${ownAId} ' data-r0='0' data-r1='0' data-r2='0' data-r3='0' id='ownGrade'>${eqData.aS}</article><span class='getin largeBtn  frontend-grade'><i class='far fa-check-square' aria-hidden='true'></i></span></div>`);
    setTimeout(function() {
      return $("mark#ownAgrade").fadeIn();
    }, 400);
  }
  if (eqData.rTnId != null) {
    // new rating for rTnId = tnId
    if (eqData.rTnId === tnId) {
      //# get all new ratings
      getRatings();
    }
  }
  if (eqData.phase != null) {
    // console.log eqData.phase
    if (eqData.phase === "e") {
      // now echo
      $("mark#eye").removeClass("echoPhase");
      $("mark#eye").addClass("opacity0");
      echoPhaseStart();
    } else if (eqData.phase === "q") {
      echoPhaseVisible = 0;
      // now quiz
      $(".eqlogo").removeClass("echo").addClass("quiz");
      $("mark#q").removeClass("echoPhase");
      // $("mark#q form").fadeIn()
      $("mark#eye").removeClass("echoPhase");
      $("mark#eye").html("");
      // $("#submitAnswer").removeClass('opacity0')
      if (ownAId === 0) {
        $("mark#ownAgrade").fadeOut();
        setTimeout(function() {
          return $("#inputs").fadeIn();
        }, 400);
      }
    // if ($("inputs").hasClass('hasAnswer'))
    // $("#inputs").removeClass('disabled')
    } else if (eqData.phase === "b") {
      // brteak
      echoPhaseVisible = 0;
      $(".eqlogo").removeClass("echo").addClass("quiz");
      $("mark#q").addClass("echoPhase");
      $("mark#eye").removeClass("echoPhase");
      $("mark#eye").html("");
      $("mark#rating").html("");
      $("mark#q").html(`<div class='question'>\n<div class='questionText expletus questionBreak'>${langText[roomLang].questionPreparing}</div>\n</div>`);
      $("#inputs").fadeOut();
      $("mark#ownAgrade").html("");
      if (beamer) {
        $("mark#q").html(` <div class='question'>\n<div class='questionText expletus questionBreak'>${langText[roomLang].questionPreparing}</div>\n</div><div id='beamerInfo'><div id='qrcode'></div><div><p>${langText[roomLang].joinNow}<a href='${shortHost}/${roomId}' class='expletus'>${shortUrl}/${roomId}</a><span>${langText[roomLang].theEchoQuizApproach}</span></div></div>`);
        new QRCode(document.getElementById("qrcode"), {
          text: `${shortHost}/${roomId}`,
          width: 256,
          height: 256,
          colorDark: "#2d6408",
          colorLight: "#ffffff",
          correctLevel: QRCode.CorrectLevel.L
        });
      }
    // correctLevel: L|M|Q|H
    } else if (eqData.phase === "x") {
      // room is closed
      $.ajax("/api/logoutFromRoomId", {
        type: "POST",
        data: {
          roomId: roomId
        },
        error: function(jqXHR, textStatus, errorThrown) {
          return $('body').append(`AJAX Error: ${textStatus}`);
        },
        success: function(data, textStatus, jqXHR) {
          // $('body').html data
          return window.location.href = "/";
        }
      });
    } else if (eqData.eyeT != null) {
      // echo: show answers
      echoPhaseStart();
      $("mark#eye").addClass("echoPhase opacity0");
      $("mark#eye").show();
      setTimeout(function() {
        $("mark#eye").html(`<div class="answers gradient">\n${eqData.eyeT}\n</div>`);
        updateAnswerBg($("#eyeArticle"));
        return $("mark#eye").removeClass("opacity0");
      }, 300);
    } else if (eqData.phase === "z") {
      if (beamer) {
        echoPhaseVisible = 0;
        questionId = -1;
        $("mark#eye").fadeOut();
        $("mark#eye").removeClass("echoPhase");
        $("mark#eye").html("");
        $(".eqlogo").removeClass("echo").addClass("quiz");
        $("mark#q").removeClass("echoPhase");
        $("#inputs").fadeOut();
        $("mark#rating").html("");
        $("mark#q").html(` <div class='question'>\n<div class='questionText expletus questionBreak'>${langText[roomLang].thankYou}</div> \n</div>`);
      } else {
        // abschluss
        echoPhaseVisible = 0;
        questionId = -1;
        $("mark#eye").fadeOut();
        $("mark#eye").removeClass("echoPhase");
        $("mark#eye").html("");
        $(".eqlogo").removeClass("echo").addClass("quiz");
        $("mark#q").removeClass("echoPhase");
        $("#inputs").fadeOut();
        $("mark#rating").html("");
        $("mark#q").html(` <div class='question'>\n<div class='questionText expletus questionBreak'>${langText[roomLang].finalQuestion}</div> \n</div>`);
        updateOwnAgrade();
      }
    }
  }
  if (eqData.ratings != null) {
    ref = eqData.ratings;
    // console.log eqData.ratings

    // $("#rating").empty()
    for (j = 0, len = ref.length; j < len; j++) {
      rating = ref[j];
      if ($(`.rId-${rating.ratingId}`).length) {
        continue;
      }
      classString = "";
      if (rating.rating === 1) {
        classString = "like";
      } else if (rating.rating === 2) {
        classString = "openend";
      } else if (rating.rating === 3) {
        classString = "dislike";
      }
      answerHtml = `<article class="rId rId-${rating.ratingId} ${classString}"><span class='aT'>${rating.answer_text}</span> <span class="votingBtns">\n  <button class="likebtn" onclick="like(1, ${rating.ratingId}, this)" onmouseenter="tolike(1, this)" onmouseleave="tolike(0, this)"><i class="far fa-thumbs-up" aria-hidden="true"></i></button>\n  <button class="openendbtn" onclick="like(2, ${rating.ratingId}, this)" onmouseenter="tolike(2, this)" onmouseleave="tolike(0, this)"><i class="far fa-question-circle" aria-hidden="true"></i></button>\n  <button class="dislikebtn" onclick="like(3, ${rating.ratingId}, this)" onmouseenter="tolike(3, this)" onmouseleave="tolike(0, this)"><i class="far fa-thumbs-down" aria-hidden="true"></i></button>\n  <button class="alertbtn" onclick="alertBtn(${rating.ratingId}, this)"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></button>\n</span>\n</article>`;
      $("#rating").append(answerHtml);
      setTimeout(function() {
        return $(".rId").addClass("vis");
      }, 200);
    }
  }
  // $(".rId-#{rating.ratingId}").addClass("vis")
  if (eqData.hideRating != null) {
    $(`.rId-${eqData.hideRating}`).remove();
  }
  if (eqData.answerRating != null) {
    // console.log eqData
    target = $(`.aId-${eqData.answerRating}`);
    if (target.length) {
      target.data('r' + eqData.rating, target.data('r' + eqData.rating) + 1);
      oldRating = eqData.oldRating;
      if (oldRating < 0) {
        oldRating = 0;
      }
      target.data('r' + oldRating, target.data('r' + oldRating) - 1);
      
      // if adminView
      updateAnswerBg(target);
    }
  }
  if (eqData.updateGradeTn != null) {
    if (eqData.updateGradeTn === tnId) {
      updateOwnAgrade();
    }
  }
  if (eqData.badgeCount != null) {
    $("#badgeCount").html(eqData.badgeCount + "<img style='--i: 0' src='/badges/question.png' />");
  }
  if (eqData.admin != null) {
    return handleAdminEq(eqData.admin);
  }
};

getRatings = function() {
  return $.ajax({
    type: "POST",
    url: "/api/getTnRatings",
    data: {
      tnId: tnId,
      questionId: questionId,
      roomId: roomId
    },
    success: function(response) {
      var jsonResponse;
      // console.log "Request successful"
      // console.log response
      jsonResponse = JSON.parse(response);
      return handleEq(jsonResponse);
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

document.addEventListener('submit', function(event) {
  var form, loaderElement;
  form = event.target;
  if (form.classList.contains('formjs')) {
    event.preventDefault();
    if (form.classList.contains('disabled')) {
      return;
    }
    if (form.hasAttribute('eq-loader')) {
      loaderElement = document.querySelector(form.getAttribute('eq-loader'));
      if (loaderElement) {
        loaderElement.innerHTML = "<i class='fa fa-spinner fa-spin'></i>";
      }
    }
    return $.ajax({
      url: form.action,
      type: "POST",
      data: $(form).serialize(),
      success: function(response) {
        var jsonResponse;
        jsonResponse = JSON.parse(response);
        return handleEq(jsonResponse);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        return console.error(`AJAX Error: ${textStatus}`);
      }
    });
  }
});


//# sourceMappingURL=eq-js.js.map
//# sourceURL=coffeescript