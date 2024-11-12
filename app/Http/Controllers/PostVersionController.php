<?php

namespace App\Http\Controllers;

use App\Models\Enums\PostVersionActionType;
use App\Models\Enums\PostVersionStatus;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\PostVersionAction;
use App\Models\User;
use App\Registries\CategoryRegistry;
use App\Registries\CategoryType;
use App\Rules\ColumnExistsRule;
use App\Rules\ImageFileExistsRule;
use App\Rules\OwnPostRule;
use App\Rules\PostVersionFileRule;
use App\Services\Dto\NewPostVersionDto;
use App\Services\Dto\PostVersionFileDto;
use App\Services\Dto\PostVersionUpdateDto;
use App\Services\PostVersionService;
use Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator as ValidatorInstance;

class PostVersionController extends Controller
{
    use ApiJsonResponseTrait;

    const int MAX_COVER_SIZE_MB = 5;
    const int MIN_COVER_WIDTH   = 768;
    const int MIN_COVER_HEIGHT  = 432;

    const int MAX_FILES_COUNT = 3;

    public function __construct(
        private readonly PostVersionService $postVersionService,
        private readonly CategoryRegistry   $categoryRegistry
    )
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'     => [Rule::enum(PostVersionStatus::class)],
            'page'       => ['integer'],
            'per_page'   => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(PostVersion::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $status = $request->enum('status', PostVersionStatus::class) ?? PostVersionStatus::Pending;

        $defaultSortOrder = $status == PostVersionStatus::Pending ? 1 : -1;
        $defaultSortField = 'updated_at';

        $perPage   = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        if ($sortOrder === 0) {
            $sortField = $defaultSortField;
            $sortOrder = $defaultSortOrder;
        } else {
            $sortField = $request->string('sort_field', $defaultSortField);
        }
        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        $postVersionsPaginator = PostVersion::whereStatus($status)
            ->orderBy($sortField, $sortDirection)
            ->with(['assignedModerator', 'actions.user'])
            ->paginate($perPage);

        $postVersions = $postVersionsPaginator->getCollection()->each(
            fn(PostVersion $item) => $item->makeVisible('assigned_moderator_id')
        )->all();

        return $this->successJsonResponse([
            'records'    => $postVersions,
            'pagination' => [
                'total_records' => $postVersionsPaginator->total(),
                'current_page'  => $postVersionsPaginator->currentPage(),
                'total_pages'   => $postVersionsPaginator->lastPage(),
            ],
        ]);
    }

    public function getById(int $id): JsonResponse
    {
        $user = Auth::user();

        $query = PostVersion::with(['post', 'actions.user', 'files'])->with([
                'actions' => function (HasMany $query) use ($user) {
                    if (!$user->is_moderator) {
                        $query->whereNot('type', PostVersionActionType::AssignModerator);
                    }
                }
            ]
        );
        if ($user->is_moderator) {
            $query = $query->with('assignedModerator');
        }

        /** @var PostVersion|null $postVersion */
        $postVersion = $query->find($id);
        if ($postVersion === null || (!$user->is_moderator && $postVersion->author_id !== $user->id)) {
            return response()->json(null);
        }

        if ($user->is_moderator) {
            $postVersion->makeVisible('assigned_moderator_id');
            $postVersion->actions->each(function (PostVersionAction $action) {
                if ($action->type === PostVersionActionType::AssignModerator) {
                    $details              = $action->details;
                    $details['moderator'] = User::find($details['moderator_id']);
                    $action->details      = $details;
                }
            });
        } else {
            $postVersion->actions->each(function (PostVersionAction $action) {
                if ($action->type !== PostVersionActionType::Submit) {
                    $action->makeHidden('user_id');
                    $action->makeHidden('user');
                }
            });
        }

        return response()->json($postVersion);
    }

    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'     => [Rule::enum(PostVersionStatus::class)],
            'page'       => ['integer'],
            'per_page'   => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(PostVersion::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();

        if ($user->id !== $userId) {
            if (!$user->is_moderator) {
                return $this->errorJsonResponse("Вам недоступны версии материалов пользователя с id $userId.");
            }

            $user = User::find($userId);
            if ($user === null) {
                return $this->errorJsonResponse("Не существует пользователя с id $userId.");
            }
        }

        $defaultSortOrder = -1;
        $defaultSortField = 'updated_at';

        $status    = $request->enum('status', PostVersionStatus::class) ?? PostVersionStatus::Pending;
        $perPage   = $request->integer('per_page', 10);
        $sortOrder = $request->integer('sort_order', $defaultSortOrder);
        if ($sortOrder === 0) {
            $sortField = $defaultSortField;
            $sortOrder = $defaultSortOrder;
        } else {
            $sortField = $request->string('sort_field', $defaultSortField);
        }
        $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';

        $query = PostVersion
            ::whereAuthorId($userId)
            ->whereStatus($status)
            ->orderBy($sortField, $sortDirection)
            ->with(['actions.user'])->with([
                    'actions' => function (HasMany $query) use ($user) {
                        if (!$user->is_moderator) {
                            $query->whereNot('type', PostVersionActionType::AssignModerator);
                        }
                    }
                ]
            );

        if ($user->is_moderator) {
            $query = $query->with('assignedModerator');
        }

        $postVersionsPaginator = $query->paginate($perPage);
        $postVersions          = $postVersionsPaginator
            ->getCollection()
            ->each(function (PostVersion $postVersion) use ($user) {
                if ($user->is_moderator) {
                    $postVersion->makeVisible('assigned_moderator_id');
                } else {
                    $postVersion->actions->each(function (PostVersionAction $action) {
                        if ($action->type !== PostVersionActionType::Submit) {
                            $action->makeHidden('user_id');
                            $action->makeHidden('user');
                        }
                    });
                }
            })
            ->all();

        return $this->successJsonResponse([
            'records'    => $postVersions,
            'pagination' => [
                'total_records' => $postVersionsPaginator->total(),
                'current_page'  => $postVersionsPaginator->currentPage(),
                'total_pages'   => $postVersionsPaginator->lastPage(),
            ],
        ]);
    }

