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
        /* Custom styles for modern, full-width layout */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            /* A subtle, modern gray */
            color: #333;
        }

        .hero-section {
            background: linear-gradient(to right, #4CAF50, #8BC34A);
            /* Fresh green gradient */
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
            /* optional */
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: snow;

            /* optional */
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
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Agri-Commodities</a>
            {{-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
            </div> --}}
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section mb-5">
        <div class="container">
            <h1>Dynamic Agricultural Planning</h1>
            <p class="lead mt-3">Pin your location to get tailored insights on commodities and interventions.</p>
        </div>
    </header>

    <!-- Main Content Section -->
    <main class="container">
        <livewire:geomapping.iplan.map-3>
    </main>

    <!-- Message Box -->
    <div id="messageBox" class="message-box d-none alert alert-success" role="alert"></div>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container p-4">
            <p class="text-center text-muted mb-0">&copy; 2025 AgriHub. All Rights Reserved.</p>
        </div>
    </footer>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> --}}
</body>

</html>
