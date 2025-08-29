<!DOCTYPE html>
<html lang="en">

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
        /* Custom styles for modern look */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ececec;
            /* Light gray background */
        }

        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #198754 100%);
            /* Green gradient for agriculture theme */
            color: white;
            padding: 8rem 0;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            text-align: center;
        }

        .feature-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            border-radius: 15px;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .search-input {
            border-radius: 50px;
            /* Pill shape for search input */
            padding: 0.75rem 1.5rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .search-button {
            border-radius: 50px;
            /* Pill shape for search button */
            padding: 0.75rem 1.5rem;
            background-color: #198754;
            /* Bootstrap success green */
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .search-button:hover {
            background-color: #218838;
            /* Darker green on hover */
        }

        .section-padding {
            padding: 4rem 0;
        }

        /* Bootstrap overrides and custom classes for responsiveness and spacing */
        .container {
            max-width: 1200px;
            /* Limit container width for better readability on large screens */
        }

        .navbar-brand {
            font-size: 1.25rem;
            /* Equivalent to text-lg */
            font-weight: 700;
            /* Equivalent to font-bold */
            color: #212529;
            /* Equivalent to text-gray-800 */
        }

        .navbar-nav .nav-item {
            margin-left: 1rem;
            /* Equivalent to space-x-4 */
        }

        .navbar-nav .nav-link {
            color: #6c757d;
            /* Equivalent to text-gray-600 */
        }

        .navbar-nav .nav-link:hover {
            color: #212529;
            /* Equivalent to hover:text-gray-900 */
        }

        .hero-section h1 {
            font-size: 3.5rem;
            /* Equivalent to text-5xl */
            font-weight: 800;
            /* Equivalent to font-extrabold */
            line-height: 1.2;
            /* Equivalent to leading-tight */
        }

        .hero-section p {
            font-size: 1.25rem;
            /* Equivalent to text-xl */
            opacity: 0.9;
            /* Equivalent to opacity-90 */
            max-width: 48rem;
            /* Equivalent to max-w-2xl */
            margin-left: auto;
            margin-right: auto;
        }

        .shadow-sm {
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }

        .py-3 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .text-green-600 {
            color: #28a745;
            /* Custom green for consistency with theme */
        }

        .text-gray-700 {
            color: #495057;
        }

        .text-sm {
            font-size: 0.875em;
        }

        .text-gray-500 {
            color: #6c757d;
        }

        .rounded-lg {
            border-radius: .3rem !important;
        }

        .shadow-md {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .text-center {
            text-align: center !important;
        }

        .mb-10 {
            margin-bottom: 2.5rem !important;
            /* Custom margin */
        }

        .w-75 {
            width: 75% !important;
        }

        .max-w-xl {
            max-width: 36rem !important;
            /* Custom max-width */
        }

        .w-full {
            width: 100% !important;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
        }

        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            /* Custom shadow */
        }

        .transition-all {
            transition: all .15s ease-in-out;
        }

        .duration-300 {
            transition-duration: .3s;
        }

        #map {
            min-height: 600px;
            height: 100%;
            width: 100%;
            border-radius: 0.5rem;
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
    </style>
    @livewireStyles
    @vite(['resources/js/app.js'])
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container mx-auto px-4">
            <a class="navbar-brand text-success" href="#">prdp-geo</a>
            {{-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
            </div> --}}
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section text-center bg-success">
        <div class="container ">
            <h1 class="mb-4">Empowering Agriculturists with Data & Insights</h1>
            <p class="mb-8">
                Find optimal locations, discover effective interventions, and enhance your agricultural productivity.
            </p>
            {{-- <div class="d-flex justify-content-center">
                <div class="input-group mb-3 w-75 max-w-xl">
                    <input type="text" class="form-control search-input"
                        placeholder="Search for agricultural insights, markets, or resources..."
                        aria-label="Search input">
                    <button class="btn search-button" type="button" id="button-addon2">Search</button>
                </div>
            </div> --}}
        </div>
    </header>

    <!-- Section for Map, Location, Commodity, and Interventions -->
    <livewire:geomapping.iplan.map />



    <!-- Call to Action Section -->
    <section class="section-padding bg-success text-white text-center">
        <div class="container">
            <h2 class="mb-4">Ready to Grow Smarter?</h2>
            <p class="lead mb-4">Join our community of progressive agriculturists today.</p>
            <button class="btn btn-light btn-lg rounded-pill px-5 py-3 shadow-lg">Get Started Now</button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="text-center text-gray-500 text-sm">
                &copy; 2025 AgriConnect. All rights reserved.
            </div>
        </div>
    </footer>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>
