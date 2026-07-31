<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Media\Services\MediaUploadService;
use Modules\User\Models\UserAvatarHistory;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255'],

            'birthday' => ['nullable', 'date'],

            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],

            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],

            'hunter_billet_number' => ['nullable', 'string', 'max:255'],

            'bio' => ['nullable', 'string'],

            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'avatar_id' => [
                'nullable',
                'integer',
                Rule::exists('media_files', 'id')->where(function ($query) {
                    $userId = Auth::id();
                    $historyIds = UserAvatarHistory::query()
                        ->where('user_id', $userId)
                        ->pluck('media_id');

                    $query->where('author_id', $userId)
                        ->where('file_type', 'like', 'image/%')
                        ->whereNull('deleted_at')
                        ->where(function ($inner) use ($historyIds) {
                            $inner->where('folder_id', MediaUploadService::FOLDER_AVATAR);

                            if ($historyIds->isNotEmpty()) {
                                $inner->orWhereIn('id', $historyIds);
                            }
                        });
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => __('user.validation.profile.first_name_string'),
            'first_name.max' => __('user.validation.profile.first_name_max'),

            'last_name.string' => __('user.validation.profile.last_name_string'),
            'last_name.max' => __('user.validation.profile.last_name_max'),

            'nik.string' => __('user.validation.profile.nik_string'),
            'nik.max' => __('user.validation.profile.nik_max'),

            'birthday.date' => __('user.validation.profile.birthday_date'),

            'email.required' => __('user.validation.profile.email_required'),
            'email.email' => __('user.validation.profile.email_invalid'),
            'email.max' => __('user.validation.profile.email_max'),

            'phone.string' => __('user.validation.profile.phone_string'),
            'phone.max' => __('user.validation.profile.phone_max'),

            'city.string' => __('user.validation.profile.city_string'),
            'city.max' => __('user.validation.profile.city_max'),

            'address.string' => __('user.validation.profile.address_string'),
            'address.max' => __('user.validation.profile.address_max'),

            'hunter_billet_number.string' => __('user.validation.profile.hunter_billet_number_string'),
            'hunter_billet_number.max' => __('user.validation.profile.hunter_billet_number_max'),

            'bio.string' => __('user.validation.profile.bio_string'),

            'avatar.image' => __('user.validation.profile.avatar_image'),
            'avatar.mimes' => __('user.validation.profile.avatar_mimes'),
            'avatar.max' => __('user.validation.profile.avatar_max'),
        ];
    }
}
