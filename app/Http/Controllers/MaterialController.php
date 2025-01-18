<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Enums\MaterialLoadPeriod;
use App\Http\Controllers\Enums\MaterialSortType;
use App\Models\Enums\GameEdition;
use App\Models\Material;
use App\Models\MaterialFile;
use App\Models\MaterialVersion;
use App\Models\MaterialView;
use App\Registries\CategoryRegistry;
use App\Registries\CategoryType;
use App\Rules\ColumnExistsRule;
use Auth;
use Illuminate\Database\Eloquent\Builder as Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    use ApiJsonResponseTrait, HandlesPagination;

    public function __construct(private readonly CategoryRegistry $categoryRegistry)
    {
    }

    public function get(Request $request): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'category'  => ['string', Rule::enum(CategoryType::class)],
            'edition'   => ['string', Rule::enum(GameEdition::class)],
            'term'      => ['string', 'nullable'],
            'sort_type' => [Rule::enum(MaterialSortType::class)],
            'period'    => [Rule::enum(MaterialLoadPeriod::class)],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['perPage' => $perPage] = $this->getPaginationParameters($request);

        $sortType = $request->enum('sort_type', MaterialSortType::class) ?? MaterialSortType::Latest;
        $period   = $request->enum('period', MaterialLoadPeriod::class) ?? MaterialLoadPeriod::AllTime;

        $materialsQuery = Material
            ::joinSub(
                MaterialVersion
                    ::withoutGlobalScopes()
                    ->whereIn('id', function (\Illuminate\Database\Query\Builder $query) {
                        $query->selectRaw('DISTINCT ON (material_id) id')
                            ->from('material_versions')
                            ->whereNull('deleted_at')
                            ->whereNotNull('published_at')
                            ->orderBy('material_id')
                            ->orderBy('published_at', 'desc');
                    }),
                'version',
                'materials.id',
                '=',
                'version.material_id'
            );

        $startDate = match ($period) {
            MaterialLoadPeriod::Day     => Carbon::now()->subDay(),
            MaterialLoadPeriod::Week    => Carbon::now()->subWeek(),
            MaterialLoadPeriod::Month   => Carbon::now()->subMonth(),
            MaterialLoadPeriod::Year    => Carbon::now()->subYear(),
            MaterialLoadPeriod::AllTime => null,
        };

        if ($startDate !== null) {
            $materialsQuery->where('version.published_at', '>=', $startDate);
        }

        switch ($sortType) {
            case MaterialSortType::Latest:
                $materialsQuery->orderBy('version.published_at', 'desc');
                break;
            case MaterialSortType::Popular:
                $materialsQuery
                    ->selectRaw('
                        (COALESCE((select count(*) from favourite_materials where materials.id = favourite_materials.material_id), 0) * 5) +
                        (COALESCE((select count(*) from material_likes where materials.id = material_likes.material_id), 0) * 4) +
                        (COALESCE((select count(*) from material_comments
                            inner join material_versions on material_versions.id = material_comments.version_id
                            where materials.id = material_versions.material_id), 0) * 3) +
                        views_count +
                        downloads_count as total_score
                    ')
                    ->orderBy('total_score', 'desc')
                    ->orderBy('version.published_at', 'desc');
                break;
        }

        $category   = $request->string('category');
        $edition    = $request->string('edition');
        $searchTerm = $request->string('term');

        if ($category->isNotEmpty()) {
            $materialsQuery->where('category', $category);
        }
        if ($edition->isNotEmpty()) {
            $materialsQuery->where(
                fn(Builder $query) => $query->where('edition', $edition)->orWhereNull('edition')
            );
        }

        if ($searchTerm->isNotEmpty()) {
            $materialsQuery->whereHas('state', function (Builder $query) use ($searchTerm) {
                $query->join('material_localizations', 'material_localizations.material_state_id', '=', 'material_states.id')
                    ->where('title', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }

        $materials = $materialsQuery->paginate($perPage);

        return $this->paginateResponse($materials);
    }

    public function getBySlug(Request $request, string $slug): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => ['required', 'string', Rule::enum(CategoryType::class)],
            'edition'  => ['nullable', 'string', Rule::enum(GameEdition::class)],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $category = $request->string('category');
        $edition  = $request->string('edition');

        $materialQuery = Material
            ::where('slug', $slug)
            ->where('category', $category)
            ->with(['state.localizations', 'versions.state.localizations', 'versions.files.state.localizations']);
        if ($edition->isNotEmpty()) {
            $materialQuery->where(
                fn(Builder $query) => $query->where('edition', $edition)->orWhereNull('edition')
            );
        }
        $material = $materialQuery->first();

        if ($material !== null) {
            $ip      = request()->ip();
            $version = $material->version;

            $isFirstVersionView = !MaterialView::whereVersionId($version->id)->whereIp($ip)->exists();
            if ($isFirstVersionView) {
                $isFirstView = !MaterialView::ofMaterial($material->id)->whereIp($ip)->exists();
                if ($isFirstView) {
                    Material::withoutTimestamps(function () use ($material) {
                        Material::whereId($material->id)->increment('views_count');
                    });
                }

                $newVersionView     = MaterialView::make();
                $newVersionView->ip = $ip;
                $newVersionView->version()->associate($version);
                $newVersionView->save();
            }
        }

        $user = Auth::user();
        $material?->versions->each(function (MaterialVersion $version) use ($material, $user) {
            $version->files->each(function (MaterialFile $file) use ($material, $user) {
                if ($user === null || !$user->is_moderator && $material->state->author_id !== $user->id) {
                    $file->makeHidden('url');
                }
            });
        });

        return response()->json($material);
    }

    public function getUserFavouriteMaterials(Request $request, int $userId): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'sort_field' => ['string', new ColumnExistsRule(Material::getModel()->getTable())],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request);

        $materials = Material::favouriteOfUser($userId)->orderBy($sortField, $sortDirection)->paginate($perPage);

        return $this->paginateResponse($materials);
    }

    public function getUserMaterials(Request $request, int $userId): JsonResponse
    {
        $validator = $this->validatePagination($request, [
            'sort_field' => ['string', new ColumnExistsRule(Material::getModel()->getTable())],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        ['perPage' => $perPage, 'sortField' => $sortField, 'sortDirection' => $sortDirection] =
            $this->getPaginationParameters($request);

        $materials = Material::ofUser($userId)->orderBy($sortField, $sortDirection)->paginate($perPage);

        return $this->paginateResponse($materials);
    }
}
