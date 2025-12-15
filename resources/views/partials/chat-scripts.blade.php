<script async>
let prevChatContactIds = [];
let clickCount = 0;



// Function to remove excess chat persons
function removeExcessChatPersons() {
    const maxDivs = 3;
    if ($("#chat-wrapper .chat-person").length === maxDivs) {
        // let removableContactId = $("#chat-wrapper .chat-person:last-child").attr('id').replace("chat_person_", "");
        prevChatContactIds.shift();

        $("#chat-wrapper .chat-person:last-child").remove();

        // const indexToRemove = prevChatContactIds.indexOf(removableContactId);
        // console.log(removableContactId,prevChatContactIds,indexToRemove);

        // if (indexToRemove !== -1) {
        //     prevChatContactIds.splice(indexToRemove, 1);
        // }
        // console.log(removableContactId,prevChatContactIds,indexToRemove);
    }
}


// Function to append a new chat person
function appendNewChatPerson(chatContactId, chatContactName, chatContactStatus, is_newsletter_contact) {
    const borderClass = getBorderClass();
    // console.log(chatContactId,is_newsletter_contact);

    // Fetch chat content from Laravel using Ajax
    fetchChatContent(chatContactId,is_newsletter_contact, function(data) {

        console.log(data);

        // check the checkMaxExecTime - START
        checkMaxExecTime(chatContactId);
        // check the checkMaxExecTime - STOP

        // Append the new chat person with dynamic content
        // data = JSON.parse(data);
        let html = '';
        let prevDate = null;

        data.response.forEach(message => {
            const msgDate = new Date(message.created_at);
            const currentDate = new Date();

            const differenceInDays = Math.floor((currentDate - msgDate) / (1000 * 60 * 60 * 24));
            const formattedDateTime = differenceInDays <= 6 ?
                msgDate.toLocaleDateString('en-US', {
                    weekday: 'long'
                }) :
                msgDate.toLocaleDateString();
            let timeString = new Date(message.created_at).toLocaleTimeString();
            let timeWithoutSecond = new Date(message.created_at).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // let formattedDateTime = msgDate + ' ' + timeString;

            if (formattedDateTime !== prevDate) {
                html += `<div class="message-date"><span>${formattedDateTime}</span></div>`;
                prevDate = formattedDateTime;
            }

            if (message.chat_type === 'outbound') {
                html +=
                    `<p class="my-txt mb-2 p-2">`;
                if(data.is_admin){
                    let agent_name = message.name ? message.name : 'System';

                    html += `<span class="agent-name d-block mb-1 pb-1 text-right border-bottom font-weight-bold"> ${agent_name}</span>`;
                }

                html += `<span class="d-block">${message.content}</span></p> <p class="snd-msg">${timeWithoutSecond}</p>`;
            } else if (message.chat_type === 'inbound' && message.chat_sms_sent_status === 5) {
                html += `<p class="other-txt mb-2 startstopmessage">${message.content}</p>`;
            } else {
                html +=
                    `<p class="other-txt mb-2">${message.content}</p> <p class="rcv-msg">${timeWithoutSecond}</p>`;
            }
        });


        $("#chat-wrapper").prepend(`
				<div class="position-relative chat-person ml-3 ${borderClass} border rounded" id="chat_person_${chatContactId}">
                    
					<h4 class="bg-${borderClass.replace('border-', '')} mb-0 px-2 py-3 d-flex align-items-center justify-content-between">${chatContactName}
                    <div class="d-flex align-items-center">
                    <i class="fas mr-2 fa-chevron-down minimise_chatbox" id="${chatContactId}"></i>
                    <div data-id="${chatContactId}" class="close_chatbox bg-${borderClass.replace('border-', '')} d-flex align-items-center justify-content-center cross-chat">
                        <i class="fas fa-times"></i>
                    </div>
                    
                    </div>
                    </h4>
					<div class="off-div" id="off-div-${chatContactId}">
						<div class="chat-box p-2" id="chat_message_${chatContactId}">
							
					${html}

						</div>
						<div class="position-relative chat-footer" id="chat_footer_${chatContactId}">
							<textarea type="text" class="text-input" placeholder="Write your text here"></textarea>

                            <select id="templateSelect" class="chat-template" style="left: 5px;">
                                <option>-- Templates --</option>
                                <option>Saved Templates</option>
                            </select>
							<button class="chat-send" id="chat_send_${chatContactId}" data-is_newsletter_contact="${is_newsletter_contact}"> 
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Uploaded to svgrepo.com" width="20px" height="20px" viewBox="0 0 32 32" xml:space="preserve">
									<path class="stone_een" d="M10.774,23.619l-1.625,5.691C9.06,29.164,9,28.992,9,28.794v-5.57l13.09-12.793L10.774,23.619z   M10.017,29.786c0.243-0.002,0.489-0.084,0.69-0.285l3.638-3.639l-2.772-1.386L10.017,29.786z M28.835,2.009L3.802,14.326  c-2.226,1.095-2.236,4.266-0.017,5.375l4.89,2.445L27.464,3.79c0.204-0.199,0.516-0.234,0.759-0.086  c0.326,0.2,0.396,0.644,0.147,0.935l-16.3,18.976l8.84,4.4c1.746,0.873,3.848-0.128,4.27-2.034l5.071-22.858  C30.435,2.304,29.588,1.639,28.835,2.009z"/>
								</svg>
							</button>
						</div>
					</div>
				</div>
			`);

        if (chatContactStatus) {
            $('#chat_footer_' + chatContactId).hide();
        }

        clickCount++;
        prevChatContactIds.push(chatContactId); // Add the current chatContactId to the array
        // console.log(prevChatContactIds);
    });

}

