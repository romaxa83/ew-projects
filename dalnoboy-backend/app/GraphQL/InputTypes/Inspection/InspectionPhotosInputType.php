<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\Models\Inspections\Inspection;
use App\Rules\ImageRule;

class InspectionPhotosInputType extends BaseInputType
{
    public const NAME = 'InspectionPhotosInputType';

    public function fields(): array
    {
        return [
            Inspection::MC_STATE_NUMBER => [
                'type' => FileType::type(),
                'description' => 'Required if it creating',
                'rules' => [
                    'required_without:id',
                    'file',
                    new ImageRule()
                ]
            ],
            Inspection::MC_VEHICLE => [
                'type' => FileType::type(),
                'description' => 'Required if it creating and there is no photo in previous inspection',
                'rules' => [
                    'nullable',
                    'file',
                    new ImageRule()
                ]
            ],
            Inspection::MC_DATA_SHEET_1 => [
                'type' => FileType::type(),
                'rules' => [
                    'nullable',
                    'file',
                    new ImageRule()
                ]
            ],
            Inspection::MC_DATA_SHEET_2 => [
                'type' => FileType::type(),
                'rules' => [
                    'nullable',
                    'file',
                    new ImageRule()
                ]
            ],
            Inspection::MC_ODO => [
                'type' => FileType::type(),
                'description' => 'Required for MAIN form vehicles',
                'rules' => [
                    'nullable',
                    'file',
                    new ImageRule()
                ]
            ],
            Inspection::MC_SIGN => [
                'type' => FileType::type(),
                'rules' => [
                    'nullable',
                    'file',
                    new ImageRule()
                ]
            ],
        ];
    }
}
