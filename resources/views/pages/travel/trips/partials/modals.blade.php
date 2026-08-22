<!--begin::Modal - Add Itinerary-->
<div class="modal fade" id="kt_modal_add_itinerary" tabindex="-1" aria-hidden="true" data-persons="{{ $trip->number_of_persons ?? 1 }}">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="{{ route('travel.itineraries.store') }}" method="POST" id="add_itinerary_form">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <div class="modal-header">
                    <h2 class="fw-bold">Add New Activity</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Planned Date & Time</label>
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-calendar-8 fs-3 position-absolute ms-4"></i>
                                <input type="text" 
                                    class="form-control form-control-solid ps-12 kt_flatpickr_datetime" placeholder="Select date time..."
                                    name="datetime"  value="{{ $trip->start_date }}"
                                    required />
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Activity Name</label>
                        <input type="text" class="form-control form-control-solid text-gray-900" name="activity" placeholder="e.g. Visit Eiffel Tower" required />
                    </div>
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Location Venue</label>
                        <div class="position-relative d-flex align-items-center">
                            <i class="ki-outline ki-geolocation fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid ps-12" name="location" placeholder="e.g. Champ de Mars, Paris" />
                        </div>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Description</label>
                        <textarea class="form-control form-control-solid text-gray-900" name="description" rows="3" placeholder="Enter activity details or description..."></textarea>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Pricing Setting</label>
                            <select class="form-select form-select-solid" name="cost_type" id="add_cost_type" required>
                                <option value="total" selected>Total Cost / Estimation</option>
                                <option value="per_person">Price Per Person</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Number of Persons</label>
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-people fs-3 position-absolute ms-4"></i>
                                <input type="number" class="form-control form-control-solid ps-12" name="number_of_persons" id="add_number_of_persons" min="1" step="1" value="{{ $trip->number_of_persons ?? 1 }}" required />
                            </div>
                            <div class="text-muted fs-8 mt-1">Ubah jika tidak semua peserta ikut aktivitas ini.</div>
                        </div>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row" id="add_total_cost_container">
                            <label class="fs-6 fw-semibold mb-2">Estimated Total Cost</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">{{ $trip->currency }}</span>
                                <input type="number" class="form-control" name="cost_estimate" id="add_cost_estimate" placeholder="0" min="0" step="any" />
                            </div>
                        </div>
                        <div class="col-md-6 fv-row" id="add_per_person_cost_container">
                            <label class="fs-6 fw-semibold mb-2">Price Per Person</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">{{ $trip->currency }}</span>
                                <input type="number" class="form-control" name="cost_per_person" id="add_cost_per_person" placeholder="0" min="0" step="any" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary px-10">Add Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--begin::Modal - Edit Itinerary-->
<div class="modal fade" id="kt_modal_edit_itinerary" tabindex="-1" aria-hidden="true" data-persons="{{ $trip->number_of_persons ?? 1 }}">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="" method="POST" id="edit_itinerary_form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="fw-bold">Edit Activity</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row g-9 mb-8">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Planned Date & Time</label>
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-calendar-8 fs-3 position-absolute ms-4"></i>
                                <input type="text" 
                                    class="form-control form-control-solid ps-12 kt_flatpickr_datetime" placeholder="Select date time..."
                                    name="datetime" id="edit_datetime"
                                    required />
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Activity Name</label>
                        <input type="text" class="form-control form-control-solid text-gray-900" name="activity" id="edit_activity" placeholder="e.g. Visit Eiffel Tower" required />
                    </div>
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Location Venue</label>
                        <div class="position-relative d-flex align-items-center">
                            <i class="ki-outline ki-geolocation fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid ps-12" name="location" id="edit_location" placeholder="e.g. Champ de Mars, Paris" />
                        </div>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2">Description</label>
                        <textarea class="form-control form-control-solid text-gray-900" name="description" id="edit_description" rows="3" placeholder="Enter activity details or description..."></textarea>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Pricing Setting</label>
                            <select class="form-select form-select-solid" name="cost_type" id="edit_cost_type" required>
                                <option value="total">Total Cost / Estimation</option>
                                <option value="per_person">Price Per Person</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Number of Persons</label>
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-people fs-3 position-absolute ms-4"></i>
                                <input type="number" class="form-control form-control-solid ps-12" name="number_of_persons" id="edit_number_of_persons" min="1" step="1" required />
                            </div>
                            <div class="text-muted fs-8 mt-1">Ubah jika tidak semua peserta ikut aktivitas ini.</div>
                        </div>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row" id="edit_total_cost_container">
                            <label class="fs-6 fw-semibold mb-2">Estimated Total Cost</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">{{ $trip->currency }}</span>
                                <input type="number" class="form-control" name="cost_estimate" id="edit_cost_estimate" placeholder="0" min="0" step="any" />
                            </div>
                        </div>
                        <div class="col-md-6 fv-row" id="edit_per_person_cost_container">
                            <label class="fs-6 fw-semibold mb-2">Price Per Person</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">{{ $trip->currency }}</span>
                                <input type="number" class="form-control" name="cost_per_person" id="edit_cost_per_person" placeholder="0" min="0" step="any" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary px-10">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!--begin::Modal - Add Budget-->
