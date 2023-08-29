<?php

namespace App\Http\Resources\Employees;

use App\Http\Resources\Admin\EmployeeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'net_salary' => floatval($this->salary),
            'rewards' => floatval($this->rewards),
            'rewards_type' => intval($this->rewards_type),
            'adversaries' => floatval($this->adversaries),
            'adversaries_type' => intval($this->adversaries_type),
            'housing_allowance' => floatval($this->housing_allowance),
            'transportation_allowance' => floatval($this->transportation_allowance),
            'date' => $this->date,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
        ];
    }
}
