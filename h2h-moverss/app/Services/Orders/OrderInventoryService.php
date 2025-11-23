<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\Order\InventoryActivity;

class OrderInventoryService
{
    private const ADD = 1;
    private const EDIT = 2;
    private const DELETE = 3;

    private int $action;
    private ?Order $order = null;
    private ?Order\Inventory $inventory = null;

    public function __construct()
    {
        $this->action = self::ADD;
    }

    public function add(): self
    {
        $this->action = self::ADD;
        return $this;
    }

    public function edit(): self
    {
        $this->action = self::EDIT;
        return $this;
    }

    public function delete(): self
    {
        $this->action = self::DELETE;
        return $this;
    }

    public function setOrder($orderId): self
    {
        $this->order = Order::query()
            ->with([
                'inventories',
                'client',
                'inventories.children',
            ])
            ->findOrFail($orderId);

        return $this;
    }

    public function setInventory($inventoryId): self
    {
        $this->inventory = Order\Inventory::query()
            ->findOrFail($inventoryId);

        return $this;
    }

    public function handler(array $data = []): ?Order\Inventory
    {
        return make_transaction(function () use ($data) {
            return $this->exec($data);
        });
    }

    private function exec(array $data = []): ?Order\Inventory
    {
        $model = null;
        if($this->action === self::ADD){
            $model = $this->addInventory($data);
        }

        if($this->action === self::EDIT){
            if(is_null($this->inventory)){
                throw new \Exception("Need to set inventory");
            }

            $model = $this->editInventory($this->inventory, $data);
        }

        if($this->action === self::DELETE){
            if(is_null($this->inventory)){
                throw new \Exception("Need to set inventory");
            }

            $this->removeInventory();
        }

        if ($this->order->sizing_is_auto) {
            $this->order->recountSizingAuto();
        }

        return $model;
    }


    private function addInventory(array $data): Order\Inventory
    {
        $model = new Order\Inventory();
        $model->order_id = $this->order->id;
        $model->is_section = $data['is_section'];
        $model->section_id = $data['section_id'] ?? 0;
        $model->item_id = $data['item_id'] ?? null;
        $model->price = $data['price'] ?? null;
        $model->qty = $data['qty'] ?? null;
        $model->weight = $data['weight'] ?? null;
        $model->volume = $data['volume'] ?? null;
        $model->title = $data['title'] ?? null;
        $model->sort = $data['sort'];

        $model->save();

        InventoryActivity::saveAsCreate($this->order, [
            'id' => $model->id,
            'title' => $model->title,
            'qty' => $model->qty,
        ]);

        return $model;
    }

    private function editInventory(Order\Inventory $model, array $data): Order\Inventory
    {
        $model->is_section = $data['is_section'] ?? $model->is_section;
        $model->section_id = $data['section_id'] ?? $model->section_id;
        $model->item_id = $data['item_id'] ?? $model->item_id;
        $model->price = $data['price'] ?? $model->price;
        $model->qty = $data['qty'] ?? $model->qty;
        $model->weight = $data['weight'] ?? $model->weight;
        $model->volume = $data['volume'] ?? $model->volume;
        $model->title = $data['title'] ?? $model->title;
        $model->sort = $data['sort'] ?? $model->sort;

        $model->save();

        InventoryActivity::saveAsUpdate($this->order, [
            'id' => $model->id,
            'title' => $model->title,
            'qty' => $model->qty,
        ]);

        return $model;
    }

    private function removeInventory(): bool
    {

        if(is_null($this->inventory)){
            return false;
        }

        foreach ($this->inventory->children as $child) {
            InventoryActivity::saveAsDelete($this->order, [
                'id' => $child->id,
                'title' => $child->title,
                'qty' => $child->qty,
            ]);
            $child->delete();
        }

        $data = [
            'id' => $this->inventory->id,
            'title' => $this->inventory->title,
            'qty' => $this->inventory->qty,
        ];

        if($res = $this->inventory->delete()){
            InventoryActivity::saveAsDelete($this->order, $data);
        }

        return $res;
    }

    public function sort(array $data): void
    {
        foreach ($data['items'] as $k => $item) {
            $data['items'][$k]['order_id'] = 164913;
            $data['items'][$k]['title'] = '164913';
        }

        Order\Inventory::upsert(
            $data['items'],
            ['id'],
            ['sort', 'section_id']
        );
    }
}
