<?php


namespace App\Services\Saas\TextBlocks;


use App\Dto\Saas\TextBlocks\IndexDto;
use App\Dto\Saas\TextBlocks\TextBlockDto;
use App\Models\Admins\Admin;
use App\Models\Saas\TextBlock;
use App\Models\Users\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TextBlockService
{

    /**
     * @param IndexDto $indexDto
     * @return LengthAwarePaginator
     */
    public function getList(IndexDto $indexDto): LengthAwarePaginator
    {
        return TextBlock::filter($indexDto->getFilter())
            ->orderByDesc('created_at')
            ->paginate($indexDto->getPerPage(), ['*'], 'page', $indexDto->getPage());
    }

    /**
     * @param TextBlockDto $textBlockDto
     * @param TextBlock|null $textBlock
     * @return TextBlock
     * @throws \Throwable
     */
    public function saveTextBlock(TextBlockDto $textBlockDto, ?TextBlock $textBlock = null): TextBlock
    {
        if ($textBlock === null) {
            $textBlock = new TextBlock();
        }
        try {
            DB::beginTransaction();

            $textBlock->block = $textBlockDto->getBlock();
            $textBlock->group = $textBlockDto->getGroup();
            $textBlock->scope = $textBlockDto->getScopes();
            $textBlock->en = $textBlockDto->getEnText();
            $textBlock->es = $textBlockDto->getEsText();
            $textBlock->ru = $textBlockDto->getRuText();

            $textBlock->save();

            DB::commit();

            return $textBlock;
        } catch (Exception $exception) {
            Log::error($exception);

            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * @param Admin|User|null $user
     * @return Collection
     */
    public function getRenderTextBlocks($user): Collection
    {
        return TextBlock::filter(['scope' => [$this->getUserScope($user)]])->get();
    }

    private function getUserScope($user): string
    {
        if ($user === null) {
            return TextBlock::TB_SCOPE_CARRIER;
        }

        if ($user instanceof Admin) {
            return TextBlock::TB_SCOPE_BACKOFFICE;
        }
        /**@var User $user*/

        if ($user->isCarrier()) {
            return TextBlock::TB_SCOPE_CARRIER;
        }

        return TextBlock::TB_SCOPE_BROKER;

    }
}
