<html lang="en" webcrx="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRDP Performance Dashboard</title>

    <link rel="icon" type="image/x-icon" href="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- CSV Parser -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"
        integrity="sha512-dfX5uYVXzyU8+KHqj8bjo7UkOdg18PaOtpa48djpNbZHwExddghZ+ZmzWT06R5v6NSk3ZUfsH6FNEDepLx9hPQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="https://geomapping.da.gov.ph/prdp/assets/css/sidlan.css?v=1753948749162">

    @livewireStyles
    @vite(['resources/js/app.js'])
</head>

<body style="min-height: 100%;" class="" cz-shortcut-listen="true">

    <div class="container py-5 min-h-100 position-relative">
        <div class="d-flex justify-content-center">
            <img class="drop-shadow" src="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png" alt=""
                style="max-width: 130px;">
        </div>
        <h1 class="chart-page-title">

            PRDP Scale-Up I-REAP Management Dashboard
            <span class="d-block text-center fs-6 d-none">As of <span class="text-warnings"
                    id="asof">--</span></span>
        </h1>


        <!-- portfolio -->
        <div class="section-title ps-lg-2 d-flex flex-column flex-lg-row justify-content-between align-items-start">
            Our Portfolio
            <a href="{{ route('sidlan.ireap.d2-portfolio') }}" target="_blank"
                class="btn btn-primary d-none d-lg-block dashboard-redirect">View Detailed Data</a>
        </div>

        <div class="row d-block d-lg-none">
            <div class="col-12">
                <a href="{{ route('sidlan.ireap.d2-portfolio') }}" target="_blank"
                    class="btn btn-primary btn-sm my-2">View Detailed Data</a>
            </div>
        </div>


        <livewire:sidlan.ireap.section-1 :irZeroOneData="$irZeroOneData" lazy />
        <!-- <livewire:sidlan.ireap.section-2  lazy />
        <livewire:sidlan.ireap.section-3  lazy /> -->
    </div>

    @livewireScripts

</body>

</html>
