<x-default-layout>
    @section('title')
        Dashboard Application
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <div class="row g-7">
        @foreach ($laporan_list as $laporan)
            @can($laporan['permission'])
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route($laporan['route']) }}" class="card card-laporan border-0 h-100 text-decoration-none">
                        <div class="card-body d-flex flex-column align-items-center text-center p-9">
                            <!-- Icon Display -->
                            <div class="laporan-icon-wrapper mb-7 shadow-sm">
                                <i class="{{ $laporan['icon'] ?? 'ki-outline ki-file-text' }} fs-2hx text-success"></i>
                            </div>

                            <!-- Title -->
                            <h3 class="card-title fw-bold text-gray-800 fs-4 mb-2">
                                {{ $laporan['nama'] }}
                            </h3>
                            
                            <p class="text-gray-500 fw-semibold fs-7 mb-7">
                                {{ $laporan['description'] }}
                            </p>

                            <!-- Footer/Action -->
                            <div class="mt-auto w-100">
                                <span class="btn btn-sm btn-light-success fw-bold px-4 py-2 w-100">
                                    <i class="ki-outline ki-eye fs-5 me-1"></i> {{ $laporan['btn'] }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
        @endforeach
    </div>

    @push('styles')
        <style>
            .card-laporan {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 20px !important;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03) !important;
            }

            .card-laporan:hover {
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
            }

            .laporan-icon-wrapper {
                width: 70px;
                height: 70px;
                background: #ffffff;
                border-radius: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .card-laporan:hover .laporan-icon-wrapper {
                background: #1c86ff;
                transform: rotate(5deg) scale(1.1);
            }

            .card-laporan:hover .laporan-icon-wrapper i {
                color: #ffffff !important;
            }

            .btn-light-primary {
                transition: all 0.3s ease;
            }

            /* .card-laporan:hover .btn-light-primary {
                background-color: #009ef7 !important;
                color: #ffffff !important;
            } */
        </style>
    @endpush
</x-default-layout>
