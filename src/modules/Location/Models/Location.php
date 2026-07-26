<?php

    namespace Modules\Location\Models;

    use App\Models\BaseModel;
    use Kalnoy\Nestedset\NodeTrait;
    use Modules\Hotel\Models\Hotel;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Location extends BaseModel
    {
        use SoftDeletes, NodeTrait;

        protected $table         = 'bc_locations';
        protected $fillable      = [
            'name',
            'content',
            'image_id',
            'map_lat',
            'map_lng',
            'map_zoom',
            'status',
            'parent_id',
            'banner_image_id',
            'trip_ideas',
        ];
        protected string $slugField     = 'slug';
        protected string $slugFromField = 'name';
        protected string $seo_type      = 'location';
        protected $casts         = [
            'trip_ideas' => 'array'
        ];

        public static function getModelName()
        {
            return __("location.name.model_name");
        }
        public static function isEnable(): bool
        {
            return setting_item('location_disable') == false;
        }

        public function scopePublished($query)
        {
            return $query->where('status', 'publish');
        }

        public function hotels(): HasMany
        {
            return $this->hasMany(Hotel::class, 'location_id');
        }
    }
