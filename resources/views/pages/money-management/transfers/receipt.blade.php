<div class="receipt-wrapper p-10 bg-white" id="receipt_content">
    <div class="text-center mb-10">
        <!-- Logo -->
        <h1 class="fw-bolder text-gray-800 mb-2">KODE VISUAL</h1>
        <p class="text-gray-400 fw-semibold fs-6">Transfer Proof - Money Management</p>
        <div class="separator separator-dashed border-gray-300 my-5"></div>
    </div>

    <div class="d-flex flex-stack mb-8">
        <div class="text-gray-800 fs-3 fw-bolder">Reference ID</div>
        <div class="text-gray-600 fs-4 fw-bold">#TRF-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="d-flex flex-stack mb-8">
        <div class="text-gray-800 fs-3 fw-bolder">Transaction Status</div>
        <div>
            <span class="badge badge-light-success fs-7 fw-bolder">SUCCESS</span>
        </div>
    </div>

    <div class="d-flex flex-stack mb-8">
        <div class="text-gray-800 fs-3 fw-bolder">Amount</div>
        <div class="text-success fs-2x fw-bold">Rp {{ number_format(abs($transaction->amount), 0, ',', '.') }}</div>
    </div>

    <div class="separator separator-dashed border-gray-300 my-10"></div>

    <div class="mb-10">
        <div class="row g-9">
            <div class="col-sm-6 text-center border-end">
                <div class="fw-bold fs-7 text-gray-500 text-uppercase mb-2">From Account</div>
                <div class="fw-bolder fs-5 text-gray-800">{{ $transaction->investment->name ?? '-' }}</div>
            </div>
            <div class="col-sm-6 text-center">
                <div class="fw-bold fs-7 text-gray-500 text-uppercase mb-2">To Account</div>
                <div class="fw-bolder fs-5 text-gray-800">{{ $transaction->destinationAccount->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="mb-10">
        <div class="row g-9">
            <div class="col-sm-6 text-center border-end">
                <div class="fw-bold fs-7 text-gray-500 text-uppercase mb-2">Transfer Date</div>
                <div class="fw-bolder fs-5 text-gray-800">{{ date('d M Y, H:i', strtotime($transaction->created_at)) }}</div>
            </div>
            <div class="col-sm-6 text-center">
                <div class="fw-bold fs-7 text-gray-500 text-uppercase mb-2">Recorded By</div>
                <div class="fw-bolder fs-5 text-gray-800">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>

    @if($transaction->description)
    <div class="p-5 bg-light-secondary rounded border-secondary border border-dashed text-center">
        <div class="fw-bold fs-7 text-gray-500 text-uppercase mb-1">Notes</div>
        <div class="fw-semibold fs-6 text-gray-700 italic">"{{ $transaction->description }}"</div>
    </div>
    @endif

    <div class="text-center mt-15 d-print-none">
        <button type="button" class="btn btn-sm btn-light-primary me-3" onclick="window.print()">
            <i class="ki-duotone ki-printer fs-3 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            Print Receipt
        </button>
    </div>
    
    <div class="text-center mt-10 visible-print d-none d-print-block">
        <p class="fs-9 text-gray-400">This is a system generated document and does not require a signature.</p>
        <p class="fs-9 text-gray-400">© {{ date('Y') }} Kode Visual - Money Management System</p>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt_content, #receipt_content * {
            visibility: visible;
        }
        #receipt_content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
