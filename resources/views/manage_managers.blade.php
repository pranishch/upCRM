<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Managers | Callback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
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
        .main-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1400px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        .table td {
            vertical-align: middle;
            padding: 0.75rem;
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
        .btn-danger {
            background-color: var(--admin-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .form-control, .form-select {
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
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
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 40px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 1rem;
            line-height: 1;
            padding: 5px;
        }
        @media (max-width: 992px) {
            .main-container {
                margin: 1rem auto;
                padding: 1.5rem;
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
        }
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table {
                min-width: 700px;
            }
            .action-buttons .btn-action {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            .navbar-nav {
                flex-direction: column;
                gap: 0.5rem;
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
            .action-buttons .btn-action {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
            h4 {
                font-size: 1.25rem;
            }
            .navbar-brand {
                font-size: 1rem;
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
                    <a class="nav-link" href="javascript:history.back()">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <div class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>
                            Quick Actions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('users.index') }}">
                                <i class="bi bi-people me-2"></i>Manage Users
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('managers.index') }}">
                                <i class="bi bi-person-gear me-2"></i>Manage Managers
                            </a></li>
                        </ul>
                    </div>
                    <div class="text-light me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        <span>{{ Auth::user()->username }}</span>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-person-gear me-2"></i>
                    Manage Managers
                    <small class="text-muted">({{ $managers->count() }} managers)</small>
                </h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createManagerModal">
                    <i class="bi bi-person-plus me-1"></i>
                    Create New Manager
                </button>
            </div>

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

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 25%">Username</th>
                            <th style="width: 25%">Email</th>
                            <th style="width: 25%">Status</th>
                            <th style="width: 25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($managers as $manager)
                            @if ($manager->userprofile && $manager->userprofile->role === 'manager')
                            <tr>
                                <td>{{ $manager->username }}</td>
                                <td>{{ $manager->email ?? 'No email' }}</td>
                                <td>
                                    @if ($manager->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('managers.dashboard', $manager->id) }}" 
                                        class="btn btn-sm btn-action btn-outline-success" 
                                        title="View Manager Dashboard">
                                            <i class="bi bi-speedometer2"></i>
                                        </a>
                                        <button class="btn btn-sm btn-action btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editManagerModal" 
                                                data-user-id="{{ $manager->id }}"
                                                data-username="{{ $manager->username }}"
                                                data-email="{{ $manager->email ?? '' }}"
                                                title="Edit Manager">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-action btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#changeRoleModal" 
                                                data-user-id="{{ $manager->id }}"
                                                data-username="{{ $manager->username }}"
                                                title="Change Role">
                                            <i class="bi bi-person-gear"></i>
                                        </button>
                                        <button class="btn btn-sm btn-action btn-outline-secondary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#resetPasswordModal" 
                                                data-user-id="{{ $manager->id }}"
                                                data-username="{{ $manager->username }}"
                                                title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <a href="{{ route('managers.delete', $manager->id) }}" 
                                        class="btn btn-sm btn-action btn-outline-danger" 
                                        title="Delete Manager"
                                        onclick="return confirm('Are you sure you want to delete the manager {{ $manager->username }}?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No managers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Manager Modal -->
    <div class="modal fade" id="createManagerModal" tabindex="-1" aria-labelledby="createManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createManagerModalLabel">Create New Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('managers.store') }}">
                    @csrf
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 password-container">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            <i class="fas fa-eye password-toggle" id="createPassword1Toggle"></i>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 password-container">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                            <i class="fas fa-eye password-toggle" id="createPassword2Toggle"></i>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="role" value="manager">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Manager</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Manager Modal -->
    <div class="modal fade" id="editManagerModal" tabindex="-1" aria-labelledby="editManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editManagerModalLabel">Edit Manager <span id="editManagerUsername"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('managers.update') }}">
                    @csrf
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="editManagerUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="editManagerUsernameInput" class="form-control @error('username') is-invalid @enderror" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="editManagerEmailInput" class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Role Modal -->
    <div class="modal fade" id="changeRoleModal" tabindex="-1" aria-labelledby="changeRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeRoleModalLabel">Change Role for <span id="roleUsername"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('managers.change_role') }}">
                    @csrf
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="user_id" id="changeRoleUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_role" class="form-label">New Role</label>
                            <select name="new_role" id="new_role" class="form-select">
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Change Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password for <span id="passwordUsername"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('managers.reset_password') }}">
                    @csrf
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="user_id" id="resetPasswordUserId">
                    <div class="modal-body">
                        <div class="mb-3 password-container">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                            <i class="fas fa-eye password-toggle" id="resetPasswordToggle"></i>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            var changeRoleModal = document.getElementById('changeRoleModal');
            changeRoleModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var username = button.getAttribute('data-username');
                var modal = this;
                modal.querySelector('#changeRoleUserId').value = userId;
                modal.querySelector('#roleUsername').textContent = username;
            });

            var resetPasswordModal = document.getElementById('resetPasswordModal');
            resetPasswordModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var username = button.getAttribute('data-username');
                var modal = this;
                modal.querySelector('#resetPasswordUserId').value = userId;
                modal.querySelector('#passwordUsername').textContent = username;
            });

            var editManagerModal = document.getElementById('editManagerModal');
            editManagerModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var username = button.getAttribute('data-username');
                var email = button.getAttribute('data-email');
                var modal = this;
                modal.querySelector('#editManagerUserId').value = userId;
                modal.querySelector('#editManagerUsername').textContent = username;
                modal.querySelector('#editManagerUsernameInput').value = username;
                modal.querySelector('#editManagerEmailInput').value = email || '';
            });

            const createPassword1Toggle = document.getElementById('createPassword1Toggle');
            const createPassword1Input = document.getElementById('password');
            createPassword1Toggle.addEventListener('click', function() {
                const type = createPassword1Input.getAttribute('type') === 'password' ? 'text' : 'password';
                createPassword1Input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            const createPassword2Toggle = document.getElementById('createPassword2Toggle');
            const createPassword2Input = document.getElementById('password_confirmation');
            createPassword2Toggle.addEventListener('click', function() {
                const type = createPassword2Input.getAttribute('type') === 'password' ? 'text' : 'password';
                createPassword2Input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            const resetPasswordToggle = document.getElementById('resetPasswordToggle');
            const resetPasswordInput = document.getElementById('new_password');
            resetPasswordToggle.addEventListener('click', function() {
                const type = resetPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                resetPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>