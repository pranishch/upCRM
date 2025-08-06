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
        * {
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden; /* Prevent horizontal scroll on body */
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e8ecef 100%);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        table, th, td, .table input, .table select, .table textarea {
            font-family: 'Arial', sans-serif;
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
            margin-left:10px;; /* Remove margins */
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
            padding: 0.75rem;
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
            width: 14%; /* Increased from 11% to 14% */
            min-width: 110px;
        }
        #allCallbacksTable .table th:nth-child(2), #allCallbacksTable .table td:nth-child(2) { /* Phone Number */
            width: 9%;
            min-width: 90px;
        }
        #allCallbacksTable .table th:nth-child(3), #allCallbacksTable .table td:nth-child(3) { /* Email */
            width: 11%;
            min-width: 110px;
        }
        #allCallbacksTable .table th:nth-child(4), #allCallbacksTable .table td:nth-child(4) { /* Address */
            width: 11%;
            min-width: 110px;
        }
        #allCallbacksTable .table th:nth-child(5), #allCallbacksTable .table td:nth-child(5) { /* Website */
            width: 9%;
            min-width: 90px;
        }
        #allCallbacksTable .table th:nth-child(6), #allCallbacksTable .table td:nth-child(6) { /* Remarks */
            width: 9%;
            min-width: 90px;
        }
        #allCallbacksTable .table th:nth-child(7), #allCallbacksTable .table td:nth-child(7) { /* Notes */
            width: 9%;
            min-width: 90px;
        }
        #allCallbacksTable .table th:nth-child(8), #allCallbacksTable .table td:nth-child(8) { /* Assigned Manager */
            width: 10%;
            min-width: 110px;
        }
        #allCallbacksTable .table th:nth-child(9), #allCallbacksTable .table td:nth-child(9) { /* Created By */
            width: 7%;
            min-width: 90px;
        }
        #allCallbacksTable .table th:nth-child(10), #allCallbacksTable .table td:nth-child(10) { /* Actions */
            width: 11%;
            min-width: 150px;
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
            .main-controller {
                padding: 1rem;
            }
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
            .navbar {
                padding: 0.5rem 1rem;
            }
            .navbar-brand {
                font-size: 0.9rem;
            }
            .table {
                min-width: 1000px; /* Ensure table is wide enough to show all columns */
            }
            #allCallbacksTable .table th,
            #allCallbacksTable .table td {
                font-size: 0.7rem; /* Reduced from 0.75rem for smaller text */
                padding: 0.25rem 0.4rem; /* Reduced from 0.3rem for compact cells */
            }
            #allCallbacksTable .table th:nth-child(1), #allCallbacksTable .table td:nth-child(1) { /* Customer Name */
                width: 14%; /* Increased from 9% to 14% */
                min-width: 80px;
            }
            #allCallbacksTable .table th:nth-child(2), #allCallbacksTable .table td:nth-child(2) { /* Phone Number */
                width: 8%;
                min-width: 70px;
            }
            #allCallbacksTable .table th:nth-child(3), #allCallbacksTable .table td:nth-child(3) { /* Email */
                width: 7%;
                min-width: 60px;
            }
            #allCallbacksTable .table th:nth-child(4), #allCallbacksTable .table td:nth-child(4) { /* Address */
                width: 9%;
                min-width: 80px;
            }
            #allCallbacksTable .table th:nth-child(5), #allCallbacksTable .table td:nth-child(5) { /* Website */
                width: 8%;
                min-width: 70px;
            }
            #allCallbacksTable .table th:nth-child(6), #allCallbacksTable .table td:nth-child(6) { /* Remarks */
                width: 8%;
                min-width: 70px;
            }
            #allCallbacksTable .table th:nth-child(7), #allCallbacksTable .table td:nth-child(7) { /* Notes */
                width: 8%;
                min-width: 70px;
            }
            #allCallbacksTable .table th:nth-child(8), #allCallbacksTable .table td:nth-child(8) { /* Assigned Manager */
                width: 2%;
                min-width: 20px;
            }
            #allCallbacksTable .table th:nth-child(9), #allCallbacksTable .table td:nth-child(9) { /* Created By */
                width: 6%;
                min-width: 70px;
            }
            #allCallbacksTable .table th:nth-child(10), #allCallbacksTable .table td:nth-child(10) { /* Actions */
                width: 9%;
                min-width: 80px;
            }
            #allCallbacksTable .table-responsive {
                overflow-x: auto; /* Enable horizontal scrolling */
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
                max-height: 250px; /* Reduced from 300px for compact view */
            }
            .search-bar input,
            .search-bar select {
                font-size: 0.75rem; /* Reduced from 0.8rem */
                padding: 0.3rem; /* Reduced from 0.4rem */
            }
            .action-buttons .btn-action {
                padding: 0.1rem 0.2rem; /* Reduced from 0.15rem 0.3rem */
                font-size: 0.6rem; /* Reduced from 0.65rem */
            }
            .modal-dialog {
                margin: 0.2rem; /* Reduced from 0.3rem */
            }
            .toast {
                font-size: 0.75rem; /* Reduced from 0.8rem */
            }
        }
        @media (max-width: 360px) {
            .main-container {
                width: 100vw;             /* Full viewport width */
                margin-left: -0.52rem;           /* Remove left margin */
                margin-right: 0;          /* Remove right margin */
                margin-top: 0.5rem;       /* Keep top margin */
                margin-bottom: 0.5rem;    /* Keep bottom margin */
                padding: 0.5rem;          /* Inner spacing */
                box-sizing: border-box;   /* Prevent overflow */
                border-radius: 2px;
            }
            .navbar {
                padding: 0.3rem 0.6rem; /* Reduced navbar padding */
                flex-wrap: nowrap; /* Prevent navbar items from wrapping */
                overflow-x: auto; /* Allow horizontal scrolling if needed */
                white-space: nowrap; /* Keep items in a single line */
            }
            .navbar-brand {
                font-size: 0.75rem; /* Smaller navbar brand font */
            }
            .navbar-toggler {
                padding: 0.2rem 0.4rem; /* Smaller toggler padding */
                font-size: 0.7rem; /* Smaller toggler icon */
            }
            .navbar-nav {
                gap: 0.2rem; /* Further reduced gap between nav items */
                flex-direction: row; /* Keep nav items in a row */
                align-items: center; /* Align items vertically */
            }
            .nav-item.dropdown .nav-link {
                font-size: 0.65rem; /* Smaller font size for Quick Actions */
                padding: 0.2rem 0.4rem; /* Smaller padding for dropdown toggle */
            }
            .dropdown-menu {
                font-size: 0.6rem; /* Smaller font size for dropdown items */
                min-width: 120px; /* Smaller dropdown width */
            }
            .dropdown-item {
                padding: 0.15rem 0.5rem; /* Smaller padding for Manage Users/Manage Managers */
            }
            .profile-link {
                padding: 0.15rem 0.3rem; /* Much smaller padding for profile link */
                font-size: 0.6rem; /* Much smaller font size */
            }
            .profile-anchor .username {
                font-size: 0.6rem; /* Much smaller username font */
            }
            .profile-anchor .bi-person-circle {
                font-size: 0.8rem; /* Much smaller icon size for profile link */
            }
            .btn-logout {
                padding: 0.08rem 0.3rem; /* Much smaller logout button padding */
                font-size: 0.5rem; /* Much smaller logout button font */
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); /* Lighter shadow for smaller size */
                display: inline-block; /* Ensure visibility */
                margin-left: 0.15rem; /* Much smaller margin for spacing */
            }
            .username {
                font-size: 0.75rem; /* Small text (around 12px) */
                max-width: 80px;           /* Limit the width of the box */
                overflow: hidden;          /* Hide overflow text */
                text-overflow: ellipsis;   /* Add ... if text too long */
                white-space: nowrap;       /* Prevent wrapping */
                display: inline-block;
            }

            /* Make logout button smaller */
            #logoutBtn {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
            /* Shrink the container of the username */
            .profile-link {
                padding: 0.2px 0.3px;
            }

            /* Shrink the full profile button (icon + username) */
            .profile-anchor {
                padding: 0.2px 0.3px;
                border-radius: 0.2px;
            
            }
            /* Adjust profile icon if needed */
            .profile-anchor i {
                font-size: 0.01rem !important;
            }

            /* Manager username or select input inside table */
            .manager-select,
            td span {
                font-size: 0.7rem !important;
            }

            /* Optional: Adjust spacing if too tight */
            .navbar-nav .me-3 {
                margin-right: 0.5rem !important;
            }
            h1, h4 {
                font-size: 0.95rem; /* Smaller heading sizes */
            }
            .system-overview {
                gap: 0.5rem; /* Smaller gap between cards */
            }
            .system-overview .card {
                padding: 0.4rem; /* Reduced card padding */
                margin-bottom: 0.5rem; /* Smaller margin */
                border-radius: 6px; /* Smaller border radius */
            }
            .system-overview .card-header {
                padding: 0.1rem 0.2rem; /* Smaller header padding */
                font-size: 0.6rem; /* Smaller header font */
            }
            .system-overview .card-body {
                padding: 0.3rem; /* Smaller body padding */
            }
            .system-overview .card-title {
                font-size: 0.7rem; /* Smaller title font for Total Managers */
                margin-bottom: 0.3rem; /* Smaller margin */
            }
            .system-overview .btn-primary {
                padding: 0.15rem 0.3rem; /* Smaller button padding */
                font-size: 0.6rem; /* Smaller button font */
            }
            .table {
                min-width: 800px; /* Slightly reduced min-width for table */
            }
            .table th {
                font-size: 0.55rem; /* Reduced font size for table headings */
                padding: 0.2rem 0.3rem; /* Reduced padding */
            }
            .table td {
                font-size: 0.5rem; /* Reduced font size for table data */
                padding: 0.2rem 0.3rem; /* Reduced padding */
            }
            #allCallbacksTable .table-responsive {
                max-height: 180px; /* Smaller table height */
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            #allCallbacksTable .table th:nth-child(1), #allCallbacksTable .table td:nth-child(1) { /* Customer Name */
                width: 14%; /* Increased from 9% to 14% */
                min-width: 60px;
            }
            #allCallbacksTable .table th:nth-child(2), #allCallbacksTable .table td:nth-child(2) { /* Phone Number */
                width: 8%;
                min-width: 55px;
            }
            #allCallbacksTable .table th:nth-child(3), #allCallbacksTable .table td:nth-child(3) { /* Email */
                width: 7%;
                min-width: 45px;
            }
            #allCallbacksTable .table th:nth-child(4), #allCallbacksTable .table td:nth-child(4) { /* Address */
                width: 9%;
                min-width: 60px;
            }
            #allCallbacksTable .table th:nth-child(5), #allCallbacksTable .table td:nth-child(5) { /* Website */
                width: 8%;
                min-width: 55px;
            }
            #allCallbacksTable .table th:nth-child(6), #allCallbacksTable .table td:nth-child(6) { /* Remarks */
                width: 8%;
                min-width: 55px;
            }
            #allCallbacksTable .table th:nth-child(7), #allCallbacksTable .table td:nth-child(7) { /* Notes */
                width: 8%;
                min-width: 55px;
            }
            #allCallbacksTable .table th:nth-child(8), #allCallbacksTable .table td:nth-child(8) { /* Assigned Manager */
                width: 2%;
                min-width: 12px;
            }
            #allCallbacksTable .table th:nth-child(9), #allCallbacksTable .table td:nth-child(9) { /* Created By */
                width: 6%;
                min-width: 50px;
            }
            #allCallbacksTable .table th:nth-child(10), #allCallbacksTable .table td:nth-child(10) { /* Actions */
                width: 9%;
                min-width: 60px;
            }
            .action-buttons .btn-action {
                padding: 0.1rem 0.2rem; /* Smaller action button padding */
                font-size: 0.5rem; /* Smaller font size */
            }
            .search-bar {
                flex-direction:row;
                gap: 0.1rem; /* Reduced from 0.3rem */
                max-width: 160px !important;
                margin-bottom: 0.01rem;
                align-items: stretch;
            }
            .search-bar input,
            .search-bar select {
                font-size: 0.55rem; /* Smaller font size */
                padding: 0.15rem 0.1rem; /* Smaller padding */
                height: 1.6rem;
                line-height: 1;
            }
            .search-bar select {
                width: 100%; /* Full width for select */
            }
            #pagination {
                gap: 0.3rem; /* Smaller gap between pagination items */
            }
            #pagination button,
            #pagination span.page-num {
                padding: 0.15rem 0.4rem; /* Smaller pagination padding */
                font-size: 0.6rem; /* Smaller font size */
                min-width: 20px; /* Smaller minimum width */
            }
            .modal-dialog {
                margin: 0.1rem; /* Smaller modal margin */
                max-width: 95%; /* Slightly smaller modal width */
            }
            .modal-content {
                border-radius: 6px; /* Smaller border radius */
            }
            .modal-header {
                padding: 0.3rem 0.5rem; /* Smaller header padding */
            }
            .modal-title {
                font-size: 0.85rem; /* Smaller modal title */
            }
            .modal-body {
                padding: 0.5rem; /* Smaller body padding */
            }
            .modal-footer {
                padding: 0.3rem; /* Smaller footer padding */
            }
            .modal-footer .btn {
                padding: 0.2rem 0.4rem; /* Smaller button padding */
                font-size: 0.65rem; /* Smaller font size */
            }
            .form-control, .form-select {
                font-size: 0.65rem; /* Smaller input/select font */
                padding: 0.2rem; /* Smaller padding */
            }
            .toast {
                font-size: 0.65rem; /* Smaller toast font */
                min-width: 200px; /* Smaller toast width */
            }
            .toast-container {
                bottom: 10px; /* Closer to bottom */
                right: 10px; /* Closer to right */
            }
            .role-badge {
                font-size: 0.5rem; /* Smaller font size for role badge (e.g., admin) */
                padding: 0.15rem 0.4rem; /* Smaller padding for role badge */
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
            background-color: orange;
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
        .toast {
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .profile-link {
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .profile-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-anchor {
            color: #fff;
            transition: color 0.3s ease;
        }

        .profile-anchor:hover {
            color: var(--primary-color);
        }

        .profile-anchor .username {
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .role-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-weight: 700;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .role-admin {
            background-color: var(--admin-color);
            color: white;
        }

        .role-badge:hover {
            transform: scale(1.1);
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
                   <div class="profile-link me-3 d-flex align-items-center">
                        <a href="#" class="profile-anchor d-flex align-items-center text-decoration-none" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="bi bi-person-circle me-2" style="font-size: 1.5rem; color: #fff;"></i>
                            <span class="username text-white fw-semibold">{{ Auth::user()->username }}</span>
                        </a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-logout" id="logoutBtn" aria-label="Logout">Logout</button>
                    </form>
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
                                @forelse ($all_callbacks as $index => $callback)
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
                                                    <select class="manager-select"
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

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="profileForm">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <button type="button" class="toggle-password position-absolute end-0 top-50 translate-middle-y me-2" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            <button type="button" class="toggle-password position-absolute end-0 top-50 translate-middle-y me-2" data-target="password_confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="errorMessages" class="text-danger" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveProfile">Save Changes</button>
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
                const customerName = row.querySelector('td:nth-child(2)').textContent;
                showDeleteModal(callbackId, customerName);
            }
        });

        function toggleEditMode(row, isEditMode) {
        // Exclude only Actions and Assigned Manager (if admin)
            const cells = row.querySelectorAll('td:not(:last-child):not(:nth-last-child(2)){!! $user_role == 'admin' ? ':not(:nth-child(8))' : '' !!}');        if (isEditMode) {
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
        // Select all editable cells except Actions and Assigned Manager (if admin)
            const cells = row.querySelectorAll('td:not(:last-child):not(:nth-last-child(2)){!! $user_role == 'admin' ? ':not(:nth-child(8))' : '' !!}');        const data = {
            callback_id: callbackId,
            customer_name: cells[0].querySelector('input')?.value.trim() || '',
            phone_number: cells[1].querySelector('input')?.value.trim() || '',
            email: cells[2].querySelector('input')?.value.trim() || null,
            address: cells[3].querySelector('input')?.value.trim() || null,
            website: cells[4].querySelector('input')?.value.trim() || null,
            remarks: cells[5].querySelector('select')?.value || null,
            notes: cells[6].querySelector('input')?.value.trim() || null
        };

        // Validate required fields client-side
        if (!data.customer_name || !data.phone_number) {
            showToast('Customer Name and Phone Number are required.', 'danger');
            return;
        }

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
                showToast('Error: ' + (result.message || 'Failed to update callback.'), 'danger');
            }
        })
        .catch(error => {
            toggleEditMode(row, false);
            showToast('An error occurred while saving the callback: ' + error.message, 'danger');
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
    // Profile Modal: Fetch profile data
        document.getElementById('profileModal').addEventListener('show.bs.modal', function() {
            fetch('{{ route("profile") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('username').value = data.username || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('first_name').value = data.first_name || '';
                    document.getElementById('last_name').value = data.last_name || '';
                    document.getElementById('password').value = '';
                    document.getElementById('password_confirmation').value = '';
                    document.getElementById('errorMessages').style.display = 'none';
                } else {
                    showToast(data.message || 'Failed to load profile data.', 'danger');
                }
            })
            .catch(() => showToast('Failed to load profile data.', 'danger'));
        });

        // Profile Modal: Save profile changes
        document.getElementById('saveProfile').addEventListener('click', function() {
            const form = document.getElementById('profileForm');
            const formData = new FormData(form);
            // Ensure CSRF token is included
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('{{ route("profile.update") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
                    showToast(result.message, 'success');
                    // Update username in the navbar
                    document.querySelector('.text-light span').textContent = formData.get('username');
                } else {
                    let errorHtml = '';
                    for (let field in result.errors) {
                        errorHtml += `<p>${result.errors[field][0]}</p>`;
                    }
                    document.getElementById('errorMessages').innerHTML = errorHtml;
                    document.getElementById('errorMessages').style.display = 'block';
                }
            })
            .catch(() => showToast('An error occurred while saving the profile.', 'danger'));
        });

        // Password Visibility Toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    </script>
</body>
</html>