<?php

namespace App\Utils;

use ReflectionClass;

trait UpdateRelationsTrait
{
    /**
     * Обновить связи.
     * @param  string  $relation  Имя рилейшена
     * @param  array  $records  Данные
     * @return int Было ли обновление в базе
     */
    public function updateRelations(string $relation, array $records, $key = 'id'): int
    {
        $ids = [];
        $changed = 0;
        if (is_array($records)) {
            foreach ($records as $v) {
                $upd = $this->$relation()
                    ->updateOrCreate(
                        [
                            $key => $v[$key] ?? null,
                        ],
                        $v);
                $ids[] = $upd->id;

                if (!$changed && ($upd->wasChanged() || $upd->wasRecentlyCreated)) {
                    $changed = 1;
                }
            }

            // Удаляем которые не в списке
            $for_delete = $this->$relation()->whereNotIn('id', $ids)->get();
            if ($for_delete && $for_delete->count()) {
                $changed = 1;

                $reflect = new ReflectionClass($this);
                foreach ($for_delete as $v) {
                    if (method_exists($this, 'addActivity')) {
                        $name = strtolower($reflect->getShortName());
                        $this->addActivity($name.'.'.$relation, [
                            'msg' => $v->value.' was removed',
                        ]);
                    }
                    $v->delete();
                }
            }
        }

        return $changed;
    }
}
