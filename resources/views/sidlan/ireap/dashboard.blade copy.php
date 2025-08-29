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

    <script src="blob:https://geomapping.da.gov.ph/b85d7d8c-2177-43a1-ba91-c88bf2b6e52e"></script>
    @livewireStyles
    @vite(['resources/js/app.js'])
</head>

<body style="min-height: 100%;" class="" cz-shortcut-listen="true">

    <div class="container py-5 min-h-100 position-relative">
        <!-- <div class="position-absolute top-0 start-0 p-4 bg-dark rounded" id="cust-tooltip" style="z-index: 1000;"></div> -->
        <div class="d-flex justify-content-center">
            <img class="drop-shadow" src="https://geomapping.da.gov.ph/prdp/assets/img/prdp_rz.png" alt=""
                style="max-width: 130px;">
        </div>
        <!-- <h1 class="text-5xl md:text-5xl font-extrabold text-center text-blue-900 mb-20 leading-tight drop-shadow-md rounded-xl"> -->
        <h1 class="chart-page-title">

            PRDP Scale-Up I-REAP Management Dashboard
            <span class="d-block text-center fs-6 d-none">As of <span class="text-warnings"
                    id="asof">--</span></span>
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


        <livewire:sidlan.ireap.section-1 :irZeroOneData="$irZeroOneData" lazy />
        {{-- <livewire:sidlan.ireap.section-2 :irZeroOneData="$irZeroOneData" lazy /> --}}


        {{-- <div class="section-title ps-lg-2 d-flex flex-column flex-lg-row justify-content-between align-items-start">
            Procurement
            <a href="https://geomapping.da.gov.ph/prdp/sidlan/d3-proc-milestone" target="_blank"
                class="btn btn-primary d-none d-lg-block dashboard-redirect">View Detailed Data</a>
        </div>

        <div class="row d-block d-lg-none">
            <div class="col-12">
                <a href="https://geomapping.da.gov.ph/prdp/sidlan/d3-proc-milestone" target="_blank"
                    class="btn btn-primary btn-sm my-2">View Detailed Data</a>
            </div>
        </div>

        <div class="row row-cols-1 row-gap-4 mt-2">


            <div class="col position-relative">
                <div class="tile-container position-relative">
                    <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
                        style="font-size: 1.2rem;">
                        <span>
                            I-REAP Subprojects Already Beyond Procurement Timeline (Number of Subprojects)
                        </span>
                        <!-- I-BUILD Subprojects Undergoing Procurement -->
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-proc-sps"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <option value="Luzon A">Luzon A</option>
                                <option value="Luzon B">Luzon B</option>
                                <option value="Visayas">Visayas</option>
                                <option value="Mindanao">Mindanao</option>
                                <option value="All Regions">All Regions</option>
                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart custom-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-procurement" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                        <div class="chartjs-tooltip"></div>
                    </div>
                    <div>
                        <ul class="small mt-2">
                            <li>
                                <span id="sps-beyond-percentage" class="text-danger">74% (17 of 23)</span> SPs already
                                beyond the procurement
                                standard (122 days)
                            </li>
                            <li>
                                <span id="highest-beyond-cluster">Luzon B</span> shares the largest proportion of SPs
                                already beyond the procurement
                                timeline, at <span class="text-danger" id="beyond-timeline-rate">100% (4 of 4
                                    SPs)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-1  row-gap-4 mt-4">

            <div class="col">
                <div class="tile-container" style="min-height: 100%;">
                    <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
                        style="font-size: 1.2rem;">
                        <span>
                            I-BUILD Subprojects Under Procurement by Status (Average Number of Days by Status)
                        </span>
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-proc-sps-activity"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <optgroup label="Clusterwide">
                                    <option value="Luzon A">Luzon A</option>
                                    <option value="Luzon B">Luzon B</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </optgroup>
                                <optgroup label="Regionwide">
                                    <option value="Cordillera Administrative Region (CAR)" data-group="region">CAR
                                    </option>
                                    <option value="Ilocos Region (Region I)" data-group="region">Region 01</option>
                                    <option value="Cagayan Valley (Region II)" data-group="region">Region 02</option>
                                    <option value="Central Luzon (Region III)" data-group="region">Region 03</option>
                                    <option value="CALABARZON (Region IV-A)" data-group="region">Region 04A</option>
                                    <option value="MIMAROPA (Region IV-B)" data-group="region">Region 04B</option>
                                    <option value="Bicol Region (Region V)" data-group="region">Region 05</option>
                                    <option value="Western Visayas (Region VI)" data-group="region">Region 06</option>
                                    <option value="Central Visayas (Region VII)" data-group="region">Region 07
                                    </option>
                                    <option value="Eastern Visayas (Region VIII)" data-group="region">Region 08
                                    </option>
                                    <option value="Zamboanga Peninsula (Region IX)" data-group="region">Region 09
                                    </option>
                                    <option value="Northern Mindanao (Region X)" data-group="region">Region 10
                                    </option>
                                    <option value="Davao Region (Region XI)" data-group="region">Region 11</option>
                                    <option value="SOCCSKSARGEN (Region XII)" data-group="region">Region 12</option>
                                    <option value="Caraga (Region XIII)" data-group="region">Region 13</option>
                                    <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)"
                                        data-group="region">BARMM</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart custom-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-procurement-current-pace" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                    </div>
                    <div>
                        <ul class="small mt-2">

                            <li>
                                <span id="most-delayed-activity">Contract perfection and issuance of NTP</span>
                                exhibits the largest extent of delay against the standard timeline,
                                at <span id="delayed-activity-rate">900% (63 of 7 days prescribed timeline)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col d-none">
                <div class="tile-container" style="min-height: 100%;">
                    <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
                        style="font-size: 1.2rem;">
                        I-BUILD Subproject Cost Under Procurement
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-proc-sps-cost"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <optgroup label="Clusterwide">
                                    <option value="Luzon A">Luzon A</option>
                                    <option value="Luzon B">Luzon B</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </optgroup>
                                <optgroup label="Regionwide">
                                    <option value="Cordillera Administrative Region (CAR)" data-group="region">CAR
                                    </option>
                                    <option value="Ilocos Region (Region I)" data-group="region">Region 01</option>
                                    <option value="Cagayan Valley (Region II)" data-group="region">Region 02</option>
                                    <option value="Central Luzon (Region III)" data-group="region">Region 03</option>
                                    <option value="CALABARZON (Region IV-A)" data-group="region">Region 04A</option>
                                    <option value="MIMAROPA (Region IV-B)" data-group="region">Region 04B</option>
                                    <option value="Bicol Region (Region V)" data-group="region">Region 05</option>
                                    <option value="Western Visayas (Region VI)" data-group="region">Region 06</option>
                                    <option value="Central Visayas (Region VII)" data-group="region">Region 07
                                    </option>
                                    <option value="Eastern Visayas (Region VIII)" data-group="region">Region 08
                                    </option>
                                    <option value="Zamboanga Peninsula (Region IX)" data-group="region">Region 09
                                    </option>
                                    <option value="Northern Mindanao (Region X)" data-group="region">Region 10
                                    </option>
                                    <option value="Davao Region (Region XI)" data-group="region">Region 11</option>
                                    <option value="SOCCSKSARGEN (Region XII)" data-group="region">Region 12</option>
                                    <option value="Caraga (Region XIII)" data-group="region">Region 13</option>
                                    <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)"
                                        data-group="region">BARMM</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-procurement-cost" height="0"
                            style="display: block; box-sizing: border-box; height: 0px; width: 0px;"
                            width="0"></canvas>
                    </div>

                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-gap-4 mt-4">
            <div class="col">
                <div class="tile-container">
                    <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
                        style="font-size: 1.2rem;">
                        I-BUILD Subprojects Actual Procurement Pace (Average Number of Days Incurred by Status)
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-proc-sps-activity-experience"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <optgroup label="Clusterwide">
                                    <option value="Luzon A">Luzon A</option>
                                    <option value="Luzon B">Luzon B</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </optgroup>
                                <optgroup label="Regionwide">
                                    <option value="Cordillera Administrative Region (CAR)" data-group="region">CAR
                                    </option>
                                    <option value="Ilocos Region (Region I)" data-group="region">Region 01</option>
                                    <option value="Cagayan Valley (Region II)" data-group="region">Region 02</option>
                                    <option value="Central Luzon (Region III)" data-group="region">Region 03</option>
                                    <option value="CALABARZON (Region IV-A)" data-group="region">Region 04A</option>
                                    <option value="MIMAROPA (Region IV-B)" data-group="region">Region 04B</option>
                                    <option value="Bicol Region (Region V)" data-group="region">Region 05</option>
                                    <option value="Western Visayas (Region VI)" data-group="region">Region 06</option>
                                    <option value="Central Visayas (Region VII)" data-group="region">Region 07
                                    </option>
                                    <option value="Eastern Visayas (Region VIII)" data-group="region">Region 08
                                    </option>
                                    <option value="Zamboanga Peninsula (Region IX)" data-group="region">Region 09
                                    </option>
                                    <option value="Northern Mindanao (Region X)" data-group="region">Region 10
                                    </option>
                                    <option value="Davao Region (Region XI)" data-group="region">Region 11</option>
                                    <option value="SOCCSKSARGEN (Region XII)" data-group="region">Region 12</option>
                                    <option value="Caraga (Region XIII)" data-group="region">Region 13</option>
                                    <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)"
                                        data-group="region">BARMM</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-procurement-current-experience" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                    </div>

                </div>
            </div>
        </div> --}}
        <!-- end of procurement -->

        {{-- <div class="section-title ps-lg-2 d-flex flex-column flex-lg-row justify-content-between align-items-start">
            Implementation / Construction
            <a href="https://geomapping.da.gov.ph/prdp/sidlan/d4a-slippage/sidlan-instance" target="_blank"
                class="btn btn-primary d-none d-lg-block dashboard-redirect">View Detailed Data</a>
        </div>

        <div class="row d-block d-lg-none">
            <div class="col-12">
                <a href="https://geomapping.da.gov.ph/prdp/sidlan/d4a-slippage" target="_blank"
                    class="btn btn-primary btn-sm my-2">View Detailed Data</a>
            </div>
        </div>
        <div class="row row-cols-1 row-gap-4 mt-2">
            <div class="col">
                <div class="tile-container">
                    <div class="tile-title d-flex flex-column flex-lg-row justify-content-between row-gap-2 align-items-start"
                        style="font-size: 1.2rem;">
                        <span>
                            I-BUILD Subprojects Under Implementation/Construction with Negative Slippages (Number of
                            Subprojects)
                        </span>
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-contstruction-sps"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <option value="Luzon A">Luzon A</option>
                                <option value="Luzon B">Luzon B</option>
                                <option value="Visayas">Visayas</option>
                                <option value="Mindanao">Mindanao</option>
                                <option value="All Regions">All Regions</option>

                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart custom-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-construction" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                    </div>
                    <div>
                        <ul class="small mt-2">
                            <li>
                                <span id="slippage-percentage">42%</span> of the on-going SPs
                                (<span id="slippage-count">37 of 89</span>) already with negative slippages
                            </li>
                            <li>
                                <span id="most-slippage-location">Luzon A</span> exhibits the largest proportion of SPs
                                with negative slippages, at <span id="slippage-location-count">57% (12 of 21
                                    SPs)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="tile-container">
                    <div class="tile-title d-flex flex-column flex-lg-row justify-content-between row-gap-2 align-items-start"
                        style="font-size: 1.2rem;">
                        Status of I-BUILD Subprojects Under Implementation (Actual vs. Target Physical Progress)
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-contstruction-slippage"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <option value="Luzon A">Luzon A</option>
                                <option value="Luzon B">Luzon B</option>
                                <option value="Visayas">Visayas</option>
                                <option value="Mindanao">Mindanao</option>
                                <option value="All Regions">All Regions</option>

                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-construction-progress" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                        <div class="chartjs-tooltip"></div>
                    </div>
                    <div>
                        <ul class="small mt-2">
                            <li>
                                Overall progress of on-going SPs presents <span id="overall-slippage"
                                    class="text-success">0.4%</span> slippage
                            </li>
                            <li>
                                <span id="location-slippage">Luzon B</span> cluster bears the highest slippage at <span
                                    class="text-danger" id="location-slippage-percentage">-2.7%</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="tile-container">
                    <div class="tile-title d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-start"
                        style="font-size: 1.2rem;">
                        <span>
                            I-BUILD Subprojects Under Implementation with Negative Slippages, by Extent of Slippage
                        </span>
                        <div class="d-flex flex-row gap-2 align-items-center small">
                            <div class="fw-normal">Show:</div>
                            <select name="" id="cbo-filter-construction-variance"
                                class="form-select filter-dropdown pe-lg-5">
                                <option value="All">All</option>
                                <optgroup label="Clusterwide">
                                    <option value="Luzon A">Luzon A</option>
                                    <option value="Luzon B">Luzon B</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </optgroup>
                                <optgroup label="Regionwide">
                                    <option value="Cordillera Administrative Region (CAR)" data-group="region">CAR
                                    </option>
                                    <option value="Ilocos Region (Region I)" data-group="region">Region 01</option>
                                    <option value="Cagayan Valley (Region II)" data-group="region">Region 02</option>
                                    <option value="Central Luzon (Region III)" data-group="region">Region 03</option>
                                    <option value="CALABARZON (Region IV-A)" data-group="region">Region 04A</option>
                                    <option value="MIMAROPA (Region IV-B)" data-group="region">Region 04B</option>
                                    <option value="Bicol Region (Region V)" data-group="region">Region 05</option>
                                    <option value="Western Visayas (Region VI)" data-group="region">Region 06</option>
                                    <option value="Central Visayas (Region VII)" data-group="region">Region 07
                                    </option>
                                    <option value="Eastern Visayas (Region VIII)" data-group="region">Region 08
                                    </option>
                                    <option value="Zamboanga Peninsula (Region IX)" data-group="region">Region 09
                                    </option>
                                    <option value="Northern Mindanao (Region X)" data-group="region">Region 10
                                    </option>
                                    <option value="Davao Region (Region XI)" data-group="region">Region 11</option>
                                    <option value="SOCCSKSARGEN (Region XII)" data-group="region">Region 12</option>
                                    <option value="Caraga (Region XIII)" data-group="region">Region 13</option>
                                    <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)"
                                        data-group="region">BARMM</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="tile-content position-relative overflow-hidden chart-container"
                        style="height: 400px;">
                        <canvas class="tile-chart custom-chart position-absolute top-0 start-0 w-100 h-100"
                            id="chrt-scope-construction-slippage" width="684" height="600"
                            style="display: block; box-sizing: border-box; height: 300px; width: 342px;"></canvas>
                    </div>
                    <div>
                        <ul class="small mt-2">
                            <li class="d-none">
                                <span id="critical-slippage" class="text-danger fw-bold">[object Object]</span> SPs at
                                risk of cancellation with 15% and above negative slippage
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- end of implementation -->



        <!-- <div class="row row-gap-3 mt-5">
        <div class="col-12">
            <div class="tile-container">
                <div class="tile-title d-none">Our Portfolio</div>
                <div class="tile-content position-relative overflow-hidden">
                    <div class="row row-cols-1 row-cols-lg-2 row-gap-4">
                        <div class="col" style="height: 500px;">
                            <div class="w-100 h-100 position-relative">

                                <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100" id="chrt-portfolio"></canvas>
                            </div>
                        </div>
                        <div class="col" style="height: 500px;">
                            <div class="d-flex flex-column gap-4 h-100 w-100">
                                <div class="position-relative" style="flex: 1 1;">
                                    <canvas id="chrt-cluster-pipelined" class="position-absolute top-0 start-0 w-100 h-100"></canvas>
                                </div>
                                <div class="position-relative" style="flex: 1 1;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div> -->

    </div>

    <!-- modal -->
    <div class="modal fade" id="modal-loading" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header d-none">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-row justify-content-center bg-transparent border-0">
                    <div class="d-flex flex-row justify-content-center gap-2 align-items-end bar-container"
                        style="height: 100px;">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                </div>
                <div class="modal-footer d-none">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Understood</button>
                </div>
            </div>
        </div>
    </div>

    <!-- subproject modal -->
    <div class="modal" id="modal-sp-list" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 align-items-start">
                    <h1 class="modal-title sp-list-title fs-5" id="exampleModalLabel"
                        style="color: #1e40af; font-weight: 700">Modal title</h1>
                    <button type="button" class="btn-close small" data-bs-dismiss="modal" aria-label="Close"
                        style="outline: none; box-shadow: none;"></button>
                </div>
                <div class="modal-body position-relative py-0" style="max-height: 80vh; overflow-y: auto;">
                    <div class="table-responsive" style="overflow-x: none; max-height: 80vh">
                        <table class="table table-hover fix-header-table small mb-0" id="tbl-chart-sps">
                            <thead class="small"></thead>
                            <tbody class="small"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-none">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts

    <script src="https://geomapping.da.gov.ph/prdp/assets/js/global.js?v1753948749163"></script>
    <script src="https://geomapping.da.gov.ph/prdp/assets/js/sidlan-dashboard/sidlan-globals.js?v1753948749163"></script>
    <script src="https://geomapping.da.gov.ph/prdp/assets/js/sidlan-dashboard/main/main-portfolio.js?v1753948749163">
    </script>
    <script src="https://geomapping.da.gov.ph/prdp/assets/js/sidlan-dashboard/main/main-procurement.js?v1753948749163">
    </script>
    <script src="https://geomapping.da.gov.ph/prdp/assets/js/sidlan-dashboard/main/main-construction.js?v1753948749163">
    </script>
    <!-- <script
        src="https://cdn.jsdelivr.net/npm/chartjs-plugin-piechart-outlabels@0.1.4/dist/chartjs-plugin-piechart-outlabels.min.js">
    </script> -->
    <script>
        var portfolio_sps_data;
        var portfolio_sps = {};
        var chart_portfolio, chart_cluster_pipelined, chart_cluster_approved;
        const portfolio_class = ['approved', 'pipelined'];
        const approved_stages = ['For Procurement', 'Procurement', 'Construction', 'Completed'];
        const modal_loading = $('#modal-loading');
        const modal_sp_list = $('#modal-sp-list');
        const chart_table_sps = $('#tbl-chart-sps');
        const cust_tp = $('#cust-tooltip');

        $(window).ready(function() {
            onLoadFunctions();
        });

        function onLoadFunctions() {
            init_controls();
            update_charts();

            // const sidlan_frame = $(document).closest('sidlan-dashboard-frame');
            // if(window.self !== window.top){
            //     console.log('in iFrame');

            //     const iFrame = $(window.top).find('iFrame');
            //     console.log(iFrame)
            // }
            // console.log(sidlan_frame);
        }

        const init_controls = () => {

            // populate proc regions
            $('#cbo-filter-proc-sps-cost').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-pipeline-status').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-pipeline-status-timeline').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-pipeline-status-pace').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-proc-sps-activity-experience').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-proc-sps-activity').find('optgroup[label="Regionwide"]').html(region_options);
            $('#cbo-filter-construction-variance').find('optgroup[label="Regionwide"]').html(region_options);

            $(document).on('change', '#cbo-filter-proc-sps', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') params.cluster = cbo_val;
                fetch_subproject_under_procurement_already_beyond(params);
            });
            $(document).on('change', '#cbo-filter-proc-sps-activity', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    const cbo_val_group = $(this).find('option:selected').closest('optgroup').attr('label');
                    if (cbo_val_group === 'Clusterwide') params.cluster = cbo_val;
                    if (cbo_val_group === 'Regionwide') params.region = cbo_val;
                }
                fetch_subproject_under_procurement_actual_pace(params, 'proc-activity');
            });
            $(document).on('change', '#cbo-filter-proc-sps-cost', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    const cbo_val_group = $(this).find('option:selected').closest('optgroup').attr('label');
                    if (cbo_val_group === 'Clusterwide') params.cluster = cbo_val;
                    if (cbo_val_group === 'Regionwide') params.region = cbo_val;
                }
                fetch_subproject_under_procurement_actual_pace(params, 'proc-cost');
            });

            $(document).on('change', '#cbo-filter-proc-sps-activity-experience', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    const cbo_val_group = $(this).find('option:selected').closest('optgroup').attr('label');
                    if (cbo_val_group === 'Clusterwide') params.cluster = cbo_val;
                    if (cbo_val_group === 'Regionwide') params.region = cbo_val;
                }
                fetch_subproject_under_procurement_actual_experience(params);
            });

            $(document).on('change', '#cbo-filter-contstruction-sps', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    params.cluster = cbo_val;
                }
                fetch_ongoing_subprojects(params, 'ongoing-list');
            });

            $(document).on('change', '#cbo-filter-contstruction-slippage', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    params.cluster = cbo_val;
                }
                fetch_ongoing_subprojects(params, 'ongoing-slippage');
            });

            $(document).on('change', '#cbo-filter-construction-variance', function() {
                const params = {};
                const cbo_val = $(this).val();
                if (cbo_val !== 'All') {
                    const cbo_val_group = $(this).find('option:selected').closest('optgroup').attr('label');
                    if (cbo_val_group === 'Clusterwide') params.cluster = cbo_val;
                    if (cbo_val_group === 'Regionwide') params.region = cbo_val;
                }
                fetch_ongoing_subprojects(params, 'ongoing-variance');
            });

            $(document).on('change',
                '#cbo-filter-pipeline-status, #cbo-filter-pipeline-status-timeline, #cbo-filter-pipeline-status-pace',
                function() {
                    const params = {};
                    const cbo_val = $(this).val();
                    const cbo_id = $(this).attr('id');
                    if (cbo_val !== 'All') {
                        const cbo_val_group = $(this).find('option:selected').closest('optgroup').attr('label');
                        if (cbo_val_group === 'Clusterwide') params.cluster = cbo_val;
                        if (cbo_val_group === 'Regionwide') params.region = cbo_val;
                    }
                    if (['cbo-filter-pipeline-status', 'cbo-filter-pipeline-status-timeline'].includes(cbo_id)) {
                        const mode = cbo_id === 'cbo-filter-pipeline-status' ? 'number' : 'timeline';
                        fetchPipelinedSPs(params, mode);
                    } else if (cbo_id === 'cbo-filter-pipeline-status-pace') {
                        fetchPipelinedSPsPace(params);
                    }
                });


            // manage custom chart
            // $(document).on('mouseleave', '.custom-chart', function(){
            //     // isMouseOverTooltip = false;
            //     const customTooltip = $(this).parent().find('.chartjs-tooltip')[0];
            //     hideCustomTooltip(customTooltip);
            // });
            $(document).on('click', '.custom-chart', function(e) {
                const chart = Chart.getChart($(this));
                const chart_id = $(this).attr('id');
                const activeElements = chart.getElementsAtEventForMode(event, 'nearest', {
                    intersect: true
                }, false);


                if (activeElements.length > 0) {
                    const chartElement = activeElements[0];
                    const currentLabel = chart.data.labels[chartElement.index];
                    const currentData = chart.data.datasets[chartElement.datasetIndex];

                    // pipeline
                    if (['chrt-pipelined-by-status', 'chrt-pipelined-by-status-timeline'].includes(chart_id)) {
                        if (chart_id === 'chrt-pipelined-by-status') showPipelinedSPs(chart, chartElement);
                        if (chart_id === 'chrt-pipelined-by-status-timeline' && chartElement.datasetIndex === 1)
                            showPipelinedSPs(chart, chartElement);
                        return;
                    }

                    // procurement
                    if (['chrt-scope-procurement', 'chrt-scope-procurement-current-pace'].includes(chart_id)) {

                        if (chartElement.datasetIndex === 1) {
                            showProcurementSPs(chart, chartElement);
                        }
                        return;
                    }

                    // implementation
                    showOngoingSPs(chart, chartElement);

                    // console.log(currentLabel, currentData.data[chartElement.index]);
                }
                // isMouseOverTooltip = true;
                // clearTimeout(hideTooltipTimeout); // Keep it open
            });
            // $(document).on('mouseleave', '.chartjs-tooltip', function(){
            //     isMouseOverTooltip = false;
            //     const customTooltip = $(this).parent().find('.chartjs-tooltip')[0];
            //     hideCustomTooltip(customTooltip); // Initiate hide when mouse leaves the tooltip
            // });

            $(document).on('click', '.dashboard-redirect', function(e) {
                e.preventDefault();
                const redirectLink = $(this).attr('href');

                // check if in iFrame
                if (window.self !== window.top) {
                    window.parent.postMessage({
                        action: 'changeSrc',
                        newSrc: redirectLink
                    }, '*');
                    return;
                }

                location.href = redirectLink;

            });

        }

        const update_charts = async () => {

            modal_loading.modal('show');
            try {

                await Promise.all([
                    fetch_portfolio(),
                    fetchPipelinedSPs(),
                    fetchPipelinedSPsPace(),
                    fetch_subproject_under_procurement_already_beyond(),
                    fetch_subproject_under_procurement_actual_pace(),
                    fetch_subproject_under_procurement_actual_experience(),
                    fetch_ongoing_subprojects(),
                ])
                // await fetch_portfolio();
                // await fetch_subproject_under_procurement_already_beyond();
                // await fetch_subproject_under_procurement_actual_pace();
                // await fetch_ongoing_subprojects();
            } finally {
                setTimeout(() => {
                    modal_loading.modal('hide');
                    // modal_loading.removeClass('show');
                    // $('body').removeClass('modal-open');
                    // $('.modal-backdrop').remove();
                    // modal_loading.css('display', 'none');
                    // Manual failsafe cleanup
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', 'auto'); // ← important!
                    $('.modal-backdrop').remove();
                    modal_loading.removeClass('show').hide();
                }, 300);
            }



        }
    </script>

</body>

</html>
