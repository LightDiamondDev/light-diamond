<?php

namespace App\Services;

use App\Models\Enums\PostVersionActionType;
use App\Models\Enums\PostVersionStatus;
use App\Models\PostCategory;
use App\Models\PostVersion;
use App\Models\PostVersionFile;
use App\Models\User;
use App\Services\Dto\NewPostVersionDto;
use App\Services\Dto\PostVersionActionDto;
use App\Services\Dto\PostVersionFileDto;
use App\Services\Dto\PostVersionUpdateDto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostVersionService
{
    public function __construct(
        private readonly PostVersionActionService $postVersionActionService,
        private readonly PostService              $postService
    )
    {
    }

    public function assignModerator(PostVersion $postVersion, User $moderator): void
    {
        Model::withoutTimestamps(fn() => $postVersion->assignedModerator()->associate($moderator)->save());

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::AssignModerator,
                ['moderator_id' => $moderator->id]
            ),
        );
    }

    public function createDraft(NewPostVersionDto $dto): PostVersion
    {
        return $this->createPostVersion($dto, PostVersionStatus::Draft);
    }

    public function submitNew(NewPostVersionDto $dto): PostVersion
    {
        $now = Carbon::now();
        $postVersion = $this->createPostVersion($dto, PostVersionStatus::Pending, $now);

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::Submit
            ),
            $now
        );

        return $postVersion;
    }

    public function updateDraft(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Draft);
    }

    public function submit(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $now = Carbon::now();
        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Pending, $now);

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::Submit
            ),
            $now
        );
    }

    public function requestChanges(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $now = Carbon::now();

        $postVersion->assignedModerator()->associate(Auth::user());

        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Draft, $now);

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::RequestChanges,
                $dto->actionDetails
            ),
            $now
        );
    }

    public function accept(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $now = Carbon::now();
        $isNewPost = $postVersion->post_id === null;

        if ($isNewPost) {
            $post = $this->postService->create($dto->slug ?? Str::slug($dto->title), $now);
            $postVersion->post()->associate($post);
        }

        $postVersion->assignedModerator()->associate(Auth::user());

        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Accepted, $now);
        if (!$isNewPost) {
            $postVersion->post->update(['updated_at' => $now]);
        }

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::Accept
            ),
            $now
        );
    }

    public function reject(PostVersion $postVersion, PostVersionUpdateDto $dto): void
    {
        $now = Carbon::now();
        $this->updatePostVersion($postVersion, $dto, PostVersionStatus::Rejected, $now);

        $postVersion->assignedModerator()->associate(Auth::user());

        $this->postVersionActionService->create(
            new PostVersionActionDto(
                $postVersion,
                Auth::user(),
                PostVersionActionType::Reject,
                $dto->actionDetails
            ),
            $now
        );
    }

    private function createPostVersion(NewPostVersionDto $dto, PostVersionStatus $status, ?Carbon $dateTime = null): PostVersion
    {
        $postVersion = PostVersion::make();
        $postVersion->author()->associate($dto->author);
        $postVersion->category()->associate($dto->category);
        $postVersion->title = $dto->title;
        $postVersion->cover = $this->saveCover($dto->coverFile);
        $postVersion->description = $dto->description;
        $postVersion->content = $dto->content;
        $postVersion->status = $status;
        if ($dateTime !== null) {
            $postVersion->created_at = $dateTime;
            $postVersion->updated_at = $dateTime;
        }
        $postVersion->save();

        foreach ($dto->files as $fileDto) {
            $this->saveNewPostVersionFile($postVersion, $fileDto);
        }

        return $postVersion;
    }

    private function updatePostVersion(PostVersion $postVersion, PostVersionUpdateDto $dto, PostVersionStatus $status, ?Carbon $dateTime = null): void
    {
        if ($dto->categoryId !== null && $postVersion->category_id !== $dto->categoryId) {
            $postVersion->category()->associate(PostCategory::find($dto->categoryId));
        }
        if ($dto->title !== null) {
            $postVersion->title = $dto->title;
        }
        if ($dto->coverFile !== null) {
            $postVersion->cover = $this->saveCover($dto->coverFile);
        }
        if ($dto->description !== null) {
            $postVersion->description = $dto->description;
        }
        if ($dto->content !== null) {
            $postVersion->content = $dto->content;
        }
        if ($dateTime !== null) {
            $postVersion->updated_at = $dateTime;
        }

        if ($dto->files !== null) {
            $fileIds = array_filter(array_map(fn(PostVersionFileDto $fileDto) => $fileDto->id, $dto->files));

            $oldFiles = $postVersion->files;
            foreach ($oldFiles as $oldFile) {
                if (!in_array($oldFile->id, $fileIds)) {
                    $oldFile->delete();
                    if ($oldFile->path !== null && !PostVersionFile::where('path', $oldFile->path)->exists()) {
                        Storage::disk('private')->delete($oldFile->path);
                    }
                }
            }

            foreach ($dto->files as $fileDto) {
                if ($fileDto->id === null) {
                    $this->saveNewPostVersionFile($postVersion, $fileDto);
                } else {
                    $file = PostVersionFile::find($fileDto->id);
                    if ($fileDto->name !== null) {
                        $file->name = $fileDto->name;
                    }
                    if ($fileDto->url !== null && $file->path === null) {
                        $file->url = $fileDto->url;
                    }
                    $file->save();
                }
            }
        }

        $postVersion->status = $status;
        $postVersion->save();
    }

    private function saveCover(UploadedFile $coverFile): string
    {
        $coverPath = $coverFile->store('images', ['disk' => 'public']);
        return str_replace('public/', '', $coverPath);
    }

    private function saveNewPostVersionFile(PostVersion $postVersion, PostVersionFileDto $fileDto): void
    {
        $file = PostVersionFile::make();
        $file->postVersion()->associate($postVersion);
        $file->name = $fileDto->name;
        $file->path = $fileDto->path;
        $file->url = $fileDto->path === null ? $fileDto->url : null;
        $file->size = $fileDto->path === null ? $fileDto->size : Storage::disk('private')->size($fileDto->path);
        $file->save();
    }
}
