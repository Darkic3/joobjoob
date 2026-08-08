<?php

namespace App\Services;

use App\DTOs\SpecificationDTO;
use App\Models\Specification;

class SpecificationService
{
    public function store(SpecificationDTO $dto): Specification
    {
        return Specification::create($this->prepareData($dto));
    }

    public function update(Specification $specification, SpecificationDTO $dto): bool
    {
        return $specification->update($this->prepareData($dto));
    }

    public function destroy(Specification $specification): bool
    {
        // به لطف SoftDeletes + cascadeOnDelete حذف فرزندان انجام می‌شود
        return $specification->delete();
    }

    /**
     * لیست مشخصه‌های والد (گروه‌ها) به صورت درختی برای نمایش در پنل
     */
    public function getParents(): \Illuminate\Database\Eloquent\Collection
    {
        return Specification::whereNull('parent_id')->with('children')->orderBy('id')->get();
    }

    public function getPaginatedSpecifications(array $filters, int $perPage = 15)
    {
        return Specification::query()
            ->with('parent')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where('title->fa', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('title->en', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['type']) && $filters['type'] !== '', function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                $q->where('active', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    private function prepareData(SpecificationDTO $dto): array
    {
        return [
            'title'      => $dto->getTitle(),
            'type'       => $dto->getType(),
            'parent_id'  => $dto->getParentId(),
            'active'     => $dto->getIsActive(),
            'is_filter'  => $dto->getIsFilter(),
            'is_color'   => $dto->getIsColor(),
            'color_code' => $dto->getColorCode(),
        ];
    }
}