    public function assignModerator(Request $request, int $versionId): JsonResponse
    {
        $postVersion = PostVersion::find($versionId);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $versionId.");
        }

        $validator = Validator::make($request->all(), [
            'moderator_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $moderator = User::find($request->integer('moderator_id'));
        if ($moderator === null || !$moderator->is_moderator) {
            return $this->errorJsonResponse('Не существует модератора с заданным id', $validator->errors());
        }

        $this->postVersionService->assignModerator($postVersion, $moderator);

        return $this->successJsonResponse();
    }

    public function createDraft(Request $request): JsonResponse
    {
        $validator = $this->makeNewPostVersionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = $this->postVersionService->createDraft($this->makeNewPostVersionDto($request));

        return $this->successJsonResponse([
            'id' => $postVersion->id
        ]);
    }

    public function submitNew(Request $request): JsonResponse
    {
        $validator = $this->makeNewPostVersionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $postVersion = $this->postVersionService->submitNew($this->makeNewPostVersionDto($request));

        return $this->successJsonResponse([
            'id' => $postVersion->id
        ]);
    }

    public function updateDraft(Request $request, int $id): JsonResponse
    {
        $postVersion = PostVersion::find($id);
        if ($postVersion === null
            || $postVersion->status !== PostVersionStatus::Draft
            || $postVersion->author_id !== Auth::user()->id
        ) {
            return $this->errorJsonResponse("У вас нет черновика с id $id.");
        }

        $validator = $this->makePostVersionUpdateValidator($request->all(), $postVersion);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->postVersionService->updateDraft($postVersion, $this->makePostVersionUpdateDto($request));

        return $this->successJsonResponse();
    }


    public function submit(Request $request, int $id): JsonResponse
    {
        $postVersion = PostVersion::find($id);
        if ($postVersion === null
            || $postVersion->status !== PostVersionStatus::Draft
            || $postVersion->author_id !== Auth::user()->id
        ) {
            return $this->errorJsonResponse("У вас нет черновика с id $id.");
        }

        $validator = $this->makePostVersionUpdateValidator($request->all(), $postVersion);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->postVersionService->submit($postVersion, $this->makePostVersionUpdateDto($request));

        return $this->successJsonResponse();
    }

    public function requestChanges(Request $request, int $id): JsonResponse
    {
        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }
        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя вернуть на доработку из-за её статуса.");
        }

        $validator = $this->makePostVersionUpdateValidator($request->all(), $postVersion, [
            'details'         => ['required', 'array'],
            'details.message' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->postVersionService->requestChanges($postVersion, $this->makePostVersionUpdateDto($request));

        return $this->successJsonResponse();
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }
        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя принять из-за её статуса.");
        }

