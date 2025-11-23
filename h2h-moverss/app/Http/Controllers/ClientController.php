<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\{MergeDuplicatesRequest, ProfileSave};
use App\Models\{Client, Order};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\{JsonResponse, Request};
use Auth, Exception, DB;

/**
 * Work with client.
 */
class ClientController extends Controller
{
    /**
     * @var Client Client Model
     */
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function mergeDuplicatesAjax(MergeDuplicatesRequest $request)
    {
        $response = [
            'success' => false
        ];
        try {
            $mergeBy = $request->get('mergeBy');
            $Clients = Client::clientCard()->whereIn('id', $request->get('duplicates'))->get();
            $mainClient = Client::clientCard()->find($mergeBy['name']['client_id']);
            unset($mergeBy['name']);
            $tags = [];
            if (!empty($mergeBy['tags'])) {
                $tags = call_user_func_array('array_merge', array_map(function ($v) {
                    return $v['value'];
                }, $mergeBy['tags']));
                $tags = array_map(function ($v) {
                    return ['key' => $v['id'], 'value' => $v['title']];
                }, $tags);
            }
            (new Client\Tag())->tagsSaver($mainClient, $tags);
            unset($mergeBy['tags']);
//            dd($mergeBy);
            foreach ($mergeBy as $relation => $relationValue) {
                // remove equal values
//                if(!empty($relationValue)) {
//                    $usedValues = [];
//                }
                $relationIds = !empty($relationValue) ? array_column($relationValue, 'id') : [];
                $DeleteCandidats = $mainClient->$relation()->whereNotIn('id', $relationIds)->get();
                if ($DeleteCandidats->isNotEmpty()) {
                    foreach ($DeleteCandidats as $DeleteCandidat) {
                        $DeleteCandidat->delete();
                    }
                }
                if ($mainClient->isDirty())
                    $mainClient->save();

                $mainClient->refresh();
                // assign from another
                foreach ($Clients as $Client) {
                    if ($Client->id != $mainClient->id && $Client->$relation->isNotEmpty()) {
                        foreach ($Client->$relation as $foreignRelation) {
                            if (in_array($foreignRelation->id, $relationIds)) {
                                //check if this relation value already exists
                                $contains = false;
                                if ($mainClient->$relation->isNotEmpty()) {
                                    $contains = $mainClient->$relation->contains(function ($v) use ($foreignRelation) {
                                        return $foreignRelation->value == $v->value;
                                    });
                                }
                                if (!$contains) {
                                    $foreignRelation->client()->associate($mainClient);
                                    $foreignRelation->save();
                                } else {
                                    $foreignRelation->delete();
                                }
                            } else {
                                $foreignRelation->delete();
                            }
                        }
                    }
                }
            }
            if ($mainClient->isDirty())
                $mainClient->save();
//            dd(Client::clientCard()->find($mainClient->id)->toArray());
            // reassign all orders ot new Client
            foreach ($Clients as $Client) {
                if ($Client->id != $mainClient->id) {
                    Order::where('client_id', $Client->id)->update(['client_id' => $mainClient->id]);
                    $Client->delete();
                }
            }

            DB::commit();
            return $this->findDuplicatesAjax();
        } catch (ValidationException $e) {
            DB::rollBack();
            $response['errors'] = $e->errors();

        } catch (Exception $e) {
            DB::rollBack();
            $response['msg'] = $e->getMessage();
        }

        return response()
            ->json($response);

    }

