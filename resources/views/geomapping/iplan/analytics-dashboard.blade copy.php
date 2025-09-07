<x-layouts.geomapping.iplan.app>
    @push('breadcrumbs')
        <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">
            Analytics Dashboard
        </li>
    @endpush

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header p-3 d-flex justify-content-end align-items-center gap-3">
                    <button id="exportExcel" class="btn btn-success d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export CSV
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 mb-0 dataTable no-footer align-center"
                               id="model-table">
                            <thead>
                                <tr>
                                    <th>Province</th>
                                    <th>Commodity</th>
                                    <th>Intervention</th>
                                    <th>Fund Requirement</th>
                                    <th>Funded</th>
                                    <th>Unfunded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($provinces as $province)
                                    @php
                                        $matrices = $province->pcipMatrices;
                                        $matrixCount = $matrices->count();
                                    @endphp

                                    @forelse ($matrices as $mIndex => $matrix)
                                        <tr>
                                            {{-- Province cell only once --}}
                                            @if ($mIndex === 0)
                                                <td rowspan="{{ $matrixCount ?: 1 }}" class="align-middle fw-semibold">
                                                    {{ $province->name }}
                                                </td>
                                            @endif

                                            {{-- Commodity --}}
                                            <td>{{ $matrix->commodity->name ?? '—' }}</td>

                                            {{-- Intervention --}}
                                            <td>{{ $matrix->intervention->name ?? '—' }}</td>

                                            {{-- Funding --}}
                                            <td>{{ number_format($matrix->funding_requirement, 2) }}</td>
                                            <td>{{ number_format($matrix->funded, 2) }}</td>
                                            <td>{{ number_format($matrix->unfunded, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="align-middle fw-semibold">{{ $province->name }}</td>
                                            <td colspan="5" class="text-muted text-center">No interventions</td>
                                        </tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.geomapping.iplan.app>
