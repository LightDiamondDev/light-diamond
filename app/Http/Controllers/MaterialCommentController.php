<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialComment;
use App\Rules\ColumnExistsRule;
use App\Rules\HtmlMinSymbolCountRule;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialCommentController extends Controller
{
    use ApiJsonResponseTrait, HandlesPagination;

    public function get(Request $request): JsonResponse
    {
        ['perPage' => $perPage] = $this->getPaginationParameters($request);

        $comments = MaterialComment
            ::with(['version.material', 'parentComment.version.material'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginateResponse($comments);
    }

    public function getMaterialComments(Request $request, int $materialId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sort_field' => ['string', new ColumnExistsRule(MaterialComment::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['sortField' => $sortField, 'sortDirection' => $sortDirection] = $this->getPaginationParameters($request);

        $materialComments = Material::find($materialId)->comments()->orderBy($sortField, $sortDirection)->get();

        return $this->successJsonResponse([
            'records' => $materialComments
        ]);
    }

    public function getUserComments(Request $request, int $userId): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'sort_field' => ['string', new ColumnExistsRule('material_comments')],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request);

        $comments = MaterialComment::whereUserId($userId)
            ->with(['version.material', 'parentComment.version.material'])
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);

        return $this->paginateResponse($comments);
    }

    public function submit(Request $request, int $materialId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content'           => ['required', 'string', 'max:65535', new HtmlMinSymbolCountRule(3)],
            'parent_comment_id' => ['integer', Rule::exists(MaterialComment::class, 'id')],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $material = Material::find($materialId);
        if ($material === null) {
            return $this->errorJsonResponse("Материал с id $materialId не найден.");
        }

        $parentComment = $request->has('parent_comment_id')
            ? MaterialComment::find($request->integer('parent_comment_id'))
            : null;

        $user = Auth::user();

        $comment          = MaterialComment::make();
        $comment->content = clean($request->get('content'), config('purifier.comment'));
        $comment->version()->associate($material->version);
        $comment->user()->associate($user);
        if ($parentComment !== null) {
            $comment->parentComment()->associate($parentComment);
        }

        $comment->save();

        return $this->successJsonResponse([
            'id' => $comment->id
        ]);
    }

    public function remove(int $commentId): JsonResponse
    {
        $comment = MaterialComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $user = Auth::user();
        if ($user->id !== $comment->user_id && !$user->is_moderator) {
            return $this->errorJsonResponse("У вас нет прав для удаления данного комментария.");
        }
        if (!$user->is_moderator && Carbon::now()->diffInMinutes($comment->created_at) >= 30) {
            return $this->errorJsonResponse("Комментарий нельзя удалить, так как он опубликован более 30 мин. назад.");
        }

        // Пока что принудительно удаляем комментарий из базы данных.
        // Позже лучше удалять не полностью, а помечать удалённым (delete вместо forceDelete).
        $comment->forceDelete();
        return $this->successJsonResponse();
    }

    public function edit(Request $request, int $commentId): JsonResponse
    {
        $comment = MaterialComment::find($commentId);
        if ($comment === null) {
            return $this->errorJsonResponse("Комментарий с id $commentId не найден.");
        }

        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:65535', new HtmlMinSymbolCountRule(3)],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        if ($user->id !== $comment->user_id) {
            return $this->errorJsonResponse("У вас нет прав для изменения данного комментария.");
        }
        if (Carbon::now()->diffInMinutes($comment->created_at) >= 30) {
            return $this->errorJsonResponse("Комментарий нельзя изменить, так как он опубликован более 30 мин. назад.");
        }

        $comment->content = $request->string('content');
        $comment->save();

        return $this->successJsonResponse();
    }
}
