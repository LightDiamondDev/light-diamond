<?php

namespace App\Http\Controllers;

use App\Models\Enums\GameEdition;
use App\Models\Enums\MaterialSubmissionStatus;
use App\Models\Enums\SubmissionType;
use App\Models\Material;
use App\Models\MaterialSubmission;
use App\Models\User;
use App\Registries\CategoryRegistry;
use App\Registries\CategoryType;
use App\Rules\ColumnExistsRule;
use App\Rules\ImageFileExistsRule;
use App\Rules\MaterialEditionRule;
use App\Rules\MaterialFileLocalizationRule;
use App\Rules\MaterialFileRule;
use App\Rules\MaterialFileSubmissionListRule;
use App\Rules\MaterialFileSubmissionRule;
use App\Rules\MaterialFileSubmissionTypeRule;
use App\Rules\MaterialHasNoSubmissionRule;
use App\Rules\MaterialLocalizationRule;
use App\Rules\MaterialSlugRule;
use App\Rules\MaterialVersionLocalizationRule;
use App\Rules\MaterialVersionRule;
use App\Rules\MaterialVersionSubmissionListRule;
use App\Rules\MaterialVersionSubmissionRule;
use App\Rules\MaterialVersionSubmissionTypeRule;
use App\Rules\OwnMaterialRule;
use App\Services\Material\Dto\MaterialSubmissionCreateDto;
use App\Services\Material\Dto\MaterialSubmissionUpdateDto;
use App\Services\Material\MaterialSubmissionService;
use App\Services\User\UserService;
use Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidatorInstance;

class MaterialSubmissionController extends Controller
{
    use ApiJsonResponseTrait;
    use HandlesPagination;

    const int MAX_ACTIVE_SUBMISSION_COUNT = 3;

