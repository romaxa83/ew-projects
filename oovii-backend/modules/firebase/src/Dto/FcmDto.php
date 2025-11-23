<?php

namespace WezomCms\Firebase\Dto;

use WezomCms\Firebase\Templates\TemplateData;

final class FcmDto
{
    public null|int $user_id;
    public null|string $entity_type;
    public null|int $entity_id;
    public string $status;
    public string $type;
    public $send_data;
    public $response_data;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->user_id = $args['user_id'] ?? null;
        $self->entity_type = $args['entity_type'] ?? null;
        $self->entity_id = $args['entity_id'] ?? null;
        $self->status = $args['status'];
        $self->type = $args['type'];
        $self->send_data = static::setSendData($args);
        $self->response_data = $args['response_data'] ?? [];

        return $self;
    }

    private static function setSendData($args): array
    {
        if(isset($args['send_data']) && !empty($args['send_data'])){
            if($args['send_data'] instanceof TemplateData){
                return $args['send_data']->asArray();
            }
            return (array)$args['send_data'];
        }

        return [];
    }
}
