<?php

namespace WezomCms\Core\Api\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Auth tokens",
 *     description="Access/refresh token and epiresIn",
 * )
 */
class Tokens
{
    /**
     * @OA\Property(
     *     title="Token type",
     *     example="Bearer"
     * )
     */
    private string $tokenType;

    /**
     * @OA\Property(
     *     title="Expires in",
     *     example=31536000
     * )
     */
    private int $expiresIn;

    /**
     * @OA\Property(
     *     title="Access token",
     *     example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNDEwZDZkYzdiNzRlMWI4MDk3Y2VmZGFmMWE1NTE0ZTYwMWM1ZGE2NzE2YmY1MWZjZjc1YTU0Y2Y3OTY5NDU1ODRiNzYzMzllNmRmODE2ZTciLCJpYXQiOjE2NDMyMDMwOTIuMTIwMDQ1LCJuYmYiOjE2NDMyMDMwOTIuMTIwMDQ3LCJleHAiOjE2NzQ3MzkwOTIuMTAyNDI4LCJzdWIiOiIzNSIsInNjb3BlcyI6W119.jz1ZLZCg2z89DrA0AgbmD9Y_D7RWy_6lAljP3VjbxVWp-jn3AcNBoVP9pEG3NRnZZehQIkuq9f6L-cw7ivG6Ow3iE_a5ZAt3DS3F8pYVglsD_rhqgrkPywk6qM5ix_QgxKuv1_2OZyM08ngYSfC72XX1QV81-85vm7SVFCYeiXUZ8HMRJzYyGpJtLpH-ca1WG4i_taSre6vDBwqRGzdVgpoFon2mPwNQptfBF_uwxvtbCjxXnuDqXDIjVfjCyJjle0eChk-rE72ftsnwneBVuRuAndwj8B-Uo4ggePl3qjLya2eE9R2QvWxhE5-9H-vMoymDpcFusYRwz2inpvzwxETARPfCxUb_h3Lk8iDvEGEcWbIY2SnHvZviss_-rQbvrppgxY1TbP9XoJqnXV94ftgbmpmAhKyEQ9-tCGofVTqTb0rOZQXQDO2B3gKwVUiLwk9Cdiah9qmeTb6BMSfAcAS3U7kt_FzYACaaFNSabm9Zm4zU7mpLpbNBh6nqzaFq8qFAxZvNa0swYzscrzSa4FLvaFk64iGjsBqWfnVwwploOz8-obIZnVl_laulo5lBnQkxKw-SvNXwIzxGdccUqFCyhPUhaOW1KsqjgNF-cQ0q8etMavasbPDMJSDe9ysNScqAGrSIFHq-muwoa0mJgf5RBxA_BKmSj7l38lJEJt8"
     * )
     */
    private string $accessToken;

    /**
     * @OA\Property(
     *     title="Refresh token",
     *     example="def502007434aa6b5a42285046a8a3bc0fe5f38c05d6768e29eafece12d1acb7b472a0b4dd4df090171001f469c66b0416bcc8dd9b2f187cd1647e4514c4333887aeb46e6a6aeb21cdbea18341566473534916838d8421c065e0fcb851cad0a8498c9b03932f35ea3609b7720374e52a11b81f3169fb72d385f54ae9ab3b4d765732b7d1846019e87626a61aee699900b41f144883a96f50d2c6de258185a511f5d841f6b5266d8c45544220b9cad48118a1f145beb17b06780fa9fa90af270744531da2fc2b976c5e8b005fb99895a745a2de222dfb37d9b861d351f9f55525c9392233187c1a9c32d8619992f82d74edd72c0983633a589350dd94b29b3ea746c49a00f226ffe24abca6e56babd88f8e58777e9dc01270f622701b580c1e483e9b2bc3680b03841768ffc4b9e06f17a8f94ea89129b45c779f2cf1ff003f2923196902b8100d98db849faec750fbe7d71fb930f425ae1957034aeb256bd48d5020"
     * )
     */
    private string $refreshToken;
}