$(document).off("click").on("click", '.chat_initialise',handleChatInit);
// Function to handle chat initialization
function handleChatInit(e) {
    e.preventDefault();
    e.stopPropagation();
    const maxDivs = 3;
    // if ($("#chat-wrapper .chat-person").length >= maxDivs) {

    // }
    // console.log("on next");
    const contact_id = $(this).data("contact_id");
    const newsletter_id = $(this).data("newsletter_id");
    const chatContactId = parseInt(contact_id ? contact_id : newsletter_id);
    const is_newsletter_contact = contact_id ? "no" : "yes";
    const chatContactName = $(this).data("name");
    var chat_contact_status = $(this).data("chat_contact_status");
    const chatContactStatus = chat_contact_status == null ? 0 : chat_contact_status;
    
    // Check if the clicked chatContactId is not in the array of previous ones

    // console.log("prevChatContactIds= "+prevChatContactIds);
    // console.log("chatContactId= "+chatContactId);

    if (!prevChatContactIds.includes(chatContactId)) {

        // console.log("in if="+chatContactId);
        removeExcessChatPersons();
        appendNewChatPerson(chatContactId, chatContactName, chatContactStatus, is_newsletter_contact);
    }
};


function getBorderClass() {
    const borderClasses = ['border-danger', 'border-info', 'border-dark'];
    return borderClasses[clickCount % 3];
}


function fetchChatContent(chatContactId,is_newsletter_contact, successCallback) {
    $.ajax({
        url: `/chat/${chatContactId}/${is_newsletter_contact}`, // Replace with your Laravel route
        type: 'GET',
        success: successCallback,
        error: function(error) {
            console.error('Error fetching chat content:', error);
        }
    });
}

function checkMaxExecTime(contactId) {
    $.ajax({
        url: `/check_max_execution_time/${contactId}`,
        method: 'GET',
        success: function(response) {
            if (response.status == '200' && response.success == true && response.response > 0) {
                // console.log($(`#chat_send_${contactId}`));
                $(`#chat_send_${contactId}`).attr('disabled', true);
            } else {
                console.log($(`#chat_send_ELSEEEEEEEEEEEE`));
                $(`#chat_send_${contactId}`).attr('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response:', xhr.responseText);
        }
    });
}

$(document).on('click', '#chat-wrapper .fas.fa-chevron-down', function(e) {
    e.preventDefault();
    let offDivs = $(this).closest('.chat-person').find('.off-div');
    $(this).css("transform-origin", "center");
    if ($(this).hasClass('rotate-180')) {
        $(this).css("transform", "rotate(0deg)");
        $(this).removeClass('rotate-180');
    } else {
        $(this).css("transform", "rotate(180deg)");
        $(this).addClass('rotate-180');
    }
    offDivs.toggle('slow');
});

$(document).on('click', '#chat-wrapper .close_chatbox', function(e) {
    e.preventDefault();
    let closeDiv = $(this).closest('.chat-person');
    let removableContactId = parseInt($(this).attr('data-id'));

    // console.log(prevChatContactIds,removableContactId);

    prevChatContactIds = prevChatContactIds.filter(function(id) {
        return id !== removableContactId;
    });
    closeDiv.remove();
    // console.log(prevChatContactIds,removableContactId);

    // let indexToRemove = prevChatContactIds.indexOf(removableContactId);
    // closeDiv.remove();
    
    // if (indexToRemove !== -1) {
    //     prevChatContactIds.splice(indexToRemove, 1);
    //     console.log(prevChatContactIds);
    // }
});

$("#chat-wrapper").on("click", ".chat-send", function(e) {
    e.preventDefault();
    e.stopPropagation();

    let timeString = new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    let chatContent = $(this).siblings(".text-input").val();
    const chatContactId = $(this).attr("id").replace("chat_send_", "");
    const isNewsletterContact = $(this).attr('data-is_newsletter_contact');
    // console.log(isNewsletterContact);

    let dataMsgs = document.getElementById(`chat_message_${chatContactId}`);
    let viewContent = chatContent;
    if (dataMsgs.children.length === 0) {
        viewContent = chatContent + `</br> Please text "STOP" to stop the conversation.`;
    }
    if (chatContent.trim() !== "") {

        // function to save data in mesage and append data in msg
        // console.log(isNewsletterContact); return false;
        saveMessageInChat(chatContent, chatContactId, viewContent, timeString, isNewsletterContact);
       
        // Clear the textarea after posting the chat
        $(this).siblings(".text-input").val("");
    }
});

function saveMessageInChat(chatContent, chatContactId, viewContent, timeString, isNewsletterContact) {
    // console.log(isNewsletterContact+"  2"); return false;
    $.ajax({
        url: '/chat',
        method: "POST",
        data: {
            content: chatContent,
            chatContactId: chatContactId,
            isNewsletter: isNewsletterContact

        },
        success: function(response) {
            let appendhtml = 
                `<p class="my-txt mb-2 p-2">`;
            if(response.is_admin){
                let agent_name = response.logged_in_user_name ? response.logged_in_user_name : 'System';

                appendhtml += `<span class="agent-name d-block mb-1 pb-1 text-right border-bottom font-weight-bold"> ${agent_name}</span>`;
            }

            appendhtml += `<span class="d-block">${viewContent}</span></p> <p class="snd-msg">${timeString}</p>`;

            $(`#chat_message_${chatContactId}`).append(appendhtml);
            
            // $(`#chat_message_${chatContactId}`).append(
            //     `<p class="my-txt mb-2">${viewContent}</p>
			// 		<p class="snd-msg">${timeString}</p>`);
            $(`#chat_contact_${chatContactId} .text-input`).val("");
        },
        error: function(xhr, status, error) {
            let jsonResponse = JSON.parse(xhr.responseText);
            toastr.error(jsonResponse.response);
        }
    });
}


</script>indexOf