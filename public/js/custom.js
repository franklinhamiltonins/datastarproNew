var pagination_number = parseInt(getFromSessionStorage('pagination_number')) || 1;
var lead_id = getFromSessionStorage('lead_id');
var contact_id = getFromSessionStorage('contact_id');
var backToUrl = getFromSessionStorage('backpage_url');
var pageType = getFromSessionStorage('page_type');

// console.log("pagination_number"+pagination_number);
// console.log("lead_id"+lead_id);
// console.log("contact_id"+contact_id);
// console.log("backToUrl"+backToUrl);
// console.log("pageType"+pageType);




function getFromSessionStorage(key) {
	return sessionStorage.getItem(key);
}

function setInSessionStorage(key, value) {
	sessionStorage.setItem(key, value);
}

function unsetSessionStorage(key) {
	sessionStorage.removeItem(key);
}


function changeBackButtonLink(event) {
	let backPageToUrl = getFromSessionStorage('backpage_url');
	if (backPageToUrl) {
		event.preventDefault();
		// console.log(backPageToUrl);return false;
		window.location.href = backPageToUrl;
	}
}

function setBackUrl(params) {
	if (typeof params === 'object') {
		Object.entries(params).forEach(([key, value]) => {
			// if (value) {
			sessionStorage.setItem(key, value.toString());
			// console.log(key + '=>+' + value);
			// }
		});
	}
}

$('#agents_leads_datatable').on('draw.dt', function (e, settings) {
	const currentPage = settings._iDisplayStart / settings._iDisplayLength + 1;
	setInSessionStorage('pagination_number', currentPage);

	const currentOrder = settings.aaSorting;
	setInSessionStorage('datatable_sort_order', JSON.stringify(currentOrder));
});

function resetAgentLeadDatatable() {

	const removalKey = ["pagination_number","datatable_sort_order"];

	removalKey.forEach((item)=> unsetSessionStorage(item));
}


function sendToProspects(params) {
	setBackUrl(params);
	window.location.href = params.lead_url;
}

function handlecallInitiation(params) {
	var handleCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$.ajax({
		type: 'POST',
		url: '/dialings/callinitiated',
		dataType: 'json',
		data: {
			_token: handleCsrfToken,
			lead_id: params.lead_id,
			contact_id: params.contact_id,
			dialing_id: params.dialing_id,
		},
		success: function (data, status, xhr) {
			setBackUrl(params);
			// console.log(params);
			if (data.is_admin) {
				toastr.error(data.message);
				return false;
			}
			if (data.left_minute > 0) {
				toastr.error(data.response);
				return false;
			}
			window.location.href = params.lead_url;
		},
		error: function (jqXHR, textStatus, errorThrown) {

			toastr.error(errorThrown);
		}
	});
}





var currentUrl = window.location.href;

// for edit leads page start
if (currentUrl.includes('leads/edit/')) {
	document.addEventListener("DOMContentLoaded", function () {
		var sessionStorageValue = getFromSessionStorage('contact_id');
		// console.log(sessionStorageValue);
		if (sessionStorageValue) {
			var contactEditClassElement = document.getElementById('contacteditclass' + sessionStorageValue);
			var customContactElement = document.getElementById('custom_contact' + sessionStorageValue);
			var collapseElement = document.getElementsByClassName('collapse' + sessionStorageValue);
			if(contactEditClassElement){
				contactEditClassElement.classList.add('call_initiated_contact');
			}
			if(customContactElement && collapseElement){
				customContactElement.click();
				setTimeout(function () {
					var offsetTop = collapseElement.offsetTop - 20;
					window.scrollTo({
						top: offsetTop,
						behavior: 'smooth'
					});
				}, 300);
			}
		}
	});
}
// for edit leads page end


//

if ((pageType == 'dialing_show' && currentUrl.includes('dialings/show')) || (pageType == 'dialing_owned_leads' && currentUrl.includes('dialings/ownedleads'))) {
	pagination_number = (pagination_number > 0) ? pagination_number : 1;

	// let parameters = {
	// 	lead_id: '',
	// 	contact_id: '',
	// 	backpage_url: '',
	// 	lead_url: '',
	// 	page_type: '',
	// 	page_type: '',
	// 	pagination_number: ''
	// };
	// console.log(parameters);
	// console.log('pagination_number');
	// setBackUrl(parameters);
}

