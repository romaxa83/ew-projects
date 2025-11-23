<?php

namespace WezomCms\Imports\Jobs\Helpers;

use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Repositories\BrandRepository;
use WezomCms\Catalog\Repositories\CategoryRepository;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Catalog\Repositories\SpecificationRepository;
use WezomCms\Catalog\Repositories\SpecValueRepository;
use WezomCms\Catalog\Services\BrandService;
use WezomCms\Catalog\Services\CategoryService;
use WezomCms\Catalog\Services\ProductService;
use WezomCms\Catalog\Services\SpecificationService;
use WezomCms\Catalog\Services\SpecValueService;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Repositories\AdminRepository;
use WezomCms\Providers\Repositories\ProviderRepository;

/**
 *  1) Название характеристи имеет такой формат Name(kk)/Name(ru) - т.е. названия разделены слешем
 *  первое название на казахском , второе на русском, если будет одно , то оно продублируется и
 *  для русского и казахского, если такой характеристики нет, то она будет создана, если есть то
 *  будет получена из бд, поэтому важно корректно прописывать их, чтоб не дублировались схожие
 *  название
 *  2) все, из ваше описаного, справедливо и для значений характеристи, если товар не имеет каких
 *  либо характеристи, поле оставляем пустым. При добавление цвета, важно, если такого цвета еще нет
 *  после добавлений отредактировать его hex-код, чтоб цвет соответсвовал описанию
 *  3) при добавлении товаров, происходит проверка товара в бд, проверяеться по его названию и всем
 *  характеристикам, если совпадение будет найдено то товар не создастся, если же будет изменена ,
 *  хоть одна характеристика будет создан новый товар
 *  4) товар обьединяется (с таким же товаром, только с другими значениями) через ключ группы
 *  (ключ может быть любам), обьедененые товары могут иметь свои наборы характеристик, цену , кол-во,
 *  но общее название и описание
 *  5) В колонках - provider, moderator, collection, указывается id соответствующих, моделей, если таких
 *  записей нет в бд, они будут проигнорированы, если есть, то привязаны (эти данные не обязательны, если у товар
 *  нет таких данных, ячейка в таблице должна быть пустой)
 *  6) В колонках brand, category - можно указывать или id модели, и тогда поведение будет как у колонок
 *  provider, moderator, collection, или название через слеш ('/'), соответсвенно в данном случае поведени будет
 *  отличаться, если в бд будет найдена запись по названию, то привяжет , если нет то будет создана новая запись и
 *  привязана к товару. (эти данные не обязательны, если у товар
 *  нет таких данных, ячейка в таблице должна быть пустой)
 *  7) колонка labels ids - принимает список id лейблов, через запятую (1,5), привязаны будут только те,
 *  которые будут найдены в бд
 */
class NewParser
{
    protected string $filePath;
    protected int $indexSpec = 27;
    protected array $specIds = [];

    private SpecificationRepository $specificationRepository;
    private SpecificationService $specificationService;
    private SpecValueRepository $specValueRepository;
    private ProductRepository $productRepository;
    private SpecValueService $specValueService;
    private ProductService $productService;
    private ProviderRepository $providerRepository;
    private AdminRepository $adminRepository;
    private CollectionRepository $collectionRepository;
    private CategoryRepository $categoryRepository;
    private CategoryService $categoryService;
    private BrandRepository $brandRepository;
    private BrandService $brandService;
    private LabelRepository $labelRepository;

    public function __construct(
        SpecificationRepository $specificationRepository,
        SpecificationService $specificationService,
        SpecValueRepository $specValueRepository,
        ProductRepository $productRepository,
        SpecValueService $specValueService,
        ProductService $productService,
        ProviderRepository $providerRepository,
        AdminRepository $adminRepository,
        CollectionRepository $collectionRepository,
        CategoryRepository $categoryRepository,
        CategoryService $categoryService,
        BrandRepository $brandRepository,
        BrandService $brandService,
        LabelRepository $labelRepository,
        private Administrator $admin
    ) {
        $this->specificationRepository = $specificationRepository;
        $this->specificationService = $specificationService;
        $this->specValueRepository = $specValueRepository;
        $this->productRepository = $productRepository;
        $this->specValueService = $specValueService;
        $this->productService = $productService;
        $this->providerRepository = $providerRepository;
        $this->adminRepository = $adminRepository;
        $this->collectionRepository = $collectionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
        $this->brandRepository = $brandRepository;
        $this->brandService = $brandService;
        $this->labelRepository = $labelRepository;
    }

    public function run(string $filePath): array
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $data = $spreadsheet->getSheet(0)
            ->toArray(null, false, true, false);

        $countCreate = 0;

