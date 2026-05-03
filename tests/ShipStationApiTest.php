<?php

namespace Tests;

use Anibalealvarezs\ShipStationApi\Enums\OrderSort;
use Anibalealvarezs\ShipStationApi\Enums\OrderStatus;
use Anibalealvarezs\ShipStationApi\Enums\SortDir;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Anibalealvarezs\ShipStationApi\ShipStationApi;
use Symfony\Component\Yaml\Yaml;
use Anibalealvarezs\ApiSkeleton\Classes\Exceptions\ApiRequestException;

class ShipStationApiTest extends TestCase
{
    private ShipStationApi $shipStationApi;
    private Generator $faker;

    /**
     * @param MockHandler $mock
     * @return GuzzleClient
     */
    protected function createMockedGuzzleClient(MockHandler $mock): GuzzleClient
    {
        $handlerStack = HandlerStack::create($mock);
        return new GuzzleClient(['handler' => $handlerStack]);
    }

    /**
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $configFile = __DIR__ . "/../config/config.yaml";
        if (file_exists($configFile)) {
            $config = Yaml::parseFile($configFile);
        } else {
            $config = [
                'shipstation_api_key' => 'key',
                'shipstation_api_secret' => 'secret'
            ];
        }
        $this->shipStationApi = new ShipStationApi(
            apiKey: $config['shipstation_api_key'],
            apiSecret: $config['shipstation_api_secret'],
        );
        $this->faker = Factory::create();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ShipStationApi::class, $this->shipStationApi);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetOrders(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['orders' => [], 'total' => 0, 'page' => 1, 'pages' => 0])),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);
        $client = new ShipStationApi(apiKey: 'key', apiSecret: 'secret', guzzleClient: $guzzle);

        $createDateStart = $this->faker->date();
        $modifyDateStart = $this->getDateBetween($createDateStart, date('Y-m-d'));

        $orders = $client->getOrders(
            pageSize: $this->faker->numberBetween(1, 500),
            createDateStart: $createDateStart,
            createDateEnd: date('Y-m-d'),
            customsCountryCode: $this->faker->countryCode,
            modifyDateStart: $modifyDateStart,
            modifyDateEnd: date('Y-m-d'),
            orderDateStart: $createDateStart,
            orderDateEnd: date('Y-m-d'),
            orderStatus: $this->faker->randomElement(OrderStatus::getValues()),
            paymentDateStart: $modifyDateStart,
            paymentDateEnd: date('Y-m-d'),
            sortBy: $this->faker->randomElement(OrderSort::getValues()),
            sortDir: $this->faker->randomElement(SortDir::getValues()),
        );

        $this->assertIsArray($orders);
        $this->assertArrayHasKey('orders', $orders);
        $this->assertIsArray($orders['orders']);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllOrders(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['orders' => [], 'total' => 0, 'page' => 1, 'pages' => 1])),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);
        $client = new ShipStationApi(apiKey: 'key', apiSecret: 'secret', guzzleClient: $guzzle);

        $createDateStart = $this->faker->date();
        $modifyDateStart = $this->getDateBetween($createDateStart, date('Y-m-d'));

        $orders = $client->getAllOrders(
            loopLimit: 1,
            createDateStart: $createDateStart,
            createDateEnd: date('Y-m-d'),
            customsCountryCode: $this->faker->countryCode,
            modifyDateStart: $modifyDateStart,
            modifyDateEnd: date('Y-m-d'),
            orderDateStart: $createDateStart,
            orderDateEnd: date('Y-m-d'),
            orderStatus: $this->faker->randomElement(OrderStatus::getValues()),
            paymentDateStart: $modifyDateStart,
            paymentDateEnd: date('Y-m-d'),
            sortBy: $this->faker->randomElement(OrderSort::getValues()),
            sortDir: $this->faker->randomElement(SortDir::getValues()),
        );

        $this->assertIsArray($orders);
        $this->assertArrayHasKey('orders', $orders);
        $this->assertIsArray($orders['orders']);
    }

    protected function getDateBetween(string $start, string $end): string
    {
        return $this->faker->dateTimeBetween($start, $end)->format('Y-m-d');
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllOrdersAndProcess(): void
    {
        $response1 = [
            'orders' => [['orderId' => 'o1']],
            'pages' => 2,
            'page' => 1
        ];
        $response2 = [
            'orders' => [['orderId' => 'o2']],
            'pages' => 2,
            'page' => 2
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($response1)),
            new Response(200, [], json_encode($response2)),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new ShipStationApi(apiKey: 'key', apiSecret: 'secret', guzzleClient: $guzzle);

        $processedCount = 0;
        $client->getAllOrdersAndProcess(function ($data) use (&$processedCount) {
            $processedCount += count($data);
        });

        $this->assertEquals(2, $processedCount);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllOrdersEmpty(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['orders' => [], 'pages' => 0, 'page' => 1])),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new ShipStationApi(apiKey: 'key', apiSecret: 'secret', guzzleClient: $guzzle);

        $result = $client->getAllOrders();
        
        $this->assertCount(0, $result['orders']);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllOrdersErrorMidLoop(): void
    {
        $response1 = [
            'orders' => [['orderId' => 'o1']],
            'pages' => 2,
            'page' => 1
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($response1)),
            new Response(500, [], 'Internal Server Error'),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new ShipStationApi(apiKey: 'key', apiSecret: 'secret', guzzleClient: $guzzle);

        $this->expectException(ApiRequestException::class);

        $client->getAllOrdersAndProcess(function ($data) {});
    }
}
