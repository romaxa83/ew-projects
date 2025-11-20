<?php

namespace App\Http\Controllers\Api\Common;

use App\DTO\Locale\TranslationsDTO;
use App\Http\Controllers\Api\ApiController;
use App\Models\Translate;
use App\Models\Version;
use App\Repositories\LanguageRepository;
use App\Repositories\TranslationRepository;
use App\Resources\Custom\CustomTranslateResource;
use App\Services\Translations\TranslationService;
use Illuminate\Http\Request;

class TranslateController extends ApiController
{
    public function __construct(
        protected TranslationService $service,
        protected TranslationRepository $repo,
        protected LanguageRepository $languageRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/translate",
     *     tags={"Translation"},
     *     summary="Получение переводов",
     *
     *     @OA\Parameter(name="key", in="query", required=false,
     *          description="Ключ перевода",
     *          @OA\Schema(type="string", example="button")
     *     ),
     *     @OA\Parameter(name="lang", in="query", required=false,
     *          description="Переводы по языку (если нужны переводы на нескольких языках lang=ru,en,ua)",
     *          @OA\Schema(type="string", example="ua",
     *              enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
     *          )
     *     ),
     *
     *     @OA\Response(response="200", description="Переводы",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/CustomTranslateResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getTranslate(Request $request)
    {
        $user = \Auth::user();
        try {
            if($user){
                $lang = $user->lang;
            } else {
                $lang = \App::getLocale();
            }

            if($request->lang){
                $lang = $request->lang;
            }

            $translates = $this->repo->getAllAsArray(Translate::TYPE_SITE, $lang, $request->key);

            $dto = (new CustomTranslateResource())->fill($translates);

            return $this->successJsonMessage($dto);
        } catch (\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/translate",
     *     tags={"Translation"},
     *     summary="Установить переводы",
     *     description="Перевод будет создан, если такой перевод уже есть (по ключу и локали) то будет перезаписан",
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/SetTranslationRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function setTranslate(Request $request)
    {
        try {
            $this->service->saveOrUpdate(
                TranslationsDTO::byRequestFromApp($request->all())
            );

            // перезаписываем версии перевода
            Version::setVersion(
                Version::TRANSLATES,
                Version::getHash($this->repo->getAllAsArray(Translate::TYPE_SITE))
            );

            return $this->successJsonMessage(__('message.translate_set'));
        } catch (\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/translate/version",
     *     tags={"Translation"},
     *     summary="Получить версию переводов",
     *
     *     @OA\Response(response="200", description="Версия переводы",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="string", description="Хеш", example="d2f757b4db9c3a4eb589dfab0ccbc5e70"),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function version()
    {
        try {
            $version = null;
            if($ver = Version::getVersionByAlias(Version::TRANSLATES)){
                $version = $ver->version;
            }

            return $this->successJsonMessage($version);
        } catch (\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/translate/version-check",
     *     tags={"Translation"},
     *     summary="Проверить версию переводов",
     *
     *     @OA\Parameter(name="hash", in="query", required=true,
     *          description="Контрольная сумма (хеш)",
     *          @OA\Schema(type="string", example="d2f757b4db9c3a4eb589dfab0ccbc5e70")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function checkVersion(Request $request)
    {
        try {
            if(Version::checkVersion($request['hash'], Version::TRANSLATES)){
                return $this->successJsonMessage(__('message.correct_version'));
            }

            return $this->errorJsonMessage(__('message.incorrect_version'));
        } catch (\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/language",
     *     tags={"Translation"},
     *     summary="Получение языков приложения",
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */

    public function getLang()
    {
        try {
            return $this->successJsonMessage($this->languageRepository->getForSelect());
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
