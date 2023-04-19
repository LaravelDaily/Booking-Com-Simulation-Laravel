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
    expect($priceForOneDay)->toEqual(100);

    $priceForTwoDays = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-11',
        '2023-05-12'
    );
    expect($priceForTwoDays)->toEqual(2 * 100);
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
    expect($priceForOneDayFirstRange)->toEqual(100);

    $priceForTwoDaysSecondRange = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-11',
        '2023-05-12'
    );
    expect($priceForTwoDaysSecondRange)->toEqual(2 * 90);

    $priceForMultipleDaysBothRanges = $this->pricingService->calculateApartmentPriceForDates(
        $prices,
        '2023-05-09',
        '2023-05-12'
    );
    expect($priceForMultipleDaysBothRanges)->toEqual(2 * 100 + 2 * 90);
});
