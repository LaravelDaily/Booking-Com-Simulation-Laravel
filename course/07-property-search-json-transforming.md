A quick reminder: we ended up the previous lesson with returning a huge JSON from the property search results.

Endpoint: `/api/search?city_id=1&adults=2&children=1`

**Result JSON**:
```json
[
    {
        "id": 2,
        "owner_id": 2,
        "name": "Central Hotel",
        "city_id": 2,
        "address_street": "16-18, Argyle Street, Camden",
        "address_postcode": "WC1H 8EG",
        "lat": "51.5291450",
        "long": "-0.1239401",
        "created_at": "2023-02-13T13:21:04.000000Z",
        "updated_at": "2023-02-13T13:21:04.000000Z",
        "city": {
            "id": 2,
            "country_id": 2,
            "name": "London",
            "lat": "51.5073510",
            "long": "-0.1277580",
            "created_at": "2023-02-13T09:04:51.000000Z",
            "updated_at": "2023-02-13T09:04:51.000000Z"
        },
        "apartments": [
            {
                "id": 2,
                "apartment_type_id": 1,
                "property_id": 2,
                "name": "Large Apartment",
                "capacity_adults": 3,
                "capacity_children": 2,
                "created_at": "2023-02-27T11:33:41.000000Z",
                "updated_at": "2023-02-27T11:33:41.000000Z",
                "size": 50,
                "bathrooms": 0,
                "apartment_type": {
                    "id": 1,
                    "name": "Entire apartment",
                    "created_at": "2023-02-28T09:02:54.000000Z",
                    "updated_at": "2023-02-28T09:02:54.000000Z"
                },
                "rooms": [
                    {
                        "id": 1,
                        "apartment_id": 2,
                        "room_type_id": 1,
                        "name": "Bedroom",
                        "created_at": "2023-03-02T10:07:05.000000Z",
                        "updated_at": "2023-03-02T10:07:05.000000Z",
                        "beds": [
                            {
                                "id": 1,
                                "room_id": 1,
                                "bed_type_id": 1,
                                "name": null,
                                "created_at": "2023-03-02T10:08:22.000000Z",
                                "updated_at": "2023-03-02T10:08:22.000000Z",
                                "bed_type": {
                                    "id": 1,
                                    "name": "Single bed",
                                    "created_at": "2023-03-02T07:37:43.000000Z",
                                    "updated_at": "2023-03-02T07:37:43.000000Z"
                                }
                            },
                            {
                                "id": 2,
                                "room_id": 1,
                                "bed_type_id": 2,
                                "name": null,
                                "created_at": "2023-03-02T10:08:22.000000Z",
                                "updated_at": "2023-03-02T10:08:22.000000Z",
                                "bed_type": {
                                    "id": 2,
                                    "name": "Large double bed",
                                    "created_at": "2023-03-02T07:37:43.000000Z",
                                    "updated_at": "2023-03-02T07:37:43.000000Z"
                                }
                            }
                        ]
                    },
                    {
                        "id": 2,
                        "apartment_id": 2,
                        "room_type_id": 2,
                        "name": "Living Room",
                        "created_at": "2023-03-02T10:07:05.000000Z",
                        "updated_at": "2023-03-02T10:07:05.000000Z",
                        "beds": []
                    }
                ]
            }
        ]
    }
]
```

Great, so we're delivering the data from the API, now front-end client may actually calculate how many beds there are and what type/size, right?

But maybe we can help and calculate it on-the-fly on server? 

Of course, it's a personal preference, but another argument would be that JSON result is really getting huge, although if we strip out all the things that we don't need, we're left with something like this:

```json
[
    {
        "id": 2,
        "name": "Central Hotel",
        "address_street": "16-18, Argyle Street, Camden",
        "address_postcode": "WC1H 8EG",
        "lat": "51.5291450",
        "long": "-0.1239401",
        "city": {
            "name": "London",
        },
        "apartments": [
            {
                "name": "Large Apartment",
                "capacity_adults": 3,
                "capacity_children": 2,
                "size": 50,
                "bathrooms": 0,
                "apartment_type": {
                    "name": "Entire apartment",
                },
                "rooms": [
                    {
                        "name": "Bedroom",
                        "beds": [
                            {
                                "name": null,
                                "bed_type": {
                                    "name": "Single bed",
                                }
                            },
                            {
                                "name": null,
                                "bed_type": {
                                    "name": "Large double bed",
                                }
                            }
                        ]
                    },
                    {
                        "name": "Living Room",
                        "beds": []
                    }
                ]
            }
        ]
    }
]
```

And not even that, what our front-end actually needs is this summary:

```json
[
    {
        "id": 2,
        "name": "Central Hotel",
        "address": "16-18, Argyle Street, Camden, WC1H 8EG, London",
        "lat": "51.5291450",
        "long": "-0.1239401",
        "apartments": [
            {
                "name": "Large Apartment",
                "beds": "2 beds (1 single, 1 large double)",
                "size": 50,
                "bathrooms": 0,
                "apartment_type": {
                    "name": "Entire apartment",
                },
            }
        ]
    }
]
```

So, let's work on this transformation, loading only the data that we need, and transforming some of the DB data into more useful string values.