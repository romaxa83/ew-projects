<?php

namespace App\Dto\GPS;

class GPSDataDto
{
    protected array $data = [];
    public static function byParams(array $params): self
    {
        $self = new self();

        $data = $params;

        $self->data = [
            'imei' => $data['ident'],
            'received_at' => $data['timestamp'],
            'latitude' => $data['position.latitude'] ?? null,
            'longitude' => $data['position.longitude'] ?? null,
            'heading' => $data['position.direction'] ?? null,
            'vehicle_mileage' => isset($data['vehicle.mileage'])
                ? convert_speed_from_km_to_miles($data['vehicle.mileage'])
                : null,
            'speed' => isset($data['position.speed'])
                ? convert_speed_from_km_to_miles($data['position.speed'])
                : null, // Мгновенная скорость в момент определения положения (км/ч)
            'device_battery_level' => $data['battery.level'] ?? null,
            'device_battery_charging_status' => $data['battery.charging.status'] ?? null,
            'driving' => $data['driving.status'] ?? null,
            'idling' => $data['idle.status'] ?? null,
            'engine_off' => isset($data['engine.ignition.status']) ? !$data['engine.ignition.status'] : null,

            'movement_status' => data_get($data, 'movement.status'),    // Текущее состояние движения , bool
            'position_satellites' => data_get($data, 'position.satellites'), // кол-во спутников, используемых для расчета координат для заданной информации о местоположении, int
            'position_valid' => data_get($data, 'position.valid'), // Является ли информация о местоположении точной и действительной для данной временной метки, bool
            'server_time_at' => data_get($data, 'server.timestamp'), // Временная метка, когда сервер получил сообщение , int
            'gsm_signal_level' => data_get($data, 'gsm.signal.level'), // Уровень сигнала мобильной сети (GSM, 3G, 4G, LTE, 5G, ...) (%) , int
            'gps_fuel_rate' => data_get($data, 'gps.fuel.rate'), // Расход топлива на основе GPS (литр/ч),  float
            'gps_fuel_used' => data_get($data, 'gps.fuel.used'), // Расход топлива на основе GPS (литр), float
            'external_powersource_voltage' => data_get($data, 'external.powersource.voltage'), // Внешнее напряжение питания (вольт), float
            'data' => $data,
        ];

        return $self;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
