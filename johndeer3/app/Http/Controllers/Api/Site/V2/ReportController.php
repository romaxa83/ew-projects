<?php

namespace App\Http\Controllers\Api\Site\V2;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\Report\ReportRepository;
use App\Resources\Report\ReportListResource;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    protected $orderBySupport = ['id', 'created_at'];
    protected $defaultOrderBy = 'created_at';

    public function __construct(protected ReportRepository $repo)
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/v2/reports",
     *     tags = {"Report"},
     *     summary="Получить всех отчетов",
     *     description ="Получение отчетов с пагинацией используя различные фильтрации и сортировку",
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
     *     @OA\Parameter(name="ps_id", in="query", required=false,
     *          description="ID ps user, если нужно передать несколько значений, то так - ?ps_id[]=1&ps_id[]=3",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="dealer_id", in="query", required=false,
     *          description="ID dealar, если нужно передать несколько значений, то так - ?dealer_id[]=1&dealer_id[]=3",
     *          @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(name="tm_id", in="query", required=false,
     *          description="ID territorial manager, если нужно передать несколько значений, то так - ?tm_id[]=1&tm_id[]=3",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="equipment_group_id", in="query", required=false,
     *          description="ID equipment group, если нужно передать несколько значений, то так - ?equipment_group_id[]=1&equipment_group_id[]=3",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован), если нужно передать несколько значений, то так - ?status[]=1&status[]=3",
     *          @OA\Schema(type="integer", example=2, enum={"1", "2", "3", "4", "5"})
     *     ),
     *     @OA\Parameter(name="model_description_id", in="query", required=false,
     *          description="ID model description, если нужно передать несколько значений, то так - ?model_description_id[]=1&model_description_id[]=3",
     *          @OA\Schema(type="integer", example=45)
     *     ),
     *     @OA\Parameter(name="machine_serial_number", in="query", required=false,
     *          description="Фильтр по серийному номеру машины",
     *          @OA\Schema(type="string", example="1Z0S760AALD121730")
     *     ),
     *     @OA\Parameter(name="year", in="query", required=false,
     *          description="Фильтр году создания",
     *          @OA\Schema(type="string", example="2022")
     *     ),
     *     @OA\Parameter(name="created", in="query", required=false,
     *          description="Фильтр по датам, формат = 2022-01-01 или 2022-01-01 13:00:00, если нужно передать диапозон то так (где первая дата это 'от', вторая - 'до') - ?created[]=2022-01-01&created[]=2022-01-03",
     *          @OA\Schema(type="string", example="2022-01-01")
     *     ),
     *     @OA\Parameter(name="country", in="query", required=false,
     *          description="Фильтр по стране, если нужно передать несколько значений, то так - ?country[]=Ukraine&country[]=Poland. Данные берем отсюда - /api/admin/report-list-filter?type=country",
     *          @OA\Schema(type="string", example="Ukraine")
     *     ),
     *     @OA\Parameter(name="region", in="query", required=false,
     *          description="Фильтр по региону если нужно передать несколько значений, то так - ?region[]=Київська%20область&region[]=Херсонська%20область. Данные берем отсюда - /api/admin/report-list-filter?type=region",
     *          @OA\Schema(type="string", example="Київська%20область")
     *     ),
     *     @OA\Parameter(name="district", in="query", required=false,
     *          description="Фильтр по району если нужно передать несколько значений, то так - ?district[]=Суворовський%20район&district[]=Корабельний%20район. Данные берем отсюда - /api/admin/report-list-filter?type=district",
     *          @OA\Schema(type="string", example="Корабельний%20район")
     *     ),
     *     @OA\Parameter(name="client_model_description_id", in="query", required=false,
     *          description="Фильтр по modelDescription клиента",
     *          @OA\Schema(type="integer", example=22)
     *     ),
     *     @OA\Parameter(name="feature_value_id", in="query", required=false,
     *          description="Фильтр по значениям таблицы (на данном этапе по культуре), если нужно передать несколько значений, то так - ?feature_value_id[]=1&feature_value_id[]=3",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="created_at", enum={"id", "created_at"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/ReportListCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {
            $reports = $this->repo->getAllReport([
                'user',
                'user.profile',
                'user.dealer',
                'user.dealer.tm',
                'clients',
                'clients.region',
                'reportClients',
                'location',
                'reportMachines',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription',
                'reportMachines.manufacturer',
                'features.feature',
                'features.value',
            ],
                $request->all(),
                $this->orderDataForQuery()
            );

            return ReportListResource::collection($reports);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

}
