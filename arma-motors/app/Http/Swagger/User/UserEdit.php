<?php

namespace App\Http\Swagger\User;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for edit user",
 *     description="Request for edit user car",
 * )
 */
class UserEdit
{
    /**
     * @OA\Property(
     *     title="uuid",
     *     description="Uuid присвоеный пользователю",
     *     example="74814d51-fc23-11eb-8274-4cd98fc26f15",
     * )
     *
     */
    public string $uuid;

    /**
     * @OA\Property(
     *     title="User status",
     *     description="Статусы пользователя (2 - клиент AA, 1 - не клиент АА) ,(поле может быть null)",
     *     example=2,
     * )
     *
     */
    public int $status;

    /**
     * @OA\Property(
     *     title="new phone",
     *     description="Новый телефон, если был запрос на смену телефона ,(поле может быть null)",
     *     example="+30997878788",
     * )
     */
    public string $newPhone;

    /**
     * @OA\Property(
     *     title="codeOKPO",
     *     description="Новый codeOKPO, если был запрос на его смену,(поле может быть null)",
     *     example="997878788",
     * )
     */
    public string $codeOKPO;

    /**
     * @OA\Property(
     *     title="name",
     *     description="ФИО пользователя,(поле может быть null)",
     *     example="Иванов Иван Иванович",
     * )
     */
    public string $name;

    /**
     * @OA\Property(
     *     title="verify",
     *     description="Верифицирован пользователь,(поле может быть null)",
     *     example=true,
     * )
     */
    public bool $verify;

    /**
     * @OA\Property(
     *     title="email",
     *     description="Email пользователя,(поле может быть null)",
     *     example="test@gmail.com",
     * )
     */
    public string $email;
}

