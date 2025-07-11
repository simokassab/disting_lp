<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use App\Models\ProjectSource;
use App\Models\Redirect;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

class HeController extends Controller
{
    private array $config = [
        'serviceIdAr' => '964770007',
        'serviceIdKU' => '964770008',
        'spId' => '964773003',
        'shortcode' => '2294',
        'channelId' => '22718',
        'opSPID' => '13160',
    ];
    // Common headers where mobile carriers might send MSISDN
    private $msisdnHeaders = [
        'X-MSISDN',
        'MSISDN',
        'X-UP-CALLING-LINE-ID',
        'X-HTS-CLID',
        'MSISDN_NUMBER',
        'X-ORIGINAL-MSISDN',
        'X-TATA-MSISDN'
    ];

    private $digitalAdsBaseUrl = 'http://callback.digitalabs.ae:9090/actions/';
    public function index(Request $request)
    {
        if (!$request->has('source')) {
            return redirect('failure?errors=source_not_found');
        }
        $source = ProjectSource::where('uuid', $request->input('source'))->first();
        if (!$source) {
            return redirect('failure?errors=source_not_found');
        }
        $msisdn = $this->getMsisdnFromHeaders($request);
//        $msisdn = "9647701394275";
        if (!$msisdn) {

//            if ($request->has('testmode') && $request->input('testmode') == '1') {
//                Redirect::create([
//                    'from' => $request->fullUrl(),
//                    'to' => 'pin flow',
//                    'user_ip' => $request->ip(),
//                ]);
////            redirect to pin flow with all the params in the url
//                return redirect('pin?' . $request->getQueryString());
//            }
//            else {
                Redirect::create([
                    'from' => $request->fullUrl(),
                    'to' => 'PIN FLOW',
                    'user_ip' => $request->ip(),
                ]);
            return redirect('pin?' . $request->getQueryString());
//            }
        }
        if ($msisdn) {
            Session::put('msisdn', $msisdn);
//            try {
                // Initialize tracking data without saving
                $trackingData = [
                    'project_source_id' => $source->id,
                    'source' => 'HE',
                    'msisdn' => $msisdn,
                    'click_id' => Tracking::identifyClickId($request),
                    'first_click' => false,
                    'second_click' => false,
                    'user_ip' => $request->ip(),
                    'pixel_id' => Tracking::getPixelId($request),
                    'campaign_id' => $request->input('campaign_id'),
                    'campaign_name' => $request->input('campaign_name'),
                    'ad_set_id' => $request->input('ad_set_id'),
                    'ad_set_name' => $request->input('ad_set_name'),
                    'ad_id' => $request->input('ad_id'),
                    'ad_name' => $request->input('ad_name'),
                    'utm_parameters' => Tracking::collectUtmParameters($request),
                    'additional_parameters' => $request->except([
                        'msisdn', 'campaign_id', 'campaign_name', 'source', 'pixel_id',
                        'ad_set_id', 'ad_set_name', 'ad_id', 'ad_name',
                        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'
                    ])
                ];

                session(['tracking_data' => $trackingData]);

                Tracking::updateOrCreate(
                    [
                        'msisdn' => $msisdn,
                        'click_id' => $trackingData['click_id'],
                        'project_source_id' => $source->id
                    ],
                    $trackingData
                );

                // Return the view
                return view('index', ['trackingData' => $trackingData]);

//            } catch (\Exception $e) {
//
//                $errors = $e->getMessage();
////                i need to redirect to failure view with the error message in the params in the URL /failure?errors=$errors
//                return redirect('failure?errors=' . $errors);
//            }
        }
        return view('index');
    }

