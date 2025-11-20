<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(type="object", title="Bearer Token",
 *     @OA\Property(property="data", type="object", description="Токены",
 *          @OA\Property(property="token_type", type="string", description="Тип авторизации", example="Bearer"),
 *          @OA\Property(property="expires_in", type="integer", description="Время жизни токена", example=31536000),
 *          @OA\Property(property="access_token", type="string", description="Access token",
 *              example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiMDc5NDU3NjE2ZDdkYzA2NDIzNjNmMGRhMTBkZGEzOWRhMzdhMjM1MmQ0NDU0YTdjYWMwOWYxYjM3ZjQ3ODVjNDM1M2IzNzg1MDE4NGEyYjAiLCJpYXQiOjE2NTMwMjkzOTIuNTA3MzQyLCJuYmYiOjE2NTMwMjkzOTIuNTA3MzQ2LCJleHAiOjE2ODQ1NjUzOTIuNDk3MjU5LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0."
 *          ),
 *          @OA\Property(property="refresh_token", type="string", description="Refresh token",
 *              example="def5020088df2ca413818352cf5bc074eb6a83db523da9393f6a551f4d056e81220aa2c1a9b6d06fcdaaf066fbaea1540043e017ed9a12cf8e79be0e14790d7ec909e493adc0cf324b1b590df87fa107aedff6a5a6987080c8a70f5e7582c727e8b5d43b53f8246c261e9bc57954ee7b833e5c22f57"
 *          ),
 *          @OA\Property(property="isAdmin", type="boolean", description="Пользователь админ", example=true),
 *     ),
 *     @OA\Property(property="success", type="boolean", example=true),
 * )
 */
class BearerTokens
{}
