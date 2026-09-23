<?php

namespace MehediIitdu\CoreComponentRepository;
use App\Models\Addon;
use Cache;

class CoreComponentRepository
{
    public static function instantiateShopRepository() {
        $data['url'] = $_SERVER['SERVER_NAME'];
        $data['code'] = config('app.system_key');
        $request_data_json = json_encode($data);
        $gate = "https://zwickytechnology.com/license.php?route=check_activation";
        $rn = self::serializeObjectResponse($gate, $request_data_json);
        self::finalizeRepository($rn);
    }

    protected static function serializeObjectResponse($zn, $request_data_json) {
        $header = array(
            'Content-Type:application/json'
        );
        $stream = curl_init();

        curl_setopt($stream, CURLOPT_URL, $zn);
        curl_setopt($stream, CURLOPT_HTTPHEADER, $header);
        curl_setopt($stream, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($stream, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($stream, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($stream, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($stream, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($stream, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($stream, CURLOPT_TIMEOUT, 8);
        // zwickytechnology.com's server rejects requests with no User-Agent (403).
        curl_setopt($stream, CURLOPT_USERAGENT, 'ActiveEcommerceCMS-LicenseCheck/1.0');

        $rn = curl_exec($stream);
        curl_close($stream);
        return $rn;
    }

    protected static function finalizeRepository($rn) {
        if($rn == "bad" && env('DEMO_MODE') != 'On') {
            return redirect('https://zwickytechnology.com/license.php?route=activation')->send();
        }
    }

    public static function initializeCache() {
        foreach(Addon::all() as $addon){
            if ($addon->purchase_code == null) {
                self::finalizeCache($addon);
            }
            $item_name = get_setting('item_name') ?? 'ecommerce';

            if(Cache::get($addon->unique_identifier.'-purchased', 'no') == 'no'){
                try {
                    $gate = "https://zwickytechnology.com/license.php?route=addon_check&identifier=".$addon->unique_identifier."&code=".$addon->purchase_code."&item=".$item_name;

                    $stream = curl_init();
                    curl_setopt($stream, CURLOPT_URL, $gate);
                    curl_setopt($stream, CURLOPT_HEADER, 0);
                    curl_setopt($stream, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($stream, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($stream, CURLOPT_TIMEOUT, 8);
                    curl_setopt($stream, CURLOPT_USERAGENT, 'ActiveEcommerceCMS-LicenseCheck/1.0');
                    $rn = curl_exec($stream);
                    curl_close($stream);
                    if($rn == 'no') {
                        self::finalizeCache($addon);
                    }
                    else{
                        Cache::rememberForever($addon->unique_identifier.'-purchased', function () {
                            return 'yes';
                        });
                    }
                } catch (\Exception $e) {

                }
            }
        }
    }

    public static function finalizeCache($addon){
        $addon->activated = 0;
        $addon->save();

        flash('Please reinstall '.$addon->name.' using valid purchase code')->warning();
        return redirect()->route('addons.index')->send();
    }
}
