<?php

namespace Anibalealvarezs\ShipStationApi;

use Carbon\Carbon;
use Anibalealvarezs\ApiSkeleton\Clients\BasicClient;
use Anibalealvarezs\ApiSkeleton\Enums\EncodingMethod;
use Anibalealvarezs\ShipStationApi\Enums\OrderSort;
use Anibalealvarezs\ShipStationApi\Enums\OrderStatus;
use Anibalealvarezs\ShipStationApi\Enums\SortDir;
use GuzzleHttp\Exception\GuzzleException;

class ShipStationApi extends BasicClient
{
    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @throws GuzzleException
     */
    public function __construct(
        string $apiKey,
        string $apiSecret,
    ) {
        return parent::__construct(
            baseUrl: 'https://ssapi.shipstation.com/',
            username: $apiKey,
            password: $apiSecret,
            encodingMethod: EncodingMethod::base64,
            delayHeader: "X-Rate-Limit-Reset",
        );
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param string|null $customerName
     * @param string|null $itemKeyword
     * @param string|null $createDateStart
     * @param string|null $createDateEnd
     * @param string|null $customsCountryCode
     * @param string|null $modifyDateStart
     * @param string|null $modifyDateEnd
     * @param string|null $orderDateStart
     * @param string|null $orderDateEnd
     * @param string|null $orderNumber
     * @param OrderStatus|null $orderStatus
     * @param string|null $paymentDateStart
     * @param string|null $paymentDateEnd
     * @param int|null $storeId
     * @param OrderSort $sortBy
     * @param SortDir|null $sortDir
     * @return array
     * @throws GuzzleException
     */
    public function getOrders(
        int $page = 1,
        ?int $pageSize = 500,
        ?string $customerName = null,
        ?string $itemKeyword = null,
        ?string $createDateStart = null,
        ?string $createDateEnd = null,
        ?string $customsCountryCode = null,
        ?string $modifyDateStart = null,
        ?string $modifyDateEnd = null,
        ?string $orderDateStart = null,
        ?string $orderDateEnd = null,
        ?string $orderNumber = null,
        ?OrderStatus $orderStatus = null,
        ?string $paymentDateStart = null,
        ?string $paymentDateEnd = null,
        ?int $storeId = null,
        OrderSort $sortBy = OrderSort::order_date,
        ?SortDir $sortDir = SortDir::desc,
    ): array {
        $query =[
            "pageSize" => $pageSize,
            "page" => $page,
        ];

        if ($customerName) {
            $query["customerName"] = $customerName;
        }
        if ($itemKeyword) {
            $query["itemKeyword"] = $itemKeyword;
        }
        if ($createDateStart) {
            $query["createDateStart"] = Carbon::parse($createDateStart)->toIso8601String();
        }
        if ($createDateEnd) {
            $query["createDateEnd"] = Carbon::parse($createDateEnd)->toIso8601String();
        }
        if ($customsCountryCode) {
            $query["customsCountryCode"] = $customsCountryCode;
        }
        if ($modifyDateStart) {
            $query["modifyDateStart"] = Carbon::parse($modifyDateStart)->toIso8601String();
        }
        if ($modifyDateEnd) {
            $query["modifyDateEnd"] = Carbon::parse($modifyDateEnd)->toIso8601String();
        }
        if ($orderDateStart) {
            $query["orderDateStart"] = Carbon::parse($orderDateStart)->toIso8601String();
        }
        if ($orderDateEnd) {
            $query["orderDateEnd"] = Carbon::parse($orderDateEnd)->toIso8601String();
        }
        if ($orderNumber) {
            $query["orderNumber"] = $orderNumber;
        }
        if ($orderStatus) {
            $query["orderStatus"] = $orderStatus->value;
        }
        if ($paymentDateStart) {
            $query["paymentDateStart"] = Carbon::parse($paymentDateStart)->toIso8601String();
        }
        if ($paymentDateEnd) {
            $query["paymentDateEnd"] = Carbon::parse($paymentDateEnd)->toIso8601String();
        }
        if ($storeId) {
            $query["storeId"] = $storeId;
        }
        if ($sortBy) {
            $query["sortBy"] = $sortBy->value;
        }
        if ($sortDir) {
            $query["sortDir"] = $sortDir->value;
        }

        // Request the spreadsheet data
        $response = $this->performRequest(
            method: "GET",
            endpoint: "orders",
            query: $query,
        );
        // Return response
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int|null $loopLimit
     * @param string|null $customerName
     * @param string|null $itemKeyword
     * @param string|null $createDateStart
     * @param string|null $createDateEnd
     * @param string|null $customsCountryCode
     * @param string|null $modifyDateStart
     * @param string|null $modifyDateEnd
     * @param string|null $orderDateStart
     * @param string|null $orderDateEnd
     * @param string|null $orderNumber
     * @param OrderStatus|null $orderStatus
     * @param string|null $paymentDateStart
     * @param string|null $paymentDateEnd
     * @param int|null $storeId
     * @param OrderSort $sortBy
     * @param SortDir|null $sortDir
     * @return array
     * @throws GuzzleException
     */
    public function getAllOrders(
        ?int $loopLimit = null,
        ?string $customerName = null,
        ?string $itemKeyword = null,
        ?string $createDateStart = null,
        ?string $createDateEnd = null,
        ?string $customsCountryCode = null,
        ?string $modifyDateStart = null,
        ?string $modifyDateEnd = null,
        ?string $orderDateStart = null,
        ?string $orderDateEnd = null,
        ?string $orderNumber = null,
        ?OrderStatus $orderStatus = null,
        ?string $paymentDateStart = null,
        ?string $paymentDateEnd = null,
        ?int $storeId = null,
        OrderSort $sortBy = OrderSort::order_date,
        ?SortDir $sortDir = SortDir::desc,
    ): array {
        $page = 0;
        $orders = [];

        do {
            $page++;
            $response = $this->getOrders(
                page: $page,
                customerName: $customerName,
                itemKeyword: $itemKeyword,
                createDateStart: $createDateStart,
                createDateEnd: $createDateEnd,
                customsCountryCode: $customsCountryCode,
                modifyDateStart: $modifyDateStart,
                modifyDateEnd: $modifyDateEnd,
                orderDateStart: $orderDateStart,
                orderDateEnd: $orderDateEnd,
                orderNumber: $orderNumber,
                orderStatus: $orderStatus,
                paymentDateStart: $paymentDateStart,
                paymentDateEnd: $paymentDateEnd,
                storeId: $storeId,
                sortBy: $sortBy,
                sortDir: $sortDir,
            );
            if (!empty($response['orders'])) {
                $orders = [...$orders, ...$response['orders']];
            }
            if (!isset($pages) && isset($response['pages'])) {
                $pages = $response['pages'];
            }
        } while (isset($pages) && $page <= $pages && (is_null($loopLimit) || $page < $loopLimit));

        return ['orders' => $orders];
    }
}
