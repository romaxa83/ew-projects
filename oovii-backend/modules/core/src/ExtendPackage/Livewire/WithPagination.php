<?php

namespace WezomCms\Core\ExtendPackage\Livewire;

trait WithPagination
{
    use \Livewire\WithPagination;

    /**
     * @param $value
     * @param $request
     */
    public function hydratePage($value, $request)
    {
        $this->setPage($value);
    }

    /**
     * @return int
     */
    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        $this->setPage(request()->query('page', $this->page));

        return $this->page;
    }

    /**
     * @return string
     */
    public function paginationView()
    {
        return 'cms-ui::pagination.livewire';
    }

    /**
     * @return string
     */
    public function paginationSimpleView()
    {
        return 'cms-ui::pagination.simple-livewire';
    }

    /**
     * @param $page
     */
    protected function setPage($page)
    {
        $this->page = filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1 ? (int) $page : 1;
    }
}
