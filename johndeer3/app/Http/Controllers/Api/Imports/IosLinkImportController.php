<?php

namespace App\Http\Controllers\Api\Imports;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Import\IosLinkImportRequest;
use App\Jobs\Import\IosLinkImportJob;
use App\Models\Import\IosLinkImport;
use App\Repositories\Import\IosLinkImportRepository;
use App\Resources\Import\IosLinkImportResource;
use Illuminate\Http\Request;

class IosLinkImportController extends ApiController
{
    protected $orderBySupport = ['id', 'status', 'created_at', 'updated_at'];
    protected $defaultOrderBy = 'status';

    public function __construct(protected IosLinkImportRepository $repo)
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/ios-links-imports",
     *     tags = {"IosLink Import"},
     *     summary="Получение списка импортов ios links",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="status",
     *             enum={"id", "status", "created_at", "updated_at"}
     *          )
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200",
     *          description="Список импортов",
     *          @OA\JsonContent(ref="#/components/schemas/IosLinkImportCollections")
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {
            $links = IosLinkImport::with(['uploader'])
                ->orderBy(
                    array_key_first($this->orderDataForQuery()),
                    current($this->orderDataForQuery())
                )
                ->paginate($this->perPage);

            return IosLinkImportResource::collection($links);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/ios-links-imports/{id}",
     *     tags = {"IosLink Import"},
     *     summary="Получение информации о импорте по ID",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *          description="ID импорта",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/IosLinkImportResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function show(IosLinkImport $iosLinkImport)
    {
        return $this->successJsonMessage(
            IosLinkImportResource::make($iosLinkImport)
        );
    }

    /**
     * @OA\Post (
     *     path="/api/admin/ios-links-imports",
     *     tags = {"IosLink Import"},
     *     summary="Загрузка файла импорта",
     *     security={{"Basic": {}}},
     *
     *     	@OA\RequestBody(required=true,
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="file", type="file", format="binary",
     *                      description="Файл с ссылками в формате xlsx , xls",
     *                   ),
     *               ),
     *           ),
     *       ),
     *
     *     @OA\Response(response="200", description="Файл загружен, идет фоновая обработка",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="boolean", example=true),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function process(IosLinkImportRequest $request)
    {
        $lastImport = $this->repo->getLastRow();

        if ($lastImport && ($lastImport->isNew() || $lastImport->isInProcess())) {
            return $this->errorJsonMessage('Import in process , try later', 422);
        }

        $model = new IosLinkImport();
        $model->fill([
            'status' => IosLinkImport::STATUS_NEW,
            'user_id' => \Auth::user()->id]);

        $file = $request->file('file');
        $model->upload('file', 'ios-links-import');
        $model->save();

        // временное хранение файла, для парсинга, после будет удален
        \Storage::put('import.' . $file->clientExtension(), $file->get());
        $pathToFile = storage_path('app/' . 'import.' . $file->clientExtension());

        IosLinkImportJob::dispatch($pathToFile, $model);

        return $this->successJsonMessage('File upload', 200);
    }

    /**
     * @OA\Get (
     *     path="/api/admin/ios-links-imports-can-use-import",
     *     tags = {"IosLink Import"},
     *     summary="Узнать можно ли запускать импорт или нет?",
     *     description="Обработка файла происходит в фоновом режими, чтоб избежать конфликтов, перед загрузкой нужно проверить - можно ли загрузить файл",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function canUseImport()
    {
        $lastImport = $this->repo->getLastRow();
        if ($lastImport && ($lastImport->isNew() || $lastImport->isInProcess())) {
            return $this->errorJsonMessage('Can\'t');
        }else {
            return $this->successJsonMessage('Can');
        }
    }
}
