<?php

namespace App\Services\Faq;

use App\Dto\Faq\Questions\AnswerQuestionDto;
use App\Dto\Faq\Questions\AskAQuestionDto;
use App\Enums\Faq\Questions\QuestionStatusEnum;
use App\Models\Admins\Admin;
use App\Models\Faq\Question;
use App\Notifications\Faq\Questions\AnswerTheQuestionNotification;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Notification;

class QuestionService
{
    public function ask(AskAQuestionDto $dto): Question
    {
        return $this->store(new Question(), $dto);
    }

    protected function store(Question $question, AskAQuestionDto $dto): Question
    {
        $this->fillQuestion($question, $dto);

        $question->save();

        return $question;
    }

    protected function fillQuestion(Question $question, AskAQuestionDto $dto): void
    {
        $question->name = $dto->getName();
        $question->email = $dto->getEmail();
        $question->question = $dto->getQuestion();
    }

    public function answer(Admin $admin, Question $question, AnswerQuestionDto $dto): Question
    {
        $this->fillAnswer($admin, $question, $dto);

        $question->save();

        Notification::route('mail', $question->getEmailString())
            ->notify(
                (new AnswerTheQuestionNotification($question))
                    ->locale(app()->getLocale())
            );

        return $question;
    }

    protected function fillAnswer(Admin $admin, Question $question, AnswerQuestionDto $dto): void
    {
        $question->admin_id = $admin->id;
        $question->answer = $dto->getAnswer();
        $question->status = QuestionStatusEnum::ANSWERED;
    }

    public function delete(Question $question): bool
    {
        if ($question->isAnswered()) {
            throw new TranslatedException('Cannot delete answered Question');
        }

        return $question->delete();
    }
}
