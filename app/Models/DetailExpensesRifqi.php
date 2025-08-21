<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailExpensesRifqi extends Model
{
    use HasFactory;

    protected $table = 'rincian_pengeluaran_rifqi';

    protected $fillable = [
        'created_time',
        'detail_expenses',
        'cost',
        'master_category_expenses_id'
    ];

    public function masterCategory()
    {
        return $this->belongsTo(MasterCategoryExpenses::class, 'master_category_expenses_id');
    }
}
