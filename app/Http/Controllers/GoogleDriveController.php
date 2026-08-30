<?php

namespace App\Http\Controllers;

use App\Models\Subject; // Importar el modelo Subject
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    public function connect()
    {
        $client = new Client;
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));
        $client->setScopes([
            Drive::DRIVE_FILE,
            Drive::DRIVE_READONLY,
        ]);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        return redirect($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $client = new Client;
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));

        try {
            $token = $client->fetchAccessTokenWithAuthCode($request->code);
            $request->session()->put('google_drive_token', $token);
        } catch (\Exception $e) {
            Log::error('Error al obtener el token de Google: '.$e->getMessage());

            return redirect('/dashboard')->with('error', 'Hubo un error al conectar con Google Drive.');
        }

        return redirect()->route('google.picker')->with('success', 'Google Drive conectado correctamente!');
    }

    public function picker(Request $request)
    {
        $token = $request->session()->get('google_drive_token');

        if (! $token) {
            return redirect()->route('google.connect')->with('error', 'Tu sesión de Google ha expirado. Por favor, conéctate de nuevo.');
        }

        $client = new Client;
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            if (isset($token['refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                $request->session()->put('google_drive_token', $client->getAccessToken());
            } else {
                return redirect()->route('google.connect')->with('error', 'Tu sesión de Google ha expirado y no se pudo refrescar. Por favor, conéctate de nuevo.');
            }
        }

        $accessToken = $client->getAccessToken();
        $subjects = Subject::all(); // Obtener todas las áreas académicas

        return view('google-drive.picker', [
            'accessToken' => $accessToken['access_token'],
            'developerKey' => config('google.developer_key'),
            'appId' => config('google.client_id'),
            'subjects' => $subjects, // Pasar las áreas a la vista
        ]);
    }
}