<div class="modal fade" id="kt_modal_add_budget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="{{ route('travel.budgets.store') }}" method="POST">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <div class="modal-header">
                    <h2 class="fw-bold">Budget Allocation</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2 text-gray-700">Category</label>
                        <select class="form-select form-select-solid" name="category" data-control="select2" data-placeholder="Choose category" data-dropdown-parent="#kt_modal_add_budget" data-minimum-results-for-search="0" required>
                            <option value="Transportasi">Transportasi</option>
                            <option value="Akomodasi">Akomodasi</option>
                            <option value="Makan & Minum">Makan & Minum</option>
                            <option value="Wisata & Hiburan">Wisata & Hiburan</option>
                            <option value="Belanja & Oleh-oleh">Belanja & Oleh-oleh</option>
                            <option value="Dana Darurat">Dana Darurat</option>
                        </select>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Allocation Amount</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">{{ $trip->currency }}</span>
                            <input type="number" class="form-control" name="amount" placeholder="0" required />
                        </div>
                    </div>
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold mb-2">Notes</label>
                        <textarea class="form-control form-control-solid" name="notes" rows="3" placeholder="Brief explanation of this allocation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="submit" class="btn btn-primary px-10">Allocate Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--begin::Modal - Edit Budget-->
<div class="modal fade" id="kt_modal_edit_budget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="" method="POST" id="edit_budget_form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="fw-bold">Edit Budget Allocation</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2 text-gray-700">Category</label>
                        <select class="form-select form-select-solid" name="category" id="edit_budget_category" data-control="select2" data-placeholder="Choose category" data-dropdown-parent="#kt_modal_edit_budget" data-minimum-results-for-search="0" required>
                            <option value="Transportasi">Transportasi</option>
                            <option value="Akomodasi">Akomodasi</option>
                            <option value="Makan & Minum">Makan & Minum</option>
                            <option value="Wisata & Hiburan">Wisata & Hiburan</option>
                            <option value="Belanja & Oleh-oleh">Belanja & Oleh-oleh</option>
                            <option value="Dana Darurat">Dana Darurat</option>
                        </select>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Allocation Amount</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text">{{ $trip->currency }}</span>
                            <input type="number" class="form-control" name="amount" id="edit_budget_amount" placeholder="0" required />
                        </div>
                    </div>
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold mb-2">Notes</label>
                        <textarea class="form-control form-control-solid" name="notes" id="edit_budget_notes" rows="3" placeholder="Brief explanation of this allocation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary px-10">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--begin::Modal - Add Expense-->
