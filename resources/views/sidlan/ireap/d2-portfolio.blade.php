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
    @vite('resources/js/app.js')

</head>

<body style="min-height: 100%;">
    <style>
        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(10px) saturate(1.2);
            box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.1);
            transition: opacity 0.6s ease-in-out, visibility 0.6s ease-in-out, transform 0.6s ease-in-out;
        }

        .loading-content {
            text-align: center;
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(5px);
        }


        .chart-icon {
            color: #dad6d6ff;
            font-size: 4rem;
            margin-bottom: 1.5rem;
            animation: bar-chart-animate 3s ease-in-out infinite;
            transform-origin: bottom center;
        }

        @keyframes bar-chart-animate {
            0% {
                transform: scaleY(0.3) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.6;
                filter: drop-shadow(0 0 10px rgba(128, 128, 128, 0.3));
            }

            10% {
                transform: scaleY(0.8) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.8;
                filter: drop-shadow(0 0 15px rgba(128, 128, 128, 0.4));
            }

            20% {
                transform: scaleY(1.2) scaleX(1);
                transform-origin: bottom center;
                opacity: 1;
                filter: drop-shadow(0 0 20px rgba(128, 128, 128, 0.5));
            }

            30% {
                transform: scaleY(0.9) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.9;
                filter: drop-shadow(0 0 18px rgba(128, 128, 128, 0.4));
            }

            40% {
                transform: scaleY(1.1) scaleX(1);
                transform-origin: bottom center;
                opacity: 1;
                filter: drop-shadow(0 0 20px rgba(128, 128, 128, 0.5));
            }

            50% {
                transform: scaleY(0.7) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.8;
                filter: drop-shadow(0 0 15px rgba(128, 128, 128, 0.4));
            }

            60% {
                transform: scaleY(1.0) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.9;
                filter: drop-shadow(0 0 18px rgba(128, 128, 128, 0.4));
            }

            70% {
                transform: scaleY(0.8) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.8;
                filter: drop-shadow(0 0 15px rgba(128, 128, 128, 0.4));
            }

            80% {
                transform: scaleY(1.05) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.9;
                filter: drop-shadow(0 0 18px rgba(128, 128, 128, 0.4));
            }

            90% {
                transform: scaleY(0.85) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.7;
                filter: drop-shadow(0 0 12px rgba(128, 128, 128, 0.3));
            }

            100% {
                transform: scaleY(0.3) scaleX(1);
                transform-origin: bottom center;
                opacity: 0.6;
                filter: drop-shadow(0 0 10px rgba(128, 128, 128, 0.3));
            }
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Initially hide the overlay */
        .loading-overlay {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }

        .loading-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.95);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .loading-content {
                padding: 1.5rem;
            }

            .chart-icon {
                font-size: 3rem;
            }

            .loading-text {
                font-size: 1.1rem;
            }
        }
    </style>
    <div class="container py-5 min-h-100">
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="loading-overlay">
            <div class="loading-content">
                <i class="fas fa-chart-column fa-3x chart-icon"></i>
            </div>
        </div>

        <x-sidlan.portfolio-header :latestTimestamp="$latestTimestamp"></x-sidlan.portfolio-header>

        <livewire:sidlan.ireap.portfolio.filter />
        <livewire:sidlan.ireap.portfolio.counter :irZeroOneData="$irZeroOneData" />

        <div class="row row-cols-1 row-cols-lg-2 row-gap-4 mt-5">
            <livewire:sidlan.ireap.portfolio.sp-by-cluster :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.sp-by-type :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.approved-sp-by-stage :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.approved-sp-cost-by-stage :irZeroOneData="$irZeroOneData" />
        </div>

        <div class="row mt-4 gap-4">
            <livewire:sidlan.ireap.portfolio.pipelined-sp-by-stage :irZeroOneData="$irZeroOneData" />
        </div>
        <div class="row mt-4 gap-4" data-view="table">
            <livewire:sidlan.ireap.portfolio.summary-by-sp-type :irZeroOneData="$irZeroOneData" />
            <livewire:sidlan.ireap.portfolio.list-of-sps :irZeroOneData="$irZeroOneData" />
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        // Loading overlay functionality
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loading-overlay');
            let componentsLoaded = 0;
            const totalComponents = document.querySelectorAll('[wire\\:id]').length;

            // Function to hide loading overlay
            function hideLoadingOverlay() {
                loadingOverlay.classList.add('hidden');
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }

            // Track Livewire component loading
            document.addEventListener('livewire:init', function() {
                // Components are initializing
            });

            document.addEventListener('livewire:navigated', function() {
                // Page navigation completed
                hideLoadingOverlay();
            });

            // Monitor when Livewire requests complete
            document.addEventListener('livewire:beforedom', function() {
                // DOM is about to be updated
            });

            document.addEventListener('livewire:updated', function() {
                componentsLoaded++;
                // If all components have been updated, hide the overlay
                if (componentsLoaded >= totalComponents && totalComponents > 0) {
                    hideLoadingOverlay();
                }
            });

            // Fallback: hide overlay after 10 seconds
            setTimeout(function() {
                if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
                    hideLoadingOverlay();
                }
            }, 10000);

            // Also hide overlay when window finishes loading
            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
                        hideLoadingOverlay();
                    }
                }, 1000);
            });
        });
    </script>
</body>

</html>