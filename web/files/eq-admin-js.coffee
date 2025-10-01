
startAdminNchan = (wsshost) ->
  # NchanSubscriber = require("nchan")
  opt = {
    subscriber: 'websocket',
    reconnect: undefined,
    shared: true
  }


  sub = new NchanSubscriber(wsshost + "/sub_id/?id=eq!!" + roomId, opt)
  # console.log sub
  sub.on 'message', (message, message_metadata) ->
    # message is a string
    # message_metadata is a hash that may contain 'id' and 'content-type'
    # console.log message
    # console.log message_metadata
    msg = JSON.parse message

    # console.log msg
    # if msg.a == "tn"
      # return handleAdminEq(msg)
    
    return handleAdminEq(msg)
      
    

    
    return

  sub.on "connect", (evt) ->
    console.log sub
    console.log evt
    # loadText()

  sub.on "error", (evt, error_description) ->
    console.log "error"
    console.log sub
    console.log evt
    console.log error_description

  sub.start()



handleAdminEq = (eqData) ->
  # console.log eqData
  if eqData.tnId?
    tnList.push({ tnId: eqData.tnId, tnName: eqData.tnName })
    parseTnList()
  
  if eqData.forceTnList?
    forceTnList()
  if eqData.forceFeedbackList?
    forceFeedbackList()

  
  if eqData.aT?
    #  new answer
    if adminView
      # console.log eqData.a
      
      if eqData.oldAid?
        $("#answers").find(".aId-" + eqData.oldAid + " .aT").text eqData.aT
        $("#answers").find(".aId-" + eqData.oldAid).removeClass("aId-" + eqData.oldAid).addClass("aId-" + eqData.aId)
      else
        answerHtml = """
          <article class="vis gradient aId-#{eqData.aId} #{eqData.aGradeClass}" data-r0=#{eqData.r0} data-r1=#{eqData.r1} data-r2=#{eqData.r2} data-r3=#{eqData.r3} ><span class='aT'>#{eqData.aT}</span> <span class="votingBtns">
            <button class="aibtn" onclick="loadGptAnswer($('#qId-#{eqData.aQid} .questionText').text(), $('#qId-#{eqData.aQid} .contextText').val(), '#{eqData.aT}', '#{eqData.aId}')"><i class="fas fa-microchip"></i></button>
            <button class="likebtn" onclick="grade(4, #{eqData.aId}, this)"><mark>#{eqData.r1}</mark><i class="fa fa-check" aria-hidden="true"></i></button>
            <button class="openendbtn" onclick="grade(5, #{eqData.aId}, this)"><mark>#{eqData.r2}</mark><i class="fas fa-balance-scale-right" aria-hidden="true"></i></button>
            <button class="dislikebtn" onclick="grade(6, #{eqData.aId}, this)")"><mark>#{eqData.r3}</mark><i class="fa fa-times" aria-hidden="true"></i></button>
            <button class="alertbtn" onclick="alertBtnAdmin(#{eqData.aId}, this)"><mark>#{eqData.alertMark}</mark><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></button>
            <button class="eyebtn" onclick="eye(1, #{eqData.aId}, this)")"><i class="fa fa-eye" aria-hidden="true"></i></button>
            <button class="eyebtndown" onclick="eye(-1, #{eqData.aId}, this)"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>
          </span>
          </article>
        """

        # <button class="userwaiting" onclick="userSpread('#{eqData.aId}')"><mark>#{eqData.r0}</mark><i class="fas fa-user-clock"></i></button>
        $("#qId-" + eqData.aQid + " .answers").append answerHtml

        $("#qId-" + eqData.aQid + "-count").text($("#qId-" + eqData.aQid + " .answers article").length)

        updateAnswerBg($("#answers .aId-" + eqData.aId))

  if eqData.q?
    questionHtml = """
        <div class='question'>
        <div class='questionText expletus'>#{eqData.q}</div>        
        </div>
      """
    $("mark#q").html questionHtml

    handleAdminEq({ qHistory: eqData.q, qId: eqData.qId })

  if eqData.qHistory?
    # new active quesiton

    if adminView
      questionHtml = """
        <section class='questionHistory' id='qId-#{eqData.qId}'>
        <div class='questionText expletus'>#{eqData.qHistory}</div>
        <div class='questionMetaFlex'>
        <div><label>Antworten</label> <mark id='qId-#{eqData.qId}-count' class='qId-count'>0</mark></div>
        <div class='contextWrapper'><label>Fragen-Kontext</label>
        <textarea class='contextText' rows=4></textarea></div>
        </div>

        <div class='answers gradient'></div>
        </section>
      """
    
      $("mark#answers").prepend questionHtml

  if eqData.placeholderQuestion?
    $("#questionTextInput").attr("placeholder", eqData.placeholderQuestion)
    $("#questionTextInput").val("")

  if eqData.answerAlertAdmin?
    $("#answers").find(".aId-" + eqData.answerAlertAdmin).remove()
  
  if eqData.answerAlert?
    alertBtn = $("#answers").find(".aId-" + eqData.answerAlert + " .alertbtn mark")
    currentCount = parseInt(alertBtn.text()) || 0
    alertBtn.text(currentCount + 1)

  if eqData.ratingAusgegebenAids?
    for aid in eqData.ratingAusgegebenAids
      target = $("#answers .aId-" + aid)
      target.data('r0', target.data('r0') + 1)
      updateAnswerBg(target)

  if eqData.answerRating?
    handleEq(eqData)



