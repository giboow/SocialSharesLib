<?php
/**
 * SocialShares Lib
 *
 * @author Philippe Gibert <philippe.gibert@gmail.com>
 * @version 1.0
 */

/**
 * Get number of Google+ shares
 * @param string $url
 * @return int|null
 */
function getGoodlePlusShares($url)
{
    $id = 'social_count_googleplus_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($count = apc_fetch($id)) !== false) {
            return $count;
        }
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    $curl_results = curl_exec ($curl);
    curl_close ($curl);
    $json = json_decode($curl_results, true);
    $count = intval( $json[0]['result']['metadata']['globalCounts']['count'] );

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $count, SOCIAL_CACHE_TTL);
    }

    return $count;
}

/**
 * Get number of Facebook shares
 * @param string $url
 * @return int|null
 */
function getFacebookShares($url)
{
    $id = 'social_count_facebook_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($count = apc_fetch($id)) !== false) {
            return $count;
        }
    }

    $json = file_get_contents(
        'http://api.ak.facebook.com/restserver.php?v=1.0&method=links.getStats&urls='.urlencode($url).'&format=json'
    );
    $datas = json_decode($json);
    if (isset($datas[0])) {
        $count = $datas[0]->total_count;
    } else {
        $count = null;
    }

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $count, SOCIAL_CACHE_TTL);
    }

    return $count;
}


/**
 * Get number of Twitter shares
 * @param string $url
 * @return int|null
 */
function getTwitterShares($url)
{
    $id = 'social_count_twitter_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($count = apc_fetch($id)) !== false) {
            return $count;
        }
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://cdn.api.twitter.com/1/urls/count.json?url=".urlencode($url));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    $curl_results = curl_exec ($curl);
    curl_close ($curl);
    $json = json_decode($curl_results, true);

    if (isset($json['count'])) {
        $count = $json['count'];
    } else {
        $count = null;
    }

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $count, SOCIAL_CACHE_TTL);
    }

    return $count;
}

/**
 * Get number of Delicious shares
 * @param string $url
 * @return int|null
 */
function getDeliciousShares($url)
{
    $id = 'social_count_delicious_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($count = apc_fetch($id)) !== false) {
            return $count;
        }
    }

    $json = file_get_contents('http://feeds.delicious.com/v2/json/urlinfo/data?url='.urlencode($url));
    $datas = json_decode($json);
    if (isset($datas->total_posts)) {
        $count = $datas->total_posts;
    } else {
        $count = null;
    }

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $count, SOCIAL_CACHE_TTL);
    }

    return $count;
}

/**
 * Get number of Pinterest shares
 * @param string $url
 * @return int|null
 */
function getPinterestShares($url) {

    $id = 'social_count_pinterest_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($count = apc_fetch($id)) !== false) {
            return $count;
        }
    }

    $url = urlencode($url);
    $json = preg_replace(
        '/^receiveCount\((.*)\)$/', "\\1",
        file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url='.$url)
    );
    $datas = json_decode($json);
    if (isset($datas->count)) {
        $count = $datas->count;
    } else {
        $count = null;
    }

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $count, SOCIAL_CACHE_TTL);
    }

    return $count;
}

/**
 * Get number of All Social shares (Facebook, Twitter, Delicous, Pinterest, Google+)
 * @param string $url
 * @return array
 */
function getSocialShares($url) {

    $id = 'social_count_global_'.md5($url);
    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_fetch')) {
        if (($counts = apc_fetch($id)) !== false) {
            return $counts;
        }
    }

    $counts = array(
        'facebook'=> getFacebookShares($url),
        'twitter'=> getTwitterShares($url),
        'delicious'=> getDeliciousShares($url),
        'pinterest'=> getPinterestShares($url),
        'googlePlus'=> getGoodlePlusShares($url),
    );

    if (defined('SOCIAL_CACHE_TTL') && function_exists('apc_store')) {
        apc_store($id, $counts, SOCIAL_CACHE_TTL);
    }
    return $counts;
}

?>