<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callback System</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden; /* Prevent horizontal scroll on body */
        }
        .body{
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
        .search-bar {
            max-width: 500px;
            margin-bottom: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        .search-bar select {
            width: 160px;
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
            width: 100vw; /* Full viewport width */
            position: fixed; /* Fix the header to the top */
            top: 0; /* Align to the very top of the viewport */
            left: 0; /* Align to the left edge */
            margin: 0; /* Remove any default margins */
            padding: 0.5rem; /* Keep existing padding */
            background: linear-gradient(135deg, orange, #34495e); /* Keep existing gradient */
            border-radius: 0; /* Remove border-radius to ensure full-width appearance */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Keep existing shadow */
            z-index: 1000; /* Ensure header stays above other content */
            box-sizing: border-box; /* Ensure padding is included in width */
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
            flex: 0 0 auto; /* Keep logout button compact */
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
            table-layout: fixed;
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
            min-width: 80px;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #000;
        }
        table th:nth-child(1), table td:nth-child(1) { min-width: 50px; max-width: 60px; } /* S.No column */
        table th:nth-child(2), table td:nth-child(2) { min-width: 70px; max-width: 90px; } /* Customer Name */
        table th:nth-child(3), table td:nth-child(3) { min-width: 70px; max-width: 90px; } /* Phone Number */
        table th:nth-child(4), table td:nth-child(4) { min-width: 70px; max-width: 90px; } /* Email */
        table th:nth-child(5), table td:nth-child(5) { min-width: 70px; max-width: 90px; } /* Address */
        table th:nth-child(6), table td:nth-child(6) { min-width: 70px; max-width:90px; } /* Website */
        table th:nth-child(7), table td:nth-child(7) { min-width: 70px; max-width: 90px; } /* Remarks */
        table th:nth-child(8), table td:nth-child(8) { min-width: 70px; max-width: 90px; } /* Notes */
        table th:nth-child(9), table td:nth-child(9) { min-width: 60px; max-width: 80px; } /* Actions */
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
                padding-right: 0.5rem;
            }
            .user-info {
                justify-content: flex-end;
                margin-left: auto;
            }
        }
        @media (max-width: 576px) {
            table {
            min-width: 950px; /* Increased from 900px to accommodate new column */
            }
            table th:nth-child(1), table td:nth-child(1) { min-width: 40px; max-width: 50px; } /* S.No column */
            table th:nth-child(2), table td:nth-child(2) { min-width: 60px; max-width: 80px; } /* Customer Name */
            table th:nth-child(3), table td:nth-child(3) { min-width: 60px; max-width: 80px; } /* Phone Number */
            table th:nth-child(4), table td:nth-child(4) { min-width: 60px; max-width: 80px; } /* Email */
            table th:nth-child(5), table td:nth-child(5) { min-width: 60px; max-width: 80px; } /* Address */
            table th:nth-child(6), table td:nth-child(6) { min-width: 60px; max-width: 80px; } /* Website */
            table th:nth-child(7), table td:nth-child(7) { min-width: 60px; max-width: 80px; } /* Remarks */
            table th:nth-child(8), table td:nth-child(8) { min-width: 60px; max-width: 80px; } /* Notes */
            table th:nth-child(9), table td:nth-child(9) { min-width: 80px; /* Increased from 50px to accommodate Save button */
                max-width: 100px; /* Increased from 70px for better fit */
                white-space: nowrap; /* Prevent wrapping */
                overflow: visible;
            }
            .callbacks-table-wrapper {
                overflow-x: auto; /* Enable horizontal scrolling */
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
            }
            .top-controls input {
                max-width: 160px; /* Reduced from 200px to fit better */
            }
            .top-controls select {
                max-width: 100px; /* Reduced from 140px to fit better */
            }
            .top-controls button,
            .action-save-btn {
                padding: 0.3rem 0.6rem; /* Slightly reduced for compactness */
                font-size: 0.8rem; /* Slightly reduced for mobile */
                line-height: 1.2; /* Ensure text is centered vertically */
                display: inline-block; /* Force inline-block to respect visibility */
                min-width: 50px; /* Ensure button has enough width */
            }
            .action-save-btn {
                margin-left: 5px; /* Space between icons and button */
            }
            .toast {
                font-size: 0.75rem; /* Reduced from 0.8rem for readability */
            }
        }
        @media (max-width: 360px) {
            /* Header adjustments */
            .header {
                padding: 0.3rem 0.5rem;
            }
            
            .user-info {
                gap: 0.5rem;
                justify-content: space-between;
                flex-wrap: wrap;
                padding: 5px;
            }
            
            .user-info h2 {
                font-size: 0.85rem !important; /* Reduced from 0.9rem */
                line-height: 1.2;
                max-width: 55%; /* Slightly reduced to prevent overflow */
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .user-info span {
                font-size: 0.75rem;
            }
            
            .btn-logout {
                padding: 0.25rem 0.5rem; /* Reduced padding for compactness */
                font-size: 0.7rem; /* Slightly smaller font */
                min-width: auto;
            }
            
            /* Main content */
            .main-content {
                margin-top: 45px;
                padding: 0.5rem;
            }
            
             section {
                padding: 1rem 0.7rem;
                border-radius: 12px;
                margin-bottom: 1.2rem;
            }

            /* Controls - Stack Vertically */
           .top-controls {
                flex-direction: column;
                gap: 0.1rem !important;
                align-items: stretch;
            }
            
            .search-bar {
                flex-direction:row;
                gap: 0.2rem; /* Reduced from 0.3rem */
                max-width: 100%;
                margin-bottom: 0.01rem;
            }
            
            .top-controls select,
            .top-controls input {
                width: 100%;
                padding: 0.5rem;
                font-size: 0.75rem;
                border-radius: 8px;
                border: 2px solid #e5e7eb;
            }
            
            .top-controls select:focus,
            .top-controls input:focus {
                border-color: #ffa500;
                box-shadow: 0 0 0 3px rgba(255,165,0,0.1);
                outline: none;
            }
            
            .top-controls button {
                width: 100%;
                padding: 0.6rem;
                font-size: 0.8rem;
                border-radius: 8px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            
            /* Table wrapper */
            .callbacks-table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0rem;
                padding: 0rem;
            }
            
            /* Table styles */
            table {
                min-width: 800px;
                font-size: 0.65rem;
            }
            
            table th,
            table td {
                padding: 0.2rem 0.3rem;
                min-width: 35px;
                max-width: 70px;
                font-size: 0.65rem;
            }
            /* Editable fields in edit mode and new row */
            .edit-mode .editable-input,
            .new-entry-row .editable,
            .edit-mode input,
            .edit-mode textarea,
            .edit-mode select,
            .new-entry-row input,
            .new-entry-row textarea,
            .new-entry-row select {
                font-size: 0.6rem; /* Smaller text for editable fields */
            }
            /* Specific column widths for 360px */
            table th:nth-child(1), table td:nth-child(1) { min-width: 30px; max-width: 40px; } /* S.No */
            table th:nth-child(2), table td:nth-child(2) { min-width: 50px; max-width: 70px; } /* Customer Name */
            table th:nth-child(3), table td:nth-child(3) { min-width: 50px; max-width: 70px; } /* Phone */
            table th:nth-child(4), table td:nth-child(4) { min-width: 50px; max-width: 70px; } /* Email */
            table th:nth-child(5), table td:nth-child(5) { min-width: 50px; max-width: 70px; } /* Address */
            table th:nth-child(6), table td:nth-child(6) { min-width: 50px; max-width: 70px; } /* Website */
            table th:nth-child(7), table td:nth-child(7) { min-width: 50px; max-width: 70px; } /* Remarks */
            table th:nth-child(8), table td:nth-child(8) { min-width: 50px; max-width: 70px; } /* Notes */
            table th:nth-child(9), table td:nth-child(9) { min-width: 60px; max-width: 80px; } /* Actions */
            
            /* Action buttons */
            .action-icon {
                font-size: 0.7rem;
                margin: 0 2px;
            }
            
            .action-save-btn {
                padding: 0.15rem 0.3rem;
                font-size: 0.6rem;
                min-width: 35px;
            }
            
            /* Pagination */
            #pagination {
                margin-top: 1rem;
                gap: 0.3rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            #pagination button,
            #pagination span.page-num {
                padding: 0.3rem 0.5rem;
                font-size: 0.7rem;
                min-width: 25px;
            }
            
            /* Toast notifications */
            .toast-container {
                bottom: 10px;
                right: 10px;
                left: 10px;
                width: auto;
            }
            
            .toast {
                font-size: 0.7rem;
                width: 100%;
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
                        Callbacks Table of {{ $is_viewing_other ? $target_user->username : Auth::user()->username }}
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
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <section id="callbacks" class="content-section">
                <div class="top-controls">
                    <div class="search-bar">
                        <select id="searchField" class="form-control">
                            <option value="all" {{ $search_field == 'all' ? 'selected' : '' }}>All Fields</option>
                            <option value="customer_name" {{ $search_field == 'customer_name' ? 'selected' : '' }}>Customer Name</option>
                            <option value="phone_number" {{ $search_field == 'phone_number' ? 'selected' : '' }}>Phone Number</option>
                            <option value="email" {{ $search_field == 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                        <input type="text" id="searchInput" class="form-control ms-2" placeholder="Search..." value="{{ $search_query ?? '' }}">
                    </div>
                    <div class="button-group">
                        @if ($can_add)
                            <button class="btn" id="addNewRow" aria-label="Add New Row" disabled><i class="fas fa-plus ms-1"></i> Add Row</button>
                        @endif
                    </div>
                </div>
                <div class="callbacks-table-wrapper">
                    <form id="callbackForm" method="POST" action="{{ route('callbacks.save') }}">
                        @csrf
                        @if ($is_viewing_other)
                            <input type="hidden" name="target_user_id" value="{{ $target_user->id }}">
                        @endif
                        <table id="callbacksTable">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">S.No</th>
                                    <th style="width: 13%;">Customer Name</th>
                                    <th style="width: 12%;">Phone Number</th>
                                    <th style="width: 14%;">Email</th>
                                    <th style="width: 11%;">Address</th>
                                    <th style="width: 15%;">Website</th>
                                    <th style="width: 12%;">Remarks</th>
                                    <th style="width: 15%;">Notes</th>
                                    <th style="width: 10%; min-width: 60px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @foreach ($callbacks as $index => $callback)
                                    <tr data-callback-id="{{ $callback->id }}" class="callback-row">
                                        <td>{{ ($callbacks->currentPage() - 1) * $callbacks->perPage() + $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="added_at" class="added-at-input" value="{{ $callback->added_at->format('Y-m-d H:i:s') }}">
                                            <span class="display-text name-input">{{ $callback->customer_name ?? '' }}</span>
                                            <input type="text" class="editable-input name-input" style="display: none;" name="customer_name" maxlength="100" pattern="[A-Za-z\s]+" title="Only alphabetical characters allowed" value="{{ $callback->customer_name ?? '' }}">
                                        </td>
                                        <td>
                                            <span class="display-text phone-input">{{ $callback->phone_number ?? '' }}</span>
                                            <input type="text" class="editable-input phone-input" style="display: none;" name="phone_number" maxlength="20" pattern="[\+\-\(\),./#0-9\s]+" title="Only numbers, +, -, (), comma, period, /, #, and spaces allowed" value="{{ $callback->phone_number ?? '' }}">
                                        </td>
                                        <td>
                                            <span class="display-text email-input">{{ $callback->email ?? '' }}</span>
                                            <input type="email" class="editable-input email-input" style="display: none;" name="email" maxlength="100" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Enter a valid email address (e.g., example@domain.com)" value="{{ $callback->email ?? '' }}">
                                        </td>
                                        <td>
                                            <span class="display-text address-input">{{ $callback->address ?? '' }}</span>
                                            <textarea class="editable-input address-input" style="display: none;" name="address" rows="1" maxlength="255">{{ $callback->address ?? '' }}</textarea>
                                        </td>
                                        <td>
                                            <span class="display-text website-input">{{ $callback->website ?? '' }}</span>
                                            <input type="url" class="editable-input website-input" style="display: none;" name="website" maxlength="255" pattern="https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(/.*)?$" title="Enter a valid URL (e.g., http://example.com)" value="{{ $callback->website ?? '' }}">
                                        </td>
                                        <td>
                                            <span class="display-text remarks-input">{{ $callback->remarks ?? '' }}</span>
                                            <select class="editable-input remarks-input" style="display: none;" name="remarks">
                                                <option value="" {{ !$callback->remarks ? 'selected' : '' }}>Select</option>
                                                <option value="Callback" {{ $callback->remarks == 'Callback' ? 'selected' : '' }}>Callback</option>
                                                <option value="Pre-sale" {{ $callback->remarks == 'Pre-sale' ? 'selected' : '' }}>Pre-sale</option>
                                                <option value="Sample rejected" {{ $callback->remarks == 'Sample rejected' ? 'selected' : '' }}>Sample rejected</option>
                                                <option value="Sale" {{ $callback->remarks == 'Sale' ? 'selected' : '' }}>Sale</option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="display-text notes-input">{{ $callback->notes ?? '' }}</span>
                                            <textarea class="editable-input notes-input" style="display: none;" name="notes" rows="1" maxlength="255">{{ $callback->notes ?? '' }}</textarea>
                                        </td>
                                        <td>
                                            @if ($can_edit && ($user_role == 'agent' || $user_role == 'admin'))
                                                <i class="fas fa-edit action-icon edit-callback" title="Edit" aria-label="Edit Callback"></i>
                                                <i class="fas fa-times action-icon cancel-edit" title="Cancel" style="display: none;" aria-label="Cancel Edit"></i>
                                                <button type="button" class="action-save-btn" style="display: none;" aria-label="Save Row">Save</button>
                                            @endif
                                            @if ($user_role == 'admin')
                                                <i class="fas fa-trash action-icon delete-callback" title="Delete" aria-label="Delete Callback"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                @if ($callbacks->hasPages())
                    <div id="pagination">
                        @if ($callbacks->onFirstPage())
                            <button disabled aria-label="First Page">««</button>
                            <button disabled aria-label="Previous Page">«</button>
                        @else
                            <button onclick="window.location.href='{{ $callbacks->url(1) }}'" aria-label="First Page">««</button>
                            <button onclick="window.location.href='{{ $callbacks->previousPageUrl() }}'" aria-label="Previous Page">«</button>
                        @endif
                        @foreach ($callbacks->getUrlRange(max(1, $callbacks->currentPage() - 2), min($callbacks->lastPage(), $callbacks->currentPage() + 2)) as $page => $url)
                            <span class="page-num {{ $callbacks->currentPage() == $page ? 'active' : '' }}" onclick="window.location.href='{{ $url }}'" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @endforeach
                        @if ($callbacks->hasMorePages())
                            <button onclick="window.location.href='{{ $callbacks->nextPageUrl() }}'" aria-label="Next Page">»</button>
                            <button onclick="window.location.href='{{ $callbacks->url($callbacks->lastPage()) }}'" aria-label="Last Page">»»</button>
                        @else
                            <button disabled aria-label="Next Page">»</button>
                            <button disabled aria-label="Last Page">»»</button>
                        @endif
                    </div>
                @endif
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
            // Show toast notification
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

            // Check if any rows are in edit mode or unsaved
            function updateAddRowButtonState() {
                const addRowButton = document.getElementById('addNewRow');
                if (!addRowButton) return;
                const unsavedRows = document.querySelectorAll('.edit-mode, [data-new-row="true"]');
                addRowButton.disabled = unsavedRows.length > 0;
            }

            // Create a template for new row
            function createNewRowTemplate() {
                const template = document.createElement('tr');
                template.className = 'new-entry-row';
                template.setAttribute('data-new-row', 'true');
                template.innerHTML = `
                    <td></td> <!-- Empty cell for S.No -->
                    <td>
                        <input type="hidden" name="added_at" class="added-at-input" value="">
                        <input type="text" name="customer_name" class="form-control editable name-input" 
                               placeholder="Name" maxlength="100" pattern="[A-Za-z\s]+" 
                               title="Only alphabetical characters allowed">
                    </td>
                    <td>
                        <input type="text" name="phone_number" class="form-control editable phone-input" 
                               placeholder="Phone" maxlength="20" pattern="\+?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}"
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
                               pattern="https?://[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+(\/.*)?"
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
                    <td>
                        <button type="button" class="action-save-btn" aria-label="Save New Row">Save</button>
                    </td>
                `;
                return template;
            }

            // [Rest of the JavaScript remains unchanged]
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

            function addNewRow() {
                const tableBody = document.querySelector('#tableBody');
                const existingNewRow = document.querySelector('[data-new-row="true"]');
                if (existingNewRow) return;
                const newRow = createNewRowTemplate();
                tableBody.appendChild(newRow);
                setAddedAtTimestamp(newRow);
                const firstInput = newRow.querySelector('input');
                if (firstInput) firstInput.focus();
                updateAddRowButtonState();
            }

            const searchInput = document.getElementById('searchInput');
            const searchField = document.getElementById('searchField');
            const tableBody = document.getElementById('tableBody');
            const pagination = document.getElementById('pagination');
            const form = document.getElementById('callbackForm');
            function performSearch() {
                const query = searchInput.value.trim();
                const field = searchField.value;
                const url = new URL(window.location.href);
                url.searchParams.set('q', query);
                url.searchParams.set('search_field', field);
                url.searchParams.set('page', '1');

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': form.querySelector('[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = data.callbacks_html;
                    if (pagination) pagination.innerHTML = data.pagination_html;
                    updateAddRowButtonState();
                })
                .catch(e => console.log(e));
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
                    row.querySelectorAll('.display-text').forEach(span => span.style.display = 'none');
                    row.querySelectorAll('.editable-input').forEach(input => input.style.display = 'block');
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
                            row.querySelector('td:nth-child(2)').appendChild(callbackIdInput); // Changed to nth-child(2) to avoid S.No column
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
                    let isValid = true;

                    formData.append('_token', form.querySelector('[name="_token"]').value);
                    if (form.querySelector('[name="target_user_id"]')) {
                        formData.append('target_user_id', form.querySelector('[name="target_user_id"]').value);
                    }

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

                    if (!inputs.customer_name.value.trim() || !inputs.phone_number.value.trim()) {
                        [inputs.customer_name, inputs.phone_number].forEach(input => input.classList.add('is-invalid'));
                        isValid = false;
                    } else if (!/^[A-Za-z\s]+$/.test(inputs.customer_name.value.trim()) || !/^\+?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}[\-\s]?[0-9]{1,4}$/.test(inputs.phone_number.value.trim())) {
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
                    if (inputs.website.value.trim() && !new RegExp('^https?://[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+(\/.*)?$').test(inputs.website.value.trim())) {
                        inputs.website.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        inputs.website.classList.remove('is-invalid');
                    }

                    if (!isValid) {
                        showToast('Please correct the invalid fields.', 'danger');
                        return;
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
                                    span.textContent = input?.value || '';
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

            const addRowButton = document.getElementById('addNewRow');
            if (addRowButton) {
                addRowButton.addEventListener('click', addNewRow);
                updateAddRowButtonState();
            }

            document.querySelector('#tableBody').addEventListener('input', function(e) {
                if (e.target.classList.contains('editable') && e.target.closest('[data-new-row="true"]')) {
                    const row = e.target.closest('[data-new-row="true"]');
                    setAddedAtTimestamp(row);
                }
            });

            document.querySelector('#tableBody').addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-callback')) {
                    if (!confirm('Are you sure you want to delete this callback?')) {
                        return;
                    }
                    const row = e.target.closest('tr');
                    const callbackId = row.dataset.callbackId;
                    fetch('{{ route('callbacks.delete') }}', {
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

            @if ($user_role === 'agent')
                document.addEventListener('copy', e => e.preventDefault());
                document.addEventListener('cut', e => e.preventDefault());
                document.addEventListener('paste', e => e.preventDefault());
            @endif
        });
    </script>
</body>
</html>