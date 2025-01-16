<?php

namespace App\Http\Controllers;

use App\Models\Enums\UserRole;
use App\Models\User;
use App\Rules\ColumnExistsRule;
use App\Rules\NotVerifiedEmailRule;
use App\Services\User\UserService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiJsonResponseTrait;
    use UsernameValidationRulesTrait;
    use PasswordValidationRulesTrait;
    use HandlesPagination;


    public function __construct(private readonly UserService $userService)
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'roles.*'    => [Rule::enum(UserRole::class)],
            'sort_field' => ['string', new ColumnExistsRule(User::getModel()->getTable())],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request);

        $query = User::orderBy($sortField, $sortDirection);
        if ($request->has('roles')) {
            $query->whereIn('role', $request->input('roles'));
        }

        $users = $query->paginate($perPage);

        return $this->paginateResponse($users);
    }

    public function getByUsername(string $username): JsonResponse
    {
        $user = User::whereUsername($username)
            ->first()
            ?->append(['materials_count', 'favourite_materials_count', 'comments_count', 'collected_likes_count', 'collected_downloads_count', 'collected_views_count']);

        return response()->json($user);
    }

    public function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'   => $this->getUsernameRules(),
            'email'      => ['required', 'email', (new NotVerifiedEmailRule())],
            'password'   => $this->getPasswordRules(false),
            'role'       => ['required', Rule::enum(UserRole::class)],
            'first_name' => ['string', 'nullable'],
            'last_name'  => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        User::create($request->only(['username', 'email', 'password', 'role', 'first_name', 'last_name']));

        return $this->successJsonResponse();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return $this->errorJsonResponse('Не найдено Пользователя с id ' . $id);
        }

        $validator = Validator::make($request->all(), [
            'username'   => $this->getUsernameRules(false, $user),
            'email'      => ['email', (new NotVerifiedEmailRule())->ignore($user->id)],
            'role'       => [Rule::enum(UserRole::class)],
            'first_name' => ['string', 'nullable'],
            'last_name'  => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $oldEmail = $user->email;

        $user->fill($request->only(['username', 'email', 'role', 'first_name', 'last_name']));
        if ($user->email !== $oldEmail) {
            $user->email_verified_at = null;
        }

        $user->save();
        return $this->successJsonResponse();
    }

    public function delete(int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return $this->errorJsonResponse('Не найдено Пользователя с id ' . $id);
        }

        $user->delete();
        return $this->successJsonResponse();
    }

    public function deleteMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        User::whereIn('id', $request->get('ids'))->delete();

        return $this->successJsonResponse();
    }

    public function canCreateSubmission(): JsonResponse
    {
        $activeSubmissionCount    = $this->userService->getUserActiveSubmissionCount(Auth::user());
        $maxActiveSubmissionCount = $this->userService->getMaximumActiveSubmissionCount();

        return response()->json([
            'can_create_submission'       => $activeSubmissionCount < $maxActiveSubmissionCount,
            'active_submission_count'     => $activeSubmissionCount,
            'max_active_submission_count' => $maxActiveSubmissionCount,
        ]);
    }
}
