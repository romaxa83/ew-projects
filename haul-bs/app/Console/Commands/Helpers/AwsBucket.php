<?php

namespace App\Console\Commands\Helpers;

use App\Dto\Inventories\BrandDto;
use App\Models\Inventories\Brand;
use App\Services\Inventories\BrandService;
use App\Services\Requests\ECom\Commands\Brand\BrandCreateCommand;
use App\Services\Requests\ECom\Commands\Brand\BrandDeleteCommand;
use Aws\S3\S3Client;
use Illuminate\Console\Command;

class AwsBucket extends Command
{
    protected $signature = 'helpers:aws_bucket';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        $s3Client = new S3Client([
            'region' => 'us-east-2',
            'credentials' => [
                'key' => '',
                'secret' => '',
            ]
        ]);

        $buckets = $s3Client->listBuckets();
        foreach ($buckets['Buckets'] as $bucket) {
            $this->info($bucket['Name']);
        }

        $b = 'bs-dev-s3';
        $result = $s3Client->listObjects(['Bucket' => $b]);

        $count = 0;
        $size = 0;

        foreach ($result['Contents'] as $object) {
            $count++;
            $size += $object['Size'];
        }

        echo "Total objects in bucket: " . $count . "\n";
        echo "Total size of objects: " . $size . " bytes\n";
    }
}


