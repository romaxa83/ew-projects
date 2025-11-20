<?php

namespace App\Models;

use App\Repositories\JD\ClientRepository;
use App\Repositories\JD\DealersRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\JD\ManufacturerRepository;
use App\Repositories\JD\ModelDescriptionRepository;
use App\Repositories\PageRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $alias
 * @property string $version
 */

class Version extends Model
{
    public $timestamps = false;

    const DEALERS = 'dealers';
    const CLIENTS = 'clients';
    const EQUIPMENT_GROUP = 'equipment_groups';
    const MODEL_DESCRIPTION = 'model_descriptions';
    const TERRITORIAL_MANAGERS = 'territorial_managers';
    const SALES_MANAGERS = 'sales_managers';
    const REGIONS = 'regions';
    const MANUFACTURER = 'manufacturers';
    const SIZE_PARAMETERS = 'size_parameters';
    const PRODUCT = 'products';

    const TRANSLATES = 'translates';
    const PAGE = 'pages';

    // хеш для импортов
    const IMPORT_EG = 'import_eg';
    const IMPORT_MD = 'import_md';
    const IMPORT_REGION = 'import_region';
    const IMPORT_DEALER = 'import_dealer';
    const IMPORT_CLIENT = 'import_client';
    const IMPORT_TM = 'import_tm';
    const IMPORT_SM = 'import_sm';
    const IMPORT_MANUFACTURE = 'import_manufacture';
    const IMPORT_SP= 'import_sp';
    const IMPORT_PRODUCT= 'import_product';

    protected $table = 'versions_import';

    public static function checkVersion($version, $alias): bool
    {
        if($hash = self::getVersionByAlias($alias)){
            return self::getVersionByAlias($alias)->version == $version;
        }
        return false;
    }

    public static function getVersionByAlias($alias)
    {
        return self::where('alias', $alias)->first();
    }

    public static function setVersion($alias, $version)
    {
        $model = self::getVersionByAlias($alias);

        if(!$model){
            $model = new self();
            $model->alias = $alias;
        }

        $model->version = $version;
        $model->save();
    }

    public static function getHash($data)
    {
        return md5(json_encode($data));
    }

    public static function getActualHash($type): string
    {
        switch ($type) {
            case self::CLIENTS:
                return self::getHash(app(ClientRepository::class)->getForHash());
            case self::DEALERS:
                return self::getHash(app(DealersRepository::class)->getForHash());
            case self::MODEL_DESCRIPTION:
                return self::getHash(app(ModelDescriptionRepository::class)->getForHash());
            case self::MANUFACTURER:
                return self::getHash(app(ManufacturerRepository::class)->getForHash());
            case self::EQUIPMENT_GROUP:
                return self::getHash(app(EquipmentGroupRepository::class)->getAllForHash());
            case self::PAGE:
                return self::getHash(app(PageRepository::class)->getForHash());
            default:
                throw new \Exception(__("message.exceptions.not implement getting hash data", [
                    "type" => $type
                ]));
        }
    }
}
