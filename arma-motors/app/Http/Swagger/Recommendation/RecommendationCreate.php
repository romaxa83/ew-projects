<?php

namespace App\Http\Swagger\Recommendation;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for create recommendation",
 *     description="Request for create recommendation",
 * )
 */
class RecommendationCreate
{
    /**
     * @OA\Property(
     *     title="uuid",
     *     description="Uuid рекомендации",
     *     example="9ee4670f-0016-11ec-8274-4cd98fc26f15",
     * )
     */
    public string $uuid;

    /**
     * @OA\Property(
     *     title="auto",
     *     description="Uuid авто",
     *     example="9ee4670f-0016-11ec-8274-4cd98fc26f15",
     * )
     */
    public string $auto;

    /**
     * @OA\Property(
     *     title="recommendation",
     *     description="название работ",
     *     example="Амортизатор передній Megane III,Fluence",
     * )
     */
    public string $recommendation;

    /**
     * @OA\Property(
     *     title="recommendation",
     *     description="комментарий, может быть null",
     *     example="comment",
     * )
     */
    public string $comment;

    /**
     * @OA\Property(
     *     title="quantity",
     *     description="кол-во",
     *     example=3,
     * )
     */
    public float $quantity;

    /**
     * @OA\Property(
     *     title="request",
     *     description="Uuid заявки",
     *     example="ee060bad-5446-11ec-8277-4cd98fc26f14",
     * )
     */
    public string $request;

    /**
     * @OA\Property(
     *     title="rejection reason",
     *     description="Причина отказа, может быть null",
     *     example=null,
     * )
     */
    public string $rejectionReason;

    /**
     * @OA\Property(
     *     title="date completion",
     *     description="Дата окнчания работ (timestamp)",
     *     example=1624350625,
     * )
     */
    public int $dateCompletion;

    /**
     * @OA\Property(
     *     title="author",
     *     description="Автор",
     *     example="Коротун Сергій Юрійович",
     * )
     */
    public int $author;

    /**
     * @OA\Property(
     *     title="executor",
     *     description="Исполнитель",
     *     example="Коротун Сергій Юрійович",
     * )
     */
    public int $executor;

    /**
     * @OA\Property(
     *     title="completed",
     *     description="Выполнена",
     *     example=true,
     * )
     */
    public bool $completed;

    /**
     * @OA\Property(
     *     title="date relevance",
     *     description="Дата актуальности (timestamp)",
     *     example=1624350625,
     * )
     */
    public int $dateRelevance;
}


