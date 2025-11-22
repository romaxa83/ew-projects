<?php

namespace App\Services\Saas\GPS\Flespi\Commands\Devices;

use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Flespi\Commands\BasePostCommand;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;

class DeviceBlockCommand extends BasePostCommand
{
    protected ?Device $device = null;

    public function device(Device $device): self
    {
        $this->device = $device;
        return $this;
    }

    protected function getUri(): string
    {
        if(!$this->device){
            throw new CommandException("For this command [".__CLASS__."] you need to transfer the device");
        }

//        dd($this->device->flespi_device_id);

        return str_replace(
            '{device_id}',
            $this->device->flespi_device_id,
            config("flespi.patches.device_block")
        );
    }
}

