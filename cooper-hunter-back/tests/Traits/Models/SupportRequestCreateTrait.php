<?php


namespace Tests\Traits\Models;


use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Support\SupportRequest;
use App\Models\Support\SupportRequestMessage;
use App\Models\Technicians\Technician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;

trait SupportRequestCreateTrait
{

    use WithFaker;

    private int $countSupportRequests = 0;

    protected function countSupportRequests(int $times): self
    {
        $this->countSupportRequests = $times;

        return $this;
    }

    protected function createSupportRequest(
        ?Technician $technician = null,
        array $attributes = []
    ): SupportRequest|Collection {
        $technician = $technician ?? Technician::factory()
                ->certified()
                ->create();

        $factory = SupportRequest::factory()
            ->for(
                SupportRequestSubject::factory()
                    ->create(),
                'subject'
            );

        if ($this->countSupportRequests > 0) {
            $factory = $factory->count($this->countSupportRequests);
        }

        $request = $factory->create(
            array_merge(
                [
                    'technician_id' => $technician->id,
                ],
                $attributes
            )
        );

        if ($this->countSupportRequests > 0) {
            $request->each(
                fn(SupportRequest $item) => SupportRequestMessage::factory()
                    ->for($technician, 'sender')
                    ->for($item)
                    ->create()
            );
        } else {
            SupportRequestMessage::factory()
                ->for($technician, 'sender')
                ->for($request)
                ->create();
        }

        return $request;
    }
}
