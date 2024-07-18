<?php

namespace App\Http\Middleware;

use App\Models\tbl_clients;
use App\Models\tbl_user_login;
use Closure;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $response = $next($request);
        $currentRouteName = $request->route()->uri();
        $endpoint = strrchr($currentRouteName, '/');
        if (strtolower($endpoint)  != '/login') {
            if (!$request->header('Authorization'))
                return response('Unauthorized Access', 401);

            $token = Crypt::decryptString($request->header('Authorization'));
            $token = json_decode($token);


            if ($token->user_id == 'SU') {
                return $next($request);
            } else {
                $tokenExpiryDate = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $token->expiration);



                $diff = (new DateTime())->diff($tokenExpiryDate);

                // $totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                $isTokenExpired = ($diff->format('%R') == '-');


                if ($isTokenExpired) {
                    return abort(401, 'Login Expired');
                } else {
                    $login = tbl_user_login::where('login_clientcode', $token->client_id)->where('login_usercode', $token->user_id)->first();
                    if ($login == null) {
                        return abort(401, 'Invalid Token');
                    } else {
                        $tokenData = ['user_id' => $token->user_id, 'client_id' => $token->client_id, 'expiration' => now()->addMinutes(intval(env('TOKEN_EXPIRY_IN_MINUTES')))];
                        $login->login_token = Crypt::encryptString(json_encode($tokenData));
                        $login->login_timestamp = now()->format("Y-m-d H:i:s");
                        $login->save();

                        $response = $next($request);
                        $response->headers->set('_token', $login->login_token);

                        //set the liscence expiry flag
                        $client = tbl_clients::where('clt_code', $token->client_id)->first();
                        // var_dump($client->clt_serviceexpiry);
                        $liscExpiryDate = DateTime::createFromFormat('Y-m-d H:i:s', $client->clt_serviceexpiry);
                        // var_dump($liscExpiryDate);
                        $diff = (new DateTime())->diff($liscExpiryDate);
                        $isExpired = ($diff->format('%R') == '-');
                        $response->headers->set('_renewLisc', $isExpired ? 'Y' : 'N');
                        return $response;
                    }
                }



                // var_dump($diff->days);
                // var_dump($diff->format('%R'));
            }



            //$login = tbl_userlogin::where('login_code', )->first();


        }
        return $next($request);
    }
}