    public function storeTracking(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'msisdn' => 'required|string',
//                'user_headers' => 'required'
            ]);

            // Get the tracking data from session
            $trackingData = session('tracking_data', []);

            // Update with the click-specific data
            $clickId = $request->input('click_id');
            $trackingData['msisdn'] = $request->input('msisdn');

            // Create the tracking record
            $tracking = Tracking::where('msisdn', $request->input('msisdn'))
                ->where('click_id', $clickId)
                ->first();
            $tracking->first_click = true;
            $tracking->save();
            return response()->json([
                'success' => true,
                'tracking_id' => $tracking->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing tracking data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to store tracking data'
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        $source = ProjectSource::where('uuid', $request->input('source'))->first();
        if (!$source) {
            return redirect('failure?errors=source_not_found');
        }

        $tracking = Tracking::where('click_id', $request->click_id)
            ->where('msisdn', $request->msisdn)
            ->where('project_source_id', $source->id)
            ->first();
        if ($tracking) {
            $tracking->first_click = true;
            $tracking->second_page_visit = true;
            $tracking->save();
        }
        return view('verify');
    }

    public function success(Request $request)
    {
        $msisdn = $request->msisdn;
        $anti_fraud_click_id = $request->ClickID;
        $tracking = Tracking::where('msisdn', $msisdn)->where('anti_fraud_click_id', $anti_fraud_click_id)->first();
        if ($tracking) {
            $tracking->success = true;
            $tracking->failure = false;
            $tracking->save();
            IntegrationLog::updateOrCreate(
                [
                    'provider' => 'SDP',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'response',
                    'status' => 'success',
                ],
                [
                    'payload' => [
                        'body' => $request->all()
                    ],
                    'url' => '',
                    'error_message' => null,

                ]);

            $query_params = [
                'clickId' => $anti_fraud_click_id,
                'campaignId' => $tracking->projectSource->campaign_id,
                'userIP' => $tracking->user_ip,
            ];
            $integration = IntegrationLog::firstOrCreate([
                'provider' => 'digital_ads',
                'tracking_id' => $tracking->id,
                'event_type' => 'request',
                'status' => 'success',
                'url' => $this->digitalAdsBaseUrl . 'SuccessCallBack',
            ], [
                'payload' => $query_params,
            ]);
            if ($integration->wasRecentlyCreated) {
                $response_digital_ads = Http::get($this->digitalAdsBaseUrl . 'SuccessCallBack?' . http_build_query($query_params));

                if ($response_digital_ads->successful()) {
                    $integration->status = 'success';
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
                else {
                    $integration->status = 'failed';
                    $integration->error_message = $response_digital_ads->body();
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
            }

        }
        return view('success');
    }

    public function failure(Request $request)
    {
        $msisdn = $request->msisdn;
        $anti_fraud_click_id = $request->ClickID;
        $tracking = Tracking::where('msisdn', $msisdn)->first();
        if ($tracking) {
            $tracking->failure = true;
            $tracking->save();
            IntegrationLog::updateOrCreate(
                [
                    'provider' => 'SDP',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'response',
                    'status' => 'failed',
                ],
                [
                    'payload' => [
                        'body' => $request->all()
                    ],
                    'url' => '',
                    'error_message' => json_encode($request->all()),
                ]);

            $query_params = [
                'clickId' => $anti_fraud_click_id,
                'campaignId' => $tracking->projectSource->campaign_id,
                'userIP' => $tracking->user_ip,
            ];

            $existing_integration = IntegrationLog::where('provider', 'digital_ads')
                ->where('tracking_id', $tracking->id)
                ->where('event_type', 'request')
                ->where('url', 'LIKE', '%FailCallBack%')
                ->first();
            if ($existing_integration) {
                $existing_integration->updated_at = now();
                $existing_integration->save();
            }
            else {
                $integration = IntegrationLog::create([
                    'provider' => 'digital_ads',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                    'status' => 'failed',
                    'payload' => $query_params,
                    'url' => $this->digitalAdsBaseUrl . 'http://callback.digitalabs.ae:9090/actions/',
                    'error_message' => json_encode($request->all()),
                ]);
                $response_digital_ads = Http::get($this->digitalAdsBaseUrl . 'FailCallBack?' . http_build_query($query_params));
                if ($response_digital_ads->status() == '200') {
                    $integration->status = 'success';
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
                else {
                    $integration->status = 'failed';
                    $integration->error_message = $response_digital_ads->body();
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
            }
        }
        return view('failure');
    }

    private function getMsisdnFromHeaders(Request $request)
    {
        foreach ($this->msisdnHeaders as $headerName) {
            $msisdn = $request->header($headerName);
            if ($msisdn) {
                return $msisdn;
            }
        }
        return null;
    }

    public function getRequestHeaders(Request $request)
    {
        // Get all headers from the request
        $headers = collect($request->headers->all())
            ->map(function($header) {
                return is_array($header) ? $header[0] : $header;
            })
            ->filter()
            ->toArray();

        // Convert to JSON and then to base64
        $jsonHeaders = json_encode($headers);
        $headersBase64 = base64_encode($jsonHeaders);
        $response = Response::json([
            'headersBase64' => $headersBase64,
            'msisdn' => Session::get('msisdn') // Include the stored MSISDN
        ]);
        return $response;
    }

    public function getAntiFraudScript(Request $request)
    {
        try {
            $baseUrl = 'https://sdp.salasto.dev:2053/Shield/AntiFraud/Prepare/';
            $queryParams = [
                'Page' => $request->page,
                'ChannelID' => $this->config['channelId'],
                'ClickID' => $request->click_id,
                'Headers' => $request->user_headers,
                'UserIP' => $request->user_ip,
                'MSISDN' => $request->msisdn
            ];
            $project_source = ProjectSource::where('uuid', $request->source)->first();
            $tracking = Tracking::where('msisdn', $request->msisdn)->where('click_id', $request->click_id)
                ->where('project_source_id',$project_source->id)
                ->first();
            $provider =  $request->save_antifraud == '1' ? 'anti_fraud_1' : 'anti_fraud_2';
            $integration = IntegrationLog::updateOrCreate(
                [
                    'provider' => $provider,
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                    'status' => 'success',
                ],
                [
                    'payload' => $queryParams,
                    'url' => $baseUrl,
//                    'metadata' => [
//                        'body' => $queryParams
//                    ]
                ]);

            // Make the request
            $response = Http::get($baseUrl . '?' . http_build_query($queryParams));
//            i want to check if response header has AntiFrauduniqid
            if ($response->header('AntiFrauduniqid')) {
                $integration->status = 'success';
                $integration->metadata = [
                    'header' => $response->headers(),
                ];
            }
            else {
                $integration->status = 'failed';
                $integration->error_message = $response->body();
                $integration->metadata = [
                    'body' => $response->body()
                ];
            }
            $integration->save();
            if ($request->save_antifraud == '1') {
                $tracking->anti_fraud_click_id = $response->header('AntiFrauduniqid');
                $tracking->mcp_uniq_id = $response->header('Mcpuniqid');
            }
            $tracking->first_click = true;
            $tracking->save();
            $query_params = [
                'clickId' => $tracking->anti_fraud_click_id,
                'campaignId' => $tracking->projectSource->campaign_id,
            ];

            $d_integration = IntegrationLog::updateOrCreate(
                [
                    'provider' => 'digital_ads',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                ],
                [
                    'payload' => $query_params,
                    'url' => $this->digitalAdsBaseUrl . 'VisitCallBack',
//                    'metadata' => [
//                        'body' => $query_params
//                    ]
                ]);

            $response_digital_ads = Http::get($this->digitalAdsBaseUrl . 'VisitCallBack?' . http_build_query($query_params));
            if ($response_digital_ads->status() == '200') {
                $d_integration->status = 'success';
                $d_integration->metadata = [
                    'header' => $response_digital_ads->headers(),
                    'body' => $response_digital_ads->body()
                ];
            }
            else {
                $d_integration->status = 'failed';
                $d_integration->error_message = $response_digital_ads->body();
                $d_integration->metadata = [
                    'body' => $response_digital_ads->body()
                ];
            }

            $d_integration->save();
            if (!$response->successful()) {
                redirect('failure');
                throw new \Exception('Anti-fraud API request failed');
            }


            // Get the script from response body and AntiFrauduniqid from header
            return Response::json([
                'success' => true,
                'script' => $response->body(),
                'antiFrauduniqid' => $response->header('AntiFrauduniqid'),
                'mcp_uniq_id' => $response->header('Mcpuniqid'),
            ]);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function savePreferredLanguage(Request $request)
    {
        $language = $request->input('language');
        // Store it in Laravel session or database
        session(['preferredLanguage' => $language]);
        return response()->json(['message' => session('preferredLanguage')]);
    }

    public function handleSubscription(Request $request)
    {
        try {
            $baseUrl = 'http://iq.as.salasto.heliveservices.com:8888/asia-he/iq-nucleus/HE/v1.2/doubleclick/sub.php';

            // Get MSISDN from session
            $msisdn = $request->msisdn;
            if (!$msisdn) {
                throw new \Exception('MSISDN not found');
            }

            $language = session('preferredLanguage');
            $tracking = Tracking::where('msisdn', $msisdn)
                ->where('anti_fraud_click_id', $request->antiFrauduniqid)->first();
            // Build query parameters
            $queryParams = [
                'serviceId' => $language == 'AR' ? $this->config['serviceIdAr'] : $this->config['serviceIdKU'],
                'spId' => $this->config['spId'],
                'shortcode' => $this->config['shortcode'],
                'msisdn' => $msisdn,
                'ClickID' => $request->antiFrauduniqid,
                'ChannelID' => $this->config['channelId'],
                'opSPID' => $this->config['opSPID'],
                'LanguageID' => $request->languageId,
                'gclid' => $tracking->click_id,
            ];

            if ($tracking) {
                $tracking->second_click = true;
                $tracking->save();
                IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'SDP',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'request',
                        'status' => 'success',
                    ],
                    [
                        'payload' => $queryParams,
                        'url' => $baseUrl,

                    ]);
                // Get the full URL with parameters
                $fullUrl = $baseUrl . '?' . http_build_query($queryParams);
                $response = Response::json([
                    'success' => true,
                    'redirectUrl' => $fullUrl
                ]);
                // Return the URL to frontend
                return $response;
            }
            else {
                throw new \Exception('Tracking data not found');
            }

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function print_headers(Request $request)
    {
        print_r($request->headers->all());
    }
}
