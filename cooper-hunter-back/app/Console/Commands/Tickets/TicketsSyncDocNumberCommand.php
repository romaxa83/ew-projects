<?php

namespace App\Console\Commands\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\Models\Catalog\Tickets\Ticket;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function App\Console\Commands\Tickets\collect;

class TicketsSyncDocNumberCommand extends Command
{
    protected $signature = 'tickets:sync-doc-numbers';

    protected $description = 'Sync document numbers for tickets';

    /**
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     * @throws IOException
     */
    public function handle(): int
    {
        $file = database_path('files/AllTicketGuids.xlsx');

        $ticketsCount = Ticket::query()->whereNull('code')->count();

        if (0 === $ticketsCount) {
            $this->info('Nothing to update');

            return self::SUCCESS;
        }

        $data = fastexcel()
            ->withoutHeaders()
            ->import(
                $file,
                fn(array $row) => [
                    'guid' => $row[1],
                    'code' => $row[2],
                    'status' => $this->resolveStatus($row[3]),
                ],
            );

        $ticketCodes = collect($data)
            ->keyBy('guid');

        $bar = $this->output->createProgressBar($ticketsCount);

        $bar->start();

        foreach (Ticket::query()->whereNull('code')->cursor() as $ticket) {
            if (
                is_null(
                    $code = $ticketCodes->get($ticket->guid)
                )
            ) {
                $bar->advance();

                continue;
            }

            $ticket->code = $code['code'];

            if ($status = $code['status']) {
                $ticket->status = $status;
            }

            $ticket->save();

            $bar->advance();
        }

        $bar->finish();

        return self::SUCCESS;
    }

    private function resolveStatus(string $status): ?TicketStatusEnum
    {
        $status = Str::snake($status);

        if (TicketStatusEnum::hasValue($status)) {
            return TicketStatusEnum::fromValue($status);
        }

        return null;
    }
}