loadGptAnswer = (frage, kontext, antwort, answer_id) ->
  
  $(".aId-#{answer_id} .aibtn").addClass("inactive")
  # MSG = '[{"role":"system","content":"You are a helpful assistant. Based on the question {q} in the user input, the following answer {a} was given. Write a very short comment in keywords for the teacher of a live session to give feedback on the answer and a {grade}: \'correct\', \'wrong\' in JSON. Be precise, answer in German!"},{"role":"user","content":"{\'q\': \'Welche SDG gibt es?\',\'a\': \'Qualität Essen\'}"},{"role":"assistant","content":"{\'comment\': \'Nicht korrekt auf SDGs bezogen.\\n\\\"Qualität Essen\\\" entspricht nicht einem SDG.\\nVielleicht war \\\"SDG 2: Null Hunger\\\" gemeint.\', \'grade\': \'wrong\'}"},{"role":"user","content":"{\'q\': \'' + frage + '\',\'a\': \'' + antwort + '\'}"}]'
  # if kontext?.length > 0

  #   kontext = kontext.replace(/'/g, "\\'").replace(/\n/g, " ")
  #   MSG = '[{"role":"system","content":"You are a helpful assistant. Based on the question {q} and context {context} in the user input, the following answer {a} was given. Write a very short comment in keywords for the teacher of a live session to give feedback on the answer and a {grade}: \'correct\', \'wrong\' in JSON. Be precise, answer in German!"},{"role":"user","content":"{\'q\': \'Welche SDG gibt es?\',\'a\': \'Qualität Essen\'}"},{"role":"assistant","content":"{\'comment\': \'Nicht korrekt auf SDGs bezogen.\\n\\\"Qualität Essen\\\" entspricht nicht einem SDG.\\nVielleicht war \\\"SDG 2: Null Hunger\\\" gemeint.\', \'grade\': \'wrong\'}"},{"role":"user","content":"{\'q\': \'' + frage + '\',\'context\': \'' + kontext + '\',\'a\': \'' + antwort + '\'}"}]'
  
  $.ajax "/api/gptAnswer",
    type: "POST"
    data: {
      roomId: roomId,
      frage: frage,
      kontext: kontext,
      antwort: antwort,
      apikey: $("#gptApiKey").val(),
      
     },
    error: (jqXHR, textStatus, errorThrown) ->
      $('body').append "AJAX Error: #{textStatus}"
    success: (data, textStatus, jqXHR) ->
      if jqXHR.status == 200
        console.log data
        # console.log JSON.parse(data)
        # msg = JSON.parse(data)
        data = JSON.parse(data)
        if (data.error)
          
          $('.aId-' + answer_id + ' .aT').append "<div class='gptAnswer'>" + data.error + "</div>"

          if (data.all?.error?.message)
            $('.aId-' + answer_id + ' .aT').append "<div class='gptAnswer'>" + data.all?.error?.message + "</div>"


          return
        # console.log data
        # console.log JSON.parse(data.response)
        response = JSON.parse(data.response)

        $('.aId-' + answer_id + ' .aT').append "<div class='gptAnswer'>" + response.feedback + "</div>"
        if (response.grade == "unclear")
          $('.aId-' + answer_id + ' .openendbtn').click()
        else if (response.grade == "correct" || response.grade == "partlycorrect")
          if $('.aId-' + answer_id).hasClass 'like'
            return
          $('.aId-' + answer_id + ' .likebtn').click()
        else if (response.grade == "wrong")
          if $('.aId-' + answer_id).hasClass 'dislike'
            return
          $('.aId-' + answer_id + ' .dislikebtn').click()
        # else
          # console.log "No grade given"


        # ajaxUrl = 'send_prompts&saveGptJson='+prompt_id
        # if template_id > 0
        #   ajaxUrl = 'template_prompt&saveGptJson=' + template_id
        # $.ajax "/api_gpt?class=" + ajaxUrl,
        #   type: "POST"
        #   data: {
        #     gpt: data
        #   },
        #   error: (jqXHR, textStatus, errorThrown) ->
        #     $('body').append "AJAX Error: #{textStatus}"
        #   success: (data, textStatus, jqXHR) ->
        #     return
        # processGptData(data, prompt_id, template_id)



