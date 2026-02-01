<x-default-layout>
    @section('title')
        Finance Settings
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.master-data.settings') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Finance Cycle Settings</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <form id="form_finance_settings" class="form">
                @csrf
                <div class="row g-9 mb-8">
                    <div class="col-md-6 fv-row">
                        <label class="required fs-6 fw-semibold mb-2">Payroll Start Day</label>
                        <div class="position-relative d-flex align-items-center w-100">
                            <select name="payroll_start_day" class="form-select form-select-solid" required>
                                <option value="last" {{ ($settings['payroll_start_day'] ?? '25') == 'last' ? 'selected' : '' }}>Last Day of Month</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ ($settings['payroll_start_day'] ?? '25') == $i ? 'selected' : '' }}>Day {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="text-muted fs-7 mt-2">
                            This date determines when the financial cycle starts for the next month's dashboard. 
                            If you set it to 25, then expenses from the 25th of the previous month will be counted as current month's expenses.
                        </div>
                    </div>
                </div>

                <div class="text-start">
                    <button type="submit" class="btn btn-primary" id="btn_save_settings">
                        <span class="indicator-label">Save Settings</span>
                        <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#form_finance_settings').submit(function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let btn = $('#btn_save_settings');
                
                btn.attr('data-kt-indicator', 'on');

                $.ajax({
                    url: "{{ route('money-management.master-data.settings.store') }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        btn.removeAttr('data-kt-indicator');
                        Swal.fire({ 
                            text: response.success, 
                            icon: "success", 
                            buttonsStyling: false, 
                            confirmButtonText: "Ok!", 
                            customClass: { confirmButton: "btn btn-primary" } 
                        });
                    },
                    error: function(xhr) {
                        btn.removeAttr('data-kt-indicator');
                        Swal.fire({ 
                            text: "Error saving settings", 
                            icon: "error", 
                            buttonsStyling: false, 
                            confirmButtonText: "Ok!", 
                            customClass: { confirmButton: "btn btn-primary" } 
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
