<?php

namespace App\Http\Controllers;

use App\Models\{Client, Division, Order};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse, Request};
use Auth;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     */
    public function index(Request $request)
    {
        if ( \Auth::user()->isPartner()) {
            return redirect()->route('company.trucks.records');
        }

        $client = Client::with([
            'phones', 'emails', 'messengers', 'messengers.type:id,title', 'notes', 'tags'
        ])->first();
        $order = Order::with([
            'client', 'extended', 'waypoints', 'notes', 'services', 'materials',
            'estimate', 'estimate.local'
        ])->first();

        if ($request->has('exception')) {
            throw new \Exception('Test exception: '.$request->get('exception'));
        }

        return view('layouts.home.body', [
            'client' => $client,
            'order' => $order,
        ]);
    }

    /**
     * Changing division and save to session and DB.
     * @param Request $request
     * @return JsonResponse
     */
    public function chooseDivision(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:divisions,id',
        ]);

        $resp = [
            'success' => false,
        ];
        if (in_array($validated['id'], $request->session()->get('division.allowed'), true)) {
            $request->session()->put('division.id', $validated['id']);
            $divisionData = Division::findOrFail($validated['id']);
            $request->session()->put('division.miscs', $divisionData->miscs);

            if ($request->session()->get('division.is_multiple')) {
                $miscs = (array)Auth::user()->miscs;

                if (!isset($miscs['division']['id']) || $miscs['division']['id'] !== $validated['id']) {
                    $miscs['division']['id'] = $validated['id'];

                    Auth::user()->miscs = $miscs;
                    Auth::user()->save();
                }
            }

            $resp['success'] = true;
        } else {
            $resp['msg'] = 'Not allowed!';
        }

        return response()
            ->json($resp);
    }

}
