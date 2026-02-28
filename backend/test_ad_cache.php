<?php
/**
 * Diagnostic: Test if AdQueueBuilder and AdvertisementAction share the same cache.
 * Run from inside the container: php /var/azuracast/www/backend/test_ad_cache.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use DI\ContainerBuilder;

// Boot the DI container
$environment = new App\Environment(dirname(__DIR__));
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(dirname(__DIR__) . '/backend/config/services.php');

// We need a simpler approach - just get the cache interface directly
// Use the same Redis factory as the app
$redisFactory = new App\Service\RedisFactory();

if (!$redisFactory->isSupported()) {
    echo "ERROR: Redis not supported\n";
    exit(1);
}

$redis = $redisFactory->getInstance();
echo "Redis connected OK\n";

// Create the same cache stack as services.php
$marshaller = new Symfony\Component\Cache\Marshaller\DefaultMarshaller(null);
$cacheAdapter = new Symfony\Component\Cache\Adapter\RedisAdapter($redis, marshaller: $marshaller);
$psr16Cache = new Symfony\Component\Cache\Psr16Cache($cacheAdapter);

echo "Marshaller class: " . get_class($marshaller) . "\n";

// Test 1: Integer value (like song counter) - this works
$psr16Cache->set('test_int_key', 42, 60);
$val = $psr16Cache->get('test_int_key');
echo "Test INT: set=42, get=" . var_export($val, true) . " => " . ($val === 42 ? "OK" : "FAIL") . "\n";

// Test 2: Boolean value (like cooldown)
$psr16Cache->set('test_bool_key', true, 60);
$val = $psr16Cache->get('test_bool_key');
echo "Test BOOL: set=true, get=" . var_export($val, true) . " => " . ($val === true ? "OK" : "FAIL") . "\n";

// Test 3: Array value (like ad cache)
$adData = [
    'is_ad_playing' => true,
    'not_before' => 0,
    'ad' => [
        'id' => 999,
        'name' => 'TEST AD',
        'advertiser_name' => 'Test',
        'media_type' => 'video',
        'media_url' => 'https://example.com/test.mp4',
        'media_path' => null,
        'duration' => 30,
    ],
];
$psr16Cache->set('test_array_key', $adData, 60);
$val = $psr16Cache->get('test_array_key');
echo "Test ARRAY: set=array, get=" . var_export($val !== null, true) . " => " . ($val !== null && $val['is_ad_playing'] === true ? "OK" : "FAIL") . "\n";
if ($val !== null) {
    echo "  is_ad_playing=" . var_export($val['is_ad_playing'], true) . "\n";
    echo "  ad.name=" . ($val['ad']['name'] ?? 'null') . "\n";
}

// Test 4: The ACTUAL key used by AdQueueBuilder
$psr16Cache->set('station_current_ad_1', $adData, 300);
$val = $psr16Cache->get('station_current_ad_1');
echo "Test REAL KEY: get=" . var_export($val !== null, true) . " => " . ($val !== null ? "OK" : "FAIL") . "\n";

// Check what's in Redis directly
echo "\nRedis raw keys with 'test':\n";
$keys = $redis->keys('*test*');
foreach ($keys as $k) {
    echo "  $k => " . substr($redis->get($k), 0, 100) . "\n";
}

echo "\nRedis raw key station_current_ad_1:\n";
$rawVal = $redis->get('station_current_ad_1');
echo "  exists=" . var_export($rawVal !== false, true) . "\n";
echo "  value prefix=" . substr($rawVal ?: '', 0, 50) . "\n";
echo "  value length=" . strlen($rawVal ?: '') . "\n";

// Cleanup test keys
$psr16Cache->delete('test_int_key');
$psr16Cache->delete('test_bool_key');
$psr16Cache->delete('test_array_key');

echo "\nDone.\n";
