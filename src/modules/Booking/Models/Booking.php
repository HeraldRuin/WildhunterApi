<?php

namespace Modules\Booking\Models;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\BaseModel;
use Carbon\Carbon;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Animals\Models\Animal;
use Modules\Booking\Services\BookingMailService;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Traits\HasPassenger;
use Modules\Coupon\Models\CouponBookings;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelAnimal;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\Wallet\Transaction;


class Booking extends BaseModel
{
    use SoftDeletes;
    use HasPassenger;
    protected $table      = 'bc_bookings';
    protected $cachedMeta = [];
    //protected $cachedMetaArr = [];
    const DRAFT      = 'draft'; // New booking, before payment processing
    const UNPAID     = 'unpaid'; // Require payment
    const PROCESSING = 'processing'; // обработка заказа
    const PREPAYMENT = 'prepayment';
    const FINISHED_PREPAYMENT = 'finish_prepayment';

    const CONFIRMED  = 'confirmed';
    const COMPLETED  = 'completed';
    const CANCELLED  = 'cancelled';
    const PAID       = 'paid';
    const PARTIAL_PAYMENT       = 'partial_payment';

    const START_COLLECTION = 'collection';
    const FINISHED_COLLECTION = 'finished_collection';
    const INVITATION = 'invitation';
    const PREPAYMENT_COLLECTION = 'prepayment_collection';
    const BED_COLLECTION = 'bed_collection';
    const FINISHED_BED = 'finish_bed_collection';

    //Типы бронирования
    const BookingTypeHotel = 'hotel';
    const BookingTypeAnimal = 'animal';
    const BookingTypeHotelAnimal = 'hotel_animal';

    protected $fillable = [
        'status',
        'is_all_places_assigned',
        'booking_number',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'commission' => 'array',
        'vendor_service_fee' => 'array',
    ];

    public static $notAcceptedStatus = [
        'draft','cancelled','unpaid'
    ];

    protected $appends = ['master_hunter_id','is_master_hunter', 'is_invited'];

    public function getGatewayObjAttribute()
    {
        return $this->gateway ? get_payment_gateway_obj($this->gateway) : false;
    }

    public function getStatusNameAttribute()
    {
        return booking_status_to_text($this->status);
    }






    public function isInvited($userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return false;
        }

        $isBookingHunter = BookingHunter::query()
            ->where('booking_id', $this->id)
            ->where('invited_by', $userId)
            ->whereNull('deleted_at')
            ->exists();

        if ($isBookingHunter) {
            return false;
        }

