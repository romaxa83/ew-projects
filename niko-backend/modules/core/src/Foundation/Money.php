<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Support\Facades\Lang;

class Money
{
    /**
     * @var array
     */
    private $settings;
    /**
     * @var bool
     */
    private $isBackend;

    /**
     * @var string|null
     */
    protected $siteCurrencySymbol;

    /**
     * @var string|null
     */
    protected $adminCurrencySymbol;

    /**
     * Money constructor.
     */
    public function __construct()
    {
        $this->settings = config('cms.core.money', []);
        $this->isBackend = app('isBackend');
        $this->siteCurrencySymbol = Lang::get($this->settings['symbol']['site']);
        $this->adminCurrencySymbol = Lang::get($this->settings['symbol']['admin']);
    }

    /**
     * @return array|string|null
     */
    public function siteCurrencySymbol()
    {
        return $this->siteCurrencySymbol;
    }

    /**
     * @return array|string|null
     */
    public function adminCurrencySymbol()
    {
        return $this->adminCurrencySymbol;
    }

    /**
     * @param $amount
     * @return string
     */
    public function format($amount)
    {
        return number_format($amount, $this->precision(), ".", " ");
    }

    /**
     * @return string|null
     */
    public function code(): ?string
    {
        return $this->settings['code'];
    }

    /**
     * @return int
     */
    public function precision(): int
    {
        return (int)$this->settings['precision'];
    }

    /**
     * @param  string|int|float  $result
     * @return string
     */
    public function addCurrency($result): string
    {
        $symbol = $this->isBackend ? $this->adminCurrencySymbol() : $this->siteCurrencySymbol();

        switch ($this->settings['position']) {
            case 'left':
                return $symbol . ' ' . $result;
                break;
            case 'right':
                return $result . ' ' . $symbol;
                break;
        }
        return $result;
    }
}