    public function findDuplicatesAjax()
    {
        $skip = request()->get('skip');
        $phoneDuplicates = DB::table(app(Client\Phone::class)->getTable())
            ->select('value', DB::raw('COUNT(DISTINCT client_id) as count'))->groupBy('value')
            ->when(!empty($skip['phones']), function ($q) use ($skip) {
                $q->whereNotIn('value', $skip['phones']);
            })
            ->whereIn('client_id', function ($q) {
                $q->select('client_id')->from(app(Order::class)->getTable())->whereIn('division_id', session()->get('division.allowed'));
            })
            ->whereNull('deleted_at')
            ->havingRaw('COUNT(DISTINCT client_id) > 1')
            ->get();

        $emailDuplicates = DB::table(app(Client\Email::class)->getTable())
            ->select('value', DB::raw('COUNT(DISTINCT client_id) as count'))->groupBy('value')
            ->when(!empty($skip['emails']), function ($q) use ($skip) {
                $q->whereNotIn('value', $skip['emails']);
            })
            ->whereIn('client_id', function ($q) {
                $q->select('client_id')->from(app(Order::class)->getTable())->whereIn('division_id', session()->get('division.allowed'));
            })
            ->whereNull('deleted_at')
            ->havingRaw('COUNT(DISTINCT client_id) > 1')
            ->get();

        // для разнообразия пойдем с конца.
        $relation = '';
        $duplicateBy = '';
        if ($phoneDuplicates->isNotEmpty()) {
            $relation = 'phones';
            $duplicateBy = $phoneDuplicates->last()->value;
            $Duplicates = Client::clientCard()->whereHas('orders', function (Builder $q) {
                $q->whereIn('division_id', session()->get('division.allowed'));
            })->whereHas('phones', function (Builder $q) use ($duplicateBy) {
                $q->where('value', $duplicateBy);
            })->get();
        } elseif ($emailDuplicates->isNotEmpty()) {
            $relation = 'emails';
            $duplicateBy = $emailDuplicates->last()->value;
            $Duplicates = Client::clientCard()->whereHas('orders', function (Builder $q) {
                $q->whereIn('division_id', session()->get('division.allowed'));
            })->whereHas('emails', function (Builder $q) use ($duplicateBy) {
                $q->where('value', $duplicateBy);
            })->get();

        }

        return response()
            ->json([
                'success' => true,
                'data' => [
                    'duplicateBy' => ['value' => $duplicateBy, 'relation' => $relation],
                    'skip' => $skip,
                    'duplicates' => isset($Duplicates) ? $Duplicates : [],
                    'phoneDuplicates' => $phoneDuplicates->count(),
                    'emailDuplicates' => $emailDuplicates->count(),
                    'totalDuplicates' => $emailDuplicates->count() + $phoneDuplicates->count(),
                ]
            ]);
    }

    public function clientsPage(): Renderable
    {
        abort_if(!Auth::user()->isRoutePatternAllowed('clients.'), 403);

        return view('layouts.clients.body', [
            'clientTags' => Client\Tag::all(['id', 'title', 'color', 'icon']),
        ]);
    }

    public function recordsAjaxDT(Request $request): JsonResponse
    {
        $recordsCollection = $this->client->getClientsDT($request);

        return response()
            ->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $recordsCollection->total(),
                'recordsFiltered' => $recordsCollection->total(),
                'data' => $recordsCollection->getCollection(),
            ]);
    }

    /**
     * Get ALL customer data + Types of numbers and messengers.
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $record = $this->client->getClient((int)$request->get('id'));

        return response()
            ->json([
                'success' => true,
                'record' => $record,
                'types' => [
                    'phones' => config('app.phone_types'),
                    'messengers' => Client\MessengerType::all(['id', 'title', 'icon'])->keyBy('id'),
                    'tags' => Client\Tag::all(['id', 'title', 'icon', 'color', 'sort'])->keyBy('id'),
                ]
            ]);
    }

    /**
     * Save ALL customer data: Client, phones, email, messengers, notes.
     * @param ProfileSave $request
     * @return JsonResponse
     * @throws \Throwable
     *
     * test @see \Tests\Feature\Client\ProfileSaveTest
     */
    public function profileSave(ProfileSave $request): JsonResponse
    {
        $validated = $request->validated();

        $client = $this->client->getClient($validated['id']);
        if (!$client) {
            throw new Exception('Client not exists. Error: 22');
        }

        $client->name = $validated['name'];
        $client->lname = $validated['lname'];

        $changed = $client->isDirty() ? 1 : 0;
        $changed += (new Client\Tag())->tagsSaver($client, $validated['selectedTags'] ?? []);

        try {
            DB::transaction(function () use ($client, $validated, &$changed) {
                $changed += $client->updateRelations('phones', $validated['phones'] ?? []);
                $changed += $client->updateRelations('emails', $validated['emails'] ?? []);
                $changed += $client->updateRelations('messengers', $validated['messengers'] ?? []);
                $changed += $client->updateRelations('notes', $validated['notes'] ?? []);

                if ($client->isDirty()) {
                    $client->save();
                }
            });
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'msg' => $changed ? 'Client was updated' : 'Nothing has been changed',
                'record' => $changed ? $client->refresh() : $client
            ]);
    }

    /**
     * Autocomplete client, search by: ID, Name, Lname, Email, Phone.
     * @param Request $request
     * @param int $pageLimit
     * @return JsonResponse
     */
    public function ajaxProfileAutocomplete(Request $request, Client $client): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'data' => $client->autocomplete($request),
            ]);
    }

}
