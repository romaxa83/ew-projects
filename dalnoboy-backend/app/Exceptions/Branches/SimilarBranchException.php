<?php


namespace App\Exceptions\Branches;


use Core\Exceptions\TranslatedException;

class SimilarBranchException extends TranslatedException
{
    public function __construct(string $branchName)
    {
        parent::__construct(
            trans('validation.custom.branches.similar_branch', ['branch_name' => $branchName])
        );
    }
}
