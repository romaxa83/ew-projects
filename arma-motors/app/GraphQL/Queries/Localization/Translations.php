<?php

namespace App\GraphQL\Queries\Localization;

use App\GraphQL\BaseGraphQL;
use App\Repositories\Localization\TranslationRepository;
use App\Services\Telegram\TelegramDev;

class Translations extends BaseGraphQL
{
    public function __construct(private TranslationRepository $translationRepository)
    {}

    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
//            if(config('cache.enable')){
//                $trans = \Cache::remember('translations', 10, function() use ($args) {
//                    return $this->normalizeTranslation(
//                        $this->translationRepository->getByPlaceForFront($args['place'], $args['lang'] ?? [])
//                    );
//                });
//
//                return $trans;
//            }

            return $this->normalizeTranslation(
                $this->translationRepository->getByPlaceForFront($args['place'], $args['lang'] ?? [])
            );
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    public function normalizeTranslation($data): array
    {
        $temps = [];
        foreach ($data as $k => $items){

            $temps[$k]['key'] = $items->key;
            $temps[$k]['place'] = $items->place;
            foreach (explode('|', $items->translation) as $kt => $item){

                if(substr($item,0, 1) == ','){
                    $item = substr($item,1);
                }

                if($item){
                    $item = explode(':', $item);
                    $temps[$k]['translation'][$kt]['lang'] = $item[0] ?? null;
                    $temps[$k]['translation'][$kt]['text'] = $item[1] ?? null;
                }
            }

//            $temps[$k]['key'] = $items['key'];
//            $temps[$k]['place'] = $items['place'];
//            foreach (explode('|', $items['translation']) as $kt => $item){
//
//                if(substr($item,0, 1) == ','){
//                    $item = substr($item,1);
//                }
//
//                if($item){
//                    $item = explode(':', $item);
//                    $temps[$k]['translation'][$kt]['lang'] = $item[0] ?? null;
//                    $temps[$k]['translation'][$kt]['text'] = $item[1] ?? null;
//                }
//            }
        }

        return $temps;
    }
}

//public function handle($request, Closure $next)
//{
//    $lang = $request->header('Content-Language', 'uk');
//    $authorization = $request->header('authorization');
//    $queue = ['GetCommonData', 'GetIndexPage', 'GetProjectsPage', 'GetUnitsSystemPage'];
//    if(!$request->input('operationName') || !in_array($request->input('operationName'), $queue) || $authorization)
//    {
//        return $next($request);
//    }
//    $key = text_clear($request->input('operationName') . $request->input('query') . json_encode($request->input('variables')) . $lang);
//    return \Cache::remember($key, 1500, function () use($next, $request){
//        return $next($request);
//    });
//
//}
