<?php

namespace App\Services\GPS;

use App\Dto\GPS\DeviceGpsData;
use App\Dto\GPS\GPSDataDto;
use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\GPS\Message;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\GPS\Alerts\GpsAlertEventService;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;
use DB;
use Exception;
use Throwable;

class GPSDataService
{
    public function createMessage(GPSDataDto $dto): void
    {
        $message = Message::query()->make($dto->getData());
        $message->saveOrFail();
    }

    public function processMessages(int $messagesLimit = null)
    {
        $date = CarbonImmutable::now();

        $defaultSpeedLimit = config('gps.default_speed_limit');
        $lowBatteryLevel = config('gps.low_battery_level');

        $messages = Message::query();
        if ($messagesLimit) {
            $messages->limit($messagesLimit);
        }
        $messages = $messages
            ->orderBy('received_at')
            ->get();

        /** @var Message $message */
        foreach ($messages as $message) {
            try {
                // иногда приходят данные за - 2004-01-01 ??? (их удаляем)
                if($message->received_at->year != $date->year) {
                    $message->delete();
                    continue;
                }
                if($message->longitude == null && $message->latitude == null) {
                    $message->delete();
                    continue;
                }
                /** @var Device $device */
                $device = Device::query()
                    ->with([
                        'truck.lastGPSHistory',
                        'trailer.lastGPSHistory',
                    ])
                    ->whereImei($message->imei)
                    ->first();
                if (!$device || !$device->status->isActive() || !$device->vehicle()) {
                    $message->delete();
                    continue;
                }

                $vehicle = $device->vehicle();

                $eventType = $this->resolveHistoryEventType($message, $vehicle);

                $speedLimit = $vehicle->getCompany()->speed_limit ?? $defaultSpeedLimit;

                DB::beginTransaction();
                if (
                    $eventType !== History::EVENT_DRIVING
                    && $vehicle->lastGPSHistory
                    && $vehicle->lastGPSHistory->event_type === $eventType
//                    && $vehicle->lastGPSHistory->received_at->startOfDay() == now()->startOfDay()
                ) {
                    $history = $vehicle->lastGPSHistory;

                    $received_at = $history->last_received_at ? $history->last_received_at->timestamp : $history->received_at->timestamp;

                    $duration = $message->received_at->timestamp - $received_at + $history->event_duration;

                    if(
                        $eventType == History::EVENT_LONG_IDLE
                        && $duration < config('gps.long_idle_min_duration')
                    ){
                        $history->update([
                            'event_type' => History::EVENT_IDLE
                        ]);
                    }

                    if(
                        $eventType == History::EVENT_IDLE
                        && $duration >= config('gps.long_idle_min_duration')
                    ){
                        $history->update([
                            'event_type' => History::EVENT_LONG_IDLE
                        ]);
                    }

                    $history->update([
                        'event_duration' => $duration,
                        'device_battery_level' => $message->device_battery_level,
                        'device_battery_charging_status' => $message->device_battery_charging_status,
                        'msg_count_for_duration' => $history->msg_count_for_duration + 1,
                        'last_received_at' => $message->received_at,
                        'sleep_mode' => $message->data['sleep.mode.enum'] ?? null,
                    ]);

                    if(!($history->latitude && $history->longitude)){
                        $history->update([
                            'latitude' => $message->latitude,
                            'longitude' => $message->longitude,
                        ]);
                    }

                } else {
                    // добавляем duration, относительно последней точки
                    $duration = null;
                    if(
                        $eventType == History::EVENT_IDLE
                        || $eventType == History::EVENT_ENGINE_OFF
                        || $eventType == History::EVENT_LONG_IDLE
                        || $eventType == History::EVENT_TRAILER_STOPPED
                    ){
                        $h = $vehicle->lastGPSHistory;
                        if($h){
                            $received = $h->last_received_at ? $h->last_received_at ->timestamp : $h->received_at->timestamp;
                            $duration = $message->received_at->timestamp - $received;
                        }
                    }

                    $history = $this->createHistory([
                        'device_id' => $device->id,
                        'received_at' => $message->received_at,
                        'latitude' => $message->latitude,
                        'longitude' => $message->longitude,
                        'truck_id' => $device->truck ? $device->truck->id : null,
                        'trailer_id' => $device->trailer ? $device->trailer->id : null,
                        'driver_id' => $vehicle->driver_id,
                        'speed' => $message->speed,
                        'heading' => $message->heading,
                        'vehicle_mileage' => $message->vehicle_mileage,
                        'device_battery_level' => $message->device_battery_level,
                        'device_battery_charging_status' => $message->device_battery_charging_status,
                        'event_type' => $eventType,
                        'event_duration' => $duration,
                        'company_id' => $vehicle->carrier_id,
                        'data' => $message->data,
                        'is_speeding' => $message->speed && ($message->speed - $speedLimit) >= 1,
                        'msg_count_for_duration' => 1,
                        'last_received_at' => $message->received_at,
                        'sleep_mode' => $message->data['sleep.mode.enum'] ?? null,
                    ]);
                }

                $alertBaseData = [
                    'history_id' => $history->id,
                    'received_at' => $message->received_at,
                    'latitude' => $message->latitude,
                    'longitude' => $message->longitude,
                    'truck_id' => $device->truck ? $device->truck->id : null,
                    'trailer_id' => $device->trailer ? $device->trailer->id : null,
                    'driver_id' => $vehicle->driver_id,
                    'speed' => $message->speed,
                    'company_id' => $vehicle->carrier_id,
                    'device_id' => $device->id,
                ];

                if ($this->hasConnectedLost($vehicle) && !$this->hasConnectedRestored($vehicle)) {
                    $alert = $this->createAlert($alertBaseData + ['alert_type' => Alert::ALERT_DEVICE_CONNECTION_RESTORED]);

                    Telegram::info("ALERT: ". Alert::ALERT_DEVICE_CONNECTION_RESTORED , null, [
                        'alert_id' => $alert->id,
                        'truck_id' => $device->truck ? $device->truck->id : null,
                        'trailer_id' => $device->trailer ? $device->trailer->id : null,
                    ]);
                }

                if ($this->hasSpeeding($message, $speedLimit, $vehicle)) {
                    $this->createAlert($alertBaseData + ['alert_type' => Alert::ALERT_TYPE_SPEEDING]);
                }

                if ($this->isBatteryStopCharging($message, $vehicle)) {
                    $alert = $this->createAlert(
                        $alertBaseData + [
                            'alert_type' => Alert::ALERT_TYPE_DEVICE_BATTERY,
                            'alert_subtype' => Alert::ALERT_SUBTYPE_BATTERY_STOP_CHARGING,
                        ]
                    );

                    Telegram::info("ALERT: ". Alert::ALERT_SUBTYPE_BATTERY_STOP_CHARGING , null, [
                        'alert_id' => $alert->id,
                        'truck_id' => $device->truck ? $device->truck->id : null,
                        'trailer_id' => $device->trailer ? $device->trailer->id : null,
                    ]);
                }

                if ($this->isBatteryLevelLow($message, $vehicle, $lowBatteryLevel)) {
                    $this->createAlert(
                        $alertBaseData + [
                            'alert_type' => Alert::ALERT_TYPE_DEVICE_BATTERY,
                            'alert_subtype' => Alert::ALERT_SUBTYPE_BATTERY_LOW,
                        ]
                    );
                }

                $vehicle->last_gps_history_id = $history->id;
                if ($eventType === History::EVENT_DRIVING) {
                    $vehicle->last_driving_at = $history->received_at;
                }
                $vehicle->save();

                $message->delete();

                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                logger_info($exception->getMessage());
            }
        }
    }

