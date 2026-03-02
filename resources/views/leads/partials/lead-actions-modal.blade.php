{{-- Lead Actions Modal --}}
<div class="modal fade" id="userLeadActions" data-source="" style="display: none">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-0">
            <div class="modal-header p-2 p-lg-3 align-items-center">
                <h5 class="modal-title">Set action for: {{ $lead->name }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route("leads.actions", $lead->id) }}" method="POST">
                @csrf
                <input type="hidden" name="contact_name" />
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}" />

                <div class="modal-body p-2 p-lg-3">
                    <div class="form-group mb-3">
                        <strong>Action:</strong>
                        <select class="form-control multiple" name="action">
                            <option value="E-mail">E-mail</option>
                            <option value="SMS">SMS</option>
                            <option value="Phone">Phone</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <strong>Initiated by Contact:</strong>
                        <select
                            class="form-control multiple ana"
                            name="contact_id"
                            onchange="handleContactChange(this)"
                            required
                        >
                            <option value="">Select Contact</option>
                            @foreach ($contactsFullNames as $key => $contactName)
                                <option
                                    value="{{ $key }}"
                                    {{ app("request")->input("contact_id") == $key ? "selected" : "" }}
                                >
                                    {{ $contactName }}
                                </option>
                            @endforeach
                        </select>
                        <div id="countyOther" class="mt-2 otherInput" style="display: none; text-transform: lowercase">
                            <input
                                placeholder="Other Contact"
                                class="form-control capitalize"
                                name="county-other"
                                type="text"
                            />
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <select
                            class="form-control multiple"
                            name="campaign_id"
                            {{ empty($campaigns) || $campaigns->isEmpty() ? "disabled" : "" }}
                        >
                            @foreach ($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <input type="date" name="contact_date" class="form-control" required />
                    </div>
                </div>

                <div class="modal-footer justify-content-between p-2 p-lg-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="confirm" class="btn btn-info">Add Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push("scripts")
    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            initContactName();
        });

        // Initialize contact name from selected option
        function initContactName() {
            const contactSelect = document.querySelector('select[name="contact_id"]');
            const contactNameInput = document.querySelector('input[name="contact_name"]');

            if (contactSelect && contactNameInput) {
                const selectedOption = contactSelect.options[contactSelect.selectedIndex];
                if (selectedOption && selectedOption.value !== '') {
                    contactNameInput.value = selectedOption.text.trim();
                }
            }
        }

        // Handle contact dropdown change
        function handleContactChange(elem) {
            updateContactName(elem);
            toggleOtherInput(elem);
        }

        // Update hidden contact name field
        function updateContactName(elem) {
            const form = elem.closest('form');
            const contactNameInput = form.querySelector('input[name="contact_name"]');

            if (elem.value !== 'other' && elem.value !== '') {
                contactNameInput.value = elem.options[elem.selectedIndex].text.trim();
            } else if (elem.value === '') {
                contactNameInput.value = '';
            }
        }

        // Toggle other input visibility
        function toggleOtherInput(elem) {
            const inputContainer = elem.closest('.form-group').querySelector('.otherInput');
            const otherInput = inputContainer ? inputContainer.querySelector('input') : null;

            if (elem.value === 'other') {
                if (inputContainer) inputContainer.style.display = 'block';
            } else {
                if (inputContainer) {
                    inputContainer.style.display = 'none';
                    if (otherInput) otherInput.value = '';
                }
            }

            // Listen for input changes
            if (otherInput) {
                otherInput.onkeyup = function () {
                    const form = elem.closest('form');
                    const contactNameInput = form.querySelector('input[name="contact_name"]');
                    contactNameInput.value = otherInput.value || elem.value;
                };
            }
        }
    </script>
@endpush
