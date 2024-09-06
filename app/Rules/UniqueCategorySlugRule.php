<?php

namespace App\Rules;

use App\Models\Enums\GameEdition;
use App\Models\PostCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class UniqueCategorySlugRule implements ValidationRule
{
    private mixed $ignoreId = null;
    private string $ignoreIdColumn = 'id';

    public function __construct(private readonly ?GameEdition $edition)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = PostCategory::whereSlug(mb_strtolower($value));
        if ($this->edition !== null) {
            $query = $query->whereEdition($this->edition);
        }
        if ($this->ignoreId !== null) {
            $query = $query->where($this->ignoreIdColumn, '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('Данный URL идентификатор уже занят.');
        }
    }

    public function ignore($id, $idColumn = null): self
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignoreId = $id;
        $this->ignoreIdColumn = $idColumn ?? $this->ignoreIdColumn;

        return $this;
    }

    public function ignoreModel($model, $idColumn = null): self
    {
        $this->ignoreIdColumn = $idColumn ?? $model->getKeyName();
        $this->ignoreId = $model->{$this->ignoreIdColumn};

        return $this;
    }
}
