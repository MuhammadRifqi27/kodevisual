<x-default-layout>
    @section('title')
    Account Settings
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('account.settings') }}
    @endsection

    @push('styles')
    <!-- Standard Cropper.js CSS from CDN to ensure styling works -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .image-crop-container {
            width: 100%;
            height: 450px;
            background-color: #f8f9fa;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Ensure the image is block and takes full width of container */
        #cropImage {
            display: block;
            max-width: 100%;
        }

        /* Fix for Metronic/Bootstrap modal z-index vs Cropper selection */
        .cropper-container {
            direction: ltr;
            font-size: 0;
            line-height: 0;
            position: relative;
            -ms-touch-action: none;
            touch-action: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        .cropper-modal {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
    @endpush

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-10">
            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Success</h4>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10 shadow-sm">
        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details">
            <div class="card-title m-0">
                <i class="ki-duotone ki-user fs-1 text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                <h3 class="fw-bold m-0">Profile Details</h3>
            </div>
        </div>
        <div id="kt_account_profile_details" class="collapse show">
            <form id="kt_account_profile_details_form" class="form" action="{{ route('account.settings.profile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                        <div class="col-lg-8">
                            <div class="image-input image-input-outline {{ $user->avatar ? '' : 'image-input-empty' }}" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                <div class="image-input-wrapper w-125px h-125px" id="avatar_preview" style="background-image: {{ $user->avatar ? 'url('.$user->avatar_url.')' : 'none' }}"></div>
                                
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                    <input type="file" name="avatar" id="avatar_input" accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" name="avatar_remove" />
                                    <input type="hidden" name="avatar_cropped" id="avatar_cropped" />
                                </label>

                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>

                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <div class="form-text">Allowed file types: png, jpg, jpeg. (Support Cropping)</div>
                            @error('avatar')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">Full Name</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="name" class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" placeholder="Full name" value="{{ old('name', $user->name) }}" />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label required fw-semibold fs-6">Email Address</label>
                        <div class="col-lg-8 fv-row">
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror" placeholder="Email address" value="{{ old('email', $user->email) }}" />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                    <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Cropping -->
    <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Your Avatar</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div class="image-crop-container">
                        <img id="cropImage" src="" alt="Image to crop">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="cropBtn">Crop & Apply</button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.account.partials._signin-method')

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        let cropper;
        const cropModalEl = document.getElementById('cropModal');
        const cropModal = new bootstrap.Modal(cropModalEl);
        const avatarInput = document.getElementById('avatar_input');
        const cropImage = document.getElementById('cropImage');
        const cropBtn = document.getElementById('cropBtn');
        const avatarCropped = document.getElementById('avatar_cropped');
        const avatarPreview = document.getElementById('avatar_preview');

        avatarInput.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    cropImage.src = e.target.result;
                    cropModal.show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Initialize cropper after modal is fully shown to avoid display issues
        cropModalEl.addEventListener('shown.bs.modal', function () {
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        });

        cropModalEl.addEventListener('hidden.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            // Clear input so same file can be selected again
            avatarInput.value = '';
        });

        cropBtn.addEventListener('click', function () {
            if (!cropper) return;
            
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            const croppedData = canvas.toDataURL('image/jpeg', 0.9);
            avatarCropped.value = croppedData;
            avatarPreview.style.backgroundImage = `url(${croppedData})`;
            
            cropModal.hide();
        });

        // Basic form handling
        const signinBtn = document.getElementById('kt_signin_password_button');
        const cancelBtn = document.getElementById('kt_password_cancel');
        
        if (signinBtn) {
            signinBtn.addEventListener('click', function() {
                document.getElementById('kt_signin_password')?.classList.add('d-none');
                document.getElementById('kt_signin_password_button')?.classList.add('d-none');
                document.getElementById('kt_signin_password_edit')?.classList.remove('d-none');
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                document.getElementById('kt_signin_password')?.classList.remove('d-none');
                document.getElementById('kt_signin_password_button')?.classList.remove('d-none');
                document.getElementById('kt_signin_password_edit')?.classList.add('d-none');
            });
        }

        @if ($errors->has('current_password') || $errors->has('password'))
            document.getElementById('kt_signin_password')?.classList.add('d-none');
            document.getElementById('kt_signin_password_button')?.classList.add('d-none');
            document.getElementById('kt_signin_password_edit')?.classList.remove('d-none');
        @endif
    </script>
    @endpush
</x-default-layout>