    private function coordsHash($message): string
    {
        return simple_hash([
            $message->latitude,
            $message->longitude,
            $message->vehicle_mileage,
        ]);
    }

    private function hasSpeeding(Message $message, int $speedLimit, Vehicle $vehicle): bool
    {
        return (
            $message->speed && ($message->speed - $speedLimit) >= 1
            && (!$vehicle->lastGPSHistory
                || (
                    $vehicle->lastGPSHistory &&
                    !($vehicle->lastGPSHistory->speed && ($vehicle->lastGPSHistory->speed - $speedLimit) >= 1)
                )
            )
        );
    }

    private function hasConnectedLost(Vehicle $vehicle): bool
    {
        return $vehicle->lastGPSHistory && $vehicle->lastGPSHistory->hasConnectionLostAlerts();
    }

    private function hasConnectedRestored(Vehicle $vehicle): bool
    {
        return $vehicle->lastGPSHistory && $vehicle->lastGPSHistory->hasConnectionRestoreAlerts();
    }

    private function isBatteryStopCharging(Message $message, Vehicle $vehicle): bool
    {
        return (
            $message->device_battery_charging_status === false
            && (
                !$vehicle->lastGPSHistory
                || $vehicle->lastGPSHistory->device_battery_charging_status !== false
            )
        );
    }

