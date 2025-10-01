var alertBtnAdmin, eye, forceFeedbackList, forceTnList, grade, handleAdminEq, loadGptAnswer, parseTnList, setRoomPhaseBtn, startAdminNchan, tnList;

startAdminNchan = function(wsshost) {
  var opt, sub;
  // NchanSubscriber = require("nchan")
  opt = {
    subscriber: 'websocket',
    reconnect: void 0,
    shared: true
  };
  sub = new NchanSubscriber(wsshost + "/sub_id/?id=eq!!" + roomId, opt);
  // console.log sub
  sub.on('message', function(message, message_metadata) {
    var msg;
    // message is a string
    // message_metadata is a hash that may contain 'id' and 'content-type'
    // console.log message
    // console.log message_metadata
    msg = JSON.parse(message);
    // console.log msg
    // if msg.a == "tn"
    // return handleAdminEq(msg)
    return handleAdminEq(msg);
  });
  sub.on("connect", function(evt) {
    console.log(sub);
    return console.log(evt);
  });
  // loadText()
  sub.on("error", function(evt, error_description) {
    console.log("error");
    console.log(sub);
    console.log(evt);
    return console.log(error_description);
  });
  return sub.start();
};

handleAdminEq = function(eqData) {
  var aid, alertBtn, answerHtml, currentCount, i, len, questionHtml, ref, target;
  // console.log eqData
  if (eqData.tnId != null) {
    tnList.push({
      tnId: eqData.tnId,
      tnName: eqData.tnName
    });
    parseTnList();
  }
  if (eqData.forceTnList != null) {
    forceTnList();
  }
  if (eqData.forceFeedbackList != null) {
    forceFeedbackList();
  }
  if (eqData.aT != null) {
    //  new answer
    if (adminView) {
      // console.log eqData.a
      if (eqData.oldAid != null) {
        $("#answers").find(".aId-" + eqData.oldAid + " .aT").text(eqData.aT);
        $("#answers").find(".aId-" + eqData.oldAid).removeClass("aId-" + eqData.oldAid).addClass("aId-" + eqData.aId);
      } else {
        answerHtml = `<article class="vis gradient aId-${eqData.aId} ${eqData.aGradeClass}" data-r0=${eqData.r0} data-r1=${eqData.r1} data-r2=${eqData.r2} data-r3=${eqData.r3} ><span class='aT'>${eqData.aT}</span> <span class="votingBtns">\n  <button class="aibtn" onclick="loadGptAnswer($('#qId-${eqData.aQid} .questionText').text(), $('#qId-${eqData.aQid} .contextText').val(), '${eqData.aT}', '${eqData.aId}')"><i class="fas fa-microchip"></i></button>\n  <button class="likebtn" onclick="grade(4, ${eqData.aId}, this)"><mark>${eqData.r1}</mark><i class="fa fa-check" aria-hidden="true"></i></button>\n  <button class="openendbtn" onclick="grade(5, ${eqData.aId}, this)"><mark>${eqData.r2}</mark><i class="fas fa-balance-scale-right" aria-hidden="true"></i></button>\n  <button class="dislikebtn" onclick="grade(6, ${eqData.aId}, this)")"><mark>${eqData.r3}</mark><i class="fa fa-times" aria-hidden="true"></i></button>\n  <button class="alertbtn" onclick="alertBtnAdmin(${eqData.aId}, this)"><mark>${eqData.alertMark}</mark><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></button>\n  <button class="eyebtn" onclick="eye(1, ${eqData.aId}, this)")"><i class="fa fa-eye" aria-hidden="true"></i></button>\n  <button class="eyebtndown" onclick="eye(-1, ${eqData.aId}, this)"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>\n</span>\n</article>`;
        // <button class="userwaiting" onclick="userSpread('#{eqData.aId}')"><mark>#{eqData.r0}</mark><i class="fas fa-user-clock"></i></button>
        $("#qId-" + eqData.aQid + " .answers").append(answerHtml);
        $("#qId-" + eqData.aQid + "-count").text($("#qId-" + eqData.aQid + " .answers article").length);
        updateAnswerBg($("#answers .aId-" + eqData.aId));
      }
    }
  }
  if (eqData.q != null) {
    questionHtml = `<div class='question'>\n<div class='questionText expletus'>${eqData.q}</div>        \n</div>`;
    $("mark#q").html(questionHtml);
    handleAdminEq({
      qHistory: eqData.q,
      qId: eqData.qId
    });
  }
  if (eqData.qHistory != null) {
    // new active quesiton
    if (adminView) {
      questionHtml = `<section class='questionHistory' id='qId-${eqData.qId}'>\n<div class='questionText expletus'>${eqData.qHistory}</div>\n<div class='questionMetaFlex'>\n<div><label>Antworten</label> <mark id='qId-${eqData.qId}-count' class='qId-count'>0</mark></div>\n<div class='contextWrapper'><label>Fragen-Kontext</label>\n<textarea class='contextText' rows=4></textarea></div>\n</div>\n\n<div class='answers gradient'></div>\n</section>`;
      $("mark#answers").prepend(questionHtml);
    }
  }
  if (eqData.placeholderQuestion != null) {
    $("#questionTextInput").attr("placeholder", eqData.placeholderQuestion);
    $("#questionTextInput").val("");
  }
  if (eqData.answerAlertAdmin != null) {
    $("#answers").find(".aId-" + eqData.answerAlertAdmin).remove();
  }
  if (eqData.answerAlert != null) {
    alertBtn = $("#answers").find(".aId-" + eqData.answerAlert + " .alertbtn mark");
    currentCount = parseInt(alertBtn.text()) || 0;
    alertBtn.text(currentCount + 1);
  }
  if (eqData.ratingAusgegebenAids != null) {
    ref = eqData.ratingAusgegebenAids;
    for (i = 0, len = ref.length; i < len; i++) {
      aid = ref[i];
      target = $("#answers .aId-" + aid);
      target.data('r0', target.data('r0') + 1);
      updateAnswerBg(target);
    }
  }
  if (eqData.answerRating != null) {
    return handleEq(eqData);
  }
};