        return BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($q) {
                $q->where('booking_id', $this->id)
                    ->whereNull('deleted_at');
            })
            ->where('hunter_id', $userId)
            ->whereNotIn('status', ['declined', 'removed'])
            ->whereNull('deleted_at')
            ->exists();
    }

    public function getCurrentUserInvitation(): ?BookingHunterInvitation
    {
        $userId = Auth::id();

        if (!$userId) {
            return null;
        }

        return BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($q) {
                $q->where('booking_id', $this->id)
                    ->whereNull('deleted_at');
            })
            ->where('hunter_id', $userId)
            ->whereNotIn('status', ['declined', 'removed'])
            ->whereNull('deleted_at')
            ->with(['bookingHunter', 'hunter'])
            ->first();
    }

    public function invitationForUser(int $userId, bool $lockForUpdate = false): ?BookingHunterInvitation
    {
        $query = BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($query) {
                $query->where('booking_id', $this->id)
                    ->whereNull('deleted_at');
            })
            ->where('hunter_id', $userId)
            ->whereNull('deleted_at');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function invitationsQuery()
    {
        return BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($q) {
                $q->where('booking_id', $this->id);
            })
            ->with(['bookingHunter', 'hunter'])
            ->orderBy('invited_at', 'desc');
    }

    public function getAllInvitations()
    {
        return $this->invitationsQuery()->get();
    }

    public function getIsMasterHunterAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (!$this->relationLoaded('bookingHunters')) {
            $this->load('bookingHunters');
        }

        $hunter = $this->bookingHunters
            ->firstWhere('invited_by', auth()->id());

        return $hunter && (bool) $hunter->is_master;
    }

    public function getIsInvitedAttribute(): bool
    {
        return $this->isInvited(auth()->id());
    }

    public function getStatusForUserAttribute()
    {
        $userId = Auth::id();
        $creatorId = $this->create_user ?? $this->customer_id;

        if ($userId && $userId === $creatorId) {
            return $this->status;
        }

        if ($userId && $this->isInvited($userId)) {
            return match ($this->status) {
                self::CANCELLED,
                self::PROCESSING,
                self::CONFIRMED,
                self::FINISHED_COLLECTION,
                self::PREPAYMENT_COLLECTION,
                self::FINISHED_PREPAYMENT,
                self::BED_COLLECTION,
                self::FINISHED_BED,
                self::PAID,
                self::COMPLETED => $this->status,
                default => self::START_COLLECTION,
            };
        }
        return $this->status;
    }

    /**
     * Получает название статуса с учетом приглашения для текущего пользователя
     */
    public function getStatusNameForUserAttribute()
    {
        return booking_status_to_text($this->status_for_user);
    }

    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case "processing":
                return "primary";
                break;
            case "completed":
                return "success";
                break;
            case "confirmed":
                return "info";
                break;
            case "cancelled":
                return "danger";
                break;
            case "paid":
                return "info";
                break;
            case "partial_payment":
                return "warning";
                break;
            case "collection":
                return "warning";
                break;
            case "finished_collection":
                return "success";
                break;
        }
    }

    public function service()
    {
        $all = get_bookable_services();
        if ($this->object_model and !empty($all[$this->object_model])) {
            return $this->hasOne($all[$this->object_model], 'id', 'object_id');
        }
        return null;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function getCheckoutUrl(): string|UrlGenerator
    {
        $is_api = request()->segment(1) == 'api';
        return url(($is_api ? 'api/' : '').\app_get_locale(false,false , "/").config('booking.booking_route_prefix') . '/' . $this->code . '/checkout');
    }

    public function getDetailUrl($full = true)
    {
        $is_api = request()->segment(1) == 'api';
        if (!$full) {
            return ($is_api ? 'api/' : '').\app_get_locale(false,false , "/").config('booking.booking_route_prefix') . '/' . $this->code;
        }
        if($is_api){
            return route('booking.thankyou',['code'=>$this->code,'token'=>request()->input('token')]);
        }
        return url(($is_api ? 'api/' : '').\app_get_locale(false,false , "/").config('booking.booking_route_prefix') . '/' . $this->code);
    }

    public function getAllMeta()
    {
        $meta = DB::table('bc_booking_meta')->select(['name','val'])->where([
            'booking_id' => $this->id,
        ])->get();
        if (!empty($meta)) {
            return $meta;
        }
        return false;
    }

    public function getMeta($key, $default = '')
    {
        //if(isset($this->cachedMeta[$key])) return $this->cachedMeta[$key];
        $val = DB::table('bc_booking_meta')->where([
            'booking_id' => $this->id,
            'name'       => $key
        ])->first();
        if (!empty($val)) {
            //$this->cachedMeta[$key]  = $val->val;
            return $val->val;
        }
        return $default;
    }

    public function getJsonMeta($key, $default = [])
    {
        $meta = $this->getMeta($key, $default);
        if(empty($meta)) return false;
        return json_decode($meta, true);
    }

    public function addMeta($key, $val, $multiple = false)
    {

        if (is_object($val) or is_array($val))
            $val = json_encode($val);
        if ($multiple) {
            return DB::table('bc_booking_meta')->insert([
                'name'       => $key,
                'val'        => $val,
                'booking_id' => $this->id
            ]);
        } else {
            $old = DB::table('bc_booking_meta')->where([
                'booking_id' => $this->id,
                'name'       => $key
            ])->first();
            if ($old) {

                return DB::table('bc_booking_meta')->where('id', $old->id)->update([
                    'val' => $val
                ]);

            } else {
                return DB::table('bc_booking_meta')->insert([
                    'name'       => $key,
                    'val'        => $val,
                    'booking_id' => $this->id
                ]);
            }
        }
    }

    public function batchInsertMeta($metaArrs = [])
    {
        if (!empty($metaArrs)) {
            foreach ($metaArrs as $key => $val) {
                $this->addMeta($key, $val, true);
            }
        }
    }

    public function deleteMeta($key)
    {
        return DB::table('bc_booking_meta')->where([
            'booking_id' => $this->id,
            'name'       => $key
        ])->delete();
    }

    public function generateCode()
    {
        return md5(uniqid() . rand(0, 99999));
    }

    public function save(array $options = [])
    {
        if (empty($this->code))
            $this->code = $this->generateCode();

//        if (!empty($this->coupon_amount))
//            $this->updateStatusCoupons();

        $wasRecentlyCreated = !$this->exists;
        $result = parent::save($options);

        if ($result && $wasRecentlyCreated) {
            $this->createBookingHunterRecord();
        }

        return $result;
    }

    protected function createBookingHunterRecord(): void
    {
        $creatorId = $this->create_user ?? $this->customer_id;

        if (!$creatorId) {
            return;
        }

        $creator = \App\Models\User::find($creatorId);
        if (!$creator) {
            return;
        }

        $bookingHunter = BookingHunter::create([
            'booking_id' => $this->id,
            'invited_by' => $creatorId,
            'is_master' => $creator->hasRole('hunter'),
            'creator_role' => $creator->role->code ?? null,
        ]);

        BookingHunterInvitation::create([
            'booking_hunter_id' => $bookingHunter->id,
            'hunter_id' => $creatorId,
            'email' => $creator->email,
            'status' => 'accepted',
            'invited_at' => now(),
            'accepted_at' => now(),
            'invitation_token' => $this->code . '-' . $creatorId,
        ]);
    }

    public function markAsProcessing($payment, $service)
    {
        $this->status = static::PROCESSING;
        $this->save();
//        event(new BookingUpdatedEvent($this));
    }

    public function markAsPaid()
    {
        if($this->paid < $this->total){
            $this->status = static::PARTIAL_PAYMENT;
        }else{
            $this->status = static::PAID;
        }

        $this->save();
//        event(new BookingUpdatedEvent($this));
    }

    public function markAsPaymentFailed(){

        $this->status = static::UNPAID;
        $this->tryRefundToWallet();
        $this->save();
//        event(new BookingUpdatedEvent($this));
    }

    public function sendNewBookingEmails()
    {
        app(BookingMailService::class)->sendNewBooking($this);
    }

    public function sendStatusUpdatedEmails()
    {
        app(BookingMailService::class)->sendStatusUpdated($this);
    }

    /**
     * Get Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vendor()
    {
        return $this->hasOne(User::class, "id", 'vendor_id');
    }

    public static function getRecentBookings($limit = 10, $user_id = false, $hotel_id = false)
    {
        $q = parent::where('status', '!=', 'draft');
        if(!empty($user_id)){
            $q->where('customer_id', $user_id);
        }
        if(!empty($hotel_id)){
            $q->where('hotel_id', $hotel_id);
        }
        return $q->orderBy('id', 'desc')->limit($limit)->get();
    }

    public static function getTopCardsReport($hotel_id = false)
    {
        $res = [];
        $query = parent::whereNotIn('status', static::$notAcceptedStatus);
        $total_data = parent::selectRaw('sum(`total`) as total_price , sum( `total` - `total_before_fees` + `commission` - `vendor_service_fee_amount` ) AS total_earning ')->whereNotIn('status',static::$notAcceptedStatus)->first();

        if ($hotel_id) {
            $query->where('hotel_id', $hotel_id);
        }

        $total_booking = (clone $query)->count('id');
        $total_service = 0;
        $services = get_bookable_services();

        if(!empty($services))
        {
            foreach ($services as $service){
                $total_service += $service::where('status', 'publish')->count('id');
            }
        }
        $res[] = [
            'size'   => 6,
            'size_md'=>3,
            'title'  => __("Revenue"),
            'amount' => format_money_main($total_data->total_price),
            'desc'   => __("Total revenue"),
            'class'  => 'purple',
            'icon'   => 'icon ion-ios-cart'
        ];
        $res[] = [
            'size'   => 6,
            'size_md'=>3,
            'title'  => __("Earning"),
            'amount' => format_money_main($total_data->total_earning),
            'desc'   => __("Total Earning"),
            'class'  => 'pink',
            'icon'   => 'icon ion-ios-gift'
        ];
        $res[] = [

            'size'   => 6,
            'size_md'=>3,
            'title'  => __("Bookings"),
            'amount' => $total_booking,
            'desc'   => __("Total bookings"),
            'class'  => 'info',
            'icon'   => 'icon ion-ios-pricetags'
        ];
        $res[] = [

            'size'   => 6,
            'size_md'=>3,
            'title'  => __("Services"),
            'amount' => $total_service,
            'desc'   => __("Total bookable services"),
            'class'  => 'success',
            'icon'   => 'icon ion-ios-flash'
        ];
        return $res;
    }

    public static function getDashboardChartData($from, $to)
    {
        $data = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => __("Total Revenue"),
                    'data'            => [],
                    'backgroundColor' => '#8892d6',
                    'stack'           => 'group-total',
                ],
                [
                    'label'           => __("Total Earning"),
                    'data'            => [],
                    'backgroundColor' => '#F06292',
                    'stack'           => 'group-extra',
                ]
            ]
        ];
        $sql_raw[] = 'sum(`total`) as total_price';
        $sql_raw[] = 'sum( `total` - `total_before_fees` + `commission` - `vendor_service_fee_amount` ) AS total_earning';
        if (($to - $from) / DAY_IN_SECONDS > 90) {
            $year = date("Y", $from);
            // Report By Month
            for ($month = 1; $month <= 12; $month++) {
                $day_last_month = date("t", strtotime($year . "-" . $month . "-01"));
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    $year . '-' . $month . '-01 00:00:00',
                    $year . '-' . $month . '-' . $day_last_month . ' 23:59:59'
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['labels'][] = date("F", strtotime($year . "-" . $month . "-01"));
                $data['datasets'][0]['data'][] = $dataBooking->total_price ?? 0;
                $data['datasets'][1]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } elseif (($to - $from) <= DAY_IN_SECONDS) {
            // Report By Hours

            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += HOUR_IN_SECONDS) {
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    date('Y-m-d H:i:s', $i),
                    date('Y-m-d H:i:s', $i + HOUR_IN_SECONDS - 1),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['labels'][] = date('H:i', $i);
                $data['datasets'][0]['data'][] = $dataBooking->total_price ?? 0;
                $data['datasets'][1]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } else {
            // Report By Day
            $period = periodDate(date('Y-m-d', $from),date('Y-m-d 23:59:59', $to));
            foreach ($period as $dt){
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    $dt->format('Y-m-d 00:00:00'),
                    $dt->format('Y-m-d 23:59:59'),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['labels'][] = display_date($dt->getTimestamp());
                $data['datasets'][0]['data'][] = $dataBooking->total_price ?? 0;
                $data['datasets'][1]['data'][] = $dataBooking->total_earning ?? 0;
            }
        }
        return $data;
    }

    public static function getBookingHistory(
        $booking_status = false,
        $customer_id_or_name = false,
        $service = false,
        $from = false,
        $to = false,
        $booking_id = false
    ) {
        //  Получаем ID броней, куда пользователя пригласили, но не те которые он отменил
        $invitedBookingIds = [];
        if ($customer_id_or_name) {
            $invitedBookingIds = DB::table('bc_booking_hunter_invitations as i')
                ->join('bc_booking_hunters as h', 'i.booking_hunter_id', '=', 'h.id')
                ->where('i.hunter_id', $customer_id_or_name)
                ->where('i.invited', true)
                ->whereNull('i.deleted_at')
                ->whereNull('h.deleted_at')
                ->whereNotIn('i.status', ['declined'])
                ->where('h.invited_by', '!=', $customer_id_or_name)
                ->pluck('h.booking_id')
                ->toArray();
        }

        $list_booking = parent::query()
            ->with([
                'animal',
                'creator',
                'hotel.translation',
                'hotel.location',
                'roomsBooking.room',
                'bookingHunters:id,booking_id,invited_by,is_master,creator_role',
            ])
            ->where('status', '!=', 'draft')
            ->select('*')
            ->selectRaw("
            CASE
                WHEN id IN (" . (!empty($invitedBookingIds) ? implode(',', $invitedBookingIds) : '0') . ")
                THEN 'invitation'
                ELSE status
            END AS display_status
        ")
            ->orderBy('id', 'desc');

        if ($customer_id_or_name) {

            if ($booking_status === 'invitation') {
                // Вкладка "Приглашения" — только приглашённые брони
                if (!empty($invitedBookingIds)) {
                    $list_booking->whereIn('id', $invitedBookingIds);
                } else {
                    return $list_booking->whereRaw('0 = 1')->paginate(10);
                }

            } else {
                // Обычные вкладки — создатель или мастер охоты
                $list_booking->where(function ($q) use ($customer_id_or_name) {
                    $q->where('create_user', $customer_id_or_name)
                        ->orWhereHas('bookingHunters', function ($h) use ($customer_id_or_name) {
                            $h->where('is_master', 1)
                                ->where('invited_by', $customer_id_or_name);
                        });
                });
            }

        } else {
            // Для админа — фильтруем только "не сбор охотников"
            $list_booking->whereNotIn('status', ['collection']);
        }

        if ($booking_status && $booking_status !== 'invitation') {

            if ($booking_status === Booking::PREPAYMENT_COLLECTION) {

                $list_booking->whereIn('status', [
                    Booking::PREPAYMENT_COLLECTION,
                    Booking::FINISHED_PREPAYMENT,
                    Booking::BED_COLLECTION,
                    Booking::FINISHED_BED,
                ]);

            } else {
                $list_booking->where('status', $booking_status);
            }
        }


        if ($service) {
            $list_booking->where('object_model', $service);
        }

        if ($from && $to) {
            $list_booking->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);
        }

//        if ($booking_id) {
//            $list_booking->where('id', $booking_id);
//        }

        $objectModels = array_keys(get_bookable_services());
        if ($objectModels !== []) {
            $list_booking->whereIn('object_model', $objectModels);
        }

        return $list_booking->paginate(10);
    }

    public static function getBookingHistoryForAdminBase($hotel_id, $booking_status = false, $booking_id = false)
    {
        $list_booking = parent::query()
            ->with([
                'animal',
                'creator',
                'hotel.translation',
                'hotel.location',
                'roomsBooking.room',
                'bookingHunters:id,booking_id,invited_by,is_master',
            ])
            ->where('status', '!=', 'draft')
            ->orderBy('id', 'desc');

        if ($hotel_id !== null) {
            $list_booking->where('hotel_id', $hotel_id);
        } else {
            return $list_booking->whereRaw('0 = 1')->paginate(10);
        }

        if (!empty($booking_status)) {
            $list_booking->where('status', $booking_status);
        }

        if (!empty($booking_id)) {
            $list_booking->where('id', $booking_id);
        }

        $objectModels = array_keys(get_bookable_services());
        if ($objectModels !== []) {
            $list_booking->whereIn('object_model', $objectModels);
        }

        return $list_booking->paginate(10);
    }

    public static function getTopCardsReportForVendor($user_id)
    {

        $res = [];
        $total_money = parent::selectRaw('sum( `total_before_fees` - `commission` + `vendor_service_fee_amount` ) AS total_price , sum( CASE WHEN `status` = "completed" THEN `total_before_fees` - `commission` + `vendor_service_fee_amount` ELSE NULL END ) AS total_earning')->whereNotIn('status',static::$notAcceptedStatus)->where("vendor_id", $user_id)->first();
        $total_booking = parent::whereNotIn('status',static::$notAcceptedStatus)->where("vendor_id", $user_id)->count('id');
        $total_service = 0;
        $services = get_bookable_services();
        if(!empty($services))
        {
            foreach ($services as $service){
                $total_service += $service::where('status', 'publish')->where("create_user", $user_id)->count('id');
            }
        }
        $res[] = [
            'title'  => __("Pending"),
            'amount' => format_money_main($total_money->total_price - $total_money->total_earning),
            'desc'   => __("Total pending"),
            'class'  => 'purple',
            'icon'   => 'icon ion-ios-cart'
        ];
        $res[] = [
            'title'  => __("Earnings"),
            'amount' => format_money_main($total_money->total_earning ?? 0),
            'desc'   => __("Total earnings"),
            'class'  => 'info',
            'icon'   => 'icon ion-ios-gift'
        ];
        $res[] = [
            'title'  => __("Bookings"),
            'amount' => $total_booking,
            'desc'   => __("Total bookings"),
            'class'  => 'pink',
            'icon'   => 'icon ion-ios-pricetags'
        ];
        $res[] = [
            'title'  => __("Services"),
            'amount' => $total_service,
            'desc'   => __("Total bookable services"),
            'class'  => 'success',
            'icon'   => 'icon ion-ios-flash'
        ];
        return $res;
    }

    public static function getTopCardsReportForBaseAdmin($user_id)
    {
        $res = [];
        $total_money = parent::selectRaw('sum( `total_before_fees` - `commission` + `vendor_service_fee_amount` ) AS total_price , sum( CASE WHEN `status` = "completed" THEN `total_before_fees` - `commission` + `vendor_service_fee_amount` ELSE NULL END ) AS total_earning')->whereNotIn('status',static::$notAcceptedStatus)->where("vendor_id", $user_id)->first();
        $total_booking = parent::whereNotIn('status',static::$notAcceptedStatus)->where("vendor_id", $user_id)->count('id');
        $total_service = 0;
        $services = get_bookable_services();
        if(!empty($services))
        {
            foreach ($services as $service){
                $total_service += $service::where('status', 'publish')->where("create_user", $user_id)->count('id');
            }
        }
        $res[] = [
            'title'  => __("Pending"),
            'amount' => format_money_main($total_money->total_price - $total_money->total_earning),
            'desc'   => __("Total pending"),
            'class'  => 'purple',
            'icon'   => 'icon ion-ios-cart'
        ];
        $res[] = [
            'title'  => __("Earnings"),
            'amount' => format_money_main($total_money->total_earning ?? 0),
            'desc'   => __("Total earnings"),
            'class'  => 'info',
            'icon'   => 'icon ion-ios-gift'
        ];
        $res[] = [
            'title'  => __("Bookings"),
            'amount' => $total_booking,
            'desc'   => __("Total bookings"),
            'class'  => 'pink',
            'icon'   => 'icon ion-ios-pricetags'
        ];
        $res[] = [
            'title'  => __("Services"),
            'amount' => $total_service,
            'desc'   => __("Total bookable services"),
            'class'  => 'success',
            'icon'   => 'icon ion-ios-flash'
        ];
        return $res;
    }

    public static function getEarningChartDataForBaseAdmin($from, $to, $user_id): array
    {
        $data = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => __("Total Earning"),
                    'data'            => [],
                    'backgroundColor' => '#F06292'
                ],
                [
                    'label'           => __("Total Pending"),
                    'data'            => [],
                    'backgroundColor' => '#8892d6'
                ]
            ]
        ];
        $sql_raw[] = 'sum( `total_before_fees` - `commission` + `vendor_service_fee_amount`) AS total_price';
        $sql_raw[] = 'sum( CASE WHEN `status` = "completed" THEN `total_before_fees` - `commission` + `vendor_service_fee_amount` ELSE NULL END ) AS total_earning';
        if (($to - $from) / DAY_IN_SECONDS > 90) {
            $year = date("Y", $from);
            // Report By Month
            for ($month = 1; $month <= 12; $month++) {
                $day_last_month = date("t", strtotime($year . "-" . $month . "-01"));
                $data['labels'][] = date("F", strtotime($year . "-" . $month . "-01"));
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    $year . '-' . $month . '-01 00:00:00',
                    $year . '-' . $month . '-' . $day_last_month . ' 23:59:59'
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } elseif (($to - $from) <= DAY_IN_SECONDS) {
            // Report By Hours
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += HOUR_IN_SECONDS) {
                $data['labels'][] = date('H:i', $i);
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    date('Y-m-d H:i:s', $i),
                    date('Y-m-d H:i:s', $i + HOUR_IN_SECONDS - 1),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } else {
            // Report By Day
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += DAY_IN_SECONDS) {
                $data['labels'][] = display_date($i);
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', $i),
                    date('Y-m-d 23:59:59', $i),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        }
        return $data;
    }
    public static function getEarningChartDataForVendor($from, $to, $user_id)
    {
        $data = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => __("Total Earning"),
                    'data'            => [],
                    'backgroundColor' => '#F06292'
                ],
                [
                    'label'           => __("Total Pending"),
                    'data'            => [],
                    'backgroundColor' => '#8892d6'
                ]
            ]
        ];
        $sql_raw[] = 'sum( `total_before_fees` - `commission` + `vendor_service_fee_amount`) AS total_price';
        $sql_raw[] = 'sum( CASE WHEN `status` = "completed" THEN `total_before_fees` - `commission` + `vendor_service_fee_amount` ELSE NULL END ) AS total_earning';
        if (($to - $from) / DAY_IN_SECONDS > 90) {
            $year = date("Y", $from);
            // Report By Month
            for ($month = 1; $month <= 12; $month++) {
                $day_last_month = date("t", strtotime($year . "-" . $month . "-01"));
                $data['labels'][] = date("F", strtotime($year . "-" . $month . "-01"));
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    $year . '-' . $month . '-01 00:00:00',
                    $year . '-' . $month . '-' . $day_last_month . ' 23:59:59'
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } elseif (($to - $from) <= DAY_IN_SECONDS) {
            // Report By Hours
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += HOUR_IN_SECONDS) {
                $data['labels'][] = date('H:i', $i);
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    date('Y-m-d H:i:s', $i),
                    date('Y-m-d H:i:s', $i + HOUR_IN_SECONDS - 1),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        } else {
            // Report By Day
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += DAY_IN_SECONDS) {
                $data['labels'][] = display_date($i);
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->where("vendor_id", $user_id)->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', $i),
                    date('Y-m-d 23:59:59', $i),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                $dataBooking = $dataBooking->first();
                $data['datasets'][1]['data'][] = $dataBooking->total_price - $dataBooking->total_earning;
                $data['datasets'][0]['data'][] = $dataBooking->total_earning ?? 0;
            }
        }
        return $data;
    }

    public static function countBookingByServiceID($service_id = false, $user_id = false, $status = false)
    {
        if (empty($service_id))
            return false;
        $count = parent::query()->where("object_id", $service_id);

        if (!empty($status)) {
            if(is_array($status)){
                $count->whereIn("status", $status);
            }else{
                $count->where("status", $status);
            }
        }

        if (!empty($user_id)) {
            $count->where("customer_id", $user_id);
        }
        return $count->count("id");
    }

    public static function getAcceptedBookingQuery($service_id,$object_type){

        $q = static::query();

        return $q->where([
            ['object_id','=',$service_id],
            ['object_model','=',$object_type],
        ])->whereNotIn('status',static::$notAcceptedStatus);

    }

    public static function clearDraftBookings($day = 2)
    {
        return true;
    }

    public static function getStatisticChartData($from, $to, $statuses = false, $customer_id = false, $vendor_id = false)
    {
        // fix ver 1.5.1
        if ($statuses) {
            $list_statuses = [];
            foreach ($statuses as $status) {
                if(!in_array($status , static::$notAcceptedStatus) ){
                    $list_statuses[] = $status;
                }
            }
            $statuses = $list_statuses;
        }
        $data = [
            "chart"  => [
                'labels'   => [],
                'datasets' => [
                    [
                        'label'           => __("Total Revenue"),
                        'data'            => [],
                        'backgroundColor' => '#8892d6',
                        'stack'           => 'group-total',
                    ],
                    [
                        'label'           => __("Total Fees"),
                        'data'            => [],
                        'backgroundColor' => '#45bbe0',
                        'stack'           => 'group-extra',
                    ],
                    [
                        'label'           => __("Total Commission"),
                        'data'            => [],
                        'backgroundColor' => '#F06292',
                        'stack'           => 'group-extra',
                    ]
                ]
            ],
            "detail" => [
                "total_booking" => [
                    "title" => __("Total Booking"),
                    "val"   => 0,
                ],
                "total_price" => [
                    "title" => __("Total Revenue"),
                    "val"   => 0,
                ],
                "total_commission" => [
                    "title" => __("Total Commission"),
                    "val"   => 0,
                ],
                "total_fees" => [
                    "title" => __("Total Fees"),
                    "val"   => 0,
                ],
                "total_earning" => [
                    "title" => __("Total Earning"),
                    "val"   => 0,
                ],
            ]
        ];
        $sql_raw[] = 'sum(`total`) as total_price';
        $sql_raw[] = 'sum( CASE WHEN `total_before_fees` > 0 THEN  `total` - `total_before_fees` - `vendor_service_fee_amount` ELSE null END ) AS total_fees';
        $sql_raw[] = 'sum( `commission` ) AS total_commission';
        if ($statuses) {
            $sql_raw[] = "count( CASE WHEN `status` != 'draft' THEN id ELSE NULL END ) AS total_booking";
            foreach ($statuses as $status) {
                if(!in_array($status , static::$notAcceptedStatus) ){
                    $sql_raw[] = "count( CASE WHEN `status` = '{$status}' THEN id ELSE NULL END ) AS {$status}";
                }
            }
        }
        if (($to - $from) / DAY_IN_SECONDS > 90) {
            $year = date("Y", $from);
            // Report By Month
            for ($month = 1; $month <= 12; $month++) {
                $day_last_month = date("t", strtotime($year . "-" . $month . "-01"));
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    $year . '-' . $month . '-01 00:00:00',
                    $year . '-' . $month . '-' . $day_last_month . ' 23:59:59'
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['chart']['labels'][] = date("F", strtotime($year . "-" . $month . "-01"));
                $data['chart']['datasets'][0]['data'][] = $dataBooking->total_price ?? 0; // for total price
                $data['chart']['datasets'][1]['data'][] = $dataBooking->total_fees ?? 0; // for total fees
                $data['chart']['datasets'][2]['data'][] = $dataBooking->total_commission ?? 0; // for total commission
                $data['detail']['total_price']['val'] += ($dataBooking->total_price ?? 0);
                $data['detail']['total_booking']['val'] += $dataBooking->total_booking ?? 0;
                $data['detail']['total_commission']['val'] += $dataBooking->total_commission ?? 0;
                $data['detail']['total_fees']['val'] += $dataBooking->total_fees ?? 0;
                $data['detail']['total_earning']['val'] += ( $dataBooking->total_fees + $dataBooking->total_commission );
                if ($statuses) {
                    foreach ($statuses as $status) {
                        $data['detail'][$status]['title'] = booking_status_to_text($status);
                        $data['detail'][$status]['val'] = ($data['detail'][$status]['val'] ?? 0) + $dataBooking->$status ?? 0;
                    }
                }
            }
        } elseif (($to - $from) <= DAY_IN_SECONDS) {
            // Report By Hours
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += HOUR_IN_SECONDS) {
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    date('Y-m-d H:i:s', $i),
                    date('Y-m-d H:i:s', $i + HOUR_IN_SECONDS - 1),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['chart']['labels'][] = date('H:i', $i);
                $data['chart']['datasets'][0]['data'][] = $dataBooking->total_price ?? 0; // for total price
                $data['chart']['datasets'][1]['data'][] = $dataBooking->total_fees ?? 0; // for total fees
                $data['chart']['datasets'][2]['data'][] = $dataBooking->total_commission ?? 0; // for total commission
                $data['detail']['total_price']['val'] += ($dataBooking->total_price ?? 0);
                $data['detail']['total_booking']['val'] += $dataBooking->total_booking ?? 0;
                $data['detail']['total_commission']['val'] += $dataBooking->total_commission ?? 0;
                $data['detail']['total_fees']['val'] += $dataBooking->total_fees ?? 0;
                $data['detail']['total_earning']['val'] += ( $dataBooking->total_fees + $dataBooking->total_commission );
                if ($statuses) {
                    foreach ($statuses as $status) {
                        $data['detail'][$status]['title'] = booking_status_to_text($status);
                        $data['detail'][$status]['val'] = ($data['detail'][$status]['val'] ?? 0) + $dataBooking->$status ?? 0;
                    }
                }
            }
        } else {
            // Report By Day
            for ($i = strtotime(date('Y-m-d', $from)); $i <= strtotime(date('Y-m-d 23:59:59', $to)); $i += DAY_IN_SECONDS) {
                $dataBooking = parent::selectRaw(implode(",", $sql_raw))->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', $i),
                    date('Y-m-d 23:59:59', $i),
                ])->whereNotIn('status',static::$notAcceptedStatus);
                if (!empty($customer_id)) {
                    $dataBooking = $dataBooking->where('customer_id', $customer_id);
                }
                if (!empty($vendor_id)) {
                    $dataBooking = $dataBooking->where('vendor_id', $vendor_id);
                }
                $dataBooking = $dataBooking->first();
                $data['chart']['labels'][] = display_date($i);
                $data['chart']['datasets'][0]['data'][] = $dataBooking->total_price ?? 0; // for total price
                $data['chart']['datasets'][1]['data'][] = $dataBooking->total_fees ?? 0; // for total fees
                $data['chart']['datasets'][2]['data'][] = $dataBooking->total_commission ?? 0; // for total commission
                $data['detail']['total_price']['val'] += ($dataBooking->total_price ?? 0);
                $data['detail']['total_booking']['val'] += $dataBooking->total_booking ?? 0;
                $data['detail']['total_commission']['val'] += $dataBooking->total_commission ?? 0;
                $data['detail']['total_fees']['val'] += $dataBooking->total_fees ?? 0;
                $data['detail']['total_earning']['val'] += ( $dataBooking->total_fees + $dataBooking->total_commission );
                if ($statuses) {
                    foreach ($statuses as $status) {
                        $data['detail'][$status]['title'] = booking_status_to_text($status);
                        $data['detail'][$status]['val'] = ($data['detail'][$status]['val'] ?? 0) + $dataBooking->$status ?? 0;
                    }
                }
            }
        }
        $data['detail']['total_price']['val'] = format_money_main($data['detail']['total_price']['val']);
        $data['detail']['total_commission']['val'] = format_money_main($data['detail']['total_commission']['val']);
        $data['detail']['total_fees']['val'] = format_money_main($data['detail']['total_fees']['val']);
        $data['detail']['total_earning']['val'] = format_money_main($data['detail']['total_earning']['val']);
        return $data;
    }

    public function getDurationNightsAttribute()
    {
        if (empty($this->start_date) || empty($this->end_date)) {
            return 1;
        }

        return max(
            1,
            Carbon::parse($this->start_date)->startOfDay()
                ->diffInDays(Carbon::parse($this->end_date)->startOfDay())
        );
    }

    public function getDurationDaysAttribute()
    {
        return max(
            1,
            Carbon::parse($this->start_date)->startOfDay()
                ->diffInDays(
                    Carbon::parse($this->end_date)->startOfDay()
                )
        );
    }
    public function getDurationHoursAttribute(){

        $days = max(1,floor((strtotime($this->end_date) - strtotime($this->start_date)) / HOUR_IN_SECONDS) );
        return $days;
    }

    public function  checkMaximumBooking($date){

    }

    public static function getBookingInRanges($object_id,$object_model,$from,$to,$object_child_id = false){

        $query = parent::selectRaw(" * , SUM( total_guests ) as total_guests ")->where([
            'object_id'=>$object_id,
            'object_model'=>$object_model,
        ])->whereNotIn('status',static::$notAcceptedStatus)
            ->where('end_date','>=',$from)
            ->where('start_date','<=',$to)
            ->groupBy('start_date')
            ->take(200);

        if($object_child_id){
            $query->where('object_child_id',$object_child_id);
        }

        return $query->get();
    }
    public static function getAllBookingInRanges($object_id,$object_model,$from,$to){

        $query = parent::selectRaw("*")->where([
            'object_id'=>$object_id,
            'object_model'=>$object_model,
        ])->whereNotIn('status',static::$notAcceptedStatus)
            ->where('end_date','>=',$from)
            ->where('start_date','<=',$to)
            ->take(200);
        return $query->get();
    }
    public function getCommissionVendor(){
        $vendorId = $this->vendor_id;
        $total = $this->total_before_fees;
        $returnArray=[
            'commission'=>0,
            'commission_type'=>'',
        ];
        if (setting_item('vendor_enable') == 1) {
            $vendor = User::find($vendorId);
            if (!empty($vendor)) {
                $commission = [];
                $commission['amount'] = setting_item('vendor_commission_amount', 10);
                $commission['type'] = setting_item('vendor_commission_type', 'percent');

                if($vendor->vendor_commission_type){
                    $commission['type'] = $vendor->vendor_commission_type;
                }
                if($vendor->vendor_commission_amount){
                    $commission['amount'] = $vendor->vendor_commission_amount;
                }

                if($commission['type'] == 'disable'){
                    return $returnArray;
                }

                if ($commission['type'] == 'percent') {
                    $returnArray['commission'] = (float)($total / 100) * $commission['amount'];
                } else {
                    $returnArray['commission']= (float)min($total,$commission['amount']);
                }
                $returnArray['commission_type'] = json_encode($commission);
            }
        }
        return $returnArray;
    }

    public function calculateCommission(){
        $data = $this->getCommissionVendor();

        $this->commission = $data['commission'];
        $this->commission_type = $data['commission_type'];
    }

    public static function getContentCalendarIcal($service_type,$id,$module){
        $proid = config('app.name') . ' ' . $_SERVER['SERVER_NAME'];
        $calendar = new Calendar($proid);
        $data  = app()->make($module)::find($id);
        if (!empty($data)) {
            $availabilityData = $data->availabilityClass::where(['target_id'=>$id,'active'=>0])->get();
            if(!empty($availabilityData)){
                foreach ($availabilityData as $availabilityDatum){
                    $eventCalendar = new Event();
                    $eventCalendar
                        ->setUniqueId($data->id.time())
                        ->setCategories(ucfirst($service_type))
                        ->setDtStart(new \DateTime($availabilityDatum->start_date))
                        ->setDtEnd(new \DateTime($availabilityDatum->end_date))
                        ->setSummary($data->title . '#'.$id.' Blocked')
                        ->setNoTime(false);
                    $calendar->addComponent($eventCalendar);
                }
            }
            $bookingData = self::where('object_id', $id)->where('object_model', $service_type)
                ->whereNotIn('status', self::$notAcceptedStatus)
                ->where('start_date','>=',now())
                ->get();
            if($service_type=='room'){
                $bookingData = HotelRoomBooking::where('room_id',$id)->whereHas('booking',function (Builder $query){
                    $query->whereNotIn('status', self::$notAcceptedStatus)
                        ->where('start_date','>=',now());
                })->get();
            }
            if (!empty($bookingData)) {
                foreach ($bookingData as $item => $value) {
                    if($service_type=='room'){
                        $customerName = $value->fist_name . ' ' . $value->last_name;
                        $description = '<p>Name:' . $customerName . '</p>
                                <p>Email:' . $value->email . '</p>
                                <p>Phone:' . $value->phone . '</p>
                                <p>Address:' . $value->address . '</p>
                                <p>Customer notes:' . $value->customer_notes . '</p>
                                <p>Total guest:' . $value->number . '</p>';
                        $eventCalendar = new Event();
                        $eventCalendar
                            ->setUniqueId($value->id.time())
                            ->setCategories(ucfirst($service_type))
                            ->setDtStart(new \DateTime($value->start_date))
                            ->setDtEnd(new \DateTime($value->end_date))
                            ->setSummary($customerName . ' Booking ' . ucfirst($service_type) . ' ' . $data->title)
                            ->setNoTime(false)
                            ->setDescriptionHTML($description);
                        $calendar->addComponent($eventCalendar);
                    }else{


                        $customerName = $value->fist_name . ' ' . $value->last_name;
                        $description = '<p>Name:' . $customerName . '</p>
                                <p>Email:' . $value->email . '</p>
                                <p>Phone:' . $value->phone . '</p>
                                <p>Address:' . $value->address . '</p>
                                <p>Customer notes:' . $value->customer_notes . '</p>
                                <p>Total guest:' . $value->total_guests . '</p>';
                        $eventCalendar = new Event();
                        if($service_type=='space'){
                            $byNight = $value->getMeta('booking_type');
                            if($byNight=='by_night'){
                                $value->end_date =  date("Y-m-d H:i:s",strtotime($value->end_date." -1day"));
                            }
                        }

                        $endDate = new \DateTime($value->end_date);

                        $eventCalendar
                            ->setUniqueId($value->code)
                            ->setCategories(ucfirst($service_type))
                            ->setDtStart(new \DateTime($value->start_date))
                            ->setDtEnd($endDate)
                            ->setSummary($customerName . ' Booking ' . ucfirst($service_type) . ' ' . $data->title)
                            ->setNoTime(false)
                            ->setDescriptionHTML($description);
                        $calendar->addComponent($eventCalendar);
                    }

                }
            }



        }
        return $calendar->render();
    }

    public function getTotalBeforeExtraPriceAttribute(){
        $extra_price = $this->getJsonMeta('extra_price');

        if(empty($extra_price) or !is_array($extra_price)) return $this->total_before_discount;

        $extra_price_collection = collect($extra_price);

        return $this->total_before_discount - $extra_price_collection->sum('total');
    }

    public function wallet_transaction(){
        return $this->belongsTo(Transaction::class,'wallet_transaction_id')->withDefault();
    }

    public function tryRefundToWallet($checkStatus = true){
        if($checkStatus and in_array($this->status,[self::CANCELLED]) ){
            return;
        }

        if( $this->customer_id and $this->wallet_transaction_id && !$this->is_refund_wallet){
            $user = User::find($this->customer_id);
            if($user) {
                $transaction = $this->wallet_transaction;
                if ($transaction->amount) {
                    $user->deposit($transaction->amount, ['type' => 'booking_refund_wallet'], $this->id);

                    $this->is_refund_wallet = 1;
                    $this->save();
                }
            }
        }
    }

    public function time_slots()
    {
        return $this->hasMany( BookingTimeSlots::class, 'booking_id');
    }

    public function coupons()
    {
        return $this->hasMany( CouponBookings::class, 'booking_id');
    }

    public function reloadCalculateTotalBooking(){
        // Get amount before discount
        $total_booking = $this->total_before_discount;
        // Get amount total coupon
        $this->coupon_amount = CouponBookings::where('booking_id',$this->id)->sum('coupon_amount');

        // Calculate total booking
        $total_booking = $total_booking - $this->coupon_amount;
        if($total_booking < 0 ){
            $total_booking = 0;
        }
        // Set amount before fees after deducting coupon
        $this->total_before_fees = $total_booking;

        //reload calculate buyer fees for admin
        $total_buyer_fee = 0;
        if(!empty($list_fees = $this->buyer_fees)){
            $list_fees = json_decode($list_fees,true);
            $total_buyer_fee = $this->service->calculateServiceFees($list_fees , $this->total_before_fees , $this->total_guests);
            $total_booking += $total_buyer_fee;
        }
        //reload calculate service fees for vendor
        $total_service_fee = 0;
        if(!empty($list_fees = $this->vendor_service_fee)){
            $total_service_fee = $this->service->calculateServiceFees($list_fees , $this->total_before_fees , $this->total_guests);
            $total_booking += $total_service_fee;
        }
        $this->vendor_service_fee_amount = $total_service_fee;

        // reload calculate commission
        $this->calculateCommission();
        $this->total = $total_booking;

        // reload calculate deposit
        if (!empty($deposit_info = $this->getMeta("deposit_info"))) {
            $deposit_info = json_decode($deposit_info , true);
            $booking_deposit_fomular = $deposit_info['fomular'];
            $tmp_price_total = $this->total;
            if ($booking_deposit_fomular == "deposit_and_fee") {
                $tmp_price_total = $this->total_before_fees;
            }
            switch ( $deposit_info['type'] ) {
                case "percent":
                    $this->deposit = $tmp_price_total * $deposit_info['amount'] / 100;
                    break;
                default:
                    $this->deposit = $deposit_info['amount'];
                    break;
            }
            if ($booking_deposit_fomular == "deposit_and_fee") {
                $this->deposit = $this->deposit + $total_buyer_fee + $total_service_fee;
            }
        }
        $this->save();
    }

    public function updateStatusCoupons(){
        CouponBookings::where('booking_id', $this->id)->update(['booking_status' => $this->status]);
    }

    public function calTotalPassenger(){
        if(empty( setting_item('booking_enable_ticket_guest_info',0))){
            return 0;
        }
        switch ($this->object_model){
            case "car":
            case "boat":
                return 0;
            case "tour":
            default:
                return $this->total_guests;

        }
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id', 'id');
    }

    public function roomsBooking(): HasMany
    {
        return $this->hasMany(HotelRoomBooking::class, 'booking_id');
    }

    /** @deprecated use roomsBooking */
    public function hotelRooms(): HasMany
    {
        return $this->roomsBooking();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_user');
    }

    public function bookingHunters(): HasMany
    {
        return $this->hasMany(BookingHunter::class, 'booking_id', 'id');
    }

    public function masterHunter(): HasOne
    {
        return $this->hasOne(BookingHunter::class, 'booking_id', 'id')
            ->where('is_master', true);
    }

    public function getMasterHunterIdAttribute(): ?int
    {
        if ($this->relationLoaded('bookingHunters')) {
            $master = $this->bookingHunters->firstWhere('is_master', true);

            return $master?->invited_by;
        }

        return $this->masterHunter?->invited_by;
    }

    public function masterHunterRowId(): ?int
    {
        if ($this->relationLoaded('bookingHunters')) {
            return $this->bookingHunters->firstWhere('is_master', true)?->id;
        }

        return $this->masterHunter?->id;
    }

    public function countAcceptedAndPaidHunters(): int
    {
        $masterId = $this->masterHunterRowId();

        if (!$masterId) {
            return 0;
        }

        return BookingHunterInvitation::query()
            ->where('booking_hunter_id', $masterId)
            ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
            ->where('prepayment_paid_status', BookingHunterInvitation::PREPAYMENT_PAID)
            ->count();
    }

    public function countAcceptedHunters(): int
    {
        $masterId = $this->masterHunterRowId();

        if (!$masterId) {
            return 0;
        }

        return BookingHunterInvitation::query()
            ->where('booking_hunter_id', $masterId)
            ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
            ->count();
    }

    public function changeCreator(User $user): void
    {
        $this->create_user = $user->id;
    }

    /**
     * @throws NotFoundException
     */
    public function changeMasterHunterCreator(User $user): void
    {
        $hunter = $this->masterHunter;

        if (!$hunter) {
            throw new NotFoundException(
                errorCode: 'master_not_found',
                domain: 'booking'
            );
        }

        $hunter->changeCreator($user);
    }

    public function getTypeTextAttribute()
    {
        return match ($this->type) {
            self::BookingTypeHotel => __('booking.types.hotel'),
            self::BookingTypeAnimal => __('booking.types.animal'),
            self::BookingTypeHotelAnimal => __('booking.types.hotel_animal'),
            default => ucfirst($this->type ?? ''),
        };
    }

    public function getAmountPerPerson(): float
    {
        return round($this->total, 2);
    }

    public function getNeededHuntersCount(): int
    {
        $count = match ($this->type) {
            self::BookingTypeHotel => (int) ($this->total_guests ?? 0),
            self::BookingTypeAnimal,
            self::BookingTypeHotelAnimal => (int) ($this->total_hunting ?? 0),
            default => 0,
        };

        return max(0, $count);
    }
}