    private function isBatteryLevelLow(Message $message, Vehicle $vehicle, int $lowBatteryLevel): bool
    {
        return (
            !is_null($message->device_battery_level) && $message->device_battery_level <= $lowBatteryLevel
            && (
                !$vehicle->lastGPSHistory
                || (
                    $vehicle->lastGPSHistory
                    && (
                        is_null($vehicle->lastGPSHistory->device_battery_level)
                        || $vehicle->lastGPSHistory->device_battery_level > $lowBatteryLevel
                    )
                )
            )
        );
    }

    public function createAlert(array $data): Alert
    {
        /** @var Alert $alert */
        $alert = Alert::query()->make($data);
        $alert->saveOrFail();

        GpsAlertEventService::gpsAlert($alert)->broadcast();

        return $alert;
    }

    public function pingDeviceConnection(
        Device $device,
        DeviceGpsData $telemetryData
    ): void
    {
        try {
            if(!$device->status->isActive()) return;

            $vehicle = $device->vehicle();

            if (!$vehicle || !$vehicle->lastGPSHistory) return;

            $now = CarbonImmutable::now('UTC');
            $last = CarbonImmutable::createFromTimestampUTC(
                $telemetryData->serverTimestamp
            );

            // если трак в движение и последние данные телеметрии приходили позже заданного времени
            // а также если у трака в последней записи по истории нет алерта connection_lost,
            // то создаем алерт что связь с девайсом утерена (в движении)
            if(
                $telemetryData->speed > 0
                && $telemetryData->movementStatus
                && $telemetryData->positionValid
                && $now->diffInSeconds($last) > config('gps.device_disconnected_time_on_not_stop')
                && !$vehicle->lastGPSHistory->hasConnectionLostAlerts()
            ){
                $alert = $this->createAlert([
                    'history_id' => $vehicle->lastGPSHistory->id,
                    'received_at' => $now,
                    'truck_id' => $device->truck ? $device->truck->id : null,
                    'trailer_id' => $device->trailer ? $device->trailer->id : null,
                    'driver_id' => $vehicle->driver_id,
                    'latitude' => $vehicle->lastGPSHistory->latitude,
                    'longitude' => $vehicle->lastGPSHistory->longitude,
                    'alert_type' => Alert::ALERT_DEVICE_CONNECTION_LOST,
                    'company_id' => $vehicle->getCompanyId(),
                    'device_id' => $device->id,
                    'speed' => $telemetryData->speed,
                    'min_lost_connection' => (string)config('gps.device_disconnected_time_on_not_stop')/60,
                ]);

                Telegram::info("ALERT". Alert::ALERT_DEVICE_CONNECTION_LOST , null, [
                    'type' => 'moving',
                    'alert_id' => $alert->id,
                    'now' => $now->toDateTimeString(),
                    'lasst' => $last->toDateTimeString(),
                    'truck_id' => $device->truck ? $device->truck->id : null,
                    'trailer_id' => $device->trailer ? $device->trailer->id : null,
                ]);

                return;
            }

//            Telegram::info("ALERT ". Alert::ALERT_DEVICE_CONNECTION_LOST , null, [
//                'speed' => $telemetryData->speed,
//                'diff' => $now->diffInSeconds($last),
//                'bool' => (bool)$now->diffInSeconds($last) > config('gps.device_disconnected_time_on_stop'),
//                'config time' => config('gps.device_disconnected_time_on_stop'),
//                'hasAlert' => (bool)$vehicle->lastGPSHistory->hasConnectionLostAlerts(),
//                'check' => (bool)($telemetryData->speed = 0
//                    && $now->diffInSeconds($last) > config('gps.device_disconnected_time_on_stop')
//                    && !$vehicle->lastGPSHistory->hasConnectionLostAlerts())
//            ]);


            if(
                $telemetryData->speed == 0
                    && $now->diffInSeconds($last) > config('gps.device_disconnected_time_on_stop')
                    && !$vehicle->lastGPSHistory->hasConnectionLostAlerts()
            ){

                $alert = $this->createAlert([
                    'history_id' => $vehicle->lastGPSHistory->id,
                    'received_at' => $now,
                    'truck_id' => $device->truck ? $device->truck->id : null,
                    'trailer_id' => $device->trailer ? $device->trailer->id : null,
                    'driver_id' => $vehicle->driver_id,
                    'latitude' => $vehicle->lastGPSHistory->latitude,
                    'longitude' => $vehicle->lastGPSHistory->longitude,
                    'alert_type' => Alert::ALERT_DEVICE_CONNECTION_LOST,
                    'company_id' => $vehicle->getCompanyId(),
                    'device_id' => $device->id,
                    'speed' => $telemetryData->speed,
                    'min_lost_connection' => (string)config('gps.device_disconnected_time_on_stop')/60,
                ]);

                Telegram::info("ALERT ". Alert::ALERT_DEVICE_CONNECTION_LOST , null, [
                    'type' => 'stop',
                    'alert_id' => $alert->id,
                    'now' => $now->toDateTimeString(),
                    'lasst' => $last->toDateTimeString(),
                    'truck_id' => $device->truck ? $device->truck->id : null,
                    'trailer_id' => $device->trailer ? $device->trailer->id : null,
                ]);
            }

        } catch (\Throwable $e) {
            Telegram::error("ALERT ". Alert::ALERT_DEVICE_CONNECTION_LOST , null, [
                'err_msd' => $e->getMessage(),
            ]);
        }
    }

