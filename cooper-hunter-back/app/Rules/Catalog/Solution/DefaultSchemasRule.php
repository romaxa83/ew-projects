<?php


namespace App\Rules\Catalog\Solution;


use App\Models\Catalog\Solutions\Solution;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\ItemNotFoundException;

class DefaultSchemasRule implements Rule
{
    private const ERROR_MESSAGE_INCORRECT_ZONES = 'validation.custom.catalog.solutions.incorrect_count_zones';
    private const ERROR_MESSAGE_DUPLICATE_COUNT_ZONES = 'validation.custom.catalog.solutions.duplicate_count_zones';
    private const ERROR_MESSAGE_INDOORS_NOT_FOUND = 'validation.custom.catalog.solutions.indoors_not_found';
    private const ERROR_MESSAGE_INDOOR_NOT_CONNECTED = 'validation.custom.catalog.solutions.indoor_not_connected';
    private const ERROR_MESSAGE_INCORRECT_SUM_BTU = 'validation.custom.catalog.solutions.incorrect_schema_btu';

    private string $errorMessage;

    private array $errorReplacement = [];

    public function __construct(private array $args) { }

    /**
     * @param string $attribute
     * @param array $schemas
     * @return bool
     */
    public function passes($attribute, $schemas): bool
    {
        $isSetSchemas = [];

        foreach ($schemas as $schema) {
            if ($schema['count_zones'] !== count($schema['indoors'])) {
                $this->errorMessage = self::ERROR_MESSAGE_INCORRECT_ZONES;
                return false;
            }
            if (in_array($schema['count_zones'], $isSetSchemas, true)) {
                $this->errorMessage = self::ERROR_MESSAGE_DUPLICATE_COUNT_ZONES;
                return false;
            }

            $isSetSchemas[] = $schema['count_zones'];

            $indoors = Solution::whereIn('id', $schema['indoors'])
                ->get();

            if ($indoors->isEmpty()) {
                $this->errorMessage = self::ERROR_MESSAGE_INDOORS_NOT_FOUND;
                return false;
            }
            $indoorBtu = 0;
            foreach ($schema['indoors'] as $indoor) {
                try {
                    /**@var Solution $indoor */
                    $indoor = $indoors->sole('id', $indoor);
                } catch (ItemNotFoundException) {
                    $this->errorMessage = self::ERROR_MESSAGE_INDOORS_NOT_FOUND;
                    return false;
                }

                if (!in_array($indoor->id, $this->args['indoors'])) {
                    $this->errorMessage = self::ERROR_MESSAGE_INDOOR_NOT_CONNECTED;
                    $this->errorReplacement = ['indoor' => $indoor->product->title];
                    return false;
                }
                $indoorBtu += $indoor->btu;
            }
            if ($indoorBtu > $this->args['btu'] + $this->args['btu'] * $this->args['max_btu_percent'] / 100) {
                $this->errorMessage = self::ERROR_MESSAGE_INCORRECT_SUM_BTU;
                $this->errorReplacement = ['count_zones' => $schema['count_zones']];
                return false;
            }
        }
        return true;
    }

    public function message(): string
    {
        return __($this->errorMessage, $this->errorReplacement);
    }
}