function resetBackClickedSessionData() {
	let parameters = {
		lead_id: '',
		contact_id: '',
		backpage_url: '',
		lead_url: '',
		page_type: '',
		page_type: '',
		pagination_number: ''
	};
	// console.log(parameters);
	// console.log('pagination_number');
	setBackUrl(parameters);
}

// Pusher.logToConsole = true;


// Initialize Pusher
var pusher = new Pusher('be87c7821bc394caf96c', {
	cluster: 'ap2',
	encrypted: true
});

// Subscribe to the channel
var channel = pusher.subscribe('lead-clicked-channel');

// Bind to the event
channel.bind('my-event', function (data) {
	let dataInDetail = JSON.parse(data.message);
	// console.log(dataInDetail);
	addNotification(dataInDetail);

	// disableButtonIfMatches(notificationData);
});




// Function to handle click event
function addNotification(dataInDetail) {

	let countElement = document.getElementById('notificationCall');
	let count = parseInt(countElement.textContent);
	countElement.textContent = count + 1;

	let notificationList = document.getElementById('notificationList');
	let seeAllmsg = document.getElementById('seeAllmsg');
	let newLi = createLi(dataInDetail.full_name, dataInDetail.message, dataInDetail.lead_id, dataInDetail.contact_id);

	// adding data in chat box - START
	appendDataInChat(dataInDetail);
	// adding data in chat box - END

	notificationList.insertAdjacentHTML('afterbegin', newLi);

	if ((notificationList.children.length > 0) && (notificationList.children.length < 5)) {
		seeAllmsg.style.display = 'none';
	}

	if (notificationList.children.length > 6) {

		// console.log('notificationList.children.length -> ', notificationList.children.length);
		let secondLastIndex = notificationList.children.length - 2;
		let lastChild = notificationList.children[notificationList.children.length - 1];

		// notificationList.lastElementChild.previousElementSibling

		notificationList.removeChild(notificationList.children[secondLastIndex]);
		if (lastChild.outerHTML !== '<li class="footer" id="seeAllmsg"><a href="/notification">See All Messages</a></li>') {
			// console.log('Not present');
			notificationList.insertAdjacentHTML('beforeend', '<li class="footer" id="seeAllmsg"><a href="/notification">See All Messages</a></li>');
		}
	}

}

// adding data in chat box - START
function appendDataInChat(dataInDetail) {
	try {
		const currentPageURL = window.location.href;
		let url_lead_id = dataInDetail.lead_id;
		let desiredURL = `http://localhost:8000/leads/edit/${url_lead_id}`;
		let hostName = window.location.hostname;
		if (window.location.protocol == 'https:') {
			desiredURL = `https://${hostName}/leads/edit/${url_lead_id}`;
		}
		// console.log(desiredURL, hostName);




		if (currentPageURL === desiredURL) {
			if (dataInDetail && dataInDetail.message && contact_id) {
				if ($('#chat-wrapper').length && $(`#chat_person_${contact_id}`).length) {

					console.log(dataInDetail.message);
					if (dataInDetail.message == 'stop') {
						$(`#chat_message_${contact_id}`).append(`<p class="other-txt mb-2 startstopmessage">${dataInDetail.manipulated_message_content}</p>`);
						$(`#chat_footer_${contact_id}`).hide();
					}
					if (dataInDetail.message == 'start') {
						$(`#chat_message_${contact_id}`).append(`<p class="other-txt mb-2 startstopmessage">${dataInDetail.manipulated_message_content}</p>`);
						$(`#chat_footer_${contact_id}`).show();
					}
				} else {
					console.log("One or both elements do not exist.");
				}
			} else {
				console.log("Required data is missing or invalid.");
			}
		} else {
			console.log("You are not on the desired page URL.");
		}
	} catch (error) {
		console.error("An error occurred:", error);
	}
}

// adding data in chat box - END

