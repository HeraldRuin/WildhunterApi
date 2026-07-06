<?php

namespace Modules\Booking\Models;

use App\Models\BaseModel;
use Modules\Location\Traits\HasLocation;

class Bookable extends BaseModel
{
    use HasLocation;
}
