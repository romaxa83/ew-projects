<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\{JsonResponse, Request};

class DivisionController extends Controller
{

    public function clearCache()
    {
        $divisionData = Division::findOrFail(session('division.id'));
        session()->put('division.miscs', $divisionData->miscs);

        return response()
            ->json([
                'success' => true,
            ]);
    }

    public function index(Request $request, Division $division)
    {
        if ($request->ajax()) {
            $divisions = $division->query()
                ->with('paymentAccounts', 'authorize')
                ->get();
            return response()
                ->json([
                    'success' => true,
                    'records' => $divisions,
                ]);
        }

        return view('layouts.render.with-container', [
            'component' => 'settings-divisions',
            'title' => 'Manage Divisions',
            'h2' => 'Manage Divisions',
        ]);
    }

    public function store(Request $request, Division $_record): JsonResponse
    {
        $validated = $request->validate([
            'record.id' => 'nullable|integer|exists:divisions,id',
            'record.name' => 'required|string|max:80',
            'record.short' => 'required|string|max:8',
            'record.title' => 'required|string|max:80',
            'record.miscs.tz' => 'required|string|max:35',
            'record.miscs.phone' => 'nullable|string|max:30',
            'record.miscs.domain' => 'nullable|string|max:50',
            'record.miscs.ringostat_project_id' => 'nullable|string',
            'record.miscs.ringostat_auth_key' => 'nullable|string',
            'record.miscs.zadarma_pbx_id' => 'nullable|string',
            'record.miscs.zadarma_api_key' => 'nullable|string',
            'record.miscs.zadarma_api_secret' => 'nullable|string',
            'record.miscs.zadarma_pbx_caller_id' => 'nullable|string',
            'record.miscs.mailchimp_api_key' => 'nullable|string',
            'record.miscs.mandrill_from_name' => 'nullable|string',
            'record.miscs.mandrill_templates_label' => 'nullable|string',
            'record.miscs.mandrill_from_email' => 'nullable|email',
            'record.miscs.local_rates_season' => 'nullable|string|in:winter,summer',
            'record.miscs.local_rates_summer_from' => 'nullable|date_format:m-d',
            'record.miscs.local_rates_summer_to' => 'nullable|date_format:m-d',
            'record.miscs.hellosign_api_key' => 'nullable|string',
            'record.miscs.hellosign_api_cc_email' => 'nullable|email',
            'record.miscs.hellosign_interstate_template_id' => 'nullable|string',
            'record.payment_accounts.*.id' => 'nullable|integer|exists:payments_accounts,id',
            'record.payment_accounts.*.division_id' => 'nullable|integer|exists:divisions,id',
            'record.payment_accounts.*.title' => 'nullable|string|max:80',
            'record.payment_accounts.*.is_active' => 'nullable|boolean',
            'record.payment_accounts.*.sort' => 'integer',
            'record.authorize.active' => 'nullable|boolean',
            'record.authorize.title' => 'nullable|string|max:80',
            'record.authorize.payment_account_id' => 'nullable|integer|exists:payments_accounts,id',
            'record.authorize.login' => 'nullable|string|max:32',
            'record.authorize.transactionKey' => 'nullable|string|max:64',
        ]);

        $record = $_record->updateOrCreate([
            'id' => $validated['record']['id']
        ], $validated['record']);

        $miscs = $record->miscs ?? [];
        $miscs['tz'] = $validated['record']['miscs']['tz'] ?? null;
        $miscs['phone'] = $validated['record']['miscs']['phone'] ?? null;
        $miscs['zadarma_pbx_id'] = $validated['record']['miscs']['zadarma_pbx_id'] ?? null;
        $miscs['zadarma_api_key'] = $validated['record']['miscs']['zadarma_api_key'] ?? null;
        $miscs['zadarma_api_secret'] = $validated['record']['miscs']['zadarma_api_secret'] ?? null;
        $miscs['zadarma_pbx_caller_id'] = $validated['record']['miscs']['zadarma_pbx_caller_id'] ?? null;
        $miscs['mailchimp_api_key'] = $validated['record']['miscs']['mailchimp_api_key'] ?? null;
        $miscs['mandrill_from_name'] = $validated['record']['miscs']['mandrill_from_name'] ?? null;
        $miscs['mandrill_templates_label'] = $validated['record']['miscs']['mandrill_templates_label'] ?? null;
        $miscs['mandrill_from_email'] = $validated['record']['miscs']['mandrill_from_email'] ?? null;
        $miscs['local_rates_season'] = $validated['record']['miscs']['local_rates_season'] ?? null;
        $miscs['local_rates_summer_from'] = $validated['record']['miscs']['local_rates_summer_from'] ?? null;
        $miscs['local_rates_summer_to'] = $validated['record']['miscs']['local_rates_summer_to'] ?? null;
        $miscs['hellosign_api_key'] = $validated['record']['miscs']['hellosign_api_key'] ?? null;
        $miscs['hellosign_api_cc_email'] = $validated['record']['miscs']['hellosign_api_cc_email'] ?? null;
        $miscs['hellosign_interstate_template_id'] = $validated['record']['miscs']['hellosign_interstate_template_id'] ?? null;
        $miscs['ringostat_project_id'] = $validated['record']['miscs']['ringostat_project_id'] ?? null;
        $miscs['ringostat_auth_key'] = $validated['record']['miscs']['ringostat_auth_key'] ?? null;


        $miscs['domain'] = null;
        if (!empty($validated['record']['miscs']['domain'])) {
            $miscs['domain'] = parse_url($validated['record']['miscs']['domain'],
                PHP_URL_HOST) ?? $validated['record']['miscs']['domain'];
        }

        $record->miscs = $miscs;
        $record->save();

        $paymentAccounts = collect($validated['record']['payment_accounts'])
            ->filter(function ($item) {
                // Allow with name or ID
                return $item['id'] || !empty($item['title']);
            })
            ->all();
        $record->updateRelations('paymentAccounts', $paymentAccounts ?? []);


        if (!empty($validated['record']['authorize']['login']) && !empty($validated['record']['authorize']['transactionKey'])) {
            $record->authorize()
                ->updateOrCreate(
                    [
                        'division_id' => $validated['record']['id'],
                    ],
                    $validated['record']['authorize']);
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }
}