eye = (value, aId, element) ->
  openEyes = document.querySelectorAll('.eyedown')
  if openEyes.length
    openEyes.forEach (p, index) ->
      p.classList.remove 'eyedown'

  parent = element.parentNode.parentNode
  if value == 1
    parent.classList.add 'eyedown'
  else if value == -1
    parent.classList.remove 'eyedown'
  
  $.ajax
    type: "POST"
    url: "/api/setEye"
    data: { roomId: roomId, showEye: value, aId: aId }
    success: (response) ->
      # console.log "Request successful"
      # console.log response
      # jsonResponse = JSON.parse(response)
      # handleEq(jsonResponse)
    error: (xhr, status, error) ->
      console.log "Request failed"
      console.log error


alertBtnAdmin = (answerId, sender) ->
  parent = $(sender).closest('article')
  if parent.find('.alertForm').length
    parent.find('.alertForm').remove()
    parent.removeClass('alertFormOpen')
    return
  parent.addClass('alertFormOpen')
  alertHtml = """
    <form class='formjs alertForm' action='/api/answerAlertAdmin'>
    <h4>Möchten Sie diese Antwort an das Moderationsteam melden?</h4>
    <label for='alert_beleidigend'>
      <input type='checkbox' id='alert_beleidigend' name='beleidigend' required checked>
      Dieser Inhalt ist beleidigend.
    </label>
    <label for='alert_user_text'>Meldegrund</label>
    <input type='hidden' name='answerId' value='#{answerId}'>
    <input type='text' id='alert_user_text' name='alert_user_text'>
    <button class='sendAlert btn'>Senden</button>
    </form>
  """
  parent.append alertHtml

