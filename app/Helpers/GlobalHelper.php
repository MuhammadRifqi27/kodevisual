<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

if (!function_exists('user_role')) {
    function user_role()
    {
        $working_area = Auth::user()->working_area;
        $application_role_name = null;

        if (!empty($working_area) && isset($working_area[0]->roles[0]->application_role_name)) {
            $application_role_name = $working_area[0]->roles[0]->application_role_name;
        }

        return $application_role_name;
    }
}

if (!function_exists('user_role_display_name')) {
    function user_role_display_name()
    {
        $working_area = Auth::user()->working_area;
        $application_role_display_name = null;

        if (!empty($working_area) && isset($working_area[0]->roles[0]->application_role_display_name)) {
            $application_role_display_name = $working_area[0]->roles[0]->application_role_display_name;
        }

        return $application_role_display_name;
    }
}

if (!function_exists('user_department')) {
    function user_department()
    {
        $working_area = Auth::user()->working_area;
        $department = '-';

        if (!empty($working_area) && isset($working_area[0]->departments[0]->department_code)) {
            $department = $working_area[0]->departments[0]->department_code;
        }

        return $department;
    }
}

if (!function_exists('format_month_year')) {
    function format_month_year($date)
    {
        //Agustus 2024
        return Carbon::parse($date)->isoFormat('MMMM YYYY');
    }
}
if (!function_exists('format_date')) {
    function format_date($date)
    {
        // 20 Agustus 2024
        return Carbon::parse($date)->translatedFormat('d F Y');
    }
}

if (!function_exists('format_date_with_time')) {
    function format_date_with_time($date)
    {
        // 20 Agustus 2024 20:30
        return Carbon::parse($date)->translatedFormat('d F Y H:i');
    }
}
if (!function_exists('format_date_with_time_sec')) {
    function format_date_with_time_sec($date)
    {
        // 20 Agustus 2024 20:30:30
        return Carbon::parse($date)->translatedFormat('d F Y H:i:s');
    }
}

if (!function_exists('striped_format_date')) {
    function striped_format_date($date)
    {
        // 01-Apr-2025
        return Carbon::parse($date)->format('d-M-Y');
    }
}

if (!function_exists('today_format_date')) {
    function today_format_date()
    {
        // Tanggal hari ini dengan format = 20 Agustus 2024
        return Carbon::now()->isoFormat('DD MMMM YYYY');
    }
}

if (!function_exists('format_short_date')) {
    function format_short_date($date)
    {
        // 20/08/2024
        return Carbon::parse($date)->format('d/m/Y');
    }
}

if (!function_exists('format_currency')) {
    function format_currency($value)
    {
        // 10.000
        return number_format($value ?? 0, 0, ',', '.');
    }
}

if (!function_exists('get_time_remaining')) {
    function get_time_remaining($start_date, $end_date)
    {
        $start = Carbon::parse($start_date)->startOfDay();
        $end = Carbon::parse($end_date)->endOfDay(); // Akhiri di akhir hari
        $now = Carbon::now();

        if ($now->gt($end)) {
            return 'Expired';
        } elseif ($now->between($start, $end) || $now->equalTo($end)) {
            // Jika sekarang sama dengan end date, hitung sebagai 1 hari tersisa
            $remainingDays = $now->diffInDays($end);
            return ($remainingDays + 1) . ' days remaining';
        } else {
            return 'Starts in ' . $now->diffInDays($start) . ' days';
        }
    }
}

if (!function_exists('is_approval_status_approved')) {
    function is_approval_status_approved($status_code)
    {
        // Jika angka genap = approve, ganjil = reject
        return $status_code % 2 === 0;
    }
}

if (!function_exists('get_status_space_badge')) {
    function get_status_space_badge($status)
    {
        if ($status == null) {
            return "<span class='badge badge-secondary'>Belum Diajukan</span>";
        } elseif ($status == '0') {
            return "<span class='badge badge-secondary'>On Review</span>";
        } elseif ($status == '1') {
            return "<span class='badge badge-success'>Approved</span>";
        } elseif ($status == '2') {
            return "<span class='badge badge-primary'>Aktif</span>";
        } elseif ($status == '3') {
            return "<span class='badge badge-warning'>Non Aktif</span>";
        } elseif ($status == '4') {
            return "<span class='badge badge-danger'>Expired</span>";
        } else {
            return "<span class='badge badge-secondary'>-</span>";
        }
    }
}

if (!function_exists('get_is_renewal_space_badge')) {
    function get_is_renewal_space_badge($is_renewal)
    {
        if ($is_renewal == 0) {
            return "<span class='badge badge-primary'>Kontrak Baru</span>";
        } else {
            return "<span class='badge badge-info'>Kontrak Perpanjang</span>";
        }
    }
}
if (!function_exists('get_status_check_badge')) {
    function get_status_check_badge($status)
    {
        if ($status == '0') {
            return "<span class='badge badge-warning'>On Review</span>";
        } elseif ($status == '1') {
            return "<span class='badge badge-danger'>Reject</span>";
        } elseif ($status == '2') {
            return "<span class='badge badge-info'>Sudah Di Revisi</span>";
        } elseif ($status == '3') {
            return "<span class='badge badge-success'>Approved</span>";
        }
    }
}

if (!function_exists('get_approve_acct_badge')) {
    function get_approve_acct_badge($is_approve_acct)
    {
        if ($is_approve_acct == 1) {
            return "<span class='badge badge-primary'>Approved</span>";
        } else {
            return "<span class='badge badge-danger'>Belum Disetujui</span>";
        }
    }
}

if (!function_exists('get_is_paid_space_badge')) {
    function get_is_paid_space_badge($is_paid, $is_approve_acct)
    {
        if ($is_paid == true && $is_approve_acct == true) {
            return "<span class='badge badge-primary'>Approve Acct</span>";
        } elseif ($is_paid == true && $is_approve_acct == false) {
            return "<span class='badge badge-success'>Submit (Review Acct)</span>";
        } else {
            return "<span class='badge badge-danger'>Belum Submit</span>";
        }
    }
}

if (!function_exists('is_space_unpaid_and_overdue')) {
    function is_space_unpaid_and_overdue($is_approve_acct, $start_date)
    {
        $start_date = Carbon::parse($start_date);
        $now = Carbon::now();

        return $is_approve_acct == 0 && $now->gt($start_date);
    }
}
