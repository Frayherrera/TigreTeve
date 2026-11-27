<?php

namespace App\Http\Controllers;

use App\Models\PushSuscripcion;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function suscribir(Request $request)
    {
        $datos = $request->validate([
            'endpoint' => ['required', 'url', 'starts_with:https://', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        PushSuscripcion::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $datos['endpoint'])],
            [
                'endpoint' => $datos['endpoint'],
                'p256dh' => $datos['keys']['p256dh'],
                'auth' => $datos['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function desuscribir(Request $request)
    {
        $datos = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
        ]);

        PushSuscripcion::where('endpoint_hash', hash('sha256', $datos['endpoint']))->delete();

        return response()->json(['ok' => true]);
    }
}