<div class="modal fade" id="kt_modal_add_expense" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="{{ route('travel.expenses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <div class="modal-header">
                    <h2 class="fw-bold text-danger">Record Real Expense</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                             <label class="required fs-6 fw-semibold mb-2">Transaction Date</label>
                             <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-calendar-8 fs-3 position-absolute ms-4"></i>
                                <input type="text" class="form-control form-control-solid ps-12 kt_flatpickr_date" name="date" required value="{{ date('Y-m-d') }}" />
                             </div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Category</label>
                            <select class="form-select form-select-solid" name="category" data-control="select2" data-placeholder="Choose category" data-dropdown-parent="#kt_modal_add_expense" data-minimum-results-for-search="0" required>
                                <option value="Transportasi">Transportasi</option>
                                <option value="Akomodasi">Akomodasi</option>
                                <option value="Makan & Minum">Makan & Minum</option>
                                <option value="Wisata & Hiburan">Wisata & Hiburan</option>
                                <option value="Belanja & Oleh-oleh">Belanja & Oleh-oleh</option>
                                <option value="Dana Darurat">Dana Darurat</option>
                            </select>
                        </div>
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Reason / Description</label>
                        <input type="text" class="form-control form-control-solid" name="description" placeholder="e.g. Lunch at Cafe" required />
                    </div>
                    <div class="fv-row mb-8">
                        <label class="required fs-6 fw-semibold mb-2">Amount Spent</label>
                        <div class="input-group input-group-solid border border-danger border-dashed rounded">
                            <span class="input-group-text bg-light-danger text-danger fw-bold">{{ $trip->currency }}</span>
                            <input type="number" class="form-control" name="amount" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="submit" class="btn btn-danger px-10">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--begin::Modal - Add Packing Item-->
<div class="modal fade" id="kt_modal_add_packing_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="{{ route('travel.packing-items.store') }}" method="POST">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                <div class="modal-header">
                    <h2 class="fw-bold">Add Packing Item</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2 text-gray-700">Category</label>
                        <select class="form-select form-select-solid" name="category" data-control="select2" data-placeholder="Choose category" data-dropdown-parent="#kt_modal_add_packing_item" data-allow-clear="true" data-minimum-results-for-search="0">
                            <option></option>
                            <option value="Pakaian">Pakaian</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Dokumen">Dokumen</option>
                            <option value="Toiletries">Toiletries</option>
                            <option value="Obat-obatan">Obat-obatan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Item Name</label>
                            <input type="text" class="form-control form-control-solid" name="item_name" placeholder="e.g. Baju, Celana, Jaket" required />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Qty</label>
                            <input type="number" class="form-control form-control-solid" name="quantity" placeholder="1" min="1" value="1" required />
                        </div>
                    </div>
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold mb-2">Notes</label>
                        <textarea class="form-control form-control-solid" name="notes" rows="3" placeholder="Catatan tambahan (opsional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="submit" class="btn btn-primary px-10">Add to Checklist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--begin::Modal - Edit Packing Item-->
<div class="modal fade" id="kt_modal_edit_packing_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="" method="POST" id="edit_packing_item_form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="fw-bold">Edit Packing Item</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2 text-gray-700">Category</label>
                        <select class="form-select form-select-solid" name="category" id="edit_packing_category" data-control="select2" data-placeholder="Choose category" data-dropdown-parent="#kt_modal_edit_packing_item" data-allow-clear="true" data-minimum-results-for-search="0">
                            <option></option>
                            <option value="Pakaian">Pakaian</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Dokumen">Dokumen</option>
                            <option value="Toiletries">Toiletries</option>
                            <option value="Obat-obatan">Obat-obatan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="row g-9 mb-8">
                        <div class="col-md-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Item Name</label>
                            <input type="text" class="form-control form-control-solid" name="item_name" id="edit_packing_item_name" placeholder="e.g. Baju, Celana, Jaket" required />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Qty</label>
                            <input type="number" class="form-control form-control-solid" name="quantity" id="edit_packing_quantity" placeholder="1" min="1" required />
                        </div>
                    </div>
                    <div class="fv-row">
                        <label class="fs-6 fw-semibold mb-2">Notes</label>
                        <textarea class="form-control form-control-solid" name="notes" id="edit_packing_notes" rows="3" placeholder="Catatan tambahan (opsional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-primary px-10">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
