<?php
namespace Modules\Review\Models;

use App\Models\User;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use SoftDeletes;

    public const string RATING_EXCELLENT = 'excellent';
    public const string RATING_VERY_GOOD = 'very_good';
    public const string RATING_AVERAGE = 'average';
    public const string RATING_POOR = 'poor';
    public const string RATING_TERRIBLE = 'terrible';
    public const string RATING_NOT_RATED = 'not_rated';

    public const array RATINGS = [
        self::RATING_EXCELLENT => 'Превосходный',
        self::RATING_VERY_GOOD => 'Очень хороший',
        self::RATING_AVERAGE => 'Хороший',
        self::RATING_POOR => 'Плохой',
        self::RATING_TERRIBLE => 'Ужасный',
    ];

    const string APPROVED = 'approved';

    protected $table    = 'bc_review';
    protected $fillable = [
        'object_id',
        'object_model',
        'title',
        'content',
        'rate_number',
        'author_ip',
        'status',
        'author_id',
    ];

    public static function getRatings(): array
    {
        return collect(self::RATINGS)
            ->map(fn($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->toArray();
    }

    public static function getDisplayTextScoreByLever($lever): string
    {
        return match ($lever) {
            5 => __("review.rate_text.excellent"),
            4 => __("review.rate_text.very_good"),
            3 => __("review.rate_text.average"),
            2 => __("review.rate_text.poor"),
            1, 0 => __("review.rate_text.terrible"),
            default => __("review.rate_text.not_rated"),
        };
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, "author_id")->withDefault([
            'name' => __("auth.labels.guest"),
            'avatar_url' => asset('uploads/0000/1/2026/11/14/avatar.png'),
            'is_guest' => true,
        ]);
    }
}
