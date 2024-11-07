<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Enums\PostLoadPeriod;
use App\Http\Controllers\Enums\PostSortType;
use App\Models\Enums\GameEdition;
use App\Models\Enums\PostVersionStatus;
use App\Models\Post;
use App\Models\PostView;
use App\Registries\CategoryRegistry;
use App\Registries\CategoryType;
use App\Rules\ColumnExistsRule;
use Illuminate\Database\Eloquent\Builder as Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use ApiJsonResponseTrait;

    public function __construct(private readonly CategoryRegistry $categoryRegistry)
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category'  => ['string', Rule::enum(CategoryType::class)],
            'edition'   => ['string', 'nullable', Rule::enum(GameEdition::class)],
            'term'      => ['string', 'nullable'],
            'page'      => ['integer'],
            'per_page'  => ['integer'],
            'sort_type' => [Rule::enum(PostSortType::class)],
            'period'    => [Rule::enum(PostLoadPeriod::class)],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $perPage  = $request->integer('per_page', 10);
        $sortType = $request->enum('sort_type', PostSortType::class) ?? PostSortType::Latest;
        $period   = $request->enum('period', PostLoadPeriod::class) ?? PostLoadPeriod::Day;

        $postsQuery = match ($sortType) {
            PostSortType::Latest  => Post::orderBy('updated_at', 'desc'),
            PostSortType::Popular => Post::withCount(['likes', 'comments', 'views'])
                ->orderByRaw('(likes_count * 2) + (comments_count * 3) + views_count DESC')
                ->orderBy('updated_at', 'desc'),
        };

        $startDate = match ($period) {
            PostLoadPeriod::Day     => Carbon::now()->subDay(),
            PostLoadPeriod::Week    => Carbon::now()->subWeek(),
            PostLoadPeriod::Month   => Carbon::now()->subMonth(),
            PostLoadPeriod::Year    => Carbon::now()->subYear(),
            PostLoadPeriod::AllTime => null,
        };

        if ($startDate !== null) {
            $postsQuery->where('updated_at', '>=', $startDate);
        }

        $categoryType = $request->enum('category', CategoryType::class);
        $edition      = $request->enum('edition', GameEdition::class);
        $searchTerm   = $request->string('term');

        if ($categoryType !== null || $edition !== null || $searchTerm->isNotEmpty()) {
            $postsQuery->whereHas('versions', function (Builder $query) use ($searchTerm, $categoryType, $edition) {
                $query->whereIn('id', function (QueryBuilder $subQuery) {
                    $subQuery->selectRaw('MAX(id)')
                        ->from('post_versions')
                        ->where('status', PostVersionStatus::Accepted)
                        ->groupBy('post_id');
                });

                if ($categoryType !== null) {
                    $query->where('category', $categoryType);
                } elseif ($edition !== null) {
                    $categoryTypes = array_map(
                        fn($category) => $category->getType(),
                        $this->categoryRegistry->getByEdition($edition)
                    );
                    $query->whereIn('category', $categoryTypes);
                }

                if ($searchTerm->isNotEmpty()) {
                    $query->where(function ($query) use ($searchTerm) {
                        $query->where('title', 'like', '%' . $searchTerm . '%')
                            ->orWhere('description', 'like', '%' . $searchTerm . '%');
                    });
                }
            });
        }

        $posts = $postsQuery->paginate($perPage);
        return $this->successJsonResponse([
            'records'    => $posts->items(),
            'pagination' => [
                'total_records' => $posts->total(),
                'current_page'  => $posts->currentPage(),
                'total_pages'   => $posts->lastPage(),
            ],
        ]);
    }

    public function getBySlug(string $slug): JsonResponse
    {
        $post = Post::whereSlug($slug)->first();
        if ($post !== null) {
            $postView = PostView::wherePostId($post->id)->first();
            if ($postView === null) {
                $newPostView     = PostView::make();
                $newPostView->ip = request()->ip();
                $newPostView->post()->associate($post);
                $newPostView->save();
            }
        }

        return response()->json($post);
    }

    public function getByUser(Request $request, int $userId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page'       => ['integer'],
            'per_page'   => ['integer'],
            'sort_field' => ['string', new ColumnExistsRule(Post::getModel()->getTable())],
            'sort_order' => ['integer', 'min:-1', 'max:1'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $defaultSortOrder = -1;
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

        $posts = Post::whereHas('versions', function ($query) use ($userId) {
            $query->where('author_id', $userId)->limit(1);
        })->orderBy($sortField, $sortDirection)->paginate($perPage);

        return $this->successJsonResponse([
            'records'    => $posts->items(),
            'pagination' => [
                'total_records' => $posts->total(),
                'current_page'  => $posts->currentPage(),
                'total_pages'   => $posts->lastPage(),
            ],
        ]);
    }
}
