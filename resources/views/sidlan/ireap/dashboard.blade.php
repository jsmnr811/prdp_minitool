<html lang="en">

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

    <style>
        /* Custom scrollbar styles for webkit browsers */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    <link rel="stylesheet" href="https://geomapping.da.gov.ph/prdp/assets/css/sidlan.css?v=1760664684387">
    @vite('resources/js/app.js')
</head>

<body style="min-height: 100%;" class="">

    <div class="container py-5 min-h-100 position-relative">
        <div class="d-flex justify-content-center">
            <img class="drop-shadow" src="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png" alt=""
                style="max-width: 130px;">
        </div>
        <h1 class="chart-page-title">
            PRDP Scale-Up I-REAP Management Dashboard
        </h1>

        <!-- portfolio -->
        <div class="section-title ps-lg-2 d-flex flex-column flex-lg-row justify-content-between align-items-start">
            Our Portfolio
            <a href="https://geomapping.da.gov.ph/prdp/sidlan/d2-portfolio" target="_blank"
                class="btn btn-primary d-none d-lg-block dashboard-redirect">View Detailed Data</a>
        </div>

        <div class="row d-block d-lg-none">
            <div class="col-12">
                <a href="https://geomapping.da.gov.ph/prdp/sidlan/d2-portfolio" target="_blank"
                    class="btn btn-primary btn-sm my-2">View Detailed Data</a>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 row-gap-4 mt-5">
            <livewire:sidlan.ireap.dashboard.sp-financing :irZeroOneData="$irZeroOneData" />

            <div class="col">

                <div class="row row-cols-1 row-gap-4 h-100">
                    <livewire:sidlan.ireap.dashboard.sp-cost-by-cluster :irZeroOneData="$irZeroOneData" />
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-gap-4 mt-4">
            <div class="col">
                <livewire:sidlan.ireap.dashboard.sp-in-pipeline-by-status />
            </div>
            <div class="col">
                <livewire:sidlan.ireap.dashboard.sp-in-pipeline-no-of-days />
            </div>
        </div>

        <div class="modal" tabindex="-1" id="pipeline-by-status-modal">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header position-relative flex-column align-items-start pb-0"
                        style="border-bottom: none;">
                        <h5 class="modal-title mb-0 fw-bold text-primary" id="modal-title">
                            I-REAP Subprojects in the Pipeline (Number of Subprojects by Status)
                        </h5>
                        <small class="text-warning fw-semibold" style="font-size: 1rem;" id="modal-subtitle">
                        </small>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive"
                            style="overflow-x: auto; min-height:50vh; max-height: 70vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #d1d5db #f9fafb;">
                            <!-- Table will be dynamically rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <div class="modal" tabindex="-1" id="pipeline-days-modal">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header position-relative flex-column align-items-start pb-0"
                        style="border-bottom: none;">
                        <h5 class="modal-title mb-0 fw-bold text-primary" id="modal-title">
                            I-REAP Subprojects in the Pipeline (No. of Days in the Current Status)
                        </h5>
                        <small class="text-warning fw-semibold" style="font-size: 1rem;" id="modal-subtitle">
                        </small>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive"
                            style="overflow-x: auto; min-height:50vh; max-height: 70vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #d1d5db #f9fafb;">
                            <!-- Table will be dynamically rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#pipeline-by-status-modal').on('hide.bs.modal', function(event) {
                    var html = `<div class="d-flex justify-content-center align-items-center" style="min-height: 50vh;"><div style="width: 70px; height: 70px;" class="spinner-border" role="status">
  <span class="visually-hidden">Loading...</span>
</div></div>`;
                    $('.table-responsive').html(html);
                });

                $('#pipeline-days-modal').on('hide.bs.modal', function(event) {
                    var html = `<div class="d-flex justify-content-center align-items-center" style="min-height: 50vh;"><div style="width: 70px; height: 70px;" class="spinner-border" role="status">
  <span class="visually-hidden">Loading...</span>
</div></div>`;
                    $('#pipeline-days-modal .table-responsive').html(html);
                });
            })
        </script>
</body>

</html>
