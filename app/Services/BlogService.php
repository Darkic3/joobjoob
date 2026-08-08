<?php

namespace App\Services;

use App\DTOs\BlogDTO;
use App\Helpers\ImageHandler;
use App\Models\Blog;

class BlogService
{
    protected const UPLOAD_FOLDER = 'blogs';

    public function store(BlogDTO $dto): Blog
    {
        $data = $this->prepareData($dto);
        $data['image'] = $this->handleImageUpload($dto->getImage());

        return Blog::create($data);
    }

    public function update(Blog $blog, BlogDTO $dto): bool
    {
        $data = $this->prepareData($dto);

        // اگر تصویر جدید آپلود شده باشد، تصویر قدیمی را حذف می‌کنیم
        if ($dto->getImage()) {
            $this->deleteImage($blog->image);
            $data['image'] = $this->handleImageUpload($dto->getImage());
        }

        return $blog->update($data);
    }

    public function destroy(Blog $blog): bool
    {
        $this->deleteImage($blog->image);

        return $blog->delete(); // به لطف SoftDeletes، حذف نرم انجام می‌شود
    }

    public function getPaginatedBlogs(array $filters, int $perPage = 15)
    {
        return Blog::query()
            ->with('category')
            ->when(! empty($filters['search']), fn ($q) => $q->where('title', 'like', '%'.$filters['search'].'%'))
            ->when(! empty($filters['category']), fn ($q) => $q->where('blog_category_id', $filters['category']))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn ($q) => $q->where('is_active', $filters['status']))
            ->latest()
            ->paginate($perPage);
    }

    private function prepareData(BlogDTO $dto): array
    {
        return [
            'title' => $dto->getTitle(),
            'slug' => $dto->getSlug(),
            'excerpt' => $dto->getExcerpt(),
            'content' => $dto->getContent(),
            'blog_category_id' => $dto->getBlogCategoryId(),
            'is_active' => $dto->getIsActive(),
            'show_in_first_page' => $dto->getShowInFirstPage(),
            'user_id' => $dto->getUserId(),
        ];
    }

    /**
     * مدیریت آپلود تصویر با استفاده از هلپر اختصاصی شما
     */
    private function handleImageUpload(?object $image): ?string
    {
        if (! $image) {
            return null;
        }

        // استفاده از ImageHandler (که خودش ImageUploader را صدا می‌زند)
        // اگر در آینده خواستید سایزهای مختلف تولید کنید، آرایه $sizes را به عنوان آرگومان سوم پاس دهید
        return ImageHandler::handle(self::UPLOAD_FOLDER, $image);
    }

    /**
     * حذف تصویر با استفاده از منطق File::delete و public_path
     * (برای جلوگیری از تداخل با تنظیمات Storage لاراول و هماهنگی کامل با ImageUploader)
     */
    private function deleteImage(?string $imageName): void
    {
        if (! $imageName) {
            return;
        }

        // حذف با هلپر اختصاصی شما (هماهنگ با ImageUploader)
        ImageHandler::delete(self::UPLOAD_FOLDER, $imageName);
    }
}