// get all data on page refresh also
document.addEventListener('DOMContentLoaded', function () {
	fetch('/get-all-unread-msg')
		.then(response => response.json())
		.then(data => {

			let countElement = document.getElementById('notificationCall');
			countElement.textContent = data.response.length;

			if ((data.response.length > 0) && (data.response.length < 5)) {
				document.getElementById('seeAllmsg').style.display = 'none';
			}
			if (data.response.length > 5) {
				document.getElementById('seeAllmsg').style.display = 'block';
			}
			if (data.response.length === 0) {
				document.getElementById('seeAllmsg').textContent = "There is no notification for you.";
			}

			data.response.forEach((each) => {
				let notificationList = document.getElementById('notificationList');
				let newLi = createLi(each.c_full_name, each.smscontent, each.lead_id, each.contact_id);

				let footerLi = notificationList.querySelector('#seeAllmsg');
				footerLi.insertAdjacentHTML('beforebegin', newLi);
			});

		})
		.catch(error => {
			console.error('Error:', error);
		});
});

// dynamic li creation function
function createLi(fullName, msg, url_lead_id, contact_id) {
	let hostName = window.location.hostname;
	return `<li >
				<a href="https://${hostName}/leads/edit/${url_lead_id}?chat_contact_open=${contact_id}">
				
					<h4> ${fullName} </h4>
					<p > ${msg} </p>
				</a>
			</li>`;
}