    public function __construct(
        private readonly MaterialSubmissionService $materialSubmissionService,
        private readonly UserService               $userService,
        private readonly CategoryRegistry          $categoryRegistry
    )
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'status'     => [Rule::enum(MaterialSubmissionStatus::class)],
            'sort_field' => ['string', new ColumnExistsRule(MaterialSubmission::getModel()->getTable())],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $status = $request->enum('status', MaterialSubmissionStatus::class) ?? MaterialSubmissionStatus::Pending;

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request, 'updated_at');

        $materialSubmissionsPaginator = MaterialSubmission::whereStatus($status)
            ->orderBy($sortField, $sortDirection)
            ->with(['material'])
            ->paginate($perPage);

        return $this->paginateResponse($materialSubmissionsPaginator);
    }

    public function getById(int $id): JsonResponse|MaterialSubmission|null
    {
        $user = Auth::user();

        /** @var MaterialSubmission|null $materialSubmission */
        $materialSubmission = MaterialSubmission::find($id);

        if ($materialSubmission === null || (!$user->is_moderator && $materialSubmission->submitter_id !== $user->id)) {
            return response()->json(null);
        }

        if (in_array($materialSubmission->status, [MaterialSubmissionStatus::Accepted, MaterialSubmissionStatus::Rejected])) {
            $updatedAt = $materialSubmission->updated_at;

            $materialSubmission->load([
                'material'           => fn(BelongsTo $q) => $q->withoutGlobalScope(SoftDeletingScope::class)->with([
                    'state'    => fn(HasOne $q) => $q->getOneOfManySubQuery()->where('published_at', '<', $updatedAt),
                    'versions' => fn(HasMany $q) => $q->withoutGlobalScope(SoftDeletingScope::class)->where('published_at', '<', $updatedAt)->with([
                        'state' => fn(HasOne $q) => $q->getOneOfManySubQuery()->where('published_at', '<', $updatedAt),
                        'files' => fn(HasMany $q) => $q->withoutGlobalScope(SoftDeletingScope::class)->where('published_at', '<', $updatedAt)->with([
                            'state' => fn(HasOne $q) => $q->getOneOfManySubQuery()->where('published_at', '<', $updatedAt)
                        ]),
                    ]),
                ]),
                'versionSubmissions' => fn(HasMany $q) => $q->with([
                    'version'         => fn(BelongsTo $q) => $q->with([
                        'state' => fn(HasOne $q) => $q->getOneOfManySubQuery()->where('published_at', '<', $updatedAt)
                    ]),
                    'fileSubmissions' => fn(HasMany $q) => $q->with([
                        'file' => fn(BelongsTo $q) => $q->with([
                            'state' => fn(HasOne $q) => $q->getOneOfManySubQuery()->where('published_at', '<', $updatedAt)
                        ]),
                    ]),
                ]),
            ]);
        } else {
            $materialSubmission->load([
                'material' => fn(BelongsTo $q) => $q->withoutGlobalScopes()->with([
                    'versions' => fn(HasMany $q) => $q->withoutGlobalScopes()->with([
                        'files' => fn(HasMany $q) => $q->withoutGlobalScopes(),
                    ]),
                ]),
                'versionSubmissions'
            ]);
        }

        return $materialSubmission;
    }

    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'status'     => [Rule::enum(MaterialSubmissionStatus::class)],
            'sort_field' => ['string', new ColumnExistsRule(MaterialSubmission::getModel()->getTable())],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();

        if ($user->id !== $userId) {
            if (!$user->is_moderator) {
                return $this->errorJsonResponse("Вам недоступны заявки на публикацию пользователя с id $userId.");
            }

            $user = User::find($userId);
            if ($user === null) {
                return $this->errorJsonResponse("Не существует пользователя с id $userId.");
            }
        }

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request, 'updated_at');

        $status = $request->enum('status', MaterialSubmissionStatus::class) ?? MaterialSubmissionStatus::Pending;

        $query = MaterialSubmission
            ::whereSubmitterId($userId)
            ->whereStatus($status)
            ->with(['material'])
            ->orderBy($sortField, $sortDirection);

        $paginator = $query->paginate($perPage);

        return $this->paginateResponse($paginator);
    }

    public function assignModerator(Request $request, int $submissionId): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($submissionId);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Не найдена заявка на публикацию с id $submissionId.");
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

        $this->materialSubmissionService->assignModerator($materialSubmission, $moderator);

        return $this->successJsonResponse();
    }

    public function message(Request $request, int $submissionId): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($submissionId);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Не найдена заявка на публикацию с id $submissionId.");
        }
        if (
            ($materialSubmission->submitter_id !== Auth::id() || $materialSubmission->is_closed)
            && !Auth::user()->is_moderator
        ) {
            return $this->errorJsonResponse("Вы не можете присылать сообщения в эту заявку.");
        }

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:2', 'max:1000'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->message($materialSubmission, $request->get('message'));

        return $this->successJsonResponse();
    }

    public function createDraft(Request $request): JsonResponse
    {
        if (!Auth::user()->is_moderator) {
            $activeSubmissionCount = $this->userService->getUserActiveSubmissionCount(Auth::user());

            if ($activeSubmissionCount >= $this->userService->getMaximumActiveSubmissionCount()) {
                return $this->errorJsonResponse('Количество активных заявок на публикацию не может больше, чем ' . self::MAX_ACTIVE_SUBMISSION_COUNT . '.');
            }
        }

        $type = $request->enum('type', SubmissionType::class);
        if ($type === SubmissionType::Delete) {
            return $this->errorJsonResponse('Заявка на удаление не может быть черновиком.');
        }

        $validator = $this->makeNewMaterialSubmissionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $materialSubmission = $this->materialSubmissionService->createDraft($this->makeMaterialSubmissionCreateDto($request));

        return $this->successJsonResponse([
            'id' => $materialSubmission->id
        ]);
    }

    public function submitNew(Request $request): JsonResponse
    {
        if (!Auth::user()->is_moderator) {
            $activeSubmissionCount = $this->userService->getUserActiveSubmissionCount(Auth::user());

            if ($activeSubmissionCount >= $this->userService->getMaximumActiveSubmissionCount()) {
                return $this->errorJsonResponse('Количество активных заявок на публикацию не может больше, чем ' . self::MAX_ACTIVE_SUBMISSION_COUNT . '.');
            }
        }

        $validator = $this->makeNewMaterialSubmissionValidator($request->all());
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $materialSubmission = $this->materialSubmissionService->submitNew($this->makeMaterialSubmissionCreateDto($request));

        return $this->successJsonResponse([
            'id' => $materialSubmission->id
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($id);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Заявка с id $id не существует.");
        }

        if ($materialSubmission->type === SubmissionType::Delete) {
            return $this->errorJsonResponse('Нельзя изменить заявку на удаление.');
        }

        if (
            ($materialSubmission->status !== MaterialSubmissionStatus::Draft || $materialSubmission->submitter_id !== Auth::id()) &&
            ($materialSubmission->status !== MaterialSubmissionStatus::Pending || !Auth::user()->is_moderator)
        ) {
            return $this->errorJsonResponse("Вы не можете изменять заявку на публикацию с $id.");
        }

        $validator = $this->makeMaterialSubmissionUpdateValidator($request->all(), $materialSubmission);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->update($materialSubmission, $this->makeMaterialSubmissionUpdateDto($request));

        return $this->successJsonResponse([
            'submission' => $this->getById($id)
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($id);
        if ($materialSubmission === null
            || $materialSubmission->status !== MaterialSubmissionStatus::Draft
            || $materialSubmission->submitter_id !== Auth::id()
        ) {
            return $this->errorJsonResponse("У вас нет черновика с id $id.");
        }

        $validator = $this->makeMaterialSubmissionUpdateValidator($request->all(), $materialSubmission);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->submit($materialSubmission, $this->makeMaterialSubmissionUpdateDto($request));

        return $this->successJsonResponse([
            'submission' => $this->getById($id)
        ]);
    }

    public function requestChanges(Request $request, int $id): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($id);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Не найдена заявка на публикацию с id $id.");
        }
        if ($materialSubmission->status !== MaterialSubmissionStatus::Pending) {
            return $this->errorJsonResponse("Эту заявку на публикацию нельзя вернуть на доработку из-за её статуса.");
        }
        if ($materialSubmission->type === SubmissionType::Delete) {
            return $this->errorJsonResponse('Нельзя вернуть на доработку заявку на удаление.');
        }

        $validator = $this->makeMaterialSubmissionUpdateValidator($request->all(), $materialSubmission, [
            'action_details'         => ['required', 'array'],
            'action_details.message' => ['required', 'string', 'max:1000'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->requestChanges($materialSubmission, $this->makeMaterialSubmissionUpdateDto($request));

        return $this->successJsonResponse([
            'submission' => $this->getById($id)
        ]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($id);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Не найдена заявка на публикацию с id $id.");
        }
        if ($materialSubmission->status !== MaterialSubmissionStatus::Pending) {
            return $this->errorJsonResponse("Эту заявку на публикацию нельзя принять из-за её статуса.");
        }

        $validator = $this->makeMaterialSubmissionUpdateValidator($request->all(), $materialSubmission);
        $validator->sometimes(
            'slug',
            ['required', 'string', new MaterialSlugRule($materialSubmission->material->edition, $materialSubmission->material->category)],
            fn() => $materialSubmission->type === SubmissionType::Create
        );
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->accept($materialSubmission, $this->makeMaterialSubmissionUpdateDto($request));

        return $this->successJsonResponse([
            'submission' => $this->getById($id)
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $materialSubmission = MaterialSubmission::find($id);
        if ($materialSubmission === null) {
            return $this->errorJsonResponse("Не найдена заявка на публикацию с id $id.");
        }

        if ($materialSubmission->status !== MaterialSubmissionStatus::Pending) {
            return $this->errorJsonResponse("Эту заявку на публикацию нельзя отклонить из-за её статуса.");
        }

        $validator = $this->makeMaterialSubmissionUpdateValidator($request->all(), $materialSubmission, [
            'action_details'        => ['required', 'array'],
            'action_details.reason' => ['required', 'string', 'max:1000'],
        ]);
        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $this->materialSubmissionService->reject($materialSubmission, $this->makeMaterialSubmissionUpdateDto($request));

        return $this->successJsonResponse([
            'submission' => $this->getById($id)
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $submission = MaterialSubmission::find($id);
        if ($submission === null) {
            return $this->successJsonResponse();
        }

        $user = Auth::user();

        if (
            !$user->is_moderator
            && ($submission->submitter_id !== $user->id || $submission->status !== MaterialSubmissionStatus::Draft)
        ) {
            return $this->errorJsonResponse("Вы не можете удалить эту заявку на публикацию.");
        }

        $this->materialSubmissionService->delete($submission);
        return $this->successJsonResponse();
    }

    private function makeMaterialSubmissionCreateDto(Request $request): MaterialSubmissionCreateDto
    {
        return MaterialSubmissionCreateDto::fromArray(
            $request->only('material', 'material_state', 'type', 'version_submissions')
        );
    }

    private function makeMaterialSubmissionUpdateDto(Request $request): MaterialSubmissionUpdateDto
    {
        return MaterialSubmissionUpdateDto::fromArray(
            $request->only('material', 'material_state', 'version_submissions', 'action_details')
        );
    }

    private function makeNewMaterialSubmissionValidator(array $data, array $additionalRules = []): ValidatorInstance
    {
        $material     = isset($data['material']['id']) ? Material::find($data['material']['id']) : null;
        $categoryType = isset($data['material']['category']) ? CategoryType::tryFrom($data['material']['category']) : null;
        $category     = $categoryType ? $this->categoryRegistry->get($categoryType) : null;

        $validator = Validator::make($data, array_merge(
            [
                'material'                                                                     => ['bail', 'required', 'array'],
                'material.id'                                                                  => ['bail', 'required_if:type,UPDATE,DELETE', 'required_with:version_submissions.*.version_id', 'integer', new OwnMaterialRule(), new MaterialHasNoSubmissionRule()],
                'material.category'                                                            => ['bail', 'required_if:type,CREATE', 'string', Rule::enum(CategoryType::class)],
                'material.edition'                                                             => ['bail', 'nullable', 'string', Rule::enum(GameEdition::class), new MaterialEditionRule($category)],
                'material_state'                                                               => ['bail', 'required_if:type,CREATE', 'array'],
                'material_state.author_id'                                                     => ['bail', 'integer', Rule::exists(User::class, 'id')],
                'material_state.localizations'                                                 => ['bail', 'required_if:type,CREATE', 'array', 'min:1'],
                'material_state.localizations.*.language'                                      => ['bail', 'required', 'string', Rule::in(['ru'])],
                'material_state.localizations.*.cover'                                         => ['bail', 'required', 'string', new ImageFileExistsRule()],
                'material_state.localizations.*.title'                                         => ['bail', 'required', 'string', 'max:150'],
                'material_state.localizations.*.description'                                   => ['bail', 'required', 'string', 'max:180'],
                'material_state.localizations.*.content'                                       => ['bail', 'required', 'string', 'max:65535'],
                'type'                                                                         => ['bail', 'required', 'string', Rule::enum(SubmissionType::class)],
                'version_submissions.*.version_id'                                             => ['bail', 'required_if:version_submissions.*.type,UPDATE,DELETE', 'integer', 'distinct', new MaterialVersionRule($material)],
                'version_submissions.*.version_state.number'                                   => ['bail', 'required_if:version_submissions.*.type,CREATE', 'string', 'max:15'],
                'version_submissions.*.version_state.localizations'                            => ['bail', 'required_if:version_submissions.*.type,CREATE', 'array', 'min:1'],
                'version_submissions.*.version_state.localizations.*.language'                 => ['bail', 'required', 'string', Rule::in(['ru'])],
                'version_submissions.*.version_state.localizations.*.name'                     => ['bail', 'nullable', 'string', 'max:20'],
                'version_submissions.*.version_state.localizations.*.changelog'                => ['bail', 'nullable', 'string', 'max:2000'],
                'version_submissions.*.type'                                                   => ['bail', 'required', 'string', Rule::enum(SubmissionType::class)],
                'version_submissions.*.file_submissions.*.file'                                => ['bail', 'required', 'array', new MaterialFileRule($material)],
                'version_submissions.*.file_submissions.*.file.id'                             => ['bail', 'required_if:version_submissions.*.file_submissions.*.type,UPDATE,DELETE', 'integer', 'distinct'],
                'version_submissions.*.file_submissions.*.file.path'                           => ['bail', 'nullable', 'required_without:version_submissions.*.file_submissions.*.file.url', 'string', 'max:255'],
                'version_submissions.*.file_submissions.*.file.url'                            => ['bail', 'nullable', 'required_without:version_submissions.*.file_submissions.*.file.path', 'string', 'max:255'],
                'version_submissions.*.file_submissions.*.file.size'                           => ['bail', 'nullable', 'integer'],
                'version_submissions.*.file_submissions.*.file.extension'                      => ['bail', 'nullable', 'string'],
                'version_submissions.*.file_submissions.*.file_state'                          => ['bail', 'required_if:version_submissions.*.file_submissions.*.type,CREATE', 'array'],
                'version_submissions.*.file_submissions.*.file_state.localizations'            => ['bail', 'required_if:version_submissions.*.file_submissions.*.type,CREATE', 'array'],
                'version_submissions.*.file_submissions.*.file_state.localizations.*.language' => ['bail', 'required', 'string', Rule::in(['ru'])],
                'version_submissions.*.file_submissions.*.file_state.localizations.*.name'     => ['bail', 'required', 'string', 'max:100'],
                'version_submissions.*.file_submissions.*.type'                                => ['bail', 'required', 'string', Rule::enum(SubmissionType::class)],
            ],
            $additionalRules
        ));

        $validator->sometimes(
            'version_submissions',
            ['bail', 'present_if:type,CREATE', 'array', new MaterialVersionSubmissionListRule($material)],
            fn() => $category?->isDownloadable()
        );
        $validator->sometimes(
            'version_submissions.*.file_submissions',
            ['bail', 'present_if:version_submissions.*.type,CREATE', 'array', new MaterialFileSubmissionListRule()],
            fn() => $category?->isDownloadable()
        );
        $validator->sometimes(
            'version_submissions.*.version_state',
            ['bail', 'required', 'array'],
            fn() => $category?->isDownloadable()
        );

        return $validator;
    }

    private function makeMaterialSubmissionUpdateValidator(array $data, MaterialSubmission $submission, array $additionalRules = []): ValidatorInstance
    {
        $material     = $submission->material;
        $categoryType = isset($data['material']['category']) ? CategoryType::tryFrom($data['material']['category']) : null;
        $category     = $categoryType ? $this->categoryRegistry->get($categoryType) : null;

        $requiredIfMaterialCreate = Rule::requiredIf((fn() => $submission->type === SubmissionType::Create));

        $validator = Validator::make($data, array_merge(
            [
                'material'                                                                     => ['bail', 'required', 'array'],
                'material.category'                                                            => ['bail', $requiredIfMaterialCreate, 'string', Rule::enum(CategoryType::class)],
                'material.edition'                                                             => ['bail', 'nullable', 'string', Rule::enum(GameEdition::class), new MaterialEditionRule($category)],
                'material_state'                                                               => ['bail', 'nullable', $requiredIfMaterialCreate, 'array'],
                'material_state.author_id'                                                     => ['bail', 'integer', Rule::exists(User::class, 'id')],
                'material_state.localizations'                                                 => ['bail', $requiredIfMaterialCreate, 'array', 'min:1'],
                'material_state.localizations.*.id'                                            => ['bail', 'integer', 'distinct', new MaterialLocalizationRule($submission)],
                'material_state.localizations.*.language'                                      => ['bail', 'required', 'string', Rule::in(['ru'])],
                'material_state.localizations.*.cover'                                         => ['bail', 'required', 'string', new ImageFileExistsRule()],
                'material_state.localizations.*.title'                                         => ['bail', 'required', 'string', 'max:150'],
                'material_state.localizations.*.description'                                   => ['bail', 'required', 'string', 'max:180'],
                'material_state.localizations.*.content'                                       => ['bail', 'required', 'string', 'max:65535'],
                'version_submissions.*.id'                                                     => ['bail', 'integer', 'distinct', new MaterialVersionSubmissionRule($submission)],
                'version_submissions.*.version_id'                                             => ['bail', 'required_if:version_submissions.*.type,UPDATE,DELETE', 'integer', 'distinct', new MaterialVersionRule($material)],
                'version_submissions.*.version_state.number'                                   => ['bail', 'required_if:version_submissions.*.type,CREATE', 'string', 'max:15'],
                'version_submissions.*.version_state.localizations'                            => ['bail', 'required_if:version_submissions.*.type,CREATE', 'array', 'min:1'],
                'version_submissions.*.version_state.localizations.*.id'                       => ['bail', 'integer', 'distinct', new MaterialVersionLocalizationRule()],
                'version_submissions.*.version_state.localizations.*.language'                 => ['bail', 'required', 'string', Rule::in(['ru'])],
                'version_submissions.*.version_state.localizations.*.name'                     => ['bail', 'nullable', 'string', 'max:20'],
                'version_submissions.*.version_state.localizations.*.changelog'                => ['bail', 'nullable', 'string', 'max:2000'],
                'version_submissions.*.type'                                                   => ['bail', 'required', 'string', Rule::enum(SubmissionType::class), new MaterialVersionSubmissionTypeRule()],
                'version_submissions.*.file_submissions.*.id'                                  => ['bail', 'integer', 'distinct', new MaterialFileSubmissionRule()],
                'version_submissions.*.file_submissions.*.file'                                => ['bail', 'array', new MaterialFileRule($material)],
                'version_submissions.*.file_submissions.*.file.id'                             => ['bail', 'required_if:version_submissions.*.file_submissions.*.type,UPDATE,DELETE', 'integer', 'distinct'],
                'version_submissions.*.file_submissions.*.file.path'                           => ['bail', 'nullable', 'required_without:version_submissions.*.file_submissions.*.file.url', 'string', 'max:255'],
                'version_submissions.*.file_submissions.*.file.url'                            => ['bail', 'nullable', 'required_without:version_submissions.*.file_submissions.*.file.path', 'string', 'max:255'],
                'version_submissions.*.file_submissions.*.file.size'                           => ['bail', 'nullable', 'integer'],
                'version_submissions.*.file_submissions.*.file.extension'                      => ['bail', 'nullable', 'string'],
                'version_submissions.*.file_submissions.*.file_state'                          => ['bail', 'nullable', 'required_if:version_submissions.*.file_submissions.*.type,CREATE', 'array'],
                'version_submissions.*.file_submissions.*.file_state.localizations'            => ['bail', 'required_if:version_submissions.*.file_submissions.*.type,CREATE', 'array'],
                'version_submissions.*.file_submissions.*.file_state.localizations.*.id'       => ['bail', 'integer', 'distinct', new MaterialFileLocalizationRule()],
                'version_submissions.*.file_submissions.*.file_state.localizations.*.language' => ['bail', 'required', 'string', Rule::in(['ru'])],
                'version_submissions.*.file_submissions.*.file_state.localizations.*.name'     => ['bail', 'required', 'string', 'max:100'],
                'version_submissions.*.file_submissions.*.type'                                => ['bail', 'required', 'string', Rule::enum(SubmissionType::class), new MaterialFileSubmissionTypeRule()],
            ],
            $additionalRules
        ));

        $validator->sometimes(
            'version_submissions',
            ['bail', 'array', new MaterialVersionSubmissionListRule($material)],
            fn() => $category?->isDownloadable()
        );
        $validator->sometimes(
            'version_submissions.*.file_submissions',
            ['bail', 'array', new MaterialFileSubmissionListRule()],
            fn() => $category?->isDownloadable()
        );
        $validator->sometimes(
            'version_submissions.*.version_state',
            ['bail', 'required', 'array'],
            fn() => $category?->isDownloadable()
        );

        return $validator;
    }
}