loadGptAnswer = function(frage, kontext, antwort, answer_id) {
  $(`.aId-${answer_id} .aibtn`).addClass("inactive");
  // MSG = '[{"role":"system","content":"You are a helpful assistant. Based on the question {q} in the user input, the following answer {a} was given. Write a very short comment in keywords for the teacher of a live session to give feedback on the answer and a {grade}: \'correct\', \'wrong\' in JSON. Be precise, answer in German!"},{"role":"user","content":"{\'q\': \'Welche SDG gibt es?\',\'a\': \'Qualität Essen\'}"},{"role":"assistant","content":"{\'comment\': \'Nicht korrekt auf SDGs bezogen.\\n\\\"Qualität Essen\\\" entspricht nicht einem SDG.\\nVielleicht war \\\"SDG 2: Null Hunger\\\" gemeint.\', \'grade\': \'wrong\'}"},{"role":"user","content":"{\'q\': \'' + frage + '\',\'a\': \'' + antwort + '\'}"}]'
  // if kontext?.length > 0

  //   kontext = kontext.replace(/'/g, "\\'").replace(/\n/g, " ")
  //   MSG = '[{"role":"system","content":"You are a helpful assistant. Based on the question {q} and context {context} in the user input, the following answer {a} was given. Write a very short comment in keywords for the teacher of a live session to give feedback on the answer and a {grade}: \'correct\', \'wrong\' in JSON. Be precise, answer in German!"},{"role":"user","content":"{\'q\': \'Welche SDG gibt es?\',\'a\': \'Qualität Essen\'}"},{"role":"assistant","content":"{\'comment\': \'Nicht korrekt auf SDGs bezogen.\\n\\\"Qualität Essen\\\" entspricht nicht einem SDG.\\nVielleicht war \\\"SDG 2: Null Hunger\\\" gemeint.\', \'grade\': \'wrong\'}"},{"role":"user","content":"{\'q\': \'' + frage + '\',\'context\': \'' + kontext + '\',\'a\': \'' + antwort + '\'}"}]'
  return $.ajax("/api/gptAnswer", {
    type: "POST",
    data: {
      roomId: roomId,
      frage: frage,
      kontext: kontext,
      antwort: antwort,
      apikey: $("#gptApiKey").val()
    },
    error: function(jqXHR, textStatus, errorThrown) {
      return $('body').append(`AJAX Error: ${textStatus}`);
    },
    success: function(data, textStatus, jqXHR) {
      var ref, ref1, ref2, ref3, response;
      if (jqXHR.status === 200) {
        console.log(data);
        // console.log JSON.parse(data)
        // msg = JSON.parse(data)
        data = JSON.parse(data);
        if (data.error) {
          $('.aId-' + answer_id + ' .aT').append("<div class='gptAnswer'>" + data.error + "</div>");
          if (((ref = data.all) != null ? (ref1 = ref.error) != null ? ref1.message : void 0 : void 0)) {
            $('.aId-' + answer_id + ' .aT').append("<div class='gptAnswer'>" + ((ref2 = data.all) != null ? (ref3 = ref2.error) != null ? ref3.message : void 0 : void 0) + "</div>");
          }
          return;
        }
        // console.log data
        // console.log JSON.parse(data.response)
        response = JSON.parse(data.response);
        $('.aId-' + answer_id + ' .aT').append("<div class='gptAnswer'>" + response.feedback + "</div>");
        if (response.grade === "unclear") {
          return $('.aId-' + answer_id + ' .openendbtn').click();
        } else if (response.grade === "correct" || response.grade === "partlycorrect") {
          if ($('.aId-' + answer_id).hasClass('like')) {
            return;
          }
          return $('.aId-' + answer_id + ' .likebtn').click();
        } else if (response.grade === "wrong") {
          if ($('.aId-' + answer_id).hasClass('dislike')) {
            return;
          }
          return $('.aId-' + answer_id + ' .dislikebtn').click();
        }
      }
    }
  });
};

