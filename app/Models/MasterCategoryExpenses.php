<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCategoryExpenses extends Model
{
    use HasFactory;

    protected $table = 'master_category_expenses';

    protected $fillable = [
        'name_category'
    ];
}
