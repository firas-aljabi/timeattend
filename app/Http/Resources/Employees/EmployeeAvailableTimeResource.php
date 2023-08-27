<?php

namespace App\Http\Resources\Employees;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAvailableTimeResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => intval($this->user_id),
            'hours_daily' => intval($this->hours_daily),
            'hours_annual' => intval($this->days_annual),
        ];
    }
}