// console.log("hi");
$(document).ready(function () {
	let debounceTimeout;

	$('#impersonate_user').on('input', function () {
		clearTimeout(debounceTimeout);
		debounceTimeout = setTimeout(() => {
			let keyword = $(this).val();

			if (keyword.length > 2) { // Start searching after 3 characters
				$.ajax({
					url: '/impersonate/search',
					method: 'post',
					data: {
						keyword: keyword
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function (data) {
						// console.log(data); // Log the response data

						if (Array.isArray(data)) {
							let suggestions = '';
							data.forEach(user => {
								suggestions += `<a href="/impersonate/${user.id}" class="list-group-item list-group-item-action p-2 small">
                                     <p class="mb-0 text-primary user-name font-weight-bold">${user.name}</p><italic class="text-break"> (${user.email}) </italic> 
                                    ${user.role == 'Super Admin' ? `<p class="d-block text-success mb-0">${user.role}</p>` : `<p class="d-block text-info mb-0">${user.role}</p>`} 
                                </a>`;
							});
							// console.log(suggestions);
							$('#suggestions').html(suggestions).show();
						} else {
							console.error("Expected an array but received:", data);
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						console.error("AJAX Error:", textStatus, errorThrown);
					}
				});
			} else {
				$('#suggestions').hide();
			}
		}, 300); // 300ms debounce
	});

	$(document).on('click', function (event) {
		if (!$(event.target).closest('#impersonate_user, #suggestions').length) {
			$('#suggestions').hide();
		}
	});

	// for navbarNotification
	$('#navbarNotification').parent().on('click', function () {
		let notificationCallVal = $('#navbarNotification').text();
		if (notificationCallVal === '0') {
			$('#seeAllmsgNavbar').text("There is no main notifications for you.");
		}
	});

	$('.has-treeview > a').on('click', function (e) {
	    const $parent = $(this).parent();
	    const $menu = $parent.find('.nav-treeview');

	    // Prevent toggling and navigation if there's a dropdown menu
	    if ($menu.length) {
	        e.preventDefault(); // Stop navigation for menu toggle
	        if ($parent.hasClass('menu-open')) {
	            $parent.removeClass('menu-open');
	            $menu.slideUp();
	        } else {
	            $('.has-treeview').removeClass('menu-open').find('.nav-treeview').slideUp();
	            $parent.addClass('menu-open');
	            $menu.slideDown();
	        }
	    }
	});


    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').hide(); // Hide all dropdowns if clicked outside
        }
    });

    // Handle click on any dropdown toggle (parent or child)
    $('.dropdown-toggle').on('click', function (e) {
        var $el = $(this).next('.dropdown-menu');
        var isVisible = $el.is(':visible');

        // console.log($el.data("id"));
        // var $parent = $(this).closest('.dropdown');

        if($el.data("id") == undefined){
        	$('.dropdown-menu').hide();
        }
        else{
        	$('.dropdown-menu').each(function () {
        		if($(this).data("id") == undefined){
        			$(this).hide();
        		}
        		else{
        			if($(this).data("id")  >= $el.data("id")){
        				$(this).hide();
        			}
        		}
        	});
        }

        // Toggle visibility of the current dropdown
        if (!isVisible) {
            $el.show();
            // $e1.addClass("show");
        }

        // Prevent event propagation to stop closing parent dropdown
        e.stopPropagation();
    });

    // Prevent closing of parent dropdown when clicking on nested dropdown
    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation(); // Prevent the parent from closing
    });

	// Close all dropdowns when clicking anywhere outside
	// $(document).on('click', function () {
	//     $('.dropdown-menu').hide();
	// });


    // $(document).on('click', function (e) {
    //     if (!$(e.target).closest('.dropdown').length) {
    //         $('.dropdown-menu').hide();
    //     }
    // });

    $("#toggleSidebar").on('click', function (e) {
    	console.log($('.sidebar').length); // should be > 0
		console.log($('.main-content').length); // should be > 0
        $('.sidebar').toggleClass('open'); // Toggle sidebar visibility
        $('.main-content').toggleClass('shifted'); // Shift content to the right
    });

    $('[data-toggle="collapse"]').on('click', function (e) {
        var target = $(this).attr('href') || $(this).data('target');
        $(target).collapse('toggle');
    });

    $('[data-toggle="modal"]').on('click', function (e) {
	    e.preventDefault(); // Prevent default link behavior
	    var target = $(this).data('target'); // Get the target modal ID

	    if (target) {
	        $(target).modal('show'); // Show the modal using Bootstrap's modal method

	        // Manually adjust the backdrop for Bootstrap 3 compatibility
	        setTimeout(function () {
	            $('.modal-backdrop').removeClass('in').addClass('show'); // Ensure correct class is added
	        }, 0); // Ensure it's executed after modal is shown
	    }
	});

	// Hide Modal
	$('[data-dismiss="modal"]').on('click', function () {
	    var target = $(this).closest('.modal'); // Find the closest modal element

	    if (target.length) {
	        target.modal('hide'); // Hide the modal using Bootstrap's modal method

	        // Manually remove the backdrop classes to avoid issues
	        // setTimeout(function () {
	        //     $('.modal-backdrop').remove(); 
	        // }, 300);
	    }
	});

    // // Close Modal on Click
    // $('[data-dismiss="modal"]').on('click', function (e) {
    //     var target = $(this).closest('.modal');
    //     if (target.length) {
    //         target.modal('hide');
    //         // console.log("yes");
    //     }
    //     else{
    //     	// target = $(this).closest('.othermodalsection');
    //     	// if (target.length) {
    //     	// 	target.removeClass('is-visible');
    //     	// 	$('body').removeClass('overflow-hidden');
    //     	// }
    //     }
    // });

    const tabSelectors = [".lowerpaneltab_leads",".upperpaneltab_leads"];

    // Loop through each tab selector
    tabSelectors.forEach((selector) => {
        // Set up click event on tabs with the class `.lowerpaneltab_leads`
        $(selector).on('click', function (e) {
            e.preventDefault();
            
            // Remove the 'active' class from all tabs
            $(selector).removeClass('active');
            
            // Add the 'active' class to the clicked tab
            $(this).addClass('active');
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Select all date inputs with the class
    const dateInputs = document.querySelectorAll('.thisYearLimitRestriction');

    // Get today's date and the current year
    const today = new Date();
    const currentYear = today.getFullYear();

    // Set the max date to Dec 31 of the current year
    const maxDate = new Date(currentYear, 11, 31); // Month is 0-based (11 = December)
    const formattedMax = maxDate.toISOString().split('T')[0]; // Format YYYY-MM-DD

    // Set min date (optional)
    const minDate = new Date(1900, 0, 1);
    const formattedMin = minDate.toISOString().split('T')[0];

    dateInputs.forEach(input => {
        // Apply min/max
        input.setAttribute('max', formattedMax);
        input.setAttribute('min', formattedMin);

        // Prevent typing manually
        input.addEventListener('keydown', function (e) {
            // Allow: Tab, Backspace, Delete, Arrow keys
            const allowedKeys = ['Tab', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'];
            if (!allowedKeys.includes(e.key)) {
                e.preventDefault();
            }
        });

        // Allow clearing the date by pressing the clear (X) icon or backspacing everything
        input.addEventListener('input', function (e) {
            // Do nothing, this allows clearing value
        });

        // (Optional) Prevent pasting text into date field
        input.addEventListener('paste', function (e) {
            e.preventDefault();
        });
    });
});