// else
// console.log "No grade given"

// ajaxUrl = 'send_prompts&saveGptJson='+prompt_id
// if template_id > 0
//   ajaxUrl = 'template_prompt&saveGptJson=' + template_id
// $.ajax "/api_gpt?class=" + ajaxUrl,
//   type: "POST"
//   data: {
//     gpt: data
//   },
//   error: (jqXHR, textStatus, errorThrown) ->
//     $('body').append "AJAX Error: #{textStatus}"
//   success: (data, textStatus, jqXHR) ->
//     return
// processGptData(data, prompt_id, template_id)
eye = function(value, aId, element) {
  var openEyes, parent;
  openEyes = document.querySelectorAll('.eyedown');
  if (openEyes.length) {
    openEyes.forEach(function(p, index) {
      return p.classList.remove('eyedown');
    });
  }
  parent = element.parentNode.parentNode;
  if (value === 1) {
    parent.classList.add('eyedown');
  } else if (value === -1) {
    parent.classList.remove('eyedown');
  }
  return $.ajax({
    type: "POST",
    url: "/api/setEye",
    data: {
      roomId: roomId,
      showEye: value,
      aId: aId
    },
    success: function(response) {},
    // console.log "Request successful"
    // console.log response
    // jsonResponse = JSON.parse(response)
    // handleEq(jsonResponse)
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

alertBtnAdmin = function(answerId, sender) {
  var alertHtml, parent;
  parent = $(sender).closest('article');
  if (parent.find('.alertForm').length) {
    parent.find('.alertForm').remove();
    parent.removeClass('alertFormOpen');
    return;
  }
  parent.addClass('alertFormOpen');
  alertHtml = `<form class='formjs alertForm' action='/api/answerAlertAdmin'>\n<h4>Möchten Sie diese Antwort an das Moderationsteam melden?</h4>\n<label for='alert_beleidigend'>\n  <input type='checkbox' id='alert_beleidigend' name='beleidigend' required checked>\n  Dieser Inhalt ist beleidigend.\n</label>\n<label for='alert_user_text'>Meldegrund</label>\n<input type='hidden' name='answerId' value='${answerId}'>\n<input type='text' id='alert_user_text' name='alert_user_text'>\n<button class='sendAlert btn'>Senden</button>\n</form>`;
  return parent.append(alertHtml);
};

setRoomPhaseBtn = function(phase) {
  return $.ajax({
    type: "POST",
    url: "/api/setRoomPhase",
    data: {
      roomId: roomId,
      phase: phase
    },
    success: function(response) {
      var questionId;
      // console.log "Request successful"
      // console.log response
      // jsonResponse = JSON.parse(response)
      // handleEq(jsonResponse)
      if (phase === "z") {
        questionId = -1;
        forceTnList();
        return forceFeedbackList();
      }
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

tnList = [];

// Function to parse the usernames list and update the HTML
parseTnList = function() {
  var i, j, len, len1, newTnHTML, tableHtml, tn;
  $("mark#tn").empty();
  if (questionId === -1) {
    tableHtml = "<table class='echoScore'><thead><tr><th>Name</th><th>EchoScore Punkte</th></tr></thead><tbody>";
    for (i = 0, len = tnList.length; i < len; i++) {
      tn = tnList[i];
      tableHtml += `<tr><td>${tn.tnName}</td><td>${tn.score}</td></tr>`;
    }
    tableHtml += "</tbody></table>";
    return $("mark#tn").append(tableHtml);
  } else {
    for (j = 0, len1 = tnList.length; j < len1; j++) {
      tn = tnList[j];
      //tnClass = if tn.tnReseted then "tnReseted" else ""
      newTnHTML = `<div id='tn-${tn.tnId}'>${tn.tnName}</div>`;
      // newtnHTML = "<div id='tn-#{tn.session_id}' class='#{tnClass}'>#{tn.tnname}</div>"
      $("mark#tn").append(newTnHTML);
    }
    return $("mark#tn").prepend("<i>" + tnList.length + " TNs online</i>");
  }
};

grade = function(value, aId, element) {
  var parent;
  parent = element.parentNode.parentNode;
  if (value === 4) {
    if (parent.classList.contains('like')) {
      value = 0;
    }
    parent.classList.toggle('like');
    parent.classList.remove('openend');
    parent.classList.remove('dislike');
  } else if (value === 5) {
    if (parent.classList.contains('openend')) {
      value = 0;
    }
    parent.classList.remove('like');
    parent.classList.toggle('openend');
    parent.classList.remove('dislike');
  } else if (value === 6) {
    if (parent.classList.contains('dislike')) {
      value = 0;
    }
    parent.classList.remove('like');
    parent.classList.remove('openend');
    parent.classList.toggle('dislike');
  }
  $.ajax({
    type: "POST",
    url: "/api/setAnswerGrade",
    data: {
      aId: aId,
      grade: value
    },
    success: function(response) {},
    // console.log "Request successful"
    // console.log response
    // todo
    // jsonResponse = JSON.parse(response)
    // handleEq(jsonResponse)
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

forceTnList = function() {
  return $.ajax({
    type: "POST",
    url: "/api/forceTnList",
    data: {
      roomId: roomId
    },
    success: function(response) {
      var jsonResponse;
      // console.log "Request successful"
      // console.log response
      jsonResponse = JSON.parse(response);
      // handleEq(jsonResponse)
      console.log(jsonResponse);
      tnList = jsonResponse;
      return parseTnList();
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

forceFeedbackList = function() {
  return $.ajax({
    type: "POST",
    url: "/api/forceFeedbackList", // Updated URL to match the new function name
    data: {
      roomId: roomId
    },
    success: function(response) {
      var jsonResponse;
      // console.log "Request successful"
      // console.log response
      jsonResponse = JSON.parse(response);
      // handleEq(jsonResponse)
      return $("#feedbackList").html(jsonResponse.feedbackList);
    },
    error: function(xhr, status, error) {
      console.log("Request failed");
      return console.log(error);
    }
  });
};

$(function() {
  return $("#eqlogo").on("click", function() {
    var newPhase, openEyes;
    openEyes = document.querySelectorAll('.eyedown');
    if (openEyes.length) {
      openEyes.forEach(function(p, index) {
        return p.classList.remove('eyedown');
      });
    }
    if ($("#eqlogo").hasClass("quiz")) {
      newPhase = "e";
    } else {
      newPhase = "q";
    }
    return $.ajax({
      type: "POST",
      url: "/api/setRoomPhase",
      data: {
        roomId: roomId,
        phase: newPhase
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
  });
});


//# sourceMappingURL=eq-admin-js.js.map
//# sourceURL=coffeescript