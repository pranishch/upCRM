<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs | Callback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        .main-container { 
            margin: 2rem auto; 
            max-width: 1200px; 
        }
        .card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); 
        }
        .card-header { 
            background: linear-gradient(135deg, #ff8c00, #ff7700); 
            color: white; 
            border: none; 
            padding: 1.5rem; 
            border-radius: 12px 12px 0 0 !important; 
        }
        .btn-orange { 
            background-color: #ff8c00; 
            border-color: #ff8c00; 
            color: white; 
            transition: all 0.3s ease; 
        }
        .btn-orange:hover { 
            background-color: #e67e00; 
            border-color: #e67e00; 
            color: white; 
            transform: translateY(-1px); 
        }
        .table th { 
            background-color: #ff8c00; 
            color: white; 
            border: none; 
            font-weight: 600; 
        }
        .table-hover tbody tr:hover { 
            background-color: #fff8f0; 
        }
        .pagination-info { 
            background-color: #f8f9fa; 
            border-radius: 8px; 
            padding: 0.75rem 1rem; 
            font-size: 0.9rem; 
            color: #6c757d; 
            border: 1px solid #e9ecef; 
        }
        .loading-overlay { 
            display: none; 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(255, 255, 255, 0.8); 
            z-index: 1000; 
            border-radius: 8px; 
        }
        .loading-spinner { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
        }
        .filter-badge { 
            background-color: #ff8c00; 
            color: white; 
            padding: 0.25rem 0.5rem; 
            border-radius: 12px; 
            font-size: 0.75rem; 
            margin-left: 0.5rem; 
        }
        .avatar-circle { 
            width: 32px; 
            height: 32px; 
            background: linear-gradient(135deg, #ff8c00, #ff7700); 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 0.8rem; 
        }
        .pagination-orange .page-link { 
            color: #ff8c00; 
            border-color: #dee2e6; 
            padding: 0.5rem 0.75rem; 
            margin: 0 2px; 
            border-radius: 6px; 
            transition: all 0.2s ease; 
            font-weight: 500; 
        }
        .pagination-orange .page-link:hover { 
            color: #fff; 
            background-color: #ff8c00; 
            border-color: #ff8c00; 
            transform: translateY(-1px); 
        }
        .pagination-orange .page-item.active .page-link { 
            background-color: #ff8c00; 
            border-color: #ff8c00; 
            color: #fff; 
            font-weight: 600; 
        }
        #results-info {
    display: none;
}
    </style>
</head>
<body>
    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1 class="mb-0">
                    <i class="fas fa-history me-2"></i>Activity Logs
                    <span id="active-filters"></span>
                </h1>
                <a href="{{ route('admin_dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
            <div class="card-body">
                <!-- Filters Row -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-1"></i>Username
                        </label>
                        <input type="text" id="username-search" class="form-control" placeholder="Search..." value="{{ request('username') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user-tag me-1"></i>Role
                        </label>
                        <select id="role-filter" class="form-select">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            <i class="fas fa-list me-1"></i>Records per page
                        </label>
                        <select id="per-page" class="form-select">
                            <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>

                <!-- Results Info -->
                <div id="results-info" class="pagination-info mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            Showing {{ $paginationInfo['from'] ?? 0 }} to {{ $paginationInfo['to'] ?? 0 }} 
                            of {{ $paginationInfo['total'] ?? 0 }} results
                        </span>
                        <button class="btn btn-sm btn-orange" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="position-relative">
                    <div class="loading-overlay" id="loading-overlay">
                        <div class="loading-spinner">
                            <div class="spinner-border text-warning"></div>
                        </div>
                    </div>
                    
                    <!-- INLINE TABLE -->
                    <div id="logs-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user me-1"></i>Username</th>
                                        <th><i class="fas fa-user-tag me-1"></i>Role</th>
                                        <th><i class="fas fa-activity me-1"></i>Action</th>
                                        <th><i class="fas fa-clock me-1"></i>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <span>{{ $log->user ? ($log->user->username ?? 'Unknown') : 'System' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($log->user)
                                                    @if ($log->user->is_superuser)
                                                        <span class="badge bg-danger rounded-pill">Admin</span>
                                                    @elseif ($log->user->userProfile && $log->user->userProfile->role === 'admin')
                                                        <span class="badge bg-danger rounded-pill">Admin</span>
                                                    @elseif ($log->user->userProfile && $log->user->userProfile->role === 'manager')
                                                        <span class="badge bg-warning rounded-pill">Manager</span>
                                                    @else
                                                        <span class="badge bg-info rounded-pill">User</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-dark rounded-pill">System</span>
                                                @endif
                                            </td>
                                            <td>{{ $log->action }}</td>
                                            <td>
                                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No logs found</h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- INLINE PAGINATION -->
                <div id="pagination-container" class="mt-4">
                    @if ($logs->hasPages())
                        <nav class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                            </small>
                            <ul class="pagination pagination-orange mb-0">
                                @if ($logs->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-angle-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->previousPageUrl() }}">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                @endif
                                
                                @php
                                    $start = max(1, $logs->currentPage() - 2);
                                    $end = min($logs->lastPage(), $logs->currentPage() + 2);
                                @endphp
                                
                                @for ($page = $start; $page <= $end; $page++)
                                    @if ($page == $logs->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $logs->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endfor
                                
                                @if ($logs->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->nextPageUrl() }}">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-angle-right"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const elements = {
            username: document.getElementById('username-search'),
            role: document.getElementById('role-filter'),
            perPage: document.getElementById('per-page'),
            tableBody: document.getElementById('table-body'),
            pagination: document.getElementById('pagination-container'),
            resultsInfo: document.getElementById('results-info'),
            loading: document.getElementById('loading-overlay'),
            activeFilters: document.getElementById('active-filters')
        };

        const debounce = (func, wait) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        };

        const updateFilters = () => {
            const filters = [];
            if (elements.username.value) filters.push('Username: ' + elements.username.value);
            if (elements.role.value) filters.push('Role: ' + elements.role.value);
            elements.activeFilters.innerHTML = filters.map(f => '<span class="filter-badge">' + f + '</span>').join('');
        };

        const fetchLogs = (page = 1) => {
            console.log('Fetching logs for page:', page); // Debug log
            
            elements.loading.style.display = 'block';
            updateFilters();

            const params = new URLSearchParams({
                username: elements.username.value,
                role: elements.role.value,
                per_page: elements.perPage.value,
                page: page,
                _token: '{{ csrf_token() }}'
            });

            console.log('Request params:', params.toString()); // Debug log

            axios.get('{{ route('activity_logs.index') }}?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                console.log('Response received:', response.data); // Debug log
                
                // Update table body
                elements.tableBody.innerHTML = response.data.html;
                
                // Update pagination - IMPORTANT: This updates the pagination HTML
                updatePaginationHTML(response.data.pagination_info);
                
                // Update results info
                const info = response.data.pagination_info;
                elements.resultsInfo.innerHTML = 
                    '<div class="d-flex justify-content-end">' +
                    '<button class="btn btn-sm btn-orange" onclick="clearFilters()"><i class="fas fa-times me-1"></i>Clear Filters</button>' +
                    '</div>';
            })
            .catch(error => {
                console.error('Error:', error);
                elements.tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>';
            })
            .finally(() => {
                elements.loading.style.display = 'none';
            });
        };

        // NEW FUNCTION: Update pagination HTML dynamically
        const updatePaginationHTML = (paginationInfo) => {
            if (paginationInfo.last_page <= 1) {
                elements.pagination.innerHTML = '';
                return;
            }
            
            const currentPage = paginationInfo.current_page;
            const lastPage = paginationInfo.last_page;
            const baseUrl = '{{ route('activity_logs.index') }}';
            
            let paginationHTML = '<nav class="d-flex justify-content-between align-items-center">';
            paginationHTML += '<small class="text-muted">Page ' + currentPage + ' of ' + lastPage + '</small>';
            paginationHTML += '<ul class="pagination pagination-orange mb-0">';
            
            // Previous button
            if (currentPage === 1) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>';
            } else {
                const prevParams = new URLSearchParams({
                    username: elements.username.value,
                    role: elements.role.value,
                    per_page: elements.perPage.value,
                    page: currentPage - 1
                });
                paginationHTML += '<li class="page-item"><a class="page-link" href="' + baseUrl + '?' + prevParams.toString() + '"><i class="fas fa-angle-left"></i></a></li>';
            }
            
            // Page numbers
            const start = Math.max(1, currentPage - 2);
            const end = Math.min(lastPage, currentPage + 2);
            
            for (let page = start; page <= end; page++) {
                const pageParams = new URLSearchParams({
                    username: elements.username.value,
                    role: elements.role.value,
                    per_page: elements.perPage.value,
                    page: page
                });
                
                if (page === currentPage) {
                    paginationHTML += '<li class="page-item active"><span class="page-link">' + page + '</span></li>';
                } else {
                    paginationHTML += '<li class="page-item"><a class="page-link" href="' + baseUrl + '?' + pageParams.toString() + '">' + page + '</a></li>';
                }
            }
            
            // Next button
            if (currentPage === lastPage) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>';
            } else {
                const nextParams = new URLSearchParams({
                    username: elements.username.value,
                    role: elements.role.value,
                    per_page: elements.perPage.value,
                    page: currentPage + 1
                });
                paginationHTML += '<li class="page-item"><a class="page-link" href="' + baseUrl + '?' + nextParams.toString() + '"><i class="fas fa-angle-right"></i></a></li>';
            }
            
            paginationHTML += '</ul></nav>';
            
            elements.pagination.innerHTML = paginationHTML;
        };

        const clearFilters = () => {
            elements.username.value = '';
            elements.role.value = '';
            elements.perPage.value = '20';
            fetchLogs(1);
        };

        elements.username.addEventListener('input', debounce(() => fetchLogs(1), 300));
        elements.role.addEventListener('change', () => fetchLogs(1));
        elements.perPage.addEventListener('change', () => fetchLogs(1));

        document.addEventListener('click', (e) => {
        // Check if clicked element or its parent is a pagination link
        let target = e.target;
        
        // Handle clicks on icons inside pagination links
        if (target.tagName === 'I' && target.closest('.pagination a')) {
            target = target.closest('.pagination a');
        }
        
        // Check if it's a pagination link
        if (target.matches('.pagination a') || target.closest('.pagination a')) {
            e.preventDefault();
            
            // Get the actual link element
            const link = target.matches('.pagination a') ? target : target.closest('.pagination a');
            
            console.log('Pagination link clicked:', link.href); // Debug log
            
            try {
                // Extract page number from URL
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || 1;
                
                console.log('Extracted page:', page); // Debug log
                
                // Call fetchLogs with the page number
                fetchLogs(parseInt(page));
            } catch (error) {
                console.error('Error parsing pagination URL:', error);
                // Fallback: try to extract page from href manually
                const href = link.getAttribute('href');
                const pageMatch = href.match(/[?&]page=(\d+)/);
                const page = pageMatch ? pageMatch[1] : 1;
                fetchLogs(parseInt(page));
            }
        }
    });

        // Initialize
        updateFilters();
    </script>
</body>
</html>