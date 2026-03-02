const pagination_number = parseInt(getFromSessionStorage('pagination_number')) || 1;
const lead_id = getFromSessionStorage('lead_id');
const contact_id = getFromSessionStorage('contact_id');
const backToUrl = getFromSessionStorage('backpage_url');
const pageType = getFromSessionStorage('page_type');

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
return false;
		window.location.href = backPageToUrl;
	}
}

function setBackUrl(params) {
	if (typeof params === 'object') {
		Object.entries(params).forEach(([key, value]) => {
			sessionStorage.setItem(key, value.toString());
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
	const handleCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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

const currentUrl = window.location.href;

if (currentUrl.includes('leads/edit/')) {
	document.addEventListener("DOMContentLoaded", function () {
		const params = new URLSearchParams(location.search);
		const contactId = params.get('contact_id');
		const sessionStoragecontact_id = getFromSessionStorage('contact_id');

		let final_contact_id;

		if(contactId){
			final_contact_id = contactId;
		}
		else{
			final_contact_id = sessionStoragecontact_id;
		}

		if (final_contact_id) {
			const contactEditClassElement = document.getElementById('contacteditclass' + final_contact_id);
			const customContactElement = document.getElementById('custom_contact' + final_contact_id);
			const collapseElement = document.getElementsByClassName('collapse' + final_contact_id);
			if(contactEditClassElement){
				contactEditClassElement.classList.add('call_initiated_contact');
			}
			if(customContactElement && collapseElement){
				customContactElement.click();
				setTimeout(function () {
					const offsetTop = collapseElement.offsetTop - 20;
					window.scrollTo({
						top: offsetTop,
						behavior: 'smooth'
					});
				}, 300);
			}
		}
	});
}

if ((pageType == 'dialing_show' && currentUrl.includes('dialings/show')) || (pageType == 'dialing_owned_leads' && currentUrl.includes('dialings/ownedleads'))) {
	pagination_number = (pagination_number > 0) ? pagination_number : 1;
}

function resetBackClickedSessionData() {
	let parameters = {
		lead_id: '',
		contact_id: '',
		backpage_url: '',
		lead_url: '',
		page_type: '',
		pagination_number: ''
	};
	setBackUrl(parameters);
}

let pusher = new Pusher('be87c7821bc394caf96c', {
	cluster: 'ap2',
	encrypted: true
});

// Subscribe to the channel
let channel = pusher.subscribe('lead-clicked-channel');

// Bind to the event
channel.bind('my-event', function (data) {
	let dataInDetail = JSON.parse(data.message);
	addNotification(dataInDetail);
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


		let secondLastIndex = notificationList.children.length - 2;
		let lastChild = notificationList.children[notificationList.children.length - 1];

		notificationList.removeChild(notificationList.children[secondLastIndex]);
		if (lastChild.outerHTML !== '<li class="footer" id="seeAllmsg"><a href="/notification">See All Messages</a></li>') {
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

		if (currentPageURL === desiredURL) {
			if (dataInDetail && dataInDetail.message && contact_id) {
				if ($('#chat-wrapper').length && $(`#chat_person_${contact_id}`).length) {
					if (dataInDetail.message == 'stop') {
						$(`#chat_message_${contact_id}`).append(`<p class="other-txt mb-2 startstopmessage">${dataInDetail.manipulated_message_content}</p>`);
						$(`#chat_footer_${contact_id}`).hide();
					}
					if (dataInDetail.message == 'start') {
						$(`#chat_message_${contact_id}`).append(`<p class="other-txt mb-2 startstopmessage">${dataInDetail.manipulated_message_content}</p>`);
						$(`#chat_footer_${contact_id}`).show();
					}
				} else {

				}
			} else {

			}
		} else {

		}
	} catch (error) {
		console.error("An error occurred:", error);
	}
}

// adding data in chat box - END
// get all data on page refresh also
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!toggleBtn || !sidebar || !mainContent) {
        console.error('Required elements not found');
        return;
    }

    toggleBtn.addEventListener('click', function (e) {
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('shifted');
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
 // Log the response data

						if (Array.isArray(data)) {
							let suggestions = '';
							data.forEach(user => {
								suggestions += `<a href="/impersonate/${user.id}" class="list-group-item list-group-item-action p-2 small">
                                     <p class="mb-0 text-primary user-name font-weight-bold">${user.name}</p><italic class="text-break"> (${user.email}) </italic>
                                    ${user.role == 'Super Admin' ? `<p class="d-block text-success mb-0">${user.role}</p>` : `<p class="d-block text-info mb-0">${user.role}</p>`}
                                </a>`;
							});

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

    document.querySelectorAll('.dropdown-menu').forEach(menu => {
	    menu.addEventListener('click', function (e) {
	        if (!e.target.closest('[data-bs-toggle="modal"]')) {
	            e.stopPropagation();
	        }
	    });
	});

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





