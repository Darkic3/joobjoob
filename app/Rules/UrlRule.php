<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UrlRule implements Rule
{
    public function __construct(
        protected string $modelClass,
        protected ?int $exceptId = null
    ) {}

    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        $model = new $this->modelClass;

        $query = $model->newQuery()
            ->where('slug', $value)
            ->whereNull('deleted_at');

        if ($this->exceptId) {
            $query->where('id', '!=', $this->exceptId);
        }

        return $query->count() === 0;
    }

    public function message(): string
    {
        return 'این آدرس قبلاً استفاده شده است.';
    }
}
