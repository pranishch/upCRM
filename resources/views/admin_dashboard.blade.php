<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard | Callback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: orange;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --admin-color: #dc3545;
            --manager-color: #ffc107;
            --agent-color: #28a745;
            --text-dark: #2c3e50;
            --shadow: 0 4px 12px rgba(0,0,0,0.15);
            --border-radius: 10px;
        }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e8ecef 100%);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        .navbar {
            box-shadow: var(--shadow);
            background: linear-gradient(to right, orange, #34495e);
            transition: all 0.3s ease;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #fff !important;
            transition: transform 0.2s ease;
        }
        .navbar-brand:hover {
            transform: scale(1.05);
        }
        .container {
            width: 100vw; /* Full viewport width */
            max-width: 100%; /* Remove max-width constraints */
            padding: 0; /* Remove default padding */
            margin: 0; /* Remove margins */
        }
        .mt-4 {
            margin-top: 0; /* Remove top margin */
        }
        .main-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            width: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            padding: 1rem 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            vertical-align: middle;
            padding: 0.75remმო�

            rem;
            border-bottom: 1px solid #e9ecef;
        }
        #allCallbacksTable .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            max-width: 100%;
        }
        #allCallbacksTable .table th {
            background: linear-gradient(to bottom, var(--primary-color), #e67e22);
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-size: 0.9rem;
            padding: 0.8rem;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }
        #allCallbacksTable .table td {
            transition: background-color 0.2s ease;
            font-size: 0.85rem;
            padding: 0.6rem;
        }
        #allCallbacksTable .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        #allCallbacksTable .table tbody tr:hover {
            background-color: #e9ecef;
            cursor: default;
        }
        #allCallbacksTable .table td:not(:last-child) {
            border-right: 1px solid #e9ecef;
        }
        #allCallbacksTable .table-responsive {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: var(--border-radius);
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }
        #allCallbacksTable .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        #allCallbacksTable .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        #allCallbacksTable .table-responsive::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        #allCallbacksTable .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #e67e22;
        }
        #allCallbacksTable .table th:nth-child(1), #allCallbacksTable .table td:nth-child(1) { /* Customer Name */
            width: 12%;
            min-width: 120px;
        }
        #allCallbacksTable .table th:nth-child(2), #allCallbacksTable .table td:nth-child(2) { /* Phone Number */
            width: 10%;
            min-width: 100px;
        }
        #allCallbacksTable .table th:nth-child(3), #allCallbacksTable .table td:nth-child(3) { /* Email */
            width: 12%;
            min-width: 120px;
        }
        #allCallbacksTable .table th:nth-child(4), #allCallbacksTable .table td:nth-child(4) { /* Address */
            width: 12%;
            min-width: 120px;
        }
        #allCallbacksTable .table th:nth-child(5), #allCallbacksTable .table td:nth-child(5) { /* Website */
            width: 10%;
            min-width: 100px;
        }
        #allCallbacksTable .table th:nth-child(6), #allCallbacksTable .table td:nth-child(6) { /* Remarks */
            width: 10%;
            min-width: 100px;
        }
        #allCallbacksTable .table th:nth-child(7), #allCallbacksTable .table td:nth-child(7) { /* Notes */
            width: 10%;
            min-width: 100px;
        }
        #allCallbacksTable .table th:nth-child(8), #allCallbacksTable .table td:nth-child(8) { /* Assigned Manager */
            width: 13.5%;
            min-width: 120px;
        }
        #allCallbacksTable .table th:nth-child(9), #allCallbacksTable .table td:nth-child(9) { /* Created By */
            width: 8%;
            min-width: 100px;
        }
        #allCallbacksTable .table th:nth-child(10), #allCallbacksTable .table td:nth-child(10) { /* Actions */
            width: 12%;
            min-width: 120px;
        }
        @media (min-width: 992px) {
            #allCallbacksTable .table {
                table-layout: fixed;
            }
            #allCallbacksTable .table td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
        @media (max-width: 992px) {
            .main-container {
                margin: 1rem auto;
                padding: 1.5rem;
            }
            .card {
                margin-bottom: 1.5rem;
            }
            .card-header {
                padding: 0.75rem 1rem;
            }
            .card-body {
                padding: 1rem;
            }
            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
            .action-buttons .btn-action {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
            .navbar-brand {
                font-size: 1.1rem;
            }
            .system-overview .card {
                flex: 1 1 100%;
            }
            #allCallbacksTable .table-responsive {
                max-height: 400px;
            }
        }
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table {
                min-width: 900px;
            }
            .card-header {
                padding: 0.5rem 0.75rem;
            }
            .card-body {
                padding: 0.75rem;
            }
            .action-buttons .btn-action {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            .navbar-nav {
                flex-direction: column;
                gap: 0.5rem;
            }
            #allCallbacksTable .table-responsive {
                max-height: 350px;
            }
            .modal-dialog {
                margin: 0.5rem;
            }
            .form-control, .form-select {
                font-size: 0.9rem;
            }
        }
        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.8rem;
                padding: 0.4rem;
            }
            .card-header {
                padding: 0.4rem 0.6rem;
            }
            .card-body {
                padding: 0.6rem;
            }
            .action-buttons .btn-action {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
            h1, h4 {
                font-size: 1.25rem;
            }
            .navbar-brand {
                font-size: 1rem;
            }
            #allCallbacksTable .table-responsive {
                max-height: 300px;
            }
        }
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
        }
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            transition: transform 0.2s ease;
        }
        .role-badge:hover {
            transform: translateY(-2px);
        }
        .role-admin { background-color: var(--admin-color); color: white; }
        .role-manager { background-color: var(--manager-color); color: #333; }
        .role-agent { background-color: var(--agent-color); color: white; }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-success {
            background-color: var(--agent-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-info {
            background-color: #17a2b8;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-info:hover {
            background-color: #138496;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-danger {
            background-color: var(--admin-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .system-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .system-overview .card {
            flex: 1 1 calc(50% - 0.5rem);
            min-width: 0;
        }
        .manager-select {
            width: 100%;
            padding: 0.25rem;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .manager-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
            transform: scale(1.01);
        }
        .modal-content {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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
        #pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.6rem;
            font-weight: 600;
            user-select: none;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin_dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i>
                Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>
                            Quick Actions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('manage_users') }}">
                                <i class="bi bi-people me-2"></i>Manage Users
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('manage_managers') }}">
                                <i class="bi bi-person-gear me-2"></i>Manage Managers
                            </a></li>
                        </ul>
                    </div>
                    <div class="text-light me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        <span>{{ Auth::user()->username }}</span>
                        <span class="role-badge role-admin">Admin</span>
                    </div>
                    <a class="nav-link" href="{{ route('logout') }}">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="main-container">
            <h1 class="mb-4">
                <i class="bi bi-speedometer2 me-2"></i>
                Admin Dashboard
            </h1>

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
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- System Overview -->
            <div class="system-overview">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Total Users
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $total_users }}</h5>
                        <a href="{{ route('manage_users') }}" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person-gear me-2"></i>Total Managers
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $total_managers }}</h5>
                        <a href="{{ route('manage_managers') }}" class="btn btn-primary">Manage Managers</a>
                    </div>
                </div>
            </div>

            <!-- All Callbacks Table -->
            <div id="allCallbacksTable">
                <div class="mb-4">
                    <h4 class="mb-3">
                        <i class="bi bi-telephone me-2"></i>
                        All Callbacks
                        <small class="text-muted">({{ $total_callbacks }} callbacks)</small>
                    </h4>
                    <div class="search-bar">
                        <select id="searchField" class="form-control">
                            <option value="all" {{ $search_field == 'all' ? 'selected' : '' }}>All Fields</option>
                            <option value="customer_name" {{ $search_field == 'customer_name' ? 'selected' : '' }}>Customer Name</option>
                            <option value="phone_number" {{ $search_field == 'phone_number' ? 'selected' : '' }}>Phone Number</option>
                            <option value="email" {{ $search_field == 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search..." value="{{ $search_query ?? '' }}">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Phone Number</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Website</th>
                                    <th>Remarks</th>
                                    <th>Notes</th>
                                    @if ($user_role == 'admin')
                                        <th>Assigned Manager</th>
                                    @endif
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="callbackTableBody">
                                @forelse ($all_callbacks as $callback)
                                    <tr data-callback-id="{{ $callback->id }}" data-row-id="{{ $callback->id }}">
                                        <td>{{ $callback->customer_name ?? 'N/A' }}</td>
                                        <td>{{ $callback->phone_number ?? 'N/A' }}</td>
                                        <td>{{ $callback->email ?? 'N/A' }}</td>
                                        <td>{{ $callback->address ?? 'N/A' }}</td>
                                        <td>{{ $callback->website ?? 'N/A' }}</td>
                                        <td>
                                            <span class="display-text remarks-input">{{ $callback->remarks ?? 'N/A' }}</span>
                                            <select class="editable-input remarks-input form-control" style="display: none;" name="remarks">
                                                <option value="" {{ !$callback->remarks ? 'selected' : '' }}>Select</option>
                                                <option value="Callback" {{ $callback->remarks == 'Callback' ? 'selected' : '' }}>Callback</option>
                                                <option value="Pre-sale" {{ $callback->remarks == 'Pre-sale' ? 'selected' : '' }}>Pre-sale</option>
                                                <option value="Sample rejected" {{ $callback->remarks == 'Sample rejected' ? 'selected' : '' }}>Sample rejected</option>
                                                <option value="Sale" {{ $callback->remarks == 'Sale' ? 'selected' : '' }}>Sale</option>
                                            </select>
                                        </td>                                
                                        <td>{{ $callback->notes ?? 'N/A' }}</td>
                                        @if ($user_role == 'admin')
                                            <td>
                                                <!-- Debug output to inspect role -->
                                                <span style="display: none;">DEBUG: Role={{ $callback->createdBy->userProfile->role ?? 'No Profile' }}</span>
                                                @if ($callback->createdBy->userProfile && $callback->createdBy->userProfile->role == 'manager')
                                                    <span>{{ $callback->createdBy->username ?? 'No Manager' }}</span>
                                                @else
                                                    <select class="manager-select form-select"
                                                            data-row-id="{{ $callback->id }}"
                                                            data-username="{{ $callback->customer_name ?? 'N/A' }}">
                                                        <option value="" {{ !$callback->manager ? 'selected' : '' }}>No Manager</option>
                                                        @foreach ($managers as $manager)
                                                            <option value="{{ $manager->id }}" {{ $callback->manager && $callback->manager->id == $manager->id ? 'selected' : '' }}>{{ $manager->username }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $callback->createdBy->username }}</td>
                                        <td class="action-buttons">
                                            <button class="btn btn-info btn-action edit-callback" data-callback-id="{{ $callback->id }}" title="Edit Callback">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-action delete-callback" data-callback-id="{{ $callback->id }}" title="Delete Callback">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user_role == 'admin' ? 10 : 9 }}" class="text-center">No callbacks found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination">
                        @if ($page_obj->hasPages())
                            @if ($page_obj->onFirstPage())
                                <button disabled aria-label="First Page">««</button>
                                <button disabled aria-label="Previous Page">«</button>
                            @else
                                <button onclick="window.location.href='{{ $page_obj->url(1) }}'" aria-label="First Page">««</button>
                                <button onclick="window.location.href='{{ $page_obj->previousPageUrl() }}'" aria-label="Previous Page">«</button>
                            @endif
                            @foreach ($page_obj->getUrlRange(1, $page_obj->lastPage()) as $page => $url)
                                @if ($page >= $page_obj->currentPage() - 2 && $page <= $page_obj->currentPage() + 2)
                                    <span class="page-num {{ $page == $page_obj->currentPage() ? 'active' : '' }}"
                                          onclick="{{ $page != $page_obj->currentPage() ? 'window.location.href=\'' . $url . '\'' : '' }}"
                                          aria-label="Page {{ $page }}">{{ $page }}</span>
                                @endif
                            @endforeach
                            @if ($page_obj->hasMorePages())
                                <button onclick="window.location.href='{{ $page_obj->nextPageUrl() }}'" aria-label="Next Page">»</button>
                                <button onclick="window.location.href='{{ $page_obj->url($page_obj->lastPage()) }}'" aria-label="Last Page">»»</button>
                            @else
                                <button disabled aria-label="Next Page">»</button>
                                <button disabled aria-label="Last Page">»»</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Manager Modal -->
    <div class="modal fade" id="assignManagerModal" tabindex="-1" aria-labelledby="assignManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignManagerModalLabel">Assign Manager for Callback <span id="assignCallbackName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignManagerForm" action="{{ route('assign_manager') }}" method="POST">
                    @csrf
                    <input type="hidden" name="callback_id" id="assignCallbackId">
                    <input type="hidden" name="manager_id" id="assignManagerId">
                    <div class="modal-body">
                        Are you sure you want to assign the selected manager to the callback for <span id="assignCallbackNameText"></span>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Manager</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCallbackModal" tabindex="-1" aria-labelledby="deleteCallbackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCallbackModalLabel">Delete Callback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the callback for <span id="deleteCallbackName"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCallback">Delete</button>
                </div>
            </div>
        </div>
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
        window.history.pushState({ page: 'admin_dashboard' }, null, window.location.href);

        // Listen for popstate event to detect back/forward navigation
        window.addEventListener('popstate', function(event) {
            // Check if navigating back (event.state is null or doesn't match current state)
            if (!event.state || event.state.page !== 'admin_dashboard') {
                // Reload the page to ensure fresh data
                window.location.reload();
            }
        });  
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

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        const searchInput = document.getElementById('searchInput');
        const searchField = document.getElementById('searchField');
        const tableBody = document.getElementById('callbackTableBody');
        const pagination = document.getElementById('pagination');

        window.loadPage = function(page) {
            const query = searchInput.value.trim();
            const field = searchField.value;
            const url = new URL('{{ route("admin_dashboard") }}');
            url.searchParams.set('page', page);
            if (query) {
                url.searchParams.set('q', query);
                url.searchParams.set('search_field', field);
            }

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.callbacks_html;
                pagination.innerHTML = data.pagination_html;
                document.querySelector('small.text-muted').textContent = `(${data.total_callbacks} callbacks)`;
                const newTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                newTooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
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

        // Inline Editing
        tableBody.addEventListener('click', function(e) {
            const editButton = e.target.closest('.edit-callback');
            const saveButton = e.target.closest('.save-callback');
            const cancelButton = e.target.closest('.cancel-callback');
            const deleteButton = e.target.closest('.delete-callback');

            if (editButton) {
                const row = editButton.closest('tr');
                const callbackId = row.getAttribute('data-callback-id');
                toggleEditMode(row, true);
            }

            if (saveButton) {
                const row = saveButton.closest('tr');
                const callbackId = row.getAttribute('data-callback-id');
                saveCallback(row, callbackId);
            }

            if (cancelButton) {
                const row = cancelButton.closest('tr');
                toggleEditMode(row, false);
            }

            if (deleteButton) {
                const row = deleteButton.closest('tr');
                const callbackId = row.getAttribute('data-callback-id');
                const customerName = row.querySelector('td:nth-child(1)').textContent;
                showDeleteModal(callbackId, customerName);
            }
        });

        function toggleEditMode(row, isEditMode) {
            const cells = row.querySelectorAll('td:not(:last-child):not(:nth-child(8))'); // Exclude Actions and Assigned Manager
            if (isEditMode) {
                cells.forEach((cell, index) => {
                    if (index === 5) { // Remarks column
                        const displayText = cell.querySelector('.display-text');
                        const select = cell.querySelector('.editable-input');
                        const value = displayText.textContent.trim() === 'N/A' ? '' : displayText.textContent.trim();
                        displayText.style.display = 'none';
                        select.style.display = 'block';
                        select.value = value;
                        cell.setAttribute('data-original-value', value);
                    } else {
                        const value = cell.textContent.trim() === 'N/A' ? '' : cell.textContent.trim();
                        cell.setAttribute('data-original-value', value);
                        if (index === 2) { // Email
                            cell.innerHTML = `<input type="email" class="form-control" value="${value}" />`;
                        } else if (index === 4) { // Website
                            cell.innerHTML = `<input type="url" class="form-control" value="${value}" />`;
                        } else {
                            cell.innerHTML = `<input type="text" class="form-control" value="${value}" />`;
                        }
                    }
                });
                row.querySelector('.action-buttons').innerHTML = `
                    <button class="btn btn-success btn-action save-callback" title="Save Changes">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn btn-secondary btn-action cancel-callback" title="Cancel">
                        <i class="bi bi-x-circle"></i>
                    </button>
                `;
            } else {
                cells.forEach((cell, index) => {
                    if (index === 5) { // Remarks column
                        const displayText = cell.querySelector('.display-text');
                        const select = cell.querySelector('.editable-input');
                        const originalValue = cell.getAttribute('data-original-value') || '';
                        displayText.textContent = originalValue || 'N/A';
                        displayText.style.display = 'block';
                        select.style.display = 'none';
                    } else {
                        const originalValue = cell.getAttribute('data-original-value') || 'N/A';
                        cell.textContent = originalValue;
                    }
                });
                const callbackId = row.getAttribute('data-callback-id');
                row.querySelector('.action-buttons').innerHTML = `
                    <button class="btn btn-info btn-action edit-callback" data-callback-id="${callbackId}" title="Edit Callback">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-action delete-callback" data-callback-id="${callbackId}" title="Delete Callback">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
            }
        }

        function saveCallback(row, callbackId) {
            const cells = row.querySelectorAll('td:not(:last-child):not(:nth-child(8))');
            const data = {
                callback_id: callbackId,
                customer_name: cells[0].querySelector('input').value.trim(),
                phone_number: cells[1].querySelector('input').value.trim(),
                email: cells[2].querySelector('input').value.trim() || null,
                address: cells[3].querySelector('input').value.trim() || null,
                website: cells[4].querySelector('input').value.trim() || null,
                remarks: cells[5].querySelector('select').value || null,
                notes: cells[6].querySelector('input').value.trim() || null
            };

            fetch('{{ route("admin_dashboard.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    cells.forEach((cell, index) => {
                        if (index === 5) { // Remarks column
                            const displayText = cell.querySelector('.display-text');
                            const select = cell.querySelector('.editable-input');
                            const value = select.value || 'N/A';
                            displayText.textContent = value;
                            displayText.style.display = 'block';
                            select.style.display = 'none';
                            cell.setAttribute('data-original-value', value === 'N/A' ? '' : value);
                        } else {
                            const input = cell.querySelector('input');
                            const value = input.value.trim() || 'N/A';
                            cell.textContent = value;
                            cell.setAttribute('data-original-value', value === 'N/A' ? '' : value);
                        }
                    });
                    row.querySelector('.action-buttons').innerHTML = `
                        <button class="btn btn-info btn-action edit-callback" data-callback-id="${callbackId}" title="Edit Callback">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-action delete-callback" data-callback-id="${callbackId}" title="Delete Callback">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    showToast(result.message, 'success');
                } else {
                    toggleEditMode(row, false);
                    showToast('Error: ' + result.message, 'danger');
                }
            })
            .catch(() => {
                toggleEditMode(row, false);
                showToast('An error occurred while saving the callback.', 'danger');
            });
        }

        function showDeleteModal(callbackId, customerName) {
            const modal = new bootstrap.Modal(document.getElementById('deleteCallbackModal'));
            document.getElementById('deleteCallbackName').textContent = customerName;
            const confirmButton = document.getElementById('confirmDeleteCallback');
            confirmButton.onclick = () => confirmDeleteCallback(callbackId);
            modal.show();
        }

        function confirmDeleteCallback(callbackId) {
            fetch('{{ route("callbacks.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ callback_ids: [callbackId] })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    document.querySelector(`tr[data-callback-id="${callbackId}"]`).remove();
                    showToast(result.message, 'success');
                    const totalCallbacks = parseInt(document.querySelector('small.text-muted').textContent.match(/\d+/)[0]) - 1;
                    document.querySelector('small.text-muted').textContent = `(${totalCallbacks} callbacks)`;
                } else {
                    showToast('Error: ' + result.message, 'danger');
                }
            })
            .catch(() => showToast('An error occurred while deleting the callback.', 'danger'))
            .finally(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCallbackModal'));
                modal.hide();
            });
        }

        tableBody.addEventListener('change', function(e) {
            const managerSelect = e.target.closest('.manager-select');
            if (managerSelect) {
                const row = managerSelect.closest('tr');
                const callbackId = row.getAttribute('data-row-id');
                const callbackName = managerSelect.getAttribute('data-username');
                const newManagerId = managerSelect.value;

                managerSelect.setAttribute('data-original-value', managerSelect.value);

                const modal = new bootstrap.Modal(document.getElementById('assignManagerModal'));
                const modalCallbackName = document.getElementById('assignCallbackName');
                const modalCallbackNameText = document.getElementById('assignCallbackNameText');
                const callbackIdInput = document.getElementById('assignCallbackId');
                const managerIdInput = document.getElementById('assignManagerId');

                modalCallbackName.textContent = callbackName;
                modalCallbackNameText.textContent = callbackName;
                callbackIdInput.value = callbackId;
                managerIdInput.value = newManagerId;

                modal.show();
            }
        });

        document.getElementById('assignManagerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const callbackId = this.querySelector('#assignCallbackId').value;
            const managerId = this.querySelector('#assignManagerId').value;
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignManagerModal'));

            fetch('{{ route("assign_manager") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    callback_id: callbackId,
                    manager_id: managerId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    modal.hide();
                    showToast(result.message, 'success');
                    const select = document.querySelector(`select[data-row-id="${callbackId}"]`);
                    if (select) {
                        select.setAttribute('data-original-value', managerId);
                    }
                } else {
                    modal.hide();
                    showToast('Error: ' + result.message, 'danger');
                    const select = document.querySelector(`select[data-row-id="${callbackId}"]`);
                    if (select) {
                        select.value = select.getAttribute('data-original-value') || '';
                    }
                }
            })
            .catch(() => {
                modal.hide();
                showToast('An error occurred while assigning the manager.', 'danger');
                const select = document.querySelector(`select[data-row-id="${callbackId}"]`);
                if (select) {
                    select.value = select.getAttribute('data-original-value') || '';
                }
            });
        });

        document.getElementById('assignManagerModal').addEventListener('hidden.bs.modal', function() {
            const selects = document.querySelectorAll('.manager-select');
            selects.forEach(select => {
                select.value = select.getAttribute('data-original-value') || '';
            });
        });

        document.querySelectorAll('.manager-select').forEach(select => {
            select.setAttribute('data-original-value', select.value);
        });
    });
    </script>
</body>
</html>
