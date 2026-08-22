{{--
    Partial: _recurring-form.blade.php
    Reusable form fields for Create & Edit recurring activity modals.
    Usage:
        @include('pages.daily-planner._recurring-form', ['rec' => null])
        @include('pages.daily-planner._recurring-form', ['rec' => $rec, 'editMode' => true])
--}}
@php
    $editMode = $editMode ?? false;
    $prefix   = $editMode ? 'edit' : 'create';
    $freq     = old('frequency', $rec->frequency ?? 'daily');
@endphp

<div class="row g-4">

    {{-- Activity Name --}}
    <div class="col-12">
        <label class="form-label required">Nama Aktivitas</label>
        <input type="text" name="activity" class="form-control"
               placeholder="Contoh: Olahraga pagi, Membaca buku, Stand-up meeting..."
               value="{{ old('activity', $rec->activity ?? '') }}" required>
    </div>

    {{-- Frequency --}}
    <div class="col-md-6">
        <label class="form-label required">Frekuensi</label>
        <select name="frequency" class="form-select" id="{{ $prefix }}_frequency">
            <option value="daily"   {{ $freq === 'daily'   ? 'selected' : '' }}>Setiap Hari (Daily)</option>
            <option value="weekly"  {{ $freq === 'weekly'  ? 'selected' : '' }}>Mingguan (Weekly)</option>
            <option value="monthly" {{ $freq === 'monthly' ? 'selected' : '' }}>Bulanan (Monthly)</option>
        </select>
    </div>

    {{-- Is Active --}}
    <div class="col-md-6">
        <label class="form-label required">Status</label>
        <select name="is_active" class="form-select">
            <option value="1" {{ old('is_active', $rec->is_active ?? true) == 1 ? 'selected' : '' }}>✅ Aktif – Generate otomatis</option>
            <option value="0" {{ old('is_active', $rec->is_active ?? true) == 0 ? 'selected' : '' }}>⏸️ Nonaktif – Hentikan sementara</option>
        </select>
    </div>

    {{-- Weekly Day Picker --}}
    <div class="col-12 weekly-fields" style="{{ $freq !== 'weekly' ? 'display:none;' : '' }}">
        <label class="form-label required">Pilih Hari dalam Seminggu</label>
        @php
            $allDays      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            $dayLabels    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
            $selectedDays = old('day_of_week', $rec->day_of_week ?? []);
        @endphp
        <div class="day-checkbox-group">
            @foreach($allDays as $idx => $day)
            <label class="day-check-label">
                <input type="checkbox" name="day_of_week[]" value="{{ $day }}"
                       class="day-check-input"
                       {{ in_array($day, (array)$selectedDays) ? 'checked' : '' }}>
                {{ $dayLabels[$idx] }}
            </label>
            @endforeach
        </div>
        <div class="form-text text-muted mt-1">Pilih satu atau lebih hari.</div>
    </div>

    {{-- Monthly Day Picker --}}
    <div class="col-md-4 monthly-fields" style="{{ $freq !== 'monthly' ? 'display:none;' : '' }}">
        <label class="form-label required">Tanggal per Bulan</label>
        <input type="number" name="day_of_month" class="form-control"
               placeholder="1 – 31" min="1" max="31"
               value="{{ old('day_of_month', $rec->day_of_month ?? '') }}">
        <div class="form-text text-muted">Berulang setiap tanggal tersebut.</div>
    </div>

    {{-- Start Date --}}
    <div class="col-md-6">
        <label class="form-label required">Tanggal Mulai</label>
        <input type="date" name="start_date" class="form-control"
               value="{{ old('start_date', $rec && $rec->start_date ? \Carbon\Carbon::parse($rec->start_date)->format('Y-m-d') : date('Y-m-d')) }}"
               required>
    </div>

    {{-- End Date --}}
    <div class="col-md-6">
        <label class="form-label">Tanggal Berakhir <span class="text-muted fs-7">(opsional)</span></label>
        <input type="date" name="end_date" class="form-control"
               value="{{ old('end_date', $rec && $rec->end_date ? \Carbon\Carbon::parse($rec->end_date)->format('Y-m-d') : '') }}">
        <div class="form-text text-muted">Kosongkan jika tidak ada batas waktu.</div>
    </div>

    {{-- Start Time --}}
    <div class="col-md-6">
        <label class="form-label required">Jam Mulai</label>
        <input type="time" name="start_time" class="form-control"
               value="{{ old('start_time', $rec->start_time ?? '08:00') }}" required>
    </div>

    {{-- Duration --}}
    <div class="col-md-6">
        <label class="form-label required">Durasi <span class="text-muted fs-7">(menit)</span></label>
        <div class="input-group">
            <input type="number" name="duration_minutes" class="form-control"
                   placeholder="60" min="1"
                   value="{{ old('duration_minutes', $rec->duration_minutes ?? 60) }}" required>
            <span class="input-group-text bg-light text-muted">menit</span>
        </div>
    </div>

</div>
