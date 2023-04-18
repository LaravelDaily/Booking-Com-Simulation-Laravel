<?php

use App\Services\PricingService;

beforeEach(function () {
    $this->pricingService = new PricingService();
});


test('pricing for single price', function () {
    $prices = collect([
        ['start_date' => '2023-05-01', 'end_date' => '2030-05-01', 'price' => 100]
    ]);

    $priceForOneDay = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-11',
        '2023-05-11'
    );
    $this->assertEquals(100, $priceForOneDay);

    $priceForTwoDays = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-11',
        '2023-05-12'
    );
    $this->assertEquals(2 * 100, $priceForTwoDays);
});

test('pricing for multiple price ranges', function () {
    $prices = collect([
        ['start_date' => '2023-05-01', 'end_date' => '2023-05-10', 'price' => 100],
        ['start_date' => '2023-05-11', 'end_date' => '2023-06-01', 'price' => 90],
    ]);

    $priceForOneDayFirstRange = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-01',
        '2023-05-01'
    );
    $this->assertEquals(100, $priceForOneDayFirstRange);

    $priceForTwoDaysSecondRange = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-11',
        '2023-05-12'
    );
    $this->assertEquals(2 * 90, $priceForTwoDaysSecondRange);

    $priceForMultipleDaysBothRanges = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-09',
        '2023-05-12'
    );
    $this->assertEquals(2 * 100 + 2 * 90, $priceForMultipleDaysBothRanges);
});
