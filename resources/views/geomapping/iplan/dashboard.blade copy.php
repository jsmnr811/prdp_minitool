<html lang="en" webcrx="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRDP Performance Dashboard</title>

    <link rel="icon" type="image/x-icon" href="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            padding-bottom: 60px;
            /* space for footer */

        }

        #map {
            min-height: 600px;
            height: 100%;
            width: 100%;
            border-radius: 0.5rem;
        }

        .search-results {
            max-height: 200px;
            overflow-y: auto;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 60px;
            background-color: #343a40;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
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
            border: 2px solid #0D6EFD ;
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
    </style>

    @livewireStyles
    @vite(['resources/js/app.js'])
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">PRDP Dashboard</a>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <livewire:geomapping.iplan.map lazy />
        @livewireScripts
        <!-- in <head> -->

    </div>
    <!-- FOOTER -->
    <footer class="footer">
        <span class="small">© {{ now()->year }} PRDP Dashboard. All rights reserved.</span>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>
