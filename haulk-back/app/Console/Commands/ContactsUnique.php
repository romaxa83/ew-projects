<?php

namespace App\Console\Commands;

use App\Models\Contacts\Contact;
use DB;
use Exception;
use Illuminate\Console\Command;

class ContactsUnique extends Command
{
    private string $table_name;
    private string $company_id;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:unique';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes contact duplicates';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->table_name = Contact::TABLE_NAME;
        $this->company_id = config('haulk.id');

        // TODO: add company filtering

        $this->info('Starting..');

        $this->info('Removing duplicates..');
        $this->removeDuplicates();
        $this->info('Done.');

        $this->info('Removing private contacts..');
        $this->removePrivateContacts();
        $this->info('Done.');

        $this->info('Removing contacts with nonsense name..');
        $this->removeNonsenseNameContacts();
        $this->info('Done.');

        $this->info('Removing contacts with *CONTACT DISPATCHER* name..');
        $this->removeContactDispatcherContacts();
        $this->info('Done.');

        $this->info('Merging similar contacts..');
        $this->mergeSimilarContacts();
        $this->info('Done.');

        $this->info('The End.');
    }

    private function removePrivateContacts(): void
    {
        $private_type_id = Contact::CONTACT_TYPE_PRIVATE;

        DB::statement("
            DELETE
            FROM {$this->table_name}
            WHERE {$this->table_name}.type_id = {$private_type_id}
            AND {$this->table_name}.carrier_id = {$this->company_id}
        ");
    }

    private function removeNonsenseNameContacts(): void
    {
        DB::statement("
            DELETE
            FROM {$this->table_name}
            WHERE {$this->table_name}.id IN (
                SELECT m.id
                FROM (
                    SELECT {$this->table_name}.id, regexp_matches({$this->table_name}.full_name, '^[^a-z\ ]+$', 'i')
                    FROM {$this->table_name}
                    WHERE {$this->table_name}.carrier_id = {$this->company_id}
                ) m
            )
        ");
    }

    private function removeContactDispatcherContacts(): void
    {
        DB::statement("
            DELETE
            FROM {$this->table_name}
            WHERE {$this->table_name}.full_name ILIKE '*CONTACT DISPATCHER*'
            AND {$this->table_name}.carrier_id = {$this->company_id}
        ");
    }

    private function removeDuplicates(): void
    {
        // remove duplicates
        // case insensitive compare
        // exclude id, create/edit/delete dates, timezone, contact type
        DB::statement("
            DELETE
            FROM {$this->table_name}
            WHERE {$this->table_name}.id NOT IN (
                SELECT u.uniq_id
                FROM
                (
                    SELECT
                        MIN(cnt.id) uniq_id,
                        MD5(
                            LOWER(
                                CAST(
                                    (
                                        cnt.full_name,
                                        cnt.address,
                                        cnt.city,
                                        cnt.zip,
                                        cnt.phone_name,
                                        cnt.email,
                                        cnt.fax,
                                        cnt.phones,
                                        cnt.comment,
                                        cnt.phone,
                                        cnt.phone_extension,
                                        cnt.state_id,
                                        cnt.working_hours
                                    ) AS TEXT
                                )
                            )
                        ) h
                    FROM {$this->table_name} cnt
                    WHERE cnt.carrier_id = {$this->company_id}
                    GROUP BY h
                    ORDER BY h
                ) u
            )
        ");
    }

    private function mergeSimilarContacts()
    {
        $contacts = DB::select("
            SELECT
                cnt.id contact_id,
                MD5(
                    LOWER(
                        CAST(
                            (
                                cnt.full_name,
                                cnt.address,
                                cnt.city,
                                cnt.state_id,
                                cnt.zip
                            ) AS TEXT
                        )
                    )
                ) contact_hash
            FROM {$this->table_name} cnt
            WHERE cnt.carrier_id = {$this->company_id}
            ORDER BY contact_hash
        ");

        if ($contacts) {
            $contacts = collect($contacts);
            $hashes = $contacts->pluck('contact_hash')->unique();

            foreach ($hashes as $hash) {
                $ids = $contacts->where('contact_hash', $hash)->pluck('contact_id');

                if ($ids->count() > 1) {
                    $contactsBatch = Contact::withoutGlobalScopes()
                        ->findMany($ids->all());

                    if ($contactsBatch->count() > 1) {
                        try {
                            DB::beginTransaction();

                            $phones = $contactsBatch->map(
                                function (Contact $el) {
                                    return [
                                        'name' => '',
                                        'number' => $el->phone,
                                        'extension' => '',
                                        'notes' => '',
                                    ];
                                }
                            )->filter(
                                function ($el) {
                                    return isset($el['number']) && $el['number'];
                                }
                            );

                            $fullContact = $contactsBatch->pop();

                            if ($phones->count()) {
                                $firstPhone = $phones->pop();
                                $fullContact->phone = $firstPhone['number'];
                            }

                            $phones = $phones->concat($contactsBatch->pluck('phones')->filter());

                            if ($fullContact->phones) {
                                $phones = $phones->concat($fullContact->phones);
                            }

                            $fullContact->phones = $phones->values();

                            $fullContact->email = $contactsBatch->pluck('email')->filter()->first();
                            $fullContact->phone_name = $contactsBatch->pluck('phone_name')->filter()->first();
                            $fullContact->fax = $contactsBatch->pluck('fax')->filter()->first();
                            $fullContact->comment = $contactsBatch->pluck('comment')->filter()->first();
                            $fullContact->phone_extension = $contactsBatch->pluck('phone_extension')->filter()->first();
                            $fullContact->working_hours = $contactsBatch->pluck('working_hours')->filter()->first();

                            $fullContact->save();

                            Contact::withoutGlobalScopes()
                                ->whereIn('id', $contactsBatch->pluck('id')->all())
                                ->forceDelete();

                            DB::commit();
                        } catch (Exception $e) {
                            DB::rollBack();
                        }
                    }
                }
            }
        }
    }
}
