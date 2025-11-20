<?php

namespace App\Type;

use App\Models\Languages;
use App\Models\Translate;

/**
 *  created - полностью заполненный отчет от PS (он еще не проверен админом, в нем нет комментария)
 *  open_edit - PS создал отчет, админ его просмотрел, нашел неточности, написал комментарий и вернул PS на редактирование
 *  edited - PS после комментария админа, отредактировал отчет, сохранил его уже с изменениями и отправил на повторную проверку
 *  in_process - PS приступил к заполнению отчет
 *  verify - полностью подтвержденный отчет от админа, без возможности редактирования (верифицировать отчет можно на статусе Edited и created)
 */

final class ReportStatus
{
    const CREATED    = 1;    // отчет создан
    const OPEN_EDIT  = 2;    // отчет открыт для редактирования
    const EDITED     = 3;    // отчет отредактирован
    const IN_PROCESS = 4;    // отчет в процессе создания
    const VERIFY     = 5;    // верифицированный

    private function __construct(){}

    public static function list(): array
    {
        return [
            self::CREATED,
            self::OPEN_EDIT,
            self::EDITED,
            self::IN_PROCESS,
            self::VERIFY
        ];
    }

    public static function listForMachineStatistics(): array
    {
        return [
            self::CREATED,
            self::EDITED,
            self::VERIFY
        ];
    }

    public static function listWithTranslatedAlias()
    {
        return [
            self::CREATED => 'reports_created',
            self::OPEN_EDIT => 'reports_open_for_editing',
            self::EDITED => 'reports_edited',
            self::IN_PROCESS => 'reports_in_progress',
            self::VERIFY => 'reports_verify'
        ];
    }

    public static function listWithName(): array
    {
        $statuses = self::listWithTranslatedAlias();
        $lang = Languages::getLang();

        $translates = Translate::query()
            ->where('lang', $lang)
            ->where('model', Translate::TYPE_SITE)
            ->whereIn('alias', $statuses)
            ->get();

        foreach ($statuses as $key => $status){
            $item = $translates->where('alias', $status)->first();
            if($item){
                $statuses[$key] = $item->text;
            }
        }

        return $statuses;
    }

    public static function canToggleToVerify($status): bool
    {
        return self::CREATED === $status || self::EDITED === $status;
    }
}