setRoomPhaseBtn = (phase) ->
  $.ajax
    type: "POST"
    url: "/api/setRoomPhase"
    data: { roomId: roomId, phase: phase }
    success: (response) ->
      # console.log "Request successful"
      # console.log response
      # jsonResponse = JSON.parse(response)
      # handleEq(jsonResponse)
      if (phase == "z")
        questionId = -1
        forceTnList()
        forceFeedbackList()
    error: (xhr, status, error) ->
      console.log "Request failed"
      console.log error

tnList = []

# Function to parse the usernames list and update the HTML
parseTnList = ->
  $("mark#tn").empty()

  if questionId == -1
    tableHtml = "<table class='echoScore'><thead><tr><th>Name</th><th>EchoScore Punkte</th></tr></thead><tbody>"
    for tn in tnList
      tableHtml += "<tr><td>#{tn.tnName}</td><td>#{tn.score}</td></tr>"

    tableHtml += "</tbody></table>"
    $("mark#tn").append tableHtml
    
  else
    for tn in tnList
      #tnClass = if tn.tnReseted then "tnReseted" else ""
      newTnHTML = "<div id='tn-#{tn.tnId}'>#{tn.tnName}</div>"

      # newtnHTML = "<div id='tn-#{tn.session_id}' class='#{tnClass}'>#{tn.tnname}</div>"
      $("mark#tn").append newTnHTML
    $("mark#tn").prepend "<i>" + tnList.length + " TNs online</i>"

grade = (value, aId, element) ->
  parent = element.parentNode.parentNode
  if value == 4
    if parent.classList.contains('like')
      value = 0
    parent.classList.toggle 'like'
    parent.classList.remove 'openend'
    parent.classList.remove 'dislike'
  else if value == 5
    if parent.classList.contains('openend')
        value = 0
    parent.classList.remove 'like'
    parent.classList.toggle 'openend'
    parent.classList.remove 'dislike'
  else if value == 6
    if parent.classList.contains('dislike')
        value = 0
    parent.classList.remove 'like'
    parent.classList.remove 'openend'
    parent.classList.toggle 'dislike'
  
  $.ajax
    type: "POST"
    url: "/api/setAnswerGrade"
    data: { aId: aId, grade: value }
    success: (response) ->
      # console.log "Request successful"
      # console.log response
      # todo
      # jsonResponse = JSON.parse(response)
      # handleEq(jsonResponse)
    error: (xhr, status, error) ->
      console.log "Request failed"
      console.log error
  
  return



forceTnList = () ->
  $.ajax
    type: "POST"
    url: "/api/forceTnList"
    data: { roomId: roomId }
    success: (response) ->
      # console.log "Request successful"
      # console.log response
      jsonResponse = JSON.parse(response)
      # handleEq(jsonResponse)
      console.log jsonResponse
      tnList = jsonResponse
      parseTnList()

    error: (xhr, status, error) ->
      console.log "Request failed"
      console.log error

forceFeedbackList = () ->
  $.ajax
    type: "POST"
    url: "/api/forceFeedbackList"  # Updated URL to match the new function name
    data: { roomId: roomId }
    success: (response) ->
      # console.log "Request successful"
      # console.log response
      jsonResponse = JSON.parse(response)
      # handleEq(jsonResponse)
      $("#feedbackList").html(jsonResponse.feedbackList)

    error: (xhr, status, error) ->
      console.log "Request failed"
      console.log error


$ ->
  $("#eqlogo").on "click", ->
    openEyes = document.querySelectorAll('.eyedown')
    if openEyes.length
      openEyes.forEach (p, index) ->
        p.classList.remove 'eyedown'
    if $("#eqlogo").hasClass("quiz")
      newPhase = "e"
    else
      newPhase = "q"
    $.ajax
      type: "POST"
      url: "/api/setRoomPhase"
      data: { roomId: roomId, phase: newPhase }
      success: (response) ->
        # console.log "Request successful"
        # console.log response
        jsonResponse = JSON.parse(response)
        handleEq(jsonResponse)
      error: (xhr, status, error) ->
        console.log "Request failed"
        console.log error