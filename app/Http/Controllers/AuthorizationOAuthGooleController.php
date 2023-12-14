<?php

namespace App\Http\Controllers;

use Google\Service\YouTube as ServiceYouTube;
use Google\Service\YouTube\Subscription;
use Google_Client;
use Illuminate\Http\Request;

class AuthorizationOAuthGooleController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function listSuscription()
  {

    // echo "Conectado";

    $client = new Google_Client();
    // $client->setAuthConfig(__DIR__ . '/oauth_client.json');
    // $client->addScope(ServiceYouTube::YOUTUBE_READONLY);


    $token = 'ya29.a0AfB_byAtkzvxEZuXf1CcIEXGjNtSTdcpj8aG1x7ntMayMg4lyrgqKZNprTO80_o_f5KmjaFmCunHwa-Ch3hbf6xB-2cdua5OKh3yhvRFdZVLraUuWiVBJKInlusooI3Siyu3JihTqDj5EcTz3Zs-AUP3PfgHzmh68AaCgYKAdcSARMSFQHGX2MiW17P0o0goWwmKeeI0MLXuQ0169';
    //  ticket = $client->verifyIdToken();
    $client->setAccessToken($token);
    $youtube = new ServiceYouTube($client);

    $iteration = 0;
    $tokenPage = '';
    $items = [];
    $historyTokenPage = [];
    do {
      $subs = $youtube->subscriptions->listSubscriptions(['snippet', 'contentDetails'], ['mine' => true, 'maxResults' => 50, 'pageToken' => $tokenPage]);
      array_push($items, ...$subs->getItems());
      $tokenPage = $subs->getNextPageToken();
      // array_push($historyTokenPage, $tokenPage);

    } while ($tokenPage !== null);

    $revocado = $client->revokeToken($token);
    dd($items);

    /**
     * Medidas a implementar para mejorar la seguridad
     * Seguridad del Token de Acceso:
     * Considera implementar medidas adicionales para proteger el token de acceso durante su transmisión y almacenamiento. Por ejemplo, puedes cifrar o firmar el token para garantizar su integridad y autenticidad.
     * Protección contra Ataques CSRF:
     * Implementa protección contra ataques de falsificación de solicitudes entre sitios (CSRF) en tu aplicación. Esto es crucial para prevenir solicitudes no deseadas que podrían originarse desde sitios maliciosos.
     * Validación del Token de Acceso:
     * En el backend, realiza una validación adecuada del token de acceso recibido. Verifica la firma y la integridad del token antes de utilizarlo para realizar solicitudes a la API de YouTube.
     */

    // $client->setApplicationName('pyGestorRecursos');
    // $client->setDeveloperKey('AIzaSyA5bv7LZH0sdh4GFtq6rb7ayEFNun65sSc');


    // Aqui añadir el token obtenido desde request
    // "?state=pass-through+value&code=4%2F0AfJohXmnRF5sWri_U23CyWz8x9Vt3Gqr4rMWMMUp-Vj1WyO8AkrvGw0dAHCA26A3SH6vhA&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly"


    // $token = $client->fetchAccessTokenWithAuthCode('4%2F0AfJohXmnRF5sWri_U23CyWz8x9Vt3Gqr4rMWMMUp-Vj1WyO8AkrvGw0dAHCA26A3SH6vhA');
    // dd($token);
    // $client->setAccessToken($token);
    // $youtube = new ServiceYouTube($client);
    // $iteration = 0;
    // $tokenPage = '';
    // $items = [];

    // // do {
    // $subs = $youtube->subscriptions->listSubscriptions(['snippet', 'contentDetails'], ['mine' => true, 'maxResults' => 50]);
    // $tokenPage = $subs->getNextPageToken();

    // $items = array_push($subs->getItems());
    // $iteration++;
    // // } while ($tokenPage !== '' || $iteration < 21);

    // dd(
    //   "Total Iteracion {$iteration}",
    //   $items
    // );
  }
}