        $validator = $this->makePostVersionUpdateValidator($request->all(), $postVersion, [
            'slug' => ['string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->postVersionService->accept($postVersion, $this->makePostVersionUpdateDto($request));

        return $this->successJsonResponse();
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $postVersion = PostVersion::find($id);
        if ($postVersion === null) {
            return $this->errorJsonResponse("Не найдена версия материала с id $id.");
        }

        if ($postVersion->status !== PostVersionStatus::Pending) {
            return $this->errorJsonResponse("Эту версию материала нельзя отклонить из-за её статуса.");
        }

        $validator = $this->makePostVersionUpdateValidator($request->all(), $postVersion, [
            'details'        => ['required', 'array'],
            'details.reason' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->postVersionService->reject($postVersion, $this->makePostVersionUpdateDto($request));

        return $this->successJsonResponse();
    }

    private function makeNewPostVersionDto(Request $request): NewPostVersionDto
    {
        $files = array_map(
            fn(array $file) => new PostVersionFileDto(
                null,
                $file['name'],
                $file['path'] ?? null,
                $file['url'] ?? null,
                $file['size'] ?? null,
                $file['extension'] ?? null,
            ),
            $request->input('files')
        );

        return new NewPostVersionDto(
            $request->integer('post_id'),
            Auth::user(),
            $request->enum('category', CategoryType::class),
            $request->string('title'),
            $request->file('cover_file'),
            $request->string('cover'),
            $request->string('description'),
            $request->string('content'),
            $files,
        );
    }

    private function makePostVersionUpdateDto(Request $request): PostVersionUpdateDto
    {
        $files = array_map(
            fn(array $file) => new PostVersionFileDto(
                $file['id'] ?? null,
                $file['name'] ?? null,
                $file['path'] ?? null,
                $file['url'] ?? null,
                $file['size'] ?? null,
                $file['extension'] ?? null,
            ),
            $request->input('files', [])
        );

        return new PostVersionUpdateDto(
            $request->enum('category', CategoryType::class),
            $request->string('title'),
            $request->file('cover_file'),
            $request->string('description'),
            $request->string('content'),
            $request->input('details'),
            $request->string('slug'),
            $files
        );
    }

    private function makeNewPostVersionValidator(array $data, array $additionalRules = []): ValidatorInstance
    {
        $post          = $data['post_id'] ? Post::find($data['post_id']) : null;
        $categoryType  = $data['category'] ? CategoryType::tryFrom($data['category']) : null;
        $category      = $categoryType ? $this->categoryRegistry->get($categoryType) : null;
        $maxFilesCount = $category && $category->isDownloadable() ? self::MAX_FILES_COUNT : 0;
        $minFilesCount = $category && $category->isDownloadable() ? 1 : 0;

        return Validator::make($data, array_merge(
            [
                'post_id'      => ['bail', 'integer', new OwnPostRule()],
                'category'     => ['bail', 'required', 'string', Rule::enum(CategoryType::class)],
                'cover_file'   => [
                    'bail',
                    'required_without:cover',
                    File::types(['jpeg', 'jpg', 'png']),
                    File::image()
                        ->max(self::MAX_COVER_SIZE_MB * 1024)
                        ->dimensions(
                            Rule::dimensions()
                                ->minWidth(self::MIN_COVER_WIDTH)
                                ->minHeight(self::MIN_COVER_HEIGHT)
                        ),
                ],
                'cover_path'   => ['bail', 'string', new ImageFileExistsRule()],
                'title'        => ['bail', 'required', 'string', 'max:150'],
                'description'  => ['bail', 'required', 'string', 'max:255'],
                'content'      => ['bail', 'required', 'string', 'max:65535'],
                'files'        => ['bail', 'required', 'array', 'max:' . $maxFilesCount, 'min:' . $minFilesCount],
                'files.*.name' => ['bail', 'required', 'string', 'max:100'],
                'files.*.path' => ['bail', 'nullable', 'required_without:files.*.url', 'string', 'max:255'],
                'files.*.url'  => ['bail', 'nullable', 'required_without:files.*.path', 'string', 'max:255'],
                'files.*.size' => ['bail', 'nullable', 'integer'],
                'files.*'      => ['bail', 'array', new PostVersionFileRule($post)],
            ],
            $additionalRules
        ));
    }

    private function makePostVersionUpdateValidator(array $data, PostVersion $version, array $additionalRules = []): ValidatorInstance
    {
        $categoryType  = $data['category'] ? CategoryType::tryFrom($data['category']) : $version->category;
        $category      = $categoryType ? $this->categoryRegistry->get($categoryType) : null;
        $maxFilesCount = $category && $category->isDownloadable() ? self::MAX_FILES_COUNT : 0;
        $minFilesCount = $category && $category->isDownloadable() ? 1 : 0;

        return Validator::make($data, array_merge(
            [
                'category'     => ['bail', 'required', 'string', Rule::enum(CategoryType::class)],
                'cover_file'   => [
                    'bail',
                    File::types(['jpeg', 'jpg', 'png']),
                    File::image()
                        ->max(self::MAX_COVER_SIZE_MB * 1024)
                        ->dimensions(
                            Rule::dimensions()
                                ->minWidth(self::MIN_COVER_WIDTH)
                                ->minHeight(self::MIN_COVER_HEIGHT)
                        ),
                ],
                'title'        => ['bail', 'string', 'max:150'],
                'description'  => ['bail', 'string', 'max:255'],
                'content'      => ['bail', 'string', 'max:65535'],
                'files'        => ['bail', 'array', 'max:' . $maxFilesCount, 'min:' . $minFilesCount],
                'files.*.id'   => ['bail', 'required_without_all:files.*.path,files.*.url', 'integer'],
                'files.*.name' => ['bail', 'required_without:files.*.id', 'string', 'max:100'],
                'files.*.path' => ['bail', 'nullable', 'required_without_all:files.*.id,files.*.url', 'string', 'max:255'],
                'files.*.url'  => ['bail', 'nullable', 'required_without_all:files.*.id,files.*.path', 'string', 'max:255'],
                'files.*.size' => ['bail', 'nullable', 'integer'],
                'files.*'      => ['bail', 'array', new PostVersionFileRule($version->post, $version)],
            ],
            $additionalRules
        ));
    }
}
