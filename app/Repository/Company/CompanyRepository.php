<?php

namespace App\Repository\Company;

use App\Http\Trait\UploadImage;
use App\Models\Company;
use App\Models\Location;
use App\Repository\BaseRepositoryImplementation;
use App\Statuses\UserTypes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CompanyRepository extends BaseRepositoryImplementation
{
    use UploadImage;

    public function getFilterItems($filter)
    {
        $records = Company::query();
        $records->when(isset($filter->orderBy), function ($records) use ($filter) {
            $records->orderBy($filter->getOrderBy(), $filter->getOrder());
        });
        return $records->paginate($filter->per_page);
    }

    public function create_company($data)
    {
        DB::beginTransaction();
        try {

            if (auth()->user()->type == UserTypes::SUPER_ADMIN) {

                $company = new Company();
                if (Arr::has($data, 'commercial_record')) {
                    $file = Arr::get($data, 'commercial_record');
                    $file_name = $this->uploadCompanyAttachment($file);
                    $company->commercial_record = $file_name;
                }
                $company->name = $data['name'];
                $company->email = $data['email'];
                $company->start_commercial_record = $data['start_commercial_record'];
                $company->end_commercial_record = $data['end_commercial_record'];
                $company->save();

                Location::create([
                    'company_id' => $company->id,
                    'longitude' => $data['longitude'],
                    'latitude' => $data['latitude'],
                    'radius' => $data['radius'],
                ]);

                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "Company was not created"];
                }
                return ['success' => true, 'data' => $company->load(['admin', 'locations'])];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_company($data)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $data['company_id']) {
                $company = $this->updateById($data['company_id'], $data);
                if (Arr::has($data, 'commercial_record')) {
                    $file = Arr::get($data, 'commercial_record');
                    $file_name = $this->uploadCompanyAttachment($file);
                    $company->commercial_record = $file_name;
                }
                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "Company was not Updated"];
                }
                return ['success' => true, 'data' => $company->load('admin')];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }




    public function model()
    {
        return Company::class;
    }
}
