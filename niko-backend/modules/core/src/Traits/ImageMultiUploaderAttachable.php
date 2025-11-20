<?php

namespace WezomCms\Core\Traits;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Traits\Model\ImageAttachable;

trait ImageMultiUploaderAttachable
{
    use ImageAttachable;

    /**
     * @return string
     */
    abstract public function getMainColumn(): string;

    /**
     * Determines the presence of the "name" field in the database.
     *
     * @return bool
     */
    public function hasNameField()
    {
        return true;
    }

    /**
     * Determines the presence of the "alt" & "title" fields in the database.
     *
     * @return bool
     */
    public function hasAltAndTitleFields()
    {
        return true;
    }

    /**
     * Return custom attributes for rename popup.
     *
     * @return array
     */
    public function customPopupFields(): array
    {
        return [];
    }

    /**
     * Determines whether to display the rename popup.
     *
     * @return bool
     */
    public function renamePopup(): bool
    {
        return $this->hasNameField() || $this->hasAltAndTitleFields() || count($this->customPopupFields()) > 0;
    }

    /**
     * @return FormRequest|null
     */
    public function renameFormRequest(): ?FormRequest
    {
        return null;
    }
}
