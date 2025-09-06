<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri Commodities Map</title>
    <link rel="icon" type="image/x-icon" href="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    @livewireStyles
    @vite(['resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7f0 0%, #e8f2e8 25%, #f0e8e0 50%, #e8f0e8 75%, #f0f2e8 100%);
            color: #2d3748;
        }

        .hero-section {
            background: linear-gradient(135deg, #35783E 0%, #2d6232 50%, #1f4a24 100%);
            color: white;
            padding: 5rem 0;
            text-align: center;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            font-weight: 700;
            color: #35783E !important;
        }

        .navbar-brand:hover {
            color: #2d6232 !important;
        }

        .card-panel {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(53, 120, 62, 0.1);
            border: 1px solid rgba(53, 120, 62, 0.05);
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #35783E;
            box-shadow: 0 0 0 3px rgba(53, 120, 62, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #35783E 0%, #2d6232 100%);
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(53, 120, 62, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2d6232 0%, #1f4a24 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(53, 120, 62, 0.4);
        }

        /* Override Bootstrap default button colors */
        .btn-primary {
            background: linear-gradient(135deg, #35783E 0%, #2d6232 100%) !important;
            border-color: #35783E !important;
            border-radius: 0.5rem;
            box-shadow: 0 4px 15px rgba(53, 120, 62, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2d6232 0%, #1f4a24 100%) !important;
            border-color: #2d6232 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(53, 120, 62, 0.4);
        }

        .btn-primary:focus,
        .btn-primary:active {
            background: linear-gradient(135deg, #2d6232 0%, #1f4a24 100%) !important;
            border-color: #2d6232 !important;
            box-shadow: 0 0 0 3px rgba(53, 120, 62, 0.25) !important;
        }

        .btn-outline-primary {
            color: #35783E !important;
            border-color: #35783E !important;
            background-color: transparent !important;
        }

        .btn-outline-primary:hover {
            background-color: #35783E !important;
            border-color: #35783E !important;
            color: white !important;
        }

        .text-primary {
            color: #35783E !important;
        }

        .border-primary {
            border-color: #35783E !important;
        }

        .bg-primary {
            background: linear-gradient(135deg, #35783E 0%, #2d6232 100%) !important;
        }

        /* Breadcrumb link styling */
        .breadcrumb-item a {
            color: #35783E !important;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #2d6232 !important;
            text-decoration: underline;
        }

        .breadcrumb-item.active {
            color: #2d3748 !important;
            font-weight: 600;
        }

        /* Custom checkbox styling */
        .form-check-input {
            border-color: #35783E !important;
        }

        .form-check-input:checked {
            background-color: #35783E !important;
            border-color: #35783E !important;
        }

        .form-check-input:focus {
            border-color: #35783E !important;
            box-shadow: 0 0 0 0.25rem rgba(53, 120, 62, 0.25) !important;
        }

        #map {
            height: 500px;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(53, 120, 62, 0.15);
            border: 2px solid rgba(53, 120, 62, 0.1);
        }

        .message-box {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            min-width: 300px;
        }

        .list-group-item {
            border: none;
            padding: 0.75rem 1rem;
        }

        .list-group-item:first-child {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .list-group-item:last-child {
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .custom-marker-icon {
            background: transparent;
            border: none;
        }

        .marker-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #35783E;
            box-shadow: 0 4px 12px rgba(53, 120, 62, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            transition: all 0.3s ease;
        }

        .marker-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(53, 120, 62, 0.4);
        }

        .marker-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .disable-controls {
            pointer-events: none;
            opacity: 0.6;
            user-select: none;
        }

        .disable-controls input,
        .disable-controls select,
        .disable-controls textarea,
        .disable-controls button {
            pointer-events: none;
            background-color: #e9ecef;
            color: #6c757d;
        }

        .checkbox-columns {
            column-count: 3;
            column-gap: 1rem;
        }

        .checkbox-columns .form-check {
            break-inside: avoid;
            margin-bottom: 0.75rem;
        }

        /* DataTable styling */
        .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 0.875rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .dataTables_length select:focus {
            border-color: #35783E;
            box-shadow: 0 0 0 0.2rem rgba(53, 120, 62, 0.25);
            outline: none;
        }

        .dataTables_length label {
            font-weight: 500;
            color: #495057;
            margin-right: 0.5rem;
        }

        /* Table responsive scrollbar fix */
        .table-responsive {
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }

        .table-responsive:hover {
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .table-responsive:hover::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive:hover::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive:hover::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive:hover::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom py-2 py-md-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-decoration-none" href="#">
                <img src="{{ asset('media/prdp-logo.png') }}" class="me-2 me-md-3 rounded" style="height: 35px;" alt="PRDP Logo" />
                <span class="fw-bold text-primary d-none d-sm-inline">National Agri-Fishery Investment Forum</span>
                <span class="fw-bold text-primary d-sm-none">NAFIF</span>
            </a>
            @auth('geomapping')
                <div class="d-flex align-items-center ms-auto">
                    <div class="border-start ps-2 ps-md-3">
                        <livewire:geomapping.iplan.logout />
                    </div>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container-fluid my-4 mx-auto flex-grow-1" style="max-width: 1400px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 p-2 p-md-3 bg-white rounded-3 shadow-sm gap-3 gap-md-0">
            <nav aria-label="breadcrumb" class="order-2 order-md-1">
                <ol class="breadcrumb mb-0 fs-6">
                    <li class="breadcrumb-item">
                        <a href="{{ route('geomapping.iplan.landing') }}" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    @stack('breadcrumbs')
                </ol>
            </nav>
            @auth('geomapping')
                @if (Auth::guard('geomapping')->check() && Auth::guard('geomapping')->user()->role === '1')
                    <div class="dropdown order-1 order-md-2">
                        <button class="btn btn-primary dropdown-toggle d-flex align-items-center w-100 w-md-auto" type="button"
                            id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list me-2"></i>Menu
                        </button>
                        <ul class="dropdown-menu shadow-lg border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('geomapping.iplan.landing') }}">
                                    <i class="bi bi-geo-alt me-2"></i>Go to Map
                                </a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('geomapping.iplan.investment.user-list') }}">
                                    <i class="bi bi-people me-2"></i>User List
                                </a></li>
                            <li><a class="dropdown-item py-2"
                                    href="{{ route('geomapping.iplan.investment.commodity-list') }}">
                                    <i class="bi bi-box-seam me-2"></i>Commodity List
                                </a></li>
                            <li><a class="dropdown-item py-2"
                                    href="{{ route('geomapping.iplan.investment.intervention-list') }}">
                                    <i class="bi bi-gear me-2"></i>Intervention List
                                </a></li>
                        </ul>
                    </div>
                @endif
            @endauth
        </div>
        {{ $slot }}
    </main>

    <div id="messageBox" class="message-box d-none alert alert-success" role="alert"></div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container p-4">
            <div class="d-flex justify-content-center mb-3">
                <img src="{{ asset('media/Scale-Up.png') }}" alt="Scale-Up Logo" style="height: 80px;" />
            </div>
            <p class="text-center text-muted mb-0">&copy; 2025 National Agri-Fishery Investment Forum. All Rights
                Reserved.</p>
        </div>
    </footer>

    @stack('modals')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')


</body>

</html>
