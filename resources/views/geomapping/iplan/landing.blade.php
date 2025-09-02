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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @livewireStyles
    @vite(['resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        .hero-section {
            background: linear-gradient(to right, #4CAF50, #8BC34A);
            color: white;
            padding: 5rem 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .card-panel {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
            border-radius: 0.5rem;
        }

        .btn-primary:hover {
            background-color: #43A047;
            border-color: #43A047;
        }

        #map {
            height: 500px;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
            border: 2px solid #228E3B;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: snow;
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
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">National Agri-Fishery Investment Forum</a>

            @auth('geomapping')
            <div class="d-flex align-items-center gap-3 ms-auto">
                @if(Auth::guard('geomapping')->check() && Auth::guard('geomapping')->user()->role == '1')
                <a href="{{ route('investment.user-list') }}" class="nav-link">Manage User</a>
                @endif

                <livewire:geomapping.iplan.logout />
            </div>
            @endauth
        </div>
    </nav>

    <main class="container-fluid my-4 mx-auto" style="max-width: 1400px;">
        <livewire:geomapping.iplan.main-map>
    </main>

    <div id="messageBox" class="message-box d-none alert alert-success" role="alert"></div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container p-4">
            <p class="text-center text-muted mb-0">&copy; 2025 National Agri-Fishery Investment Forum. All Rights Reserved.</p>
        </div>
    </footer>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>

</html>