        foreach ($data ?? [] as $k => $item) {
            if ($k === 0) {
                // здесь заголовки

                if (in_array(null, $item, true)) {
                    throw new Exception("Загаловки колонок не могут быть пустыми");
                }

                // получения или создания (если нету) характеристики
                foreach (array_slice($item, $this->indexSpec) ?? [] as $title) {
                    $tmp = $this->getNames($title);
                    $spec = $this->specificationRepository->getByName($tmp['ru']);

                    if (!$spec) {
                        $spec = $this->specificationService->createFromImport(
                            [
                                'translations' => $tmp
                            ]
                        );
                    }
                    $this->specIds[] = $spec->id;
                }
            } else {
                $attributes = [
                    'provider_id' => $this->getProviderId($item[22]),
                    'moderator_id' => $this->getModeratorId($item[21]),
                    'group_key' => $item[1],
                    'category_id' => $item[3],
                    'brand_id' => $item[2],
                    'cost' => $item[16] ?? 0,
                    'costDiscount' => $item[17] ?? 0,
                    'amountOneUser' => $item[19] ?? 0,
                    'amount' => $item[18] ?? 0,
                    'sort' => $item[20],
                    'publishedAt' => $item[14],
                    'expiresAt' => $item[15],
                    'collection_id' => $item[23],
                    'labels' => $item[24],
                    'weight' => $item[25],
                    'dimensions' => $this->getDimensions($item[26]),
                    'translations' => [
                        'ru' => [
                            'name' => $item[4],
                            'description' => $item[5],
                            'feature_1' => $item[6],
                            'feature_2' => $item[7],
                            'feature_3' => $item[8],
                        ],
                        'kk' => [
                            'name' => $item[9],
                            'description' => $item[10],
                            'feature_1' => $item[11],
                            'feature_2' => $item[12],
                            'feature_3' => $item[13],
                        ]
                    ],
                ];

                $attributes = $this->checkRelatedModel($attributes);

                // specifications
                foreach (array_slice($item, $this->indexSpec) ?? [] as $key => $specValueName) {
                    if ($specValueName) {
                        $tmp = $this->getNames($specValueName);

                        $val = $this->specValueRepository->getByNameAndSpec($tmp['ru'], $this->specIds[$key]);

                        if (!$val) {
                            $val = $this->specValueService->createFromImportWithTranslation(
                                [
                                    'specification_id' => $this->specIds[$key],
                                    'translations' => $tmp
                                ]
                            );
                        }

                        $attributes['specifications'][] = [
                            'spec_id' => $this->specIds[$key],
                            'value_id' => $val->id
                        ];
                    }
                }
                // exist or create product
                if (!$this->productRepository->existByImport($item[4], $attributes['specifications'] ?? [])) {
                    $this->productService->createFromImport($attributes);
                    $countCreate++;
                }
            }
        }

        return [
            'countCreate' => $countCreate
        ];
    }

    private function getModeratorId($id): ?int
    {
        if ($this->admin->onlyProvider()) {
            return null;
        }

        if ($this->admin->isModerator()) {
            return $this->admin->id;
        }

        return $id;
    }

    private function getProviderId($id): ?int
    {
        if ($this->admin->onlyProvider()) {
            return $this->admin->id;
        }

        return $id;
    }

    private function getNames(string $str): array
    {
        $tmp = explode('/', $str);

        return [
            'ru' => trim(last($tmp)),
            'kk' => trim(current($tmp))
        ];
    }

    private function getDimensions(string $value): array
    {
        $dimensions = explode('*', $value);
        for ($i = 0; $i < 3; $i++) {
            if (!isset($dimensions[$i])) {
                $dimensions[$i] = 1;
            }
        }

        return Product::sortDimensions($dimensions);
    }

    private function checkRelatedModel($data): array
    {
        if (isset($data['provider_id'])) {
            if (!$this->adminRepository->existBy('id', $data['provider_id'])) {
                $data['provider_id'] = null;
            }
        }
        if (isset($data['moderator_id'])) {
            if (!$this->adminRepository->existBy('id', $data['moderator_id'])) {
                $data['moderator_id'] = null;
            }
        }
        if (isset($data['collection_id'])) {
            if (!$this->collectionRepository->existBy('id', $data['collection_id'])) {
                $data['collection_id'] = null;
            }
        }
        if (isset($data['category_id'])) {
            if (is_numeric($data['category_id'])) {
                if (!$this->categoryRepository->existBy('id', $data['category_id'])) {
                    $data['category_id'] = null;
                }
            } else {
                $tmp = $this->getNames($data['category_id']);
                $category = $this->categoryRepository->getByName($tmp['ru']);
                if (!$category) {
                    $category = $this->categoryService->createFromImport(
                        [
                            'translations' => $tmp
                        ]
                    );
                }
                $data['category_id'] = $category->id;
            }
        }
        if (isset($data['brand_id'])) {
            if (is_numeric($data['brand_id'])) {
                if (!$this->brandRepository->existBy('id', $data['brand_id'])) {
                    $data['brand_id'] = null;
                }
            } else {
                $tmp = $this->getNames($data['brand_id']);
                $brand = $this->brandRepository->getByName($tmp['ru']);
                if (!$brand) {
                    $brand = $this->brandService->createFromImport(
                        [
                            'translations' => $tmp
                        ]
                    );
                }
                $data['brand_id'] = $brand->id;
            }
        }
        if (isset($data['labels'])) {
            $tmp = [];
            foreach (explode(',', $data['labels']) ?? [] as $id) {
                if ($this->labelRepository->existBy('id', $id)) {
                    $tmp[] = $id;
                }
            }
            $data['labels'] = $tmp;
        }

        return $data;
    }
}

