<?php

namespace App\Repositories\FinanceSetting;

use App\Models\FinanceSetting;
use Illuminate\Support\Collection;
use LaravelEasyRepository\Implementations\Eloquent;

class FinanceSettingRepositoryImplement extends Eloquent implements FinanceSettingRepository
{
    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected $model;

    public function __construct(FinanceSetting $model)
    {
        $this->model = $model;
    }

    public function allForUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get()->pluck('value', 'key');
    }

    public function get(int $userId, string $key, $default = null)
    {
        return $this->model->where('user_id', $userId)->where('key', $key)->first()?->value ?? $default;
    }

    public function upsertMany(int $userId, array $keyValues): void
    {
        foreach ($keyValues as $key => $value) {
            $this->model->updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
