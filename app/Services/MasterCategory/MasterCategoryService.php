<?php

namespace App\Services\MasterCategory;

use LaravelEasyRepository\BaseService;

interface MasterCategoryService extends BaseService
{
    public function masterCategoryStore($validate_data, $id = null);
}
