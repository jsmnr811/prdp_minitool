<div class="row g-4">
    <!-- Mobile: Stack vertically, Desktop: Side by side -->
    <div class="col-12 col-lg-9 order-2 order-lg-1">
        <div class="card shadow-sm p-2 p-sm-3 p-md-4">
            <!-- Province Dropdown Skeleton for Role 1 -->
            <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mb-3">
                <div class="flex-fill">
                    <div class="placeholder-glow mb-2">
                        <span class="placeholder col-4 bg-secondary"></span>
                    </div>
                    <div class="placeholder bg-secondary rounded" style="height: 38px;"></div>
                </div>
                <div class="flex-fill">
                    <div class="placeholder-glow mb-2">
                        <span class="placeholder col-5 bg-secondary"></span>
                    </div>
                    <div class="placeholder bg-secondary rounded" style="height: 38px;"></div>
                </div>
            </div>

            <!-- Search Box Skeleton -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
                <div class="placeholder-glow">
                    <span class="placeholder col-6 bg-primary"></span>
                </div>
                <div class="placeholder bg-success rounded" style="height: 32px; width: 120px;"></div>
            </div>

            <div class="position-relative mb-3">
                <div class="placeholder bg-light rounded" style="height: 48px;"></div>
            </div>

            <!-- Map Container Skeleton -->
            <div class="position-relative">
                <div class="bg-light rounded shadow-sm d-flex align-items-center justify-content-center"
                     style="height: 50vh; min-height: 300px; max-height: 600px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading map...</span>
                        </div>
                        <div class="placeholder-glow">
                            <span class="placeholder col-8 bg-secondary"></span>
                        </div>
                        <div class="placeholder-glow mt-2">
                            <span class="placeholder col-6 bg-secondary"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Helper Skeleton -->
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12 bg-light"></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12 bg-light"></span>
                    </div>
                </div>
            </div>

            <div class="placeholder-glow mt-3">
                <span class="placeholder col-8 bg-muted"></span>
            </div>
        </div>
    </div>

    <!-- Sidebar Skeleton - Mobile: Full width, Desktop: Narrow -->
    <div class="col-12 col-lg-3 order-1 order-lg-2">
        <!-- Commodity Toggles Skeleton -->
        <div class="card shadow-sm p-4 h-100">
            <div class="placeholder-glow mb-3">
                <span class="placeholder col-8 bg-primary"></span>
            </div>
            <hr class="mt-2">
            <div class="row row-cols-2 g-3">
                @for ($i = 0; $i < 8; $i++)
                <div class="col">
                    <div class="d-flex align-items-center">
                        <div class="placeholder bg-secondary rounded me-2" style="width: 16px; height: 16px;"></div>
                        <div class="placeholder bg-light rounded me-2" style="width: 24px; height: 24px;"></div>
                        <div class="placeholder-glow flex-fill">
                            <span class="placeholder col-12 bg-secondary"></span>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>


    </div>
</div>
<!-- Scripts -->


