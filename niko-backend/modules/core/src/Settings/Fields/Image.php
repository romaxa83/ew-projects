<?php

namespace WezomCms\Core\Settings\Fields;

class Image extends AbstractField
{
    /**
     *     'image' => 'cms-core::administrator.images', // Set path to configuration array
     *     // or
     *     'image' => [ // array key - field name in DB.
     *         'directory' => 'administrators', // The name of the directory to store downloadable image.
     *         'placeholder' => 'no-avatar.png',
     *         'storage' => 'public', // Storage driver name. Not required.
     *         'sizes' => [
     *             // sizes list
     *         ],
     *         'default' => 'medium',
     *     ],
     */
    protected $settings;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_IMAGE;
    }

    /**
     * @return bool
     */
    public function isAttachment(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param  mixed  $settings
     * @return AbstractField
     */
    public function setSettings($settings): AbstractField
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return array
     */
    public function extractSettings(): array
    {
        $settings = is_array($this->settings) ? $this->settings : config()->get((string) $this->settings, []);

        if ($this->isMultilingual()) {
            $settings['multilingual'] = true;
        }

        return $settings;
    }
}
