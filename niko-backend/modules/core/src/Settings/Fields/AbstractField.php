<?php

namespace WezomCms\Core\Settings\Fields;

use Illuminate\Support\Arr;
use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Settings\RenderSettings;

abstract class AbstractField
{
    public const TYPE_INPUT = 'input';
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTI_SELECT = 'multi_select';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_WYSIWYG = 'wysiwyg';
    public const TYPE_RADIO = 'radio';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_SLUG = 'slug';
    public const TYPE_GOOGLE_MAP = 'google_map';

    protected $attributes = [];
    protected $type = self::TYPE_INPUT;
    protected $inputName;
    protected $renderSettings;
    protected $name;
    protected $key;
    protected $sort = 0;
    protected $rules;
    protected $isMultilingual = false;
    protected $helpText = '';
    protected $smallText = '';
    protected $buttonBefore;
    protected $iconBefore = '';
    protected $buttonAfter;
    protected $iconAfter = '';
    protected $value;
    protected $storageId;
    protected $valueObj;
    protected $class;
    protected $default = null;

    /**
     * AbstractField constructor.
     * @param  RenderSettings|null  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        if (null !== $renderSettings) {
            $this->setRenderSettings($renderSettings);
        }
    }

    /**
     * @param  null|RenderSettings  $renderSettings
     * @return AbstractField|static
     */
    public static function make(?RenderSettings $renderSettings = null)
    {
        return new static($renderSettings);
    }

    /**
     * @param  mixed  $inputName
     * @return AbstractField
     */
    public function setInputName($inputName): AbstractField
    {
        $this->inputName = $inputName;

        return $this;
    }

    /**
     * @param  null  $locale
     * @return string
     */
    public function getInputName($locale = null)
    {
        if ($this->inputName) {
            $name = $this->inputName;
        } elseif ($this->isMultilingual && $this->getType() !== AbstractField::TYPE_IMAGE) {
            $name = $this->key . '[{locale}]';
        } else {
            $name = $this->key;
        }

        return str_replace('{locale}', $locale, $name);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  mixed  $key
     * @return AbstractField
     */
    public function setKey($key): AbstractField
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param  mixed  $name
     * @return AbstractField
     */
    public function setName($name): AbstractField
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  mixed  $rules
     * @return AbstractField
     */
    public function setRules($rules): AbstractField
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param  bool  $isMultilingual
     * @return AbstractField
     */
    public function setIsMultilingual(bool $isMultilingual = true): AbstractField
    {
        $this->isMultilingual = $isMultilingual;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultilingual(): bool
    {
        return $this->isMultilingual;
    }

    /**
     * @param  RenderSettings  $renderSettings
     * @return AbstractField
     */
    public function setRenderSettings(RenderSettings $renderSettings)
    {
        $this->renderSettings = $renderSettings;

        return $this;
    }

    /**
     * @return null|RenderSettings
     */
    public function getRenderSettings(): ?RenderSettings
    {
        return $this->renderSettings;
    }

    /**
     * @return string|null
     */
    public function getGroup()
    {
        if ($this->renderSettings) {
            return $this->renderSettings->getTab()->getKey();
        }
    }

    /**
     * @param  int  $sort
     * @return $this
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param  string  $text
     * @return $this
     */
    public function setHelpText(string $text): AbstractField
    {
        $this->helpText = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getHelpText(): string
    {
        return $this->helpText;
    }

    /**
     * @param  string  $text
     * @return $this
     */
    public function setSmallText(string $text): AbstractField
    {
        $this->smallText = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getSmallText(): string
    {
        return $this->smallText;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttribute($name, $value): AbstractField
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param  iterable  $attributes
     * @return $this
     */
    public function setAttributes(iterable $attributes): AbstractField
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function buildAttributes(): string
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[] = $name . '="' . e(is_array($value) ? implode(' ', $value) : $value) . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * @param  string  $icon
     * @return $this
     */
    public function setIconBefore(string $icon): AbstractField
    {
        $this->iconBefore = $icon;

        return $this;
    }

    /**
     * Font awesome icon class
     * @return string
     */
    public function getIconBefore(): string
    {
        return $this->iconBefore;
    }

    /**
     * @param  string  $icon
     * @return $this
     */
    public function setIconAfter(string $icon): AbstractField
    {
        $this->iconAfter = $icon;

        return $this;
    }

    /**
     * Font awesome icon class
     * @return string
     */
    public function getIconAfter(): string
    {
        return $this->iconAfter;
    }


    /**
     * @return ButtonInterface|null
     */
    public function getButtonBefore()
    {
        return $this->buttonBefore;
    }

    /**
     * @param  ButtonInterface  $buttonBefore
     * @return $this
     */
    public function setButtonBefore($buttonBefore): AbstractField
    {
        $this->buttonBefore = $buttonBefore;

        return $this;
    }

    /**
     * @return ButtonInterface|null
     */
    public function getButtonAfter()
    {
        return $this->buttonAfter;
    }

    /**
     * @param  ButtonInterface  $buttonAfter
     * @return $this
     */
    public function setButtonAfter($buttonAfter): AbstractField
    {
        $this->buttonAfter = $buttonAfter;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasToBeGrouped(): bool
    {
        return $this->getButtonBefore() || $this->getIconBefore() || $this->getButtonAfter() || $this->getIconAfter();
    }

    /**
     * @return bool
     */
    public function isAttachment(): bool
    {
        return false;
    }

    /**
     * @param  mixed  $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param  null  $locale
     * @return string
     */
    public function getValue($locale = null)
    {
        if ($locale) {
            return Arr::get($this->value, $locale);
        }

        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param  mixed  $value
     * @return AbstractField
     */
    public function default($value): AbstractField
    {
        $this->default = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStorageId()
    {
        return $this->storageId;
    }

    /**
     * @param  mixed  $storageId
     * @return AbstractField
     */
    public function setStorageId($storageId): AbstractField
    {
        $this->storageId = $storageId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValueObj()
    {
        return $this->valueObj;
    }

    /**
     * @param  mixed  $valueObj
     * @return AbstractField
     */
    public function setValueObj($valueObj): AbstractField
    {
        $this->valueObj = $valueObj;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param  mixed  $class
     * @return AbstractField
     */
    public function setClass($class): AbstractField
    {
        $this->class = $class;

        return $this;
    }
}
