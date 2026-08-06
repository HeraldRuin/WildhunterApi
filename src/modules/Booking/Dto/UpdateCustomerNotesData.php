<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\UpdateCustomerNotesRequest;

class UpdateCustomerNotesData
{
    public function __construct(
        public string $code,
        public string $customerNotes,
    ) {}

    public static function fromRequest(UpdateCustomerNotesRequest $request): self
    {
        $data = $request->validated();

        return new self(
            code: $data['code'],
            customerNotes: $data['customer_notes'],
        );
    }
}
