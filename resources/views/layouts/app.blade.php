<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'College Student Information System') }}</title>

    <!-- Local Offline Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Local Offline Bootstrap Icons CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --sis-primary: #1e3a8a;
            --sis-primary-light: #3b82f6;
            --sis-sidebar-bg: #0f172a;
            --sis-sidebar-hover: #1e293b;
            --sis-sidebar-active: #2563eb;
            --sis-sidebar-text: #94a3b8;
            --sis-sidebar-text-active: #ffffff;
            --sis-body-bg: #f8fafc;
            --sis-card-border: #e2e8f0;
        }

        body {
            background-color: var(--sis-body-bg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sis-sidebar-bg);
            min-height: 100vh;
            color: var(--sis-sidebar-text);
            transition: all 0.3s;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar .brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .sidebar .brand i {
            font-size: 1.6rem;
            color: #60a5fa;
        }

        .sidebar-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 1rem 1.5rem 0.4rem;
            font-weight: 600;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0.5rem 0.75rem;
            margin: 0;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 0.25rem;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            color: var(--sis-sidebar-text);
            border-radius: 0.5rem;
            font-size: 0.92rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.15rem;
            width: 1.4rem;
            text-align: center;
        }

        .sidebar-nav .nav-link:hover {
            color: #f1f5f9;
            background-color: var(--sis-sidebar-hover);
        }

        .sidebar-nav .nav-link.active {
            color: var(--sis-sidebar-text-active);
            background-color: var(--sis-sidebar-active);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        /* Main Content Layout */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--sis-card-border);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-body {
            padding: 1.75rem;
            flex: 1;
        }

        /* Card and Table Styling */
        .card {
            border: 1px solid var(--sis-card-border);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            background: #ffffff;
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--sis-card-border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }

        .table tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.92rem;
        }

        .badge-subtle {
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 0.375rem;
        }

        /* Print formatting */
        @media print {
            .sidebar, .top-navbar, .no-print, .btn, .pagination {
                display: none !important;
            }
            .main-wrapper {
                margin-left: 0 !important;
            }
            .content-body {
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: -260px;
            }
            .sidebar.show {
                margin-left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <i class="bi bi-mortarboard-fill"></i>
            <span>{{ config('app.name', 'College SIS') }}</span>
        </a>

        <div class="sidebar-section-title">Overview</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <div class="sidebar-section-title">Academic Structure</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('programs.index') }}" class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}">
                        <i class="bi bi-award"></i>
                        <span>Academic Programs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('terms.index') }}" class="nav-link {{ request()->routeIs('terms.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Academic Terms</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Classrooms</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">People & Registry</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('instructors.index') }}" class="nav-link {{ request()->routeIs('instructors.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Faculty Instructors</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">Curriculum & Scheduling</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        <span>Courses Catalog</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span>Class Sections</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('enrollments.index') }}" class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
                        <i class="bi bi-card-checklist"></i>
                        <span>Enrollments</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-title">Administration</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="{{ route('admin.imports.index') }}" class="nav-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Bulk Import Hub</span>
                    </a>
                </li>
            </ul>
        @endif

        @if(auth()->user()->isInstructor())
            <div class="sidebar-section-title">Teaching Portal</div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="{{ route('sections.index') }}" class="nav-link {{ request()->routeIs('sections.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>My Assigned Classes</span>
                    </a>
                </li>
            </ul>
        @endif

        @if(auth()->user()->isStudent())
            <div class="sidebar-section-title">Student Portal</div>
            <ul class="sidebar-nav">
                @if(auth()->user()->student)
                    <li class="nav-item">
                        <a href="{{ route('student.registration') }}" class="nav-link {{ request()->routeIs('student.registration*') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square"></i>
                            <span>Course Registration</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('student.schedule') }}" class="nav-link {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                            <i class="bi bi-calendar3-week"></i>
                            <span>My Class Schedule</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('students.transcript', auth()->user()->student) }}" class="nav-link {{ request()->routeIs('students.transcript') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-medical"></i>
                            <span>Academic Transcript</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        <div class="sidebar-section-title">Account</div>
        <ul class="sidebar-nav mb-4">
            <li class="nav-item">
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>
                    <span>Profile Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                    @csrf
                    <a href="javascript:void(0)" onclick="document.getElementById('sidebar-logout-form').submit();" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log Out</span>
                    </a>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navigation -->
        <header class="top-navbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
                        <i class="bi bi-database-check me-1"></i> PostgreSQL 18 Ready
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold d-none d-md-inline-block">
                        <i class="bi bi-wifi-off me-1"></i> Offline Local Network
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 px-3 py-1 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-semibold text-truncate" style="max-width: 140px; font-size: 0.88rem;">{{ auth()->user()->name }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">{{ auth()->user()->role }}</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person text-secondary"></i> Profile Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Main Body -->
        <main class="content-body">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="mt-auto py-3 px-4 bg-white border-top text-center text-muted text-sm d-flex justify-content-between align-items-center">
            <small>&copy; {{ date('Y') }} {{ config('app.name', 'College SIS') }} - Enterprise Student Information System</small>
            <small class="text-secondary">v2.0 • Offline Local Network Edition</small>
        </footer>
    </div>

    <!-- Local Offline Bootstrap 5.3 Bundle JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
