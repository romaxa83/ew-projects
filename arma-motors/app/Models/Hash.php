<?php

namespace App\Models;

use App\Exceptions\ErrorsCode;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Repositories\Catalog\Car\ModelRepository;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Repositories\Dealership\DealershipRepository;
use App\Repositories\HashRepository;
use App\Services\Telegram\TelegramDev;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $alias
 * @property string $hash
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 */

class Hash extends BaseModel
{
    public $timestamps = false;

    public const ALIAS_BRAND = 'brand';
    public const ALIAS_MODEL = 'model';
    public const ALIAS_SERVICE = 'service';
    public const ALIAS_DEALERSHIP = 'dealership';

    public const TABLE_NAME = 'hashes';

    protected $table = self::TABLE_NAME;

    /**
     * @param array $data
     * @return string
     */
    public static function hash(array $data): string
    {
        return md5(json_encode($data));
    }

    public static function repoByAlias(): array
    {
        return [
            self::ALIAS_BRAND => new BrandRepository(),
            self::ALIAS_MODEL => new ModelRepository(),
            self::ALIAS_SERVICE => new ServiceRepository(),
            self::ALIAS_DEALERSHIP => new DealershipRepository(),
        ];
    }

    public static function assetAlias(string $alias): void
    {
        if(!key_exists($alias, self::repoByAlias())){
            throw new \InvalidArgumentException(__('error.hash alias not valid', ['alias' => $alias]), ErrorsCode::BAD_REQUEST);
        }
    }

    public function getHash(string $alias): string
    {
        self::assetAlias($alias);
        $repository = app(HashRepository::class);
        if($hash = $repository->getByAlias($alias)){

            return $hash->hash;
        }

        return self::createOrUpdate($alias);
    }

    public static function createOrUpdate(string $alias): string
    {
        $repository = app(HashRepository::class);
        $data = (self::repoByAlias()[$alias])->getDataForHash();

        $hashString = self::hash($data);

        if($hash = $repository->getByAlias($alias)){
            $hash->hash = $hashString;

            // @todo dev-telegram
            TelegramDev::info("Перезаписан хеш данных по - {$alias}");
        } else {
            $hash = new self();
            $hash->alias = $alias;
            $hash->hash = $hashString;

            // @todo dev-telegram
            TelegramDev::info("Создан хеш данных по - {$alias}");
        }
        $hash->save();

        return $hashString;
    }
}


