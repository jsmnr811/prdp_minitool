<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRDP Performance Dashboard</title>
    <link rel="icon" type="image/x-icon" href="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- CSV Parser -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://geomapping.da.gov.ph/prdp/assets/css/sidlan.css?v=1760319035241">
</head>

<body style="min-height: 100%;">

    <div class="container py-5 min-h-100">
        <x-sidlan.portfolio-header></x-sidlan.portfolio-header>

        <livewire:sidlan.ireap.portfolio.filter />
        <livewire:sidlan.ireap.portfolio.counter :irZeroOneData="$irZeroOneData" />

        <div class="row row-cols-1 row-cols-lg-2 row-gap-4 mt-5">
            <livewire:sidlan.ireap.portfolio.sp-by-cluster :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.sp-by-type :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.approved-sp-by-stage :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.approved-sp-cost-by-stage :irZeroOneData="$irZeroOneData" />
        </div>
        <div class="row mt-4 gap-4" data-view="table">
            <livewire:sidlan.ireap.portfolio.summary-by-sp-type :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.list-of-sps :irZeroOneData="$irZeroOneData" />
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>