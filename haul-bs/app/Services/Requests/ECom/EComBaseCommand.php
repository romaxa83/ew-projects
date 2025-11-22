<?php

namespace App\Services\Requests\ECom;

use App\Foundations\Modules\Request\Models\FailRequest;
use App\Models\Customers\Customer;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use App\Services\Requests\BaseCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\RequestClient;
use App\Services\Requests\RequestMethodEnum;

abstract class EComBaseCommand extends BaseCommand
{
    abstract public function getUri(array $data = null): string;

    abstract public function getMethod(): RequestMethodEnum;

    public function getRequestClient(): RequestClient
    {
        return resolve(EComRequestClient::class);
    }

    protected function handlerRequestException(
        \Throwable $e,
        array $data,
        array $headers
    )
    {
        $modelId = null;

        $name = get_class($this);
        $name = explode('\\', $name);
        $name = $name[count($name) - 2];

        $modelType = match ($name) {
            'Category' => Category::MORPH_NAME,
            'Brand' => Customer::MORPH_NAME,
            'Customer' => Brand::MORPH_NAME,
            'Feature' => Feature::MORPH_NAME,
            'FeatureValue' => Value::MORPH_NAME,
            'Inventory' => Inventory::MORPH_NAME,
            'Parts' => Order::MORPH_NAME,
            default => null,
        };
        if(isset($data['guid'])) $modelId = $data['guid'];
        if(isset($data['id'])) $modelId = $data['id'];

        FailRequest::create(
            type: FailRequest::ECOM_TYPE,
            modelId: $modelId,
            modelType: $modelType,
            reason:  $e->getMessage(),
            data: [
                'method' => $this->getMethod(),
                'uri' => $this->getUri($data),
                'payload' => $data,
                'headers' => $headers,
                'exception' => $e->getTrace()[0] ?? [],
            ]
        );
    }

    public function assetIdForUri(array $data, string $field = 'id'): void
    {
        if(!isset($data[$field])){
            $name = null;
            $trace = debug_backtrace();
            if(isset($trace[0]['file'])){
                $path = $trace[0]['file'];
                $name = last(explode('/', $path));
                $name = str_replace('.php', '', $name);
            }

            throw new RequestCommandException("For this command [$name] you need to pass '$field' to uri");
        }
    }
}