    public function checkDeviceConnectionAlert(Device $device)
    {
        if(!$device->status->isActive()) return;

        $vehicle = $device->vehicle();

        if (!$vehicle || !$vehicle->lastGPSHistory) return;

        $allowedLastActivityTime = CarbonImmutable::now()->addMinutes(-1 * config('gps.device_disconnected_time'));

        // время если авто стоит
        $allowedLastActivityTimeStop = CarbonImmutable::now()->addMinutes(-1 * config('gps.device_disconnected_time_not_speed'));

        if($vehicle->lastGPSHistory->speed == 0){
            $time = $allowedLastActivityTimeStop;
            $timeAsStr = config('gps.device_disconnected_time');
        } else {
            $time = $allowedLastActivityTime;
            $timeAsStr = config('gps.device_disconnected_time_not_speed');
        }

        if (
            $vehicle->lastGPSHistory->sleep_mode == 0
            && $vehicle->lastGPSHistory->updated_at < $time
            && !$vehicle->lastGPSHistory->hasConnectionLostAlerts()
        ) {
            $alert = $this->createAlert([
                'history_id' => $vehicle->lastGPSHistory->id,
                'received_at' => now(),
                'truck_id' => $device->truck ? $device->truck->id : null,
                'trailer_id' => $device->trailer ? $device->trailer->id : null,
                'driver_id' => $vehicle->driver_id,
                'latitude' => $vehicle->lastGPSHistory->latitude,
                'longitude' => $vehicle->lastGPSHistory->longitude,
                'alert_type' => Alert::ALERT_DEVICE_CONNECTION_LOST,
                'company_id' => $vehicle->getCompanyId(),
                'device_id' => $device->id,
            ]);

            Telegram::info("ALERT [{Alert::ALERT_DEVICE_CONNECTION_LOST}]", null, [
                'alert_id' => $alert->id,
                'time' => $time,
                'timeAsStr' => $timeAsStr,
                'truck_id' => $device->truck ? $device->truck->id : null,
                'trailer_id' => $device->trailer ? $device->trailer->id : null,
            ]);
        }
    }

    /**
     * @throws Throwable
     */
    private function createHistory(array $data): History
    {
        /** @var History $history */
        $history = History::query()->make($data);
        $history->saveOrFail();

        return $history;
    }
    private function resolveHistoryEventType(Message $message, Vehicle $vehicle): string
    {
        if($vehicle->isTrailer()){
            if(
                $message->engine_off
                || ($message->engine_off == false && $message->speed == 0)
                || $message->idling
            ){
                return History::EVENT_TRAILER_STOPPED;
            }

            return History::EVENT_DRIVING;
        }
        // for truck
        if (
            $message->idling
            || ($message->engine_off == false && $message->speed == 0)
        ) {

            $duration = $vehicle->lastGPSHistory
                ? CarbonImmutable::now()->timestamp - $vehicle->lastGPSHistory->received_at->timestamp
                : 0;

            return  $duration >= config('gps.long_idle_min_duration')
                    ? History::EVENT_LONG_IDLE
                    : History::EVENT_IDLE;
        }

        if ($message->engine_off && $message->speed == 0) {
            return  History::EVENT_ENGINE_OFF;
        }

        return History::EVENT_DRIVING;
    }
}
