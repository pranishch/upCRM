<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Callback System</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
        }
        
        .body{
            font-family:'Arial', sans-serif;
        }
        .content-section{
            font-family:'Arial', sans-serif;
        }
        .user-info{
            font-family:'Arial', sans-serif;
        }
        .callbacks-table-wrapper {
            overflow-x: hidden;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #F9FAFB;
        }
        .top-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .top-controls button {
            background: orange;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .top-controls button:hover {
            background: #2563eb;
        }
        .top-controls button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .top-controls input, .top-controls select {
            padding: 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
        }
        .top-controls select {
            width: 160px;
        }
        .top-controls input {
            width: 220px;
        }
        #pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.6rem;
            font-weight: 600;
            user-select: none;
        }
        h2 {
            color: #000;
        }
        #pagination button,
        #pagination span.page-num {
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 0.4rem 0.8rem;
            cursor: pointer;
            min-width: 32px;
            text-align: center;
            color: #333;
            transition: all 0.2s ease;
        }
        #pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #pagination span.page-num.active {
            background: navy;
            color: white;
            border-color: navy;
            cursor: default;
        }
        #pagination button:not(:disabled):hover,
        #pagination span.page-num:not(.active):hover {
            background-color: lightblue;
            color: #fff;
            border-color: lightblue;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1055;
        }
        .toast {
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .is-invalid {
            border-color: red;
            background-color: #fff5f5;
            animation: shake 0.3s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%, 75% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
        }
        .new-entry-row .form-control,
        .new-entry-row textarea {
            background-color: #f7fafc;
        }
        .btn-logout {
            background: orange;
            border: none;
            padding: 0.5rem 1.2rem;
            color: white;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-logout:hover {
            background: orange;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .main-content {
            margin-top: 53px;
            padding: 20px;
            width: 100%;
            background-color: #eff4f9;
        }
        .header {
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            margin: 0;
            padding: 0.5rem;
            background: linear-gradient(135deg, orange, #34495e);
            border-radius: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            box-sizing: border-box;
        }
        .user-info {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1.5rem;
            width: 100%;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
        }
        .user-info h2 {
            flex: 0 0 auto;
            margin:0;
        }
        .user-info span {
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: capitalize;
            flex: 0 0 auto;
        }
        .user-info form {
            flex: 0 0 auto;
        }
        section {
            background: #f8f7f5;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(30, 144, 255, 0.15);
            padding: 2rem;
            margin-bottom: 3rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            table-layout: auto;
            font-family:'Arial', sans-serif;
        }
        table thead tr {
            background-color: #ffa500;
            color: #000;
            font-weight: 700;
        }
        table th, table td {
            padding: 0.5rem 0.8rem;
            border-bottom: 1px solid lightgray;
            vertical-align: middle;
            text-align: left;
            min-width: 0;
            max-width: auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #000;
        }
        table th:nth-child(1), table td:nth-child(1) { width: 5%; } /* S.N */
        table th:nth-child(2), table td:nth-child(2) { width: 13%; } /* Customer Name */
        table th:nth-child(3), table td:nth-child(3) { width: 12%; } /* Phone Number */
        table th:nth-child(4), table td:nth-child(4) { width: 14%; } /* Email */
        table th:nth-child(5), table td:nth-child(5) { width: 11%; } /* Address */
        table th:nth-child(6), table td:nth-child(6) { width: 15%; } /* Website */
        table th:nth-child(7), table td:nth-child(7) { width: 12%; } /* Remarks */
        table th:nth-child(8), table td:nth-child(8) { width: 16%; } /* Notes */
        table th:nth-child(9), table td:nth-child(9) { width: 9%; } /* Created By */
        table th:nth-child(10), table td:nth-child(10) { width: 8%; } /* Actions */
        table tbody tr:nth-child(even) {
            background-color: whitesmoke;
        }
        table tbody tr:hover {
            background-color: lightblue;
        }
        table input, table textarea, table select {
            width: 100%;
            box-sizing: border-box;
            font-size: 0.9rem;
        }
        .action-icon {
            cursor: pointer;
            margin: 0 5px;
            color: #eda935;
        }
        .action-icon:hover {
            color: #222;
        }
        .edit-mode td, .new-entry-row td {
            padding: 0;
        }
        .edit-mode .editable-input, .new-entry-row .editable {
            border: 1px solid #ccc;
            padding: 0.25rem;
            margin: 0;
        }
        .action-save-btn {
            background: green;
            color: white;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .action-save-btn:hover {
            background: darkgreen;
        }
        @media (max-width: 768px) {
            table {
                display: table;
                width: 100%;
            }
            table th, table td {
                padding: 0.4rem 0.6rem;
                min-width: 60px;
                max-width: 100px;
            }
            .top-controls input {
                width: 100%;
                max-width: 200px;
            }
            .top-controls select {
                width: 100%;
                max-width: 140px;
            }
            .main-content {
                padding: 1rem;
            }
            .header {
                justify-content: flex-end;
                padding-right: 0.8rem;
            }
            .user-info {
                gap: 1rem;
                flex-wrap: wrap;
            }
            .user-info h2 {
                font-size: 1.3rem;
            }
        }
        @media (max-width:576px) {
            .main-content {
                padding: 0.6rem; 
            }
            .header {
                padding-right: 0.6rem; 
            }
            .user-info h2 {
                font-size: 1.1rem;
            }
            .user-info span {
                font-size: 0.9rem;
            }
            .btn-logout {
                padding: 0.4rem 0.9rem;
                font-size: 0.9rem;
            }
            table {
                min-width: 900px;
            }
            table th,
            table td {
                font-size: 0.7rem;
                padding: 0.25rem 0.4rem;
                min-width: 45px;
                max-width: 80px;
            }
            table th:nth-child(1), table td:nth-child(1) { width: 5%; } /* S.N */
            table th:nth-child(2), table td:nth-child(2) { width: 11%; } /* Customer Name */
            table th:nth-child(3), table td:nth-child(3) { width: 10%; } /* Phone Number */
            table th:nth-child(4), table td:nth-child(4) { width: 12%; } /* Email */
            table th:nth-child(5), table td:nth-child(5) { width: 9%; } /* Address */
            table th:nth-child(6), table td:nth-child(6) { width: 13%; } /* Website */
            table th:nth-child(7), table td:nth-child(7) { width: 10%; } /* Remarks */
            table th:nth-child(8), table td:nth-child(8) { width: 14%; } /* Notes */
            table th:nth-child(9), table td:nth-child(9) { width: 7%; } /* Created By */
            table th:nth-child(10), table td:nth-child(10) { min-width: 80px; max-width: 100px; white-space: nowrap; overflow: visible; } /* Actions */
            .callbacks-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .top-controls input {
                max-width: 160px;
            }
            .top-controls select {
                max-width: 100px;
            }
            .top-controls button,
            .action-save-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
                line-height: 1.2;
                display: inline-block;
                min-width: 50px;
            }
            .action-save-btn {
                margin-left: 5px;
            }
            .toast {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <main class="main-content">
            <header class="header">
                <div class="user-info" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; color: white; font-size: 1.5rem; font-weight: 600;">
                        Callbacks of {{ $manager->username }}
                    </h2>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: white;">{{ auth()->user()->userprofile->role ?? 'agent' }}</span>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-logout" id="logoutBtn" aria-label="Logout">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            @if (session('success') || session('error'))
                <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                    {{ session('success') ?? session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <section id="callbacks" class="content-section">
                <div class="top-controls">
                    <div class="search-bar">
                        <select id="searchField" class="form-control">
                            <option value="all" {{ request('search_field', 'all') == 'all' ? 'selected' : '' }}>All Fields</option>
                            <option value="customer_name" {{ request('search_field') == 'customer_name' ? 'selected' : '' }}>Customer Name</option>
                            <option value="phone_number" {{ request('search_field') == 'phone_number' ? 'selected' : '' }}>Phone Number</option>
                            <option value="email" {{ request('search_field') == 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                        <input type="text" id="searchInput" class="form-control ms-2" placeholder="Search..." value="{{ request('q') }}">
                    </div>
                    @if (auth()->id() == $manager->id)
                        <button type="button" id="addNewRow" aria-label="Add New Row">Add New Row</button>
                    @endif
                </div>
                <div class="callbacks-table-wrapper">
                    <form id="callbackForm" method="POST" action="{{ route('callbacks.save') }}">
                        @csrf
                        <input type="hidden" name="target_user_id" value="{{ $manager->id }}">
                        <table id="callbacksTable">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Customer Name</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Website</th>
                                    <th>Remarks</th>
                                    <th>Notes</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @include('manager_dashboard_callbacks', ['callbacks' => $callbacks, 'user_role' => $user_role, 'can_edit' => $can_edit, 'manager' => $manager])
                            </tbody>
                        </table>
                    </form>
                </div>
                <div id="pagination">
                    @include('manager_dashboard_pagination', ['page_obj' => $page_obj, 'search_query' => request('q'), 'search_field' => request('search_field', 'all')])
                </div>
            </section>
        </main>
    </div>
    <div class="toast-container">
        <div id="saveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white ms-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function showToast(message, type) {
                const toastEl = document.getElementById('saveToast');
                if (!toastEl) return;
                const toastBody = toastEl.querySelector('.toast-body');
                toastBody.textContent = message;
                toastEl.classList.remove('bg-success', 'bg-danger');
                toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
                const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                toast.show();
            }

            function updateAddRowButtonState() {
                const addRowButton = document.getElementById('addNewRow');
                if (!addRowButton) return;
                const unsavedRows = document.querySelectorAll('.edit-mode, [data-new-row="true"]');
                addRowButton.disabled = unsavedRows.length > 0;
            }

            function createNewRowTemplate() {
                const template = document.createElement('tr');
                template.className = 'new-entry-row';
                template.setAttribute('data-new-row', 'true');
                template.innerHTML = `
                    <td>New</td>
                    <td>
                        <input type="hidden" name="added_at" class="added-at-input" value="">
                        <input type="text" name="customer_name" class="form-control editable name-input" 
                              placeholder="Name" maxlength="100" pattern="[A-Za-z\s]+" 
                              title="Only alphabetical characters allowed">
                    </td>
                    <td>
                        <input type="text" name="phone_number" class="form-control editable phone-input" 
                              placeholder="Phone" maxlength="20" pattern="[\+\-\(\),./#0-9\s]+" 
                              title="Only numbers, +, -, (), comma, period, /, #, and spaces allowed">
                    </td>
                    <td>
                        <input type="email" name="email" class="form-control editable email-input" 
                              placeholder="Email" maxlength="100" 
                              pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" 
                              title="Enter a valid email address (e.g., example@domain.com)">
                    </td>
                    <td>
                        <textarea name="address" class="form-control editable address-input" 
                                  rows="1" placeholder="Address" maxlength="255"></textarea>
                    </td>
                    <td>
                        <input type="url" name="website" class="form-control editable website-input" 
                              placeholder="Website" maxlength="255" 
                              pattern="https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(/.*)?$" 
                              title="Enter a valid URL (e.g., http://example.com)">
                    </td>
                    <td>
                        <select name="remarks" class="form-control editable remarks-input">
                            <option value="" selected>Select</option>
                            <option value="Callback">Callback</option>
                            <option value="Pre-sale">Pre-sale</option>
                            <option value="Sample rejected">Sample rejected</option>
                            <option value="Sale">Sale</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="notes" class="form-control editable notes-input" rows="1" 
                                  placeholder="Notes" maxlength="255"></textarea>
                    </td>
                    <td>{{ auth()->user()->username }}</td>
                    <td>
                        <button type="button" class="action-save-btn" aria-label="Save New Row">Save</button>
                    </td>
                `;
                return template;
            }

            function sanitizeInput(value) {
                const div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function setAddedAtTimestamp(row) {
                const addedAtInput = row.querySelector('.added-at-input');
                if (addedAtInput && !addedAtInput.value) {
                    const now = new Date();
                    addedAtInput.value = now.toISOString().slice(0, 19).replace('T', ' ');
                }
            }

            const addNewRowButton = document.getElementById('addNewRow');
            if (addNewRowButton) {
                addNewRowButton.addEventListener('click', function() {
                    const tableBody = document.getElementById('tableBody');
                    const emptyRow = tableBody.querySelector('tr td[colspan="10"]');
                    if (emptyRow) emptyRow.parentElement.remove();
                    const newRow = createNewRowTemplate();
                    tableBody.prepend(newRow);
                    setAddedAtTimestamp(newRow);
                    updateAddRowButtonState();
                });
            }

            const searchInput = document.getElementById('searchInput');
            const searchField = document.getElementById('searchField');
            const tableBody = document.getElementById('tableBody');
            const pagination = document.getElementById('pagination');

            window.loadPage = function(page) {
                const query = searchInput.value.trim();
                const field = searchField.value;
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                if (query) {
                    url.searchParams.set('q', query);
                    url.searchParams.set('search_field', field);
                } else {
                    url.searchParams.delete('q');
                    url.searchParams.delete('search_field');
                }

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = data.callbacks_html;
                    pagination.innerHTML = data.pagination_html;
                    updateAddRowButtonState();
                })
                .catch(() => showToast('An error occurred while loading the page.', 'danger'));
            };

            function performSearch() {
                window.loadPage(1);
            }

            let debounceTimeout;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(performSearch, 300);
            });
            searchField.addEventListener('change', performSearch);

            document.querySelector('#tableBody').addEventListener('click', function(e) {
                if (e.target.classList.contains('edit-callback')) {
                    const row = e.target.closest('tr');
                    row.classList.add('edit-mode');
                    const isCreatedByManager = row.querySelector('td:nth-child(9)').textContent.trim() === '{{ $manager->username }}';
                    const isAdmin = '{{ $user_role }}' === 'admin';
                    row.querySelectorAll('.display-text').forEach(span => {
                        span.style.display = 'none';
                    });
                    row.querySelectorAll('.editable-input').forEach(input => {
                        if (isAdmin || isCreatedByManager || input.classList.contains('remarks-input') || input.classList.contains('notes-input')) {
                            input.style.display = 'block';
                        }
                    });
                    row.querySelector('.cancel-edit').style.display = 'inline';
                    row.querySelector('.action-save-btn').style.display = 'inline';
                    e.target.style.display = 'none';
                    const callbackId = row.getAttribute('data-callback-id');
                    if (callbackId) {
                        let callbackIdInput = row.querySelector('input[name="callback_id"]');
                        if (!callbackIdInput) {
                            callbackIdInput = document.createElement('input');
                            callbackIdInput.type = 'hidden';
                            callbackIdInput.name = 'callback_id';
                            row.querySelector('td:nth-child(2)').appendChild(callbackIdInput);
                        }
                        callbackIdInput.value = callbackId;
                    }
                    row.setAttribute('data-edited', 'true');
                    updateAddRowButtonState();
                }
            });

            document.querySelector('#tableBody').addEventListener('click', function(e) {
                if (e.target.classList.contains('cancel-edit')) {
                    const row = e.target.closest('tr');
                    row.classList.remove('edit-mode');
                    row.querySelectorAll('.display-text').forEach(span => span.style.display = 'inline');
                    row.querySelectorAll('.editable-input').forEach(input => input.style.display = 'none');
                    row.querySelector('.cancel-edit').style.display = 'none';
                    row.querySelector('.edit-callback').style.display = 'inline';
                    row.querySelector('.action-save-btn').style.display = 'none';
                    const callbackIdInput = row.querySelector('input[name="callback_id"]');
                    if (callbackIdInput) callbackIdInput.remove();
                    row.removeAttribute('data-edited');
                    row.querySelectorAll('.editable-input').forEach(input => {
                        const displayText = row.querySelector(`.display-text.${input.classList[1]}`)?.textContent || '';
                        input.value = displayText;
                    });
                    updateAddRowButtonState();
                }
            });

            document.querySelector('#tableBody').addEventListener('click', function(e) {
                if (e.target.classList.contains('action-save-btn')) {
                    const row = e.target.closest('tr');
                    const form = document.getElementById('callbackForm');
                    const formData = new FormData();
                    const isNewRow = row.classList.contains('new-entry-row');
                    const callbackId = row.getAttribute('data-callback-id');
                    const isCreatedByManager = row.querySelector('td:nth-child(9)').textContent.trim() === '{{ $manager->username }}';

                    formData.append('_token', form.querySelector('[name="_token"]').value);
                    formData.append('target_user_id', form.querySelector('[name="target_user_id"]').value);

                    const inputs = {
                        added_at: row.querySelector('.added-at-input'),
                        customer_name: row.querySelector(isNewRow ? '[name="customer_name"]' : '.name-input.editable-input'),
                        phone_number: row.querySelector(isNewRow ? '[name="phone_number"]' : '.phone-input.editable-input'),
                        email: row.querySelector(isNewRow ? '[name="email"]' : '.email-input.editable-input'),
                        address: row.querySelector(isNewRow ? '[name="address"]' : '.address-input.editable-input'),
                        website: row.querySelector(isNewRow ? '[name="website"]' : '.website-input.editable-input'),
                        remarks: row.querySelector(isNewRow ? '[name="remarks"]' : '.remarks-input.editable-input'),
                        notes: row.querySelector(isNewRow ? '[name="notes"]' : '.notes-input.editable-input'),
                    };

                    if (Object.values(inputs).some(input => !input)) {
                        showToast('Missing input fields.', 'danger');
                        return;
                    }

                    if (callbackId) {
                        formData.append('callback_id', callbackId);
                    }

                    Object.entries(inputs).forEach(([key, input]) => {
                        formData.append(key, sanitizeInput(input.value.trim()));
                    });

                    if (isNewRow || isCreatedByManager) {
                        let isValid = true;
                        if (!inputs.customer_name.value.trim() || !inputs.phone_number.value.trim()) {
                            [inputs.customer_name, inputs.phone_number].forEach(input => input.classList.add('is-invalid'));
                            isValid = false;
                        } else if (!/^[A-Za-z\s]+$/.test(inputs.customer_name.value.trim()) || !/^[\+\-\(\),./#0-9\s]+$/.test(inputs.phone_number.value.trim())) {
                            [inputs.customer_name, inputs.phone_number].forEach(input => input.classList.add('is-invalid'));
                            isValid = false;
                        } else {
                            [inputs.customer_name, inputs.phone_number].forEach(input => input.classList.remove('is-invalid'));
                        }
                        if (inputs.email.value.trim() && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(inputs.email.value.trim())) {
                            inputs.email.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            inputs.email.classList.remove('is-invalid');
                        }
                        if (inputs.website.value.trim() && !/^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(\/.*)?$/.test(inputs.website.value.trim())) {
                            inputs.website.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            inputs.website.classList.remove('is-invalid');
                        }
                        if (!isValid) {
                            showToast('Please correct the invalid fields.', 'danger');
                            return;
                        }
                    }

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            if (isNewRow) {
                                row.remove();
                            } else {
                                row.classList.remove('edit-mode');
                                row.querySelectorAll('.display-text').forEach(span => {
                                    const input = row.querySelector(`.editable-input.${span.classList[1]}`);
                                    if (input) {
                                        span.textContent = input.value || '';
                                    }
                                    span.style.display = 'inline';
                                });
                                row.querySelectorAll('.editable-input').forEach(input => input.style.display = 'none');
                                row.querySelector('.cancel-edit').style.display = 'none';
                                row.querySelector('.edit-callback').style.display = 'inline';
                                row.querySelector('.action-save-btn').style.display = 'none';
                                row.querySelector('input[name="callback_id"]')?.remove();
                                row.removeAttribute('data-edited');
                            }
                            updateAddRowButtonState();
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => showToast('An error occurred while saving.', 'danger'));
                }
            });

            document.querySelector('#tableBody').addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-callback')) {
                    if (!confirm('Are you sure you want to delete this callback?')) {
                        return;
                    }
                    const row = e.target.closest('tr');
                    const callbackId = row.dataset.callbackId;
                    fetch('{{ route("callbacks.delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('[name="_token"]').value
                        },
                        body: JSON.stringify({ callback_ids: [callbackId] })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            row.remove();
                            updateAddRowButtonState();
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => showToast('An error occurred while deleting.', 'danger'));
                }
            });

            document.querySelector('#tableBody').addEventListener('input', function(e) {
                if (e.target.classList.contains('editable') && e.target.closest('[data-new-row="true"]')) {
                    const row = e.target.closest('[data-new-row="true"]');
                    setAddedAtTimestamp(row);
                }
            });
        });
    </script>
</body>
</html>