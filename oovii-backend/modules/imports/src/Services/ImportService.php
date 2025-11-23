<?php

namespace WezomCms\Imports\Services;

use WezomCms\Core\Models\Administrator;
use WezomCms\Imports\Models\Import;

class ImportService
{
    public function createRow($type, Administrator|null $user = null): Import
    {
        $model = new Import();
        $model->administrator_id = $user->id ?? null;
        $model->type = $type;

        $model->save();

        return $model;
    }

    public function setStatus($status, Import $model): Import
    {
        $model->status = $status;

        $model->save();

        return $model;
    }

    public function setDoneStatus(Import $model, $message, $error = null): Import
    {
        $model->status = Import::STATUS_DONE;
        $model->message = $message;
        $model->error_data = $error;

        $model->save();

        return $model;
    }
}

