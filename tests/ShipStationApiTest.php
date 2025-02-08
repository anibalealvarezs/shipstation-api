<?php

namespace Tests;

use Anibalealvarezs\ShipStationApi\Enums\OrderSort;
use Anibalealvarezs\ShipStationApi\Enums\OrderStatus;
use Anibalealvarezs\ShipStationApi\Enums\SortDir;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Anibalealvarezs\ShipStationApi\ShipStationApi;
use Symfony\Component\Yaml\Yaml;

class ShipStationApiTest extends TestCase
{
    private ShipStationApi $shipStationApi;
    private Generator $faker;

    /**
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $config = Yaml::parseFile(__DIR__ . "/../config/config.yaml");
        $this->shipStationApi = new ShipStationApi(
            apiKey: $config['shipstation_api_key'],
            apiSecret: $config['shipstation_api_secret'],
        );
        $this->faker = Factory::create();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(shipStationApi::class, $this->shipStationApi);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetOrders(): void
    {
        $createDateStart = $this->faker->date();
        $modifyDateStart = $this->getDateBetween($createDateStart, date('Y-m-d'));

        $orders = $this->shipStationApi->getOrders(
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
        $this->assertArrayHasKey('total', $orders);
        $this->assertArrayHasKey('page', $orders);
        $this->assertArrayHasKey('pages', $orders);
        $this->assertIsArray($orders['orders']);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllOrders(): void
    {
        $createDateStart = $this->faker->date();
        $modifyDateStart = $this->getDateBetween($createDateStart, date('Y-m-d'));

        $orders = $this->shipStationApi->getAllOrders(
            loopLimit: $this->faker->numberBetween(1, 200),
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
}