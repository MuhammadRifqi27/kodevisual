<x-default-layout>

    @section('title')
    Dashboard testing
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('detail-expenses-rifqi') }}
    @endsection

    <div class="container-xxl">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Tambah Rincian Keuangan</h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="me-2">
                            <a href="{{ route('financial_summary.detail.expenses') }}" class="btn btn-secondary rounded">
                                <i class="ki-duotone ki-double-left">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="">
                                <label for="exampleFormControlInput1" class="required form-label">Detail Expenses</label>
                                <input type="text" class="form-control form-control-solid" placeholder="Nama Pengeluaran" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="exampleFormControlInput1" class="required form-label">Cost</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control form-control-lg" id="total-nominal" placeholder="Masukkan Nominal..." autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="exampleFormControlInput1" class="required form-label">Category</label>
                            <select class="form-select" data-control="select2" data-placeholder="Select an option">
                                <option></option>
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</x-default-layout>