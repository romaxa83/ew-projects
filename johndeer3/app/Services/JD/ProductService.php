<?php

namespace App\Services\JD;

use App\DTO\JD\ProductDTO;
use App\Models\JD\Product;

class ProductService
{
    public function createFromImport(ProductDTO $dto): Product
    {
        $model = new Product();
        $model->jd_id = $dto->jdID;
        $model->size_name = $dto->sizeName;
        $model->jd_model_description_id = $dto->jdModelDescriptionID;
        $model->jd_equipment_group_id = $dto->jdEquipmentGroupID;
        $model->jd_manufacture_id = $dto->jdManufactureID;
        $model->jd_size_parameter_id = $dto->jdSizeParameterID;
        $model->status = $dto->status;
        $model->type = $dto->type;

        $model->save();

        return $model;
    }

    public function updateFromImport(Product $model, ProductDTO $dto): Product
    {
        $model->jd_id = $dto->jdID;
        $model->size_name = $dto->sizeName;
        $model->jd_model_description_id = $dto->jdModelDescriptionID;
        $model->jd_equipment_group_id = $dto->jdEquipmentGroupID;
        $model->jd_manufacture_id = $dto->jdManufactureID;
        $model->jd_size_parameter_id = $dto->jdSizeParameterID;
        $model->status = $dto->status;
        $model->type = $dto->type;

        $model->save();

        return $model;
    }

}

