<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramDev;
use App\Traits\ApiOrderBy;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 *     title="JohnDeer Demonstration API documentation",
 *     version="1.0.0",
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Get(
 *     path="/api/version",
 *     @OA\Response(response="200", description="An example resource")
 * )
 * @OA\Tag(
 *     name="Auth",
 *     description="Авторизация",
 * )
 * @OA\Tag(
 *     name="Аdmin-panel",
 *     description="Роуты, предназначен дли админ панели",
 * )
 * @OA\Tag(
 *     name="Notifications",
 *     description="Шаблоны для push-уведомлений",
 * )
 * @OA\Tag(
 *     name="Features",
 *     description="Таблица характеристик техники, при демонстрации",
 * )
 * @OA\Tag(
 *     name="IosLink",
 *     description="Ссылки для скачивания приложения (ios)",
 * )
 * @OA\Tag(
 *     name="IosLink Import",
 *     description="Импорт файлов с линками",
 * )
 * @OA\Tag(
 *     name="Dealers",
 *     description="Дилеры, тянуться с BOED",
 * )
 * @OA\Tag(
 *     name="Catalog",
 *     description="Каталог, данные тянутся и синхронизируются с BOED",
 * )
 * @OA\Tag(
 *     name="Page",
 *     description="Текстовые страницы",
 * )
 * @OA\Tag(
 *     name="Report",
 *     description="Отчет",
 * )
 * @OA\Tag(
 *     name="User (for admin)",
 *     description="Пользователь, для админ-панели",
 * )
 * @OA\Tag(
 *     name="User (for mobile)",
 *     description="Пользователь, для МП",
 * )
 * @OA\Tag(
 *     name="Statistic filter",
 *     description="Данные для фильтров статистики",
 * )
 * @OA\Tag(
 *     name="Statistic",
 *     description="Данные для статистики",
 * )
 * @OA\Tag(
 *     name="Translation",
 *     description="Переводы",
 * )
 * @OA\Server(
 *     description="stage server",
 *     url="http://jddemo.wezom.agency/api"
 * )
 * @OA\Server(
 *     description="production server",
 *     url="https://api.jd-demonstration.com/api"
 * )
 * @OA\ExternalDocumentation(
 *     description="find more info here",
 *     url="https://swagger.io/about"
 *   )
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     securityScheme="Basic"
 * )
 * @OA\Parameter(
 *     parameter="Content-Language",
 *     name="Content-Language",
 *     in="header",
 *     required=false,
 *     @OA\Schema(
 *        type="string",
 *        default="kk"
 *     )
 * )
 */

class ApiController extends Controller
{
    use ApiOrderBy;

    protected $perPage = 10;

    public function __construct()
    {
        $this->perPage = request()->input('per_page') ?: $this->perPage;

        $this->checkAndFillOrderBy(request()->input('order_by') ?? $this->defaultOrderBy);
        $this->checkAndFillOrderByType(request()->input('order_type') ?? $this->defaultOrderByType);
    }

    protected function successJsonMessage($message, $code = Response::HTTP_OK)
    {
        return response()->json([
            'data' => $message,
            'success' => true
        ], $code);
    }

    protected function errorJsonMessage($message, $code = Response::HTTP_OK)
    {
        TelegramDev::warn($message, \Auth::user()->login ?? 'unknown', $this->trace(debug_backtrace()));

        return response()->json([
            'data' => $message,
            'success' => false
        ], $code);
    }

    protected function trace($trace): string
    {
        $caller = $trace[1];
        $class = $caller['class'] ?? '';
        $method = $caller['function'] ?? '';

        return "{$class}@$method";
    }
}
