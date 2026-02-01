<!--begin::Sign-in Method-->
<div class="card mb-5 mb-xl-10 shadow-sm">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method" aria-expanded="true" aria-controls="kt_account_signin_method">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <i class="ki-duotone ki-key fs-1 text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
            <h3 class="fw-bold m-0">Security (Change Password)</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_signin_method" class="collapse show">
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Email Address-->
            <div class="d-flex flex-wrap align-items-center">
                <!--begin::Label-->
                <div id="kt_signin_password">
                    <div class="fs-6 fw-bold mb-1">Password</div>
                    <div class="fw-semibold text-gray-600">************</div>
                </div>
                <!--end::Label-->
                <!--begin::Action-->
                <div id="kt_signin_password_button" class="ms-auto">
                    <button class="btn btn-light btn-active-light-primary">Change Password</button>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Email Address-->
            
            <!--begin::Password change form-->
            <div id="kt_signin_password_edit" class="d-none flex-row-fluid">
                <!--begin::Form-->
                <form id="kt_signin_change_password" class="form" action="{{ route('account.settings.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row mb-1">
                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="current_password" class="form-label fs-6 fw-bold mb-3">Current Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid @error('current_password') is-invalid @enderror" name="current_password" id="current_password" />
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="password" class="form-label fs-6 fw-bold mb-3">New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror" name="password" id="password" />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="password_confirmation" class="form-label fs-6 fw-bold mb-3">Confirm New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid" name="password_confirmation" id="password_confirmation" />
                            </div>
                        </div>
                    </div>
                    <div class="fs-7 fw-semibold text-muted mb-7">Password must be at least 8 characters and contain symbols</div>
                    <div class="d-flex">
                        <button id="kt_password_submit" type="submit" class="btn btn-primary me-2 px-6">Update Password</button>
                        <button id="kt_password_cancel" type="button" class="btn btn-color-gray-500 btn-active-light-primary px-6">Cancel</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Password change form-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Sign-in Method-->

<!--begin::Deactivate Account-->
<div class="card shadow-sm">
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_deactivate">
        <div class="card-title m-0">
            <i class="ki-duotone ki-trash fs-1 text-danger me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            <h3 class="fw-bold m-0">Deactivate Account</h3>
        </div>
    </div>
    <div id="kt_account_deactivate" class="collapse show">
        <form id="kt_account_deactivate_form" class="form">
            <div class="card-body border-top p-9">
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                    <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">You are Deactivating Your Account</h4>
                            <div class="fs-6 text-gray-700">For extra security, this requires admin approval. Once deactivated, you will lose access to all your data.</div>
                        </div>
                    </div>
                </div>
                <div class="form-check form-check-solid fv-row">
                    <input name="deactivate" class="form-check-input" type="checkbox" value="" id="deactivate" />
                    <label class="form-check-label fw-semibold ps-2 fs-6" for="deactivate">I confirm my account deactivation</label>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button id="kt_account_deactivate_account_submit" type="button" class="btn btn-danger fw-semibold" onclick="alert('Feature not implemented yet. Please contact administrator.')">Deactivate Account</button>
            </div>
        </form>
    </div>
</div>
<!--end::Deactivate Account-->
