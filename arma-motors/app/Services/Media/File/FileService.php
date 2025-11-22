<?php

namespace App\Services\Media\File;

use App\DTO\Media\FileDTO;
use App\DTO\Order\OrderPdfDTO;
use App\Models\Media\File as FileModel;
use App\Models\Order\Order;
use App\Repositories\Media\FileRepository;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class FileService
{
    private string $defaultDisk;
    private FilesystemAdapter $storage;

    public function __construct(protected FileRepository $fileRepository)
    {
        $this->defaultDisk = config('image.storage');
        $this->storage = \Storage::disk($this->defaultDisk);
    }

    public function getStorage(): FilesystemAdapter
    {
        return $this->storage;
    }

    public function createFileRecord(UploadedFile $file, FileDTO $dto): FileModel
    {
        $model = new FileModel();

        $model->entity_type = $dto->getModelClass();
        $model->entity_id = $dto->getModelId();
        $model->model = $dto->getModel();
        $model->type = $dto->getType();
        $model->basename = $file->getFilename();
        $model->hash = $file->hashName();
        $model->mime = $file->getClientMimeType();
        $model->ext = $file->getClientOriginalExtension();
        $model->size = $file->getSize();

        $model->save();

        return $model;
    }

    public function storeFile(FileModel $model, UploadedFile $file, array $settings = []): void
    {
        $dir = "files/{$model->model}/{$model->entity_id}";
        $this->getStorage()->makeDirectory($dir);
        $this->getStorage()->putFile($dir, $file);
    }

    public function deleteFile(FileModel $model): void
    {
        if(file_exists($model->pathToFileStorage())){
            unlink($model->pathToFileStorage());
            // если папка пуста , удаляем ее
            if(!glob("{$model->pathToFolderStorage()}*")){
                rmdir($model->pathToFolderStorage());
            }
        }
        // удаляем модель
        $model->forceDelete();
    }

    public function generateOrderPDF(Order $order , $data, $type)
    {
        try {
            $dir = $order->fileUploadDir();
            $fileName = $order->fileName($type);
            $path = $order->storagePath($type);

            $this->getStorage()->makeDirectory($dir);

            PdfFacade::loadView(
                "pdf.order.{$type}",
                resolve(OrderPdfDTO::class)->fill($data, $type),
                [],
                'UTF-8'
            )
                ->save($this->getStorage()->path($path));

            if(null == $this->fileRepository->getByHash($fileName)){
                $model = new FileModel();
                $model->entity_type = Order::class;
                $model->entity_id = $order->id;
                $model->model = 'order';
                $model->type = $type;
                $model->basename = "{$type}_{$order->uuid}";
                $model->hash = "$fileName";
                $model->mime = 'application/pdf';
                $model->ext = 'pdf';

                $model->save();
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
//            dd($e->getMessage());
        }
    }

    public function generateOrderHistoryPDF(\App\Models\History\Order $order , $data, $type)
    {
        try {
            $dir = $order->fileUploadDir();
            $fileName = $order->fileName($type);
            $path = $order->storagePath($type);

            $this->getStorage()->makeDirectory($dir);

//            logger('ORDER HISTORY PATH', [
//                'path_storage' => $this->getStorage()->path($path),
//                'path' => $path
//            ]);

            PdfFacade::setOptions([
                'logOutputFile' => storage_path('logs/log.htm'),
                'tempDir' => storage_path('logs/')
            ])->loadView(
                "pdf.order.{$type}",
                resolve(OrderPdfDTO::class)->fill($data, $type),
                [],
                'UTF-8'
            )
                ->save($this->getStorage()->path($path));

            logger('ORDER HISTORY LOAD');

            if(null == $this->fileRepository->getByHash($fileName)){
                $model = new FileModel();
                $model->entity_type = \App\Models\History\Order::class;
                $model->entity_id = $order->id;
                $model->model = 'order-history';
                $model->type = $type;
                $model->basename = "{$type}_{$order->aa_id}";
                $model->hash = "$fileName";
                $model->mime = 'application/pdf';
                $model->ext = 'pdf';

                $model->save();
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function generateInvoiceHistoryPDF(\App\Models\History\Invoice $invoice , $data, $type)
    {
        try {
            $dir = $invoice->fileUploadDir();
            $fileName = $invoice->fileName($type);
            $path = $invoice->storagePath($type);

            $this->getStorage()->makeDirectory($dir);

            logger('INVOICE HISTORY PATH', [
                'path_logo' => public_path('img/pdf/arma_motors.png'),
            ]);


//            Storage::makeDirectory('public/'. $dir);
//            $pathN = storage_path("app/public/" . $dir . '/'. $fileName);
//////            return storage_path("app/public/" . self::PDF_FILE_GENERATE_DIR . "/" . $this->pdfFileName());
////
//            PdfFacade::setPaper('A4')
//                ->setOptions([
//                    'isRemoteEnabled' => true,
//                    'dpi' => 80,
////                    'tempDir' => storage_path('temp/'),
//                    'logOutputFile' => null
//                ])
//                ->loadView(
//                    view: "pdf.order.{$type}",
//                    data: resolve(OrderPdfDTO::class)->fill($data, $type),
//                    encoding: 'UTF-8'
//                )
//                ->save($pathN);

//            dd(
//                $this->getStorage()->path($path),
//                $pathN
//            );

            PdfFacade::setOptions([
//                'logOutputFile' => storage_path('logs/log.htm'),
//                'tempDir' => storage_path('logs/')
            ])->loadView(
                "pdf.order.{$type}",
                resolve(OrderPdfDTO::class)->fill($data, $type),
                [],
                'UTF-8'
            )
                ->save($this->getStorage()->path($path));

            logger('INVOICE HISTORY LOAD');

            if(null == $this->fileRepository->getByHash($fileName)){
                $model = new FileModel();
                $model->entity_type = \App\Models\History\Invoice::class;
                $model->entity_id = $invoice->id;
                $model->model = 'invoice-history';
                $model->type = $type;
                $model->basename = "{$type}_{$invoice->aa_uuid}";
                $model->hash = "$fileName";
                $model->mime = 'application/pdf';
                $model->ext = 'pdf';

                $model->save();
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }
}


