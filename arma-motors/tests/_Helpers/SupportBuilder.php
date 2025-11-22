<?php

namespace Tests\_Helpers;

use App\Models\Support\Category;
use App\Models\Support\Message;
use App\Models\User\User;
use App\ValueObjects\Email;
use Database\Factories\Support\CategoryFactory;
use Database\Factories\Support\CategoryTranslationFactory;

class SupportBuilder
{
    private int $status = Message::STATUS_DRAFT;
    private null|int $categoryId = null;
    private null|int $userId = null;
    private string $email = 'support@admin.com';
    private null|string $text = null;

    private int $count = 1;
    private $asOne = false;

    private $activeCategory = true;
    private $onlyCategory = false;

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    // UserID
    public function getUserId()
    {
        if(null == $this->userId){
            $user = User::factory()->create();
            $this->setUserId($user->id);
        }

        return $this->userId;
    }
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return new Email($this->email);
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setStatus($status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // Count
    public function getCount()
    {
        return $this->count;
    }
    public function setCount($count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getActiveCategory(): bool
    {
        return $this->activeCategory;
    }

    public function noActiveCategory(): self
    {
        $this->activeCategory = false;

        return $this;
    }

    public function asOne(): self
    {
        $this->asOne = true;

        return $this;
    }

    public function asOneCategory(): self
    {
        $this->asOneCategory = true;

        return $this;
    }

    public function onlyCategory(): self
    {
        $this->onlyCategory = true;

        return $this;
    }

    public function create()
    {
        $category = $this->createCategory();

        if($this->onlyCategory){
            $this->clear();
            return $category;
        }

        $message = $this->save($category->id);

        $this->clear();

        return $message;
    }

    private function save($categoryId)
    {
        if($this->categoryId){
            $categoryId = $this->categoryId;
        }


        $data = [
            'user_id' => $this->getUserId(),
            'status' => $this->getStatus(),
            'email' => $this->getEmail(),
            'text' => $this->getText(),
            'category_id' => $categoryId,
        ];

        if($this->asOne){
            return Message::factory()->new($data)->create();
        }

        return Message::factory()->new($data)->count($this->getCount())->create();
    }

    public function createCategory(): Category
    {
        $model = CategoryFactory::new([
            'active' => $this->getActiveCategory(),
        ])->create();

        CategoryTranslationFactory::new(['category_id' => $model->id])->create(['lang' => 'ru']);
        CategoryTranslationFactory::new(['category_id' => $model->id])->create(['lang' => 'uk']);

        return $model;
    }

    private function clear()
    {
        //message
        $this->email = 'support@admin.com';
        $this->status = Message::STATUS_DRAFT;
        $this->count = 1;
        $this->asOne = false;
        $this->userId = null;
        $this->categoryId = null;
        $this->text = null;
        // category
        $this->activeCategory = true;
        $this->onlyCategory = false;
        $this->asOneCategory = false;
    }
}

