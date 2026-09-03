# Fleetbase PHP SDK API examples

These 220 examples are generated from the locked official Postman contract. CI executes every fenced snippet against a hermetic PSR-18 transport.

Create `$fleetbase` once as shown in the README, then use the relevant service call below. Fixture identifiers and payloads are illustrative; replace them with values from your application.

## Contacts

### Create a Contact

Creates a contact for the current company. Contacts are used as customers, facilitators, personnel, or other addressable people in Fleet-Ops workflows.

`POST {{base_url}}/{{namespace}}/contacts`

```php
$result = $fleetbase->contacts->createContact(
    [
        'body' => [
            'name' => 'John Doe',
            'type' => 'customer',
            'title' => 'Mr',
            'email' => 'john@exampleco.com',
            'phone' => '+1 563-920-4264',
        ],
    ],
    []
);
```

### Delete a Contact

Delete a Contact.

`DELETE {{base_url}}/{{namespace}}/contacts/:id`

```php
$result = $fleetbase->contacts->deleteContact(
    [
        'id' => 'contact_id-fixture',
    ],
    []
);
```

### Query Contacts

Returns a paginated list of contacts for the current organization. Use filters such as `query`, `limit`, `offset`, and `sort` to narrow and order the results.

`GET {{base_url}}/{{namespace}}/contacts`

```php
$result = $fleetbase->contacts->queryContacts(
    [],
    [
        'query' => [
            'query' => 'contact_name-fixture',
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Contact

Retrieve a Contact.

`GET {{base_url}}/{{namespace}}/contacts/:id`

```php
$result = $fleetbase->contacts->retrieveContact(
    [
        'id' => 'contact_id-fixture',
    ],
    []
);
```

### Update a Contact

Updates a contact's profile, type, primary place, photo, or metadata.

`PUT {{base_url}}/{{namespace}}/contacts/:id`

```php
$result = $fleetbase->contacts->updateContact(
    [
        'id' => 'contact_id-fixture',
        'body' => [
            'name' => 'John Doe',
            'title' => 'Mr',
            'email' => 'john@exampleco.com',
            'phone' => '563-920-4264',
            'meta' => [
                'external_ref' => 'john-doe',
            ],
        ],
    ],
    []
);
```

## Customers

### Create a Customer

Creates a customer account (Contact + linked User) after verifying the code from `Request Customer Creation Code`. Returns the customer with a Sanctum `token` — persist this client-side and send it back as the `Customer-Token` header on authenticated requests.

`POST {{base_url}}/{{namespace}}/customers`

```php
$result = $fleetbase->customers->createCustomer(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
            'code' => 'verification_code-fixture',
            'name' => 'Jane Customer',
            'password' => 'customer_password-fixture',
            'phone' => 'randomPhoneNumber-fixture',
            'place' => [
                'name' => 'Home',
                'street1' => '123 Main Street',
                'city' => 'Kingston',
                'province' => 'Kingston',
                'postal_code' => '00000',
                'country' => 'JM',
            ],
        ],
    ],
    []
);
```

### Create a Customer Order

Creates an Order on behalf of the authenticated customer. Accepts the canonical Fleet-Ops Order create shape — the same fields as `POST /v1/orders` would accept from an operator. The customer's `uuid` is automatically attached as `orders.customer_uuid`; any client-supplied `customer` field is ignored. `status` is forced to `created` (customers cannot self-dispatch). The Order lands in the company resolved from the API credential.

`POST {{base_url}}/{{namespace}}/customers/orders`

```php
$result = $fleetbase->customers->createCustomerOrder(
    [
        'body' => [
            'type' => 'transport',
            'scheduled_at' => '2026-05-25T10:00:00Z',
            'notes' => 'Handle with care.',
            'pickup' => [
                'name' => 'Pickup',
                'street1' => '4169 N State RD 7',
                'city' => 'Lauderdale Lakes',
                'province' => 'FL',
                'postal_code' => '33319',
                'country' => 'US',
            ],
            'dropoff' => [
                'name' => 'Dropoff',
                'city' => 'Kingston',
                'country' => 'JM',
            ],
            'entities' => [
                [
                    'name' => 'Wireless Headphones',
                    'description' => 'Electronics',
                    'weight' => 2.5,
                    'weight_unit' => 'lb',
                    'declared_value' => 150,
                    'currency' => 'USD',
                ],
            ],
        ],
    ],
    []
);
```

### Forgot Customer Password

Sends a password-reset verification code to the customer's email or phone. Always returns `{ status: ok }` regardless of whether the identity matches an account (prevents enumeration).

`POST {{base_url}}/{{namespace}}/customers/forgot-password`

```php
$result = $fleetbase->customers->forgotCustomerPassword(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
        ],
    ],
    []
);
```

### List Customer Orders

Lists orders owned by the authenticated customer (scoped to `orders.customer_uuid`).

`GET {{base_url}}/{{namespace}}/customers/orders`

```php
$result = $fleetbase->customers->listCustomerOrders(
    [],
    []
);
```

### List Customer Places

Lists the authenticated customer's saved Places (delivery addresses, etc.).

`GET {{base_url}}/{{namespace}}/customers/places`

```php
$result = $fleetbase->customers->listCustomerPlaces(
    [],
    []
);
```

### Login Customer

Authenticates a customer with email/phone + password. Returns the customer with a Sanctum `token` to use as `Customer-Token`.

`POST {{base_url}}/{{namespace}}/customers/login`

```php
$result = $fleetbase->customers->loginCustomer(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
            'password' => 'customer_password-fixture',
        ],
    ],
    []
);
```

### Logout All Customer Sessions

Revokes every Sanctum token issued to the customer's linked user (sign out everywhere).

`POST {{base_url}}/{{namespace}}/customers/logout-all`

```php
$result = $fleetbase->customers->logoutAllCustomerSessions(
    [],
    []
);
```

### Logout Customer

Revokes the Sanctum token used to make this request. The customer's other active sessions are unaffected — use `Logout All Customer Sessions` to revoke every token for the linked user.

`POST {{base_url}}/{{namespace}}/customers/logout`

```php
$result = $fleetbase->customers->logoutCustomer(
    [],
    []
);
```

### Register Customer Device

Registers a push-notification device token against the authenticated customer's linked user.

`POST {{base_url}}/{{namespace}}/customers/register-device`

```php
$result = $fleetbase->customers->registerCustomerDevice(
    [
        'body' => [
            'token' => 'push_token-fixture',
            'platform' => 'ios',
        ],
    ],
    []
);
```

### Request Customer Creation Code

Sends an email or SMS verification code to start a customer signup. Required before calling `Create a Customer`. Optionally include `name` and `phone` so the verification email greets the customer by name and the pending user row is pre-seeded with real values.

`POST {{base_url}}/{{namespace}}/customers/request-creation-code`

```php
$result = $fleetbase->customers->requestCustomerCreationCode(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
            'mode' => 'email',
            'name' => 'customer_name-fixture',
            'phone' => 'customer_phone-fixture',
        ],
    ],
    []
);
```

### Request Customer Login SMS

Starts SMS-based passwordless login by sending a verification code to the customer's phone. Falls back to email if SMS delivery fails and an email is on file.

`POST {{base_url}}/{{namespace}}/customers/login-with-sms`

```php
$result = $fleetbase->customers->requestCustomerLoginSms(
    [
        'body' => [
            'phone' => 'customer_phone-fixture',
        ],
    ],
    []
);
```

### Reset Customer Password

Verifies the reset code from `Forgot Customer Password` and sets a new password. All existing tokens for the customer's user are revoked on success.

`POST {{base_url}}/{{namespace}}/customers/reset-password`

```php
$result = $fleetbase->customers->resetCustomerPassword(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
            'code' => 'verification_code-fixture',
            'password' => 'customer_password-fixture',
        ],
    ],
    []
);
```

### Retrieve Authenticated Customer

Returns the profile of the customer identified by the `Customer-Token` header.

`GET {{base_url}}/{{namespace}}/customers/me`

```php
$result = $fleetbase->customers->retrieveAuthenticatedCustomer(
    [],
    []
);
```

### Retrieve a Customer Order

Fetches a single order by id, public id, or tracking number. Returns 404 if the order doesn't belong to the authenticated customer.

`GET {{base_url}}/{{namespace}}/customers/orders/{{customer_order_id}}`

```php
$result = $fleetbase->customers->retrieveCustomerOrder(
    [
        'customer_order_id' => 'customer_order_id-fixture',
    ],
    []
);
```

### Update Authenticated Customer

Updates the authenticated customer's profile. Changes to `name`, `email`, and `phone` are mirrored onto the linked user so subsequent logins work.

`PUT {{base_url}}/{{namespace}}/customers/me`

```php
$result = $fleetbase->customers->updateAuthenticatedCustomer(
    [
        'body' => [
            'name' => 'customer_name-fixture',
            'phone' => 'customer_phone-fixture',
            'email' => 'customer_email-fixture',
        ],
    ],
    []
);
```

### Verify Customer Login Code

Verifies the SMS/email code from `Request Customer Login SMS` and returns the customer with a Sanctum `token`. When `for` is `fleetops_create_customer` this proxies to `Create a Customer`.

`POST {{base_url}}/{{namespace}}/customers/verify-code`

```php
$result = $fleetbase->customers->verifyCustomerLoginCode(
    [
        'body' => [
            'identity' => 'customer_identity-fixture',
            'code' => 'verification_code-fixture',
            'for' => 'fleetops_customer_login',
        ],
    ],
    []
);
```

## Devices

### Attach Device

Attach this device to a vehicle.

`POST {{base_url}}/{{namespace}}/devices/{{device_id}}/attach`

```php
$result = $fleetbase->devices->attachDevice(
    [
        'device_id' => 'device_id-fixture',
        'body' => [
            'vehicle' => 'vehicle_id-fixture',
        ],
    ],
    []
);
```

### Create a Device

Create a device.

`POST {{base_url}}/{{namespace}}/devices`

```php
$result = $fleetbase->devices->createDevice(
    [
        'body' => [
            'name' => 'OBD Tracker 12',
            'type' => 'obd',
            'device_id' => 'OBD-12',
            'serial_number' => 'SN-10001',
            'status' => 'active',
        ],
    ],
    []
);
```

### Delete a Device

Delete a device.

`DELETE {{base_url}}/{{namespace}}/devices/{{device_id}}`

```php
$result = $fleetbase->devices->deleteDevice(
    [
        'device_id' => 'device_id-fixture',
    ],
    []
);
```

### Detach Device

Detach this device from its current resource.

`POST {{base_url}}/{{namespace}}/devices/{{device_id}}/detach`

```php
$result = $fleetbase->devices->detachDevice(
    [
        'device_id' => 'device_id-fixture',
    ],
    []
);
```

### Query Devices

Query devices.

`GET {{base_url}}/{{namespace}}/devices`

```php
$result = $fleetbase->devices->queryDevices(
    [],
    []
);
```

### Retrieve a Device

Retrieve a device.

`GET {{base_url}}/{{namespace}}/devices/{{device_id}}`

```php
$result = $fleetbase->devices->retrieveDevice(
    [
        'device_id' => 'device_id-fixture',
    ],
    []
);
```

### Update a Device

Update a device.

`PUT {{base_url}}/{{namespace}}/devices/{{device_id}}`

```php
$result = $fleetbase->devices->updateDevice(
    [
        'device_id' => 'device_id-fixture',
        'body' => [
            'status' => 'maintenance',
        ],
    ],
    []
);
```

## Drivers

### Change Driver Password

Changes the password of a driver who is signed in, proving the current one. A password change is an authorisation decision rather than an attribute update, which is why it is not part of `PUT /drivers/:id` — that endpoint does not accept a password at all. Supplying the wrong `current_password` is refused and changes nothing. Every other session is revoked when the password changes, and a fresh token is returned in the same response, so the caller keeps working while other devices are signed out.

`POST {{base_url}}/{{namespace}}/drivers/:id/change-password`

```php
$result = $fleetbase->drivers->changeDriverPassword(
    [
        'id' => 'driver_id-fixture',
        'body' => [
            'current_password' => 'created_driver_password-fixture',
            'password' => 'driver_new_password-fixture',
            'password_confirmation' => 'driver_new_password-fixture',
            'device_name' => 'navigator',
        ],
    ],
    []
);
```

### Create a Driver

Creates a driver profile and linked user account. Provide a unique email and phone number, then optionally assign a vehicle, vendor, current job, location, or photo.

`POST {{base_url}}/{{namespace}}/drivers`

```php
$result = $fleetbase->drivers->createDriver(
    [
        'body' => [
            'name' => 'John Doe',
            'email' => 'randomEmail-fixture',
            'phone' => 'randomPhoneNumber-fixture',
            'password' => 'driver_seed_password-fixture',
        ],
    ],
    []
);
```

### Delete a Driver

Use this endpoint to delete a driver.

`DELETE {{base_url}}/{{namespace}}/drivers/:id`

```php
$result = $fleetbase->drivers->deleteDriver(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### Get Driver Current Organization

Returns the driver current organization.

`GET {{base_url}}/{{namespace}}/drivers/:id/current-organization`

```php
$result = $fleetbase->drivers->getDriverCurrentOrganization(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### List Driver Manifests

Lists the manifests assigned to a driver, newest first. A manifest is a driver's route: an order-agnostic sequence of stops which may span several orders, or none the driver has seen as an order. Defaults to a recent window rather than the driver's whole history. Use `status` and `on` to narrow it further.

`GET {{base_url}}/{{namespace}}/drivers/:id/manifests`

```php
$result = $fleetbase->drivers->listDriverManifests(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### List Driver Organizations

Lists organizations a driver belongs to.

`GET {{base_url}}/{{namespace}}/drivers/:id/organizations`

```php
$result = $fleetbase->drivers->listDriverOrganizations(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### Login Driver

Authenticates a driver with email/phone and password.

`POST {{base_url}}/{{namespace}}/drivers/login`

```php
$result = $fleetbase->drivers->loginDriver(
    [
        'body' => [
            'identity' => 'driver_identity-fixture',
            'password' => 'driver_password-fixture',
        ],
    ],
    []
);
```

### Query Drivers

Returns drivers for the current company. Use filters such as `vendor`, search, pagination, or sort parameters to narrow the result set.

`GET {{base_url}}/{{namespace}}/drivers`

```php
$result = $fleetbase->drivers->queryDrivers(
    [],
    [
        'query' => [
            'id' => 'driver_id-fixture',
        ],
    ]
);
```

### Register Device

Registers a driver device token through the non-id route.

`POST {{base_url}}/{{namespace}}/drivers/register-device`

```php
$result = $fleetbase->drivers->registerDevice(
    [
        'body' => [
            'token' => 'device_token-fixture',
            'platform' => 'ios',
        ],
    ],
    []
);
```

### Register Driver Device

Registers a device token for a specific driver.

`POST {{base_url}}/{{namespace}}/drivers/:id/register-device`

```php
$result = $fleetbase->drivers->registerDriverDevice(
    [
        'id' => 'driver_id-fixture',
        'body' => [
            'token' => 'device_token-fixture',
            'platform' => 'ios',
        ],
    ],
    []
);
```

### Request Driver Login SMS

Starts driver SMS verification login.

`POST {{base_url}}/{{namespace}}/drivers/login-with-sms`

```php
$result = $fleetbase->drivers->requestDriverLoginSms(
    [
        'body' => [
            'phone' => 'driver_phone-fixture',
        ],
    ],
    []
);
```

### Request Driver Password Reset

Sends a password reset code to a driver who cannot sign in. The code goes by email or SMS depending on whether `identity` looks like an email address or a phone number. The response is the same whether or not the identity belongs to a driver. That is deliberate: an endpoint that answered differently for an unknown number would be a way to enumerate an organization's drivers. `driver_reset_identity` defaults to a reserved address that belongs to nobody, so running the collection documents the endpoint without sending mail to a stranger. Point it at a real driver to exercise delivery.

`POST {{base_url}}/{{namespace}}/drivers/forgot-password`

```php
$result = $fleetbase->drivers->requestDriverPasswordReset(
    [
        'body' => [
            'identity' => 'driver_reset_identity-fixture',
        ],
    ],
    []
);
```

### Reset Driver Password

Sets a new password using the code sent by `POST /drivers/forgot-password`. A wrong code, an expired code and an unknown identity all return the same error, so the endpoint cannot be used to test which of the three happened. Every session is revoked on success. A reset is a recovery from losing control of an account, so nothing that was signed in stays signed in. `driver_password_reset_code` is a code issued for this flow specifically — a login code will not do, because the endpoint matches on purpose as well as on value. The password is set back to the one already in force so the run stays repeatable and the driver login requests further down still authenticate.

`POST {{base_url}}/{{namespace}}/drivers/reset-password`

```php
$result = $fleetbase->drivers->resetDriverPassword(
    [
        'body' => [
            'identity' => 'driver_identity-fixture',
            'code' => 'driver_password_reset_code-fixture',
            'password' => 'driver_password-fixture',
        ],
    ],
    []
);
```

### Retrieve a Driver

This endpoint allows you to retrieve a driver object to view it's details.

`GET {{base_url}}/{{namespace}}/drivers/:id`

```php
$result = $fleetbase->drivers->retrieveDriver(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### Simulate Driver Route

Simulates driver movement between two resolvable points, or pass order to simulate an order route.

`POST {{base_url}}/{{namespace}}/drivers/:id/simulate`

```php
$result = $fleetbase->drivers->simulateDriverRoute(
    [
        'id' => 'driver_id-fixture',
        'body' => [
            'start' => [
                'latitude' => 1.3521,
                'longitude' => 103.8198,
            ],
            'end' => [
                'latitude' => 1.2903,
                'longitude' => 103.8519,
            ],
        ],
    ],
    []
);
```

### Switch Driver Organization

Switches the driver session to another organization. The driver must already belong to the target organization, and it must not be the one they are currently in — switching to the current organization is rejected. A driver created through `POST /drivers` belongs to a single organization, so this example uses one that belongs to more than one.

`POST {{base_url}}/{{namespace}}/drivers/:id/switch-organization`

```php
$result = $fleetbase->drivers->switchDriverOrganization(
    [
        'id' => 'multi_org_driver_id-fixture',
        'body' => [
            'next' => 'secondary_organization_id-fixture',
        ],
    ],
    []
);
```

### Toggle Driver Online

Toggles or sets driver online status.

`POST {{base_url}}/{{namespace}}/drivers/:id/toggle-online`

```php
$result = $fleetbase->drivers->toggleDriverOnline(
    [
        'id' => 'driver_id-fixture',
        'body' => [
            'online' => true,
        ],
    ],
    []
);
```

### Track Driver

`PATCH {{base_url}}/{{namespace}}/drivers/:id/track`

```php
$result = $fleetbase->drivers->trackDriver(
    [
        'id' => 'driver_id-fixture',
    ],
    []
);
```

### Update a Driver

Updates a driver's account fields, assignment, status, location, photo, or metadata.

`PUT {{base_url}}/{{namespace}}/drivers/:id`

```php
$result = $fleetbase->drivers->updateDriver(
    [
        'id' => 'driver_id-fixture',
        'body' => [
            'name' => 'John Doe',
            'email' => 'randomEmail-fixture',
            'phone' => 'randomPhoneNumber-fixture',
        ],
    ],
    []
);
```

### Verify Driver Login Code

Verifies driver login code and returns a driver token.

`POST {{base_url}}/{{namespace}}/drivers/verify-code`

```php
$result = $fleetbase->drivers->verifyDriverLoginCode(
    [
        'body' => [
            'identity' => 'driver_identity-fixture',
            'code' => 'verification_code-fixture',
        ],
    ],
    []
);
```

## Entities

### Create an Entity

Creates an entity such as a parcel, package, or item. Attach it to a payload and optionally set a destination or waypoint within that payload route.

`POST {{base_url}}/{{namespace}}/entities`

```php
$result = $fleetbase->entities->createEntity(
    [
        'body' => [
            'name' => 'SampleEntity',
            'type' => 'parcel',
            'payload' => 'payload_id-fixture',
            'customer' => 'ACustomer',
            'internal_id' => 'ENTITY001',
            'description' => 'Sample description',
            'meta' => [
                'warehouse_bin' => '1',
                'warehouse_rack' => '3',
                'warehouse_section' => '4',
            ],
            'weight' => 2.5,
            'weight_unit' => 'kg',
            'length' => 10,
            'width' => 5,
            'height' => 8,
            'dimensions_unit' => 'mm',
            'declared_value' => 1500,
            'price' => 1200,
            'sale_price' => 900,
            'sku' => 'SKU123',
            'currency' => 'USD',
        ],
    ],
    []
);
```

### Delete a Entity

Delete an Entity.

`DELETE {{base_url}}/{{namespace}}/entities/:id`

```php
$result = $fleetbase->entities->deleteEntity(
    [
        'id' => 'entity_id-fixture',
    ],
    []
);
```

### Query Entities

Returns entities for the current company. Use filters such as `type`, `payload`, or `destination` to narrow the result set.

`GET {{base_url}}/{{namespace}}/entities`

```php
$result = $fleetbase->entities->queryEntities(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
            'type' => 'parcel',
        ],
    ]
);
```

### Retrieve an Entity

Retrieve an Entity.

`GET {{base_url}}/{{namespace}}/entities/:id`

```php
$result = $fleetbase->entities->retrieveEntity(
    [
        'id' => 'entity_id-fixture',
    ],
    []
);
```

### Update a Entity

Updates an entity's descriptive fields, payload assignment, destination, dimensions, weight, pricing, or metadata.

`PUT {{base_url}}/{{namespace}}/entities/:id`

```php
$result = $fleetbase->entities->updateEntity(
    [
        'id' => 'entity_id-fixture',
        'body' => [
            'internal_id' => 'ENTITY001-1',
            'description' => 'New entity description',
            'destination' => '',
            'sku' => 'SKUABC123',
            'currency' => 'SGD',
        ],
    ],
    []
);
```

## Equipment

### Create Equipment

Create equipment.

`POST {{base_url}}/{{namespace}}/equipment`

```php
$result = $fleetbase->equipment->createEquipment(
    [
        'body' => [
            'name' => 'Liftgate LG-12',
            'code' => 'LG-12',
            'type' => 'liftgate',
            'status' => 'available',
            'serial_number' => 'LG120045',
            'manufacturer' => 'Maxon',
            'model' => 'BMR',
            'currency' => 'USD',
        ],
    ],
    []
);
```

### Delete Equipment

Delete equipment.

`DELETE {{base_url}}/{{namespace}}/equipment/{{equipment_id}}`

```php
$result = $fleetbase->equipment->deleteEquipment(
    [
        'equipment_id' => 'equipment_id-fixture',
    ],
    []
);
```

### Query Equipment

Query equipment.

`GET {{base_url}}/{{namespace}}/equipment`

```php
$result = $fleetbase->equipment->queryEquipment(
    [],
    []
);
```

### Retrieve Equipment

Retrieve equipment.

`GET {{base_url}}/{{namespace}}/equipment/{{equipment_id}}`

```php
$result = $fleetbase->equipment->retrieveEquipment(
    [
        'equipment_id' => 'equipment_id-fixture',
    ],
    []
);
```

### Update Equipment

Update equipment.

`PUT {{base_url}}/{{namespace}}/equipment/{{equipment_id}}`

```php
$result = $fleetbase->equipment->updateEquipment(
    [
        'equipment_id' => 'equipment_id-fixture',
        'body' => [
            'status' => 'maintenance',
        ],
    ],
    []
);
```

## Fleets

### Create a Fleet

Creates a fleet for grouping drivers and vehicles. Assign a service area when the fleet should be constrained to a specific operating area.

`POST {{base_url}}/{{namespace}}/fleets`

```php
$result = $fleetbase->fleets->createFleet(
    [
        'body' => [
            'name' => 'Haulers',
            'service_area' => 'service_area_id-fixture',
        ],
    ],
    []
);
```

### Delete a Fleet

Deletes a fleet.

`DELETE {{base_url}}/{{namespace}}/fleets/:id`

```php
$result = $fleetbase->fleets->deleteFleet(
    [
        'id' => 'fleet_id-fixture',
    ],
    []
);
```

### Query Fleets

Returns a paginated list of fleets for the current organization. Use pagination and sorting parameters to control the result set.

`GET {{base_url}}/{{namespace}}/fleets`

```php
$result = $fleetbase->fleets->queryFleets(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Fleet

Retrieves a fleet.

`GET {{base_url}}/{{namespace}}/fleets/:id`

```php
$result = $fleetbase->fleets->retrieveFleet(
    [
        'id' => 'fleet_id-fixture',
    ],
    []
);
```

### Update a Fleet

Updates a fleet's name or assigned service area.

`PUT {{base_url}}/{{namespace}}/fleets/:id`

```php
$result = $fleetbase->fleets->updateFleet(
    [
        'id' => 'fleet_id-fixture',
        'body' => [
            'name' => 'Haulers',
            'service_area' => 'service_area_id-fixture',
        ],
    ],
    []
);
```

## Fuel Reports

### Create a Fuel Report

Create a Fuel Report

`POST {{base_url}}/{{namespace}}/fuel-reports`

```php
$result = $fleetbase->fuelReports->createFuelReport(
    [
        'body' => [
            'driver' => 'driver_id-fixture',
            'odometer' => 12042,
            'volume' => 42.5,
            'metric_unit' => 'liter',
            'location' => [
                'latitude' => 1.3521,
                'longitude' => 103.8198,
            ],
            'amount' => 120.5,
            'currency' => 'USD',
            'status' => 'submitted',
        ],
    ],
    []
);
```

### Delete a Fuel Report

Delete a Fuel Report

`DELETE {{base_url}}/{{namespace}}/fuel-reports/:id`

```php
$result = $fleetbase->fuelReports->deleteFuelReport(
    [
        'id' => 'fuel_report_id-fixture',
    ],
    []
);
```

### Query Fuel Reports

Query Fuel Reports

`GET {{base_url}}/{{namespace}}/fuel-reports`

```php
$result = $fleetbase->fuelReports->queryFuelReports(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Fuel Report

Retrieve a Fuel Report

`GET {{base_url}}/{{namespace}}/fuel-reports/:id`

```php
$result = $fleetbase->fuelReports->retrieveFuelReport(
    [
        'id' => 'fuel_report_id-fixture',
    ],
    []
);
```

### Update a Fuel Report

Update a Fuel Report

`PUT {{base_url}}/{{namespace}}/fuel-reports/:id`

```php
$result = $fleetbase->fuelReports->updateFuelReport(
    [
        'id' => 'fuel_report_id-fixture',
        'body' => [
            'odometer' => 12050,
            'volume' => 43.1,
            'metric_unit' => 'liter',
            'amount' => 122.75,
            'currency' => 'USD',
            'status' => 'approved',
        ],
    ],
    []
);
```

## Fuel Transactions

### Create a Fuel Transaction

Create a fuel transaction.

`POST {{base_url}}/{{namespace}}/fuel-transactions`

```php
$result = $fleetbase->fuelTransactions->createFuelTransaction(
    [
        'body' => [
            'provider' => 'petroapp',
            'provider_transaction_id' => 'TX-timestamp-fixture',
            'vehicle' => 'vehicle_id-fixture',
            'station_name' => 'North Depot Fuel',
            'transaction_at' => '2026-05-07T08:30:00Z',
            'volume' => 42.5,
            'metric_unit' => 'liter',
            'amount' => 6500,
            'currency' => 'USD',
        ],
    ],
    []
);
```

### Delete a Fuel Transaction

Delete a fuel transaction.

`DELETE {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}`

```php
$result = $fleetbase->fuelTransactions->deleteFuelTransaction(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
    ],
    []
);
```

### Match Fuel Transaction Order

Match this fuel transaction to an order.

`POST {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-order`

```php
$result = $fleetbase->fuelTransactions->matchFuelTransactionOrder(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
        'body' => [
            'order' => 'order_id-fixture',
        ],
    ],
    []
);
```

### Match Fuel Transaction Vehicle

Match this fuel transaction to a vehicle.

`POST {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/match-vehicle`

```php
$result = $fleetbase->fuelTransactions->matchFuelTransactionVehicle(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
        'body' => [
            'vehicle' => 'vehicle_id-fixture',
        ],
    ],
    []
);
```

### Query Fuel Transactions

Query fuel transactions.

`GET {{base_url}}/{{namespace}}/fuel-transactions`

```php
$result = $fleetbase->fuelTransactions->queryFuelTransactions(
    [],
    []
);
```

### Reprocess Fuel Transaction

Reprocess matching and fuel report generation for this fuel transaction.

`POST {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/reprocess`

```php
$result = $fleetbase->fuelTransactions->reprocessFuelTransaction(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
    ],
    []
);
```

### Retrieve a Fuel Transaction

Retrieve a fuel transaction.

`GET {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}`

```php
$result = $fleetbase->fuelTransactions->retrieveFuelTransaction(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
    ],
    []
);
```

### Review Fuel Transaction

Mark this fuel transaction as reviewed or ignored.

`POST {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}/review`

```php
$result = $fleetbase->fuelTransactions->reviewFuelTransaction(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
        'body' => [
            'status' => 'reviewed',
        ],
    ],
    []
);
```

### Update a Fuel Transaction

Update a fuel transaction.

`PUT {{base_url}}/{{namespace}}/fuel-transactions/{{fuel_transaction_id}}`

```php
$result = $fleetbase->fuelTransactions->updateFuelTransaction(
    [
        'fuel_transaction_id' => 'fuel_transaction_id-fixture',
        'body' => [
            'sync_status' => 'reviewed',
        ],
    ],
    []
);
```

## Geofences

### Get Driver Geofence History

Get Driver Geofence History

`GET {{base_url}}/{{namespace}}/geofences/driver/:driverId/history`

```php
$result = $fleetbase->geofences->getDriverGeofenceHistory(
    [
        'driverId' => 'driver_id-fixture',
    ],
    [
        'query' => [
            'per_page' => '50',
        ],
    ]
);
```

### Get Geofence Dwell Report

Get Geofence Dwell Report

`GET {{base_url}}/{{namespace}}/geofences/dwell-report`

```php
$result = $fleetbase->geofences->getGeofenceDwellReport(
    [],
    [
        'query' => [
            'from' => 'from_datetime-fixture',
            'to' => 'to_datetime-fixture',
        ],
    ]
);
```

### Get Geofence Inventory

Get Geofence Inventory

`GET {{base_url}}/{{namespace}}/geofences/inventory`

```php
$result = $fleetbase->geofences->getGeofenceInventory(
    [],
    []
);
```

### List Geofence Events

List Geofence Events

`GET {{base_url}}/{{namespace}}/geofences/events`

```php
$result = $fleetbase->geofences->listGeofenceEvents(
    [],
    [
        'query' => [
            'per_page' => '50',
            'event_type' => 'entered',
        ],
    ]
);
```

## Issues

### Create an Issue

Create an Issue

`POST {{base_url}}/{{namespace}}/issues`

```php
$result = $fleetbase->issues->createIssue(
    [
        'body' => [
            'driver' => 'driver_id-fixture',
            'location' => [
                'latitude' => 1.3521,
                'longitude' => 103.8198,
            ],
            'report' => 'Vehicle tire pressure warning',
            'category' => 'vehicle',
            'type' => 'maintenance',
            'priority' => 'medium',
            'status' => 'open',
        ],
    ],
    []
);
```

### Delete an Issue

Delete an Issue

`DELETE {{base_url}}/{{namespace}}/issues/:id`

```php
$result = $fleetbase->issues->deleteIssue(
    [
        'id' => 'issue_id-fixture',
    ],
    []
);
```

### Query Issues

Query Issues

`GET {{base_url}}/{{namespace}}/issues`

```php
$result = $fleetbase->issues->queryIssues(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve an Issue

Retrieve an Issue

`GET {{base_url}}/{{namespace}}/issues/:id`

```php
$result = $fleetbase->issues->retrieveIssue(
    [
        'id' => 'issue_id-fixture',
    ],
    []
);
```

### Update an Issue

Update an Issue

`PUT {{base_url}}/{{namespace}}/issues/:id`

```php
$result = $fleetbase->issues->updateIssue(
    [
        'id' => 'issue_id-fixture',
        'body' => [
            'report' => 'Updated issue report',
            'category' => 'vehicle',
            'type' => 'maintenance',
            'priority' => 'high',
            'status' => 'resolved',
        ],
    ],
    []
);
```

## Labels

### Render Label

Renders a PDF, text, or base64 label for an order, waypoint, or entity id.

`GET {{base_url}}/{{namespace}}/labels/:id`

```php
$result = $fleetbase->labels->renderLabel(
    [
        'id' => 'order_id-fixture',
    ],
    [
        'query' => [
            'format' => 'stream',
            'type' => 'order',
        ],
    ]
);
```

## Manifests

### Optimize a Manifest

Re-sequences the stops a driver has not done yet, nearest first. This is the driver's optimise, not the orchestrator's. The orchestrator allocates orders across a fleet and produces manifests; this reorders the stops of one manifest that is already assigned. It is a nearest-neighbour walk over road distances: from the driver's position to the closest remaining stop, then the closest from there. That is usually a large improvement on an arbitrary order and is not guaranteed optimal. Completed and skipped stops keep their place — a route already driven is not re-planned. A manifest with fewer than three stops still to do is returned unchanged, since there is no ordering to find. Send `latitude` and `longitude` to start the walk from where the driver actually is. Without them it starts from the first stop still to do.

`POST {{base_url}}/{{namespace}}/manifests/:id/optimize`

```php
$result = $fleetbase->manifests->optimizeManifest(
    [
        'id' => 'manifest_id-fixture',
        'body' => [
            'latitude' => 1.3521,
            'longitude' => 103.8198,
        ],
    ],
    []
);
```

### Retrieve a Manifest

Retrieves a manifest with its stops, in the sequence they are to be driven. Each stop carries its place inline — a route of twenty stops is one request, not twenty-one — along with its status, estimated and actual arrival, and the distance and duration from the previous stop.

`GET {{base_url}}/{{namespace}}/manifests/:id`

```php
$result = $fleetbase->manifests->retrieveManifest(
    [
        'id' => 'manifest_id-fixture',
    ],
    []
);
```

### Update a Manifest Stop

Marks a stop on a manifest as arrived, completed or skipped. Status changes run through the manifest's own transitions rather than writing a column, so arrival and completion timestamps are recorded and a manifest completes itself when its last stop does. Any other status is refused and changes nothing.

`PATCH {{base_url}}/{{namespace}}/manifest-stops/:id`

```php
$result = $fleetbase->manifests->updateManifestStop(
    [
        'id' => 'manifest_stop_id-fixture',
        'body' => [
            'status' => 'arrived',
        ],
    ],
    []
);
```

## Onboard

### Get Driver Onboard Settings

Returns driver onboarding settings for an organization.

`GET {{base_url}}/{{namespace}}/onboard/driver-onboard-settings/:companyId`

```php
$result = $fleetbase->onboard->getDriverOnboardSettings(
    [
        'companyId' => 'organization_id-fixture',
    ],
    []
);
```

## Orchestrator

### Commit Orchestrator Plan

Commits a proposed orchestrator plan by creating manifests and applying vehicle and driver assignments to orders. Assignment records must use public resource IDs. This endpoint is engine-agnostic: commit the `assignments` returned by route-aware VROOM, VROOM capacity-only, native capacity-only, or multi-phase orchestration runs. Do not submit internal UUIDs or database IDs.

`POST {{base_url}}/{{namespace}}/orchestrator/commit`

```php
$result = $fleetbase->orchestrator->commitOrchestratorPlan(
    [
        'body' => [
            'scheduled_date' => '2026-05-16',
            'assignments' => [
                [
                    'order_id' => 'order_id-fixture',
                    'vehicle_id' => 'vehicle_id-fixture',
                    'driver_id' => 'driver_id-fixture',
                    'sequence' => 1,
                    'arrival' => 1778918400,
                    'duration' => 900,
                    'distance' => 4200,
                ],
            ],
        ],
    ],
    []
);
```

### Run Orchestrator

Runs an orchestration phase and returns a proposed assignment plan without committing changes. Use `options.engine` to force an engine: `greedy` and `capacity` are built in and need no external service, while `vroom` requires a reachable VROOM instance. Omitting `options.engine` uses the orchestrator engine configured in admin settings, which defaults to `greedy`. For normal route-aware VROOM allocation, send `options.engine: "vroom"` and omit `allocation_strategy` or set it to `route_aware`. Vehicles must have usable positions because VROOM will solve against route coordinates. For capacity-only VROOM allocation, send `options.engine: "vroom"` and `options.allocation_strategy: "capacity_only"`. This mode answers which vehicles can carry the selected orders by weight, volume, pallets, parcels, skills, and task limits without requiring vehicle locations. Use `options.vehicle_packing: "minimize_vehicles"` to bias VROOM toward filling feasible vehicles before opening another vehicle; use `balanced` or `none` to disable that packing bias. For Fleetbase's deterministic built-in capacity allocation, send `options.engine: "capacity"`. This native engine is useful as a fallback and debugging baseline for capacity-only allocation without VROOM. The consumable API accepts and returns public IDs only. Use `order_ids`, `vehicle_ids`, `driver_ids`, `prior_assignments.*_id`, and response `*_id` values as public IDs. Do not submit internal UUIDs.

`POST {{base_url}}/{{namespace}}/orchestrator/run`

```php
$result = $fleetbase->orchestrator->runOrchestrator(
    [
        'body' => [
            'mode' => 'assign_vehicles',
            'order_ids' => [
                'order_id-fixture',
            ],
            'vehicle_ids' => [
                'vehicle_id-fixture',
            ],
            'driver_ids' => [],
            'prior_assignments' => [],
            'options' => [
                'engine' => 'greedy',
                'allocation_strategy' => 'route_aware',
                'geometry' => false,
                'respect_capacity' => true,
                'respect_skills' => true,
                'return_to_depot' => false,
            ],
        ],
    ],
    []
);
```

## Order Configs

### Query Order Configs

Lists OrderConfigs available to the company resolved from the API credential.

`GET {{base_url}}/{{namespace}}/order-configs`

```php
$result = $fleetbase->orderConfigs->queryOrderConfigs(
    [],
    []
);
```

### Retrieve an Order Config

Fetches a single OrderConfig. The `{id}` segment accepts any identifier supported by `OrderConfig::resolveFromIdentifier` — `uuid`, `public_id`, `namespace`, or short `key`. Use `transport` to retrieve the system default flow.

`GET {{base_url}}/{{namespace}}/order-configs/{{order_config_id}}`

```php
$result = $fleetbase->orderConfigs->retrieveOrderConfig(
    [
        'order_config_id' => 'order_config_id-fixture',
    ],
    []
);
```

## Orders

### Cancel an Order

Cancels an order without deleting the order resource.

`DELETE {{base_url}}/{{namespace}}/orders/:id/cancel`

```php
$result = $fleetbase->orders->cancelOrder(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Capture Photo for Order

Captures proof photos for an order or order subject.

`POST {{base_url}}/{{namespace}}/orders/:id/capture-photo/:subjectId`

```php
$result = $fleetbase->orders->capturePhotoForOrder(
    [
        'id' => 'order_id-fixture',
        'subjectId' => 'subject_id-fixture',
        'body' => [
            'photos' => [
                'proof_photo_base64-fixture',
            ],
            'remarks' => 'Verified by Photo',
            'data' => [],
        ],
    ],
    []
);
```

### Capture QR Code for Order

Captures a QR code proof for an order or order subject. The response includes the updated proof data associated with the order.

`POST {{base_url}}/{{namespace}}/orders/:id/capture-qr/:subject-id`

```php
$result = $fleetbase->orders->captureQrCodeForOrder(
    [
        'id' => 'order_id-fixture',
        'subject-id' => '',
        'body' => [
            'code' => 'qr_code-fixture',
            'data' => [],
            'raw_data' => [],
        ],
    ],
    []
);
```

### Capture Signature for Order

Captures a signature proof for an order or order subject. Use this when a workflow requires signed proof of delivery or pickup.

`POST {{base_url}}/{{namespace}}/orders/:id/capture-signature/:subject-id`

```php
$result = $fleetbase->orders->captureSignatureForOrder(
    [
        'id' => 'order_id-fixture',
        'subject-id' => '',
        'body' => [
            'signature' => 'proof_signature_base64-fixture',
            'data' => [],
        ],
    ],
    []
);
```

### Complete an Order

Completes an order after all waypoints are complete.

`POST {{base_url}}/{{namespace}}/orders/:id/complete`

```php
$result = $fleetbase->orders->completeOrder(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Create an Order

Creates a new order for the current company. Provide an existing payload ID, an inline payload, a pickup/dropoff pair, or at least two waypoints; Fleetbase creates or attaches the payload before creating the order. You can assign a driver, vehicle, facilitator, customer, service quote, or schedule at creation time. When `dispatch` is true, Fleetbase dispatches the order after it is created unless the order belongs to an integrated vendor flow.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrder(
    [
        'body' => [
            'pickup' => 'Singapore 018971',
            'dropoff' => '321 Orchard Rd, Singapore',
            'waypoints' => [
                '10 Bayfront Avenue, Singapore 018956',
                '18 Marina Gardens Drive, Singapore 018953',
                '80 Mandai Lake Rd, Singapore 729826',
                '1 Beach Road, Singapore 189673',
            ],
            'dispatch' => false,
            'driver' => 'driver_id-fixture',
            'facilitator' => 'vendor_id-fixture',
            'customer' => 'contact_id-fixture',
            'meta' => [
                'Warehouse' => 'WAREHOUSE-123',
            ],
            'notes' => 'Order notes',
        ],
    ],
    []
);
```

### Create an Order using Complete Payload

Creates an order using the Complete Payload payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingCompletePayload(
    [
        'body' => [
            'pickup' => 'Singapore 018971',
            'dropoff' => '321 Orchard Rd, Singapore',
            'dispatch' => false,
            'driver' => 'driver_id-fixture',
            'customer' => 'contact_id-fixture',
            'notes' => 'Deliver through receiving bay.',
        ],
    ],
    []
);
```

### Create an Order using Coordinates

Creates an order with `pickup` and `dropoff` given as coordinate objects. `Place::createFromMixed` accepts a place as more than a public id. When the value is a coordinate pair it resolves through `createFromCoordinates`, so no address lookup is involved. Latitude and longitude are read through a list of aliases — `lat`, `latitude`, `x`, `0` and `lon`, `lng`, `longitude`, `y`, `1` — so several spellings are equivalent.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingCoordinates(
    [
        'body' => [
            'pickup' => [
                'latitude' => 1.2830632,
                'longitude' => 103.8579965,
            ],
            'dropoff' => [
                'lat' => 1.4043,
                'lng' => 103.793,
            ],
        ],
    ],
    []
);
```

### Create an Order using GeoJSON Points

Creates an order with `pickup` and `dropoff` given as GeoJSON Point objects. `Place::createFromMixed` recognises GeoJSON directly, so a client already holding GeoJSON geometry does not have to convert it. Note the GeoJSON axis order is `[longitude, latitude]`, the reverse of the coordinate-object form.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingGeojsonPoints(
    [
        'body' => [
            'pickup' => [
                'type' => 'Point',
                'coordinates' => [
                    103.8579965,
                    1.2830632,
                ],
            ],
            'dropoff' => [
                'type' => 'Point',
                'coordinates' => [
                    103.793,
                    1.4043,
                ],
            ],
        ],
    ],
    []
);
```

### Create an Order using Payload

Creates an order using the Payload payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingPayload(
    [
        'body' => [
            'payload' => [
                'pickup' => 'Singapore 018971',
                'dropoff' => '321 Orchard Rd, Singapore',
                'entities' => [
                    [
                        'name' => 'UltraHD 4K Smart TV',
                        'description' => '65-inch high-definition smart TV with vibrant colors and a sleek design.',
                        'currency' => 'USD',
                        'price' => 1200,
                    ],
                    [
                        'name' => 'Bluetooth Wireless Headphones',
                        'description' => 'Noise-cancelling, over-ear headphones with long-lasting battery life.',
                        'currency' => 'USD',
                        'price' => 250,
                    ],
                    [
                        'name' => 'Smart Fitness Watch',
                        'description' => 'Water-resistant fitness watch with heart rate monitor and GPS tracking.',
                        'currency' => 'USD',
                        'price' => 199.99,
                    ],
                ],
            ],
            'meta' => [
                'Warehouse' => 'WAREHOUSE-123',
            ],
            'notes' => 'Order notes',
        ],
    ],
    []
);
```

### Create an Order using Waypoints and Entities with Photos

Creates an order using the Waypoints and Entities with Photos payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingWaypointsAndEntitiesWithPhotos(
    [
        'body' => [
            'payload' => [
                'waypoints' => [
                    'Singapore 018971',
                    '321 Orchard Rd, Singapore',
                ],
                'entities' => [
                    [
                        'destination' => 0,
                        'name' => 'UltraHD 4K Smart TV',
                        'description' => '65-inch high-definition smart TV with vibrant colors and a sleek design.',
                        'currency' => 'USD',
                        'price' => 1200,
                        'photo' => 'https://cdn.thewirecutter.com/wp-content/media/2025/07/BEST-BUDGET-4K-TV-2048px-3185-2x1-1.jpg?width=1024&quality=75&crop=2:1&auto=webp',
                    ],
                    [
                        'destination' => 0,
                        'name' => 'Bluetooth Wireless Headphones',
                        'description' => 'Noise-cancelling, over-ear headphones with long-lasting battery life.',
                        'currency' => 'USD',
                        'price' => 250,
                        'photo' => 'https://cdn.thewirecutter.com/wp-content/media/2025/07/BEST-BUDGET-4K-TV-2048px-3185-2x1-1.jpg?width=1024&quality=75&crop=2:1&auto=webp',
                    ],
                    [
                        'destination' => 1,
                        'name' => 'Smart Fitness Watch',
                        'description' => 'Water-resistant fitness watch with heart rate monitor and GPS tracking.',
                        'currency' => 'USD',
                        'price' => 199.99,
                        'photo' => 'https://cdn.thewirecutter.com/wp-content/media/2025/07/BEST-BUDGET-4K-TV-2048px-3185-2x1-1.jpg?width=1024&quality=75&crop=2:1&auto=webp',
                    ],
                ],
            ],
            'meta' => [
                'Warehouse' => 'WAREHOUSE-123',
            ],
            'notes' => 'Order notes',
        ],
    ],
    []
);
```

### Create an Order using Waypoints and Entity Destinations

Creates an order using the Waypoints and Entity Destinations payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingWaypointsAndEntityDestinations(
    [
        'body' => [
            'payload' => [
                'waypoints' => [
                    'Singapore 018971',
                    '321 Orchard Rd, Singapore',
                ],
                'entities' => [
                    [
                        'destination' => 0,
                        'name' => 'UltraHD 4K Smart TV',
                        'description' => '65-inch high-definition smart TV with vibrant colors and a sleek design.',
                        'currency' => 'USD',
                        'price' => 1200,
                    ],
                    [
                        'destination' => 0,
                        'name' => 'Bluetooth Wireless Headphones',
                        'description' => 'Noise-cancelling, over-ear headphones with long-lasting battery life.',
                        'currency' => 'USD',
                        'price' => 250,
                    ],
                    [
                        'destination' => 1,
                        'name' => 'Smart Fitness Watch',
                        'description' => 'Water-resistant fitness watch with heart rate monitor and GPS tracking.',
                        'currency' => 'USD',
                        'price' => 199.99,
                    ],
                ],
            ],
            'meta' => [
                'Warehouse' => 'WAREHOUSE-123',
            ],
            'notes' => 'Order notes',
        ],
    ],
    []
);
```

### Create an Order using only Pickup Dropoff

Creates an order using the only Pickup Dropoff payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingOnlyPickupDropoff(
    [
        'body' => [
            'pickup' => 'Singapore 018971',
            'dropoff' => '321 Orchard Rd, Singapore',
        ],
    ],
    []
);
```

### Create an Order using only Waypoints

Creates an order using the only Waypoints payload shape. Promoted from a stored example so the shape is actually exercised: the Postman CLI runs requests, never examples, so these shapes had no coverage at all.

`POST {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->createOrderUsingOnlyWaypoints(
    [
        'body' => [
            'waypoints' => [
                [
                    1.3521,
                    103.8198,
                ],
                '10 Bayfront Avenue, Singapore 018956',
                '18 Marina Gardens Drive, Singapore 018953',
                '80 Mandai Lake Rd, Singapore 729826',
                '1 Beach Road, Singapore 189673',
                'Sentosa, Singapore',
            ],
        ],
    ],
    []
);
```

### Delete an Order

Deletes an order resource.

`DELETE {{base_url}}/{{namespace}}/orders/:id`

```php
$result = $fleetbase->orders->deleteOrder(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Dispatch an Order

Dispatches an order to an assigned or eligible driver. The response returns the order after dispatch state has been applied.

`PATCH {{base_url}}/{{namespace}}/orders/:id/dispatch`

```php
$result = $fleetbase->orders->dispatchOrder('order_id-fixture');
```

### Get Editable Entity Fields

Returns configured editable entity fields for an order.

`GET {{base_url}}/{{namespace}}/orders/:id/editable-entity-fields`

```php
$result = $fleetbase->orders->getEditableEntityFields(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Get Order Distance and Time

Returns and updates the order distance/time matrix.

`GET {{base_url}}/{{namespace}}/orders/:id/distance-and-time`

```php
$result = $fleetbase->orders->getOrderDistanceAndTime(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Get Order ETA

Returns ETA data for an order.

`GET {{base_url}}/{{namespace}}/orders/:id/eta`

```php
$result = $fleetbase->orders->getOrderEta(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### Get Order Next Activity

Returns the next workflow activity for an order. Use it to determine the next operational step available to the assigned driver or dispatcher.

`GET {{base_url}}/{{namespace}}/orders/:id/next-activity`

```php
$result = $fleetbase->orders->getOrderNextActivity(
    [
        'id' => 'order_id-fixture',
    ],
    [
        'query' => [
            'waypoint' => 'current_waypoint_id-fixture',
        ],
    ]
);
```

### Get Order Tracker

Returns public tracking data for an order.

`GET {{base_url}}/{{namespace}}/orders/:id/tracker`

```php
$result = $fleetbase->orders->getOrderTracker(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### List Order Comments

Lists comments attached to an order.

`GET {{base_url}}/{{namespace}}/orders/:id/comments`

```php
$result = $fleetbase->orders->listOrderComments(
    [
        'id' => 'order_id-fixture',
    ],
    []
);
```

### List Order Proofs

Lists proof of delivery resources for an order or subject.

`GET {{base_url}}/{{namespace}}/orders/:id/proofs/:subjectId`

```php
$result = $fleetbase->orders->listOrderProofs(
    [
        'id' => 'order_id-fixture',
        'subjectId' => 'subject_id-fixture',
    ],
    []
);
```

### Query Orders

Returns orders for the current company. Use filters such as `status`, `payload`, `customer`, `facilitator`, or `nearby` to narrow the result set.

`GET {{base_url}}/{{namespace}}/orders`

```php
$result = $fleetbase->orders->queryOrders(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
            'status' => 'created',
        ],
    ]
);
```

### Retrieve an Order

Retrieves a single order by ID. The response includes the public order fields plus loaded payload, tracking, assignment, customer, and facilitator data when available.

`GET {{base_url}}/{{namespace}}/orders/{{order_id}}`

```php
$result = $fleetbase->orders->retrieveOrder(
    [
        'order_id' => 'order_id-fixture',
    ],
    []
);
```

### Schedule an Order

Schedules an order for a specific date and optional time. Fleetbase parses the schedule using the supplied timezone or the company timezone.

`PATCH {{base_url}}/{{namespace}}/orders/:id/schedule`

```php
$result = $fleetbase->orders->scheduleOrder(
    [
        'id' => 'order_id-fixture',
        'body' => [
            'date' => '2024-02-11',
            'time' => '8am',
            'timezone' => 'Asia/Singapore',
        ],
    ],
    []
);
```

### Set Order Destination

Sets the destination waypoint or place for an order. The response returns the updated order after the destination is changed.

`PATCH {{base_url}}/{{namespace}}/orders/:id/set-destination/:placeId`

```php
$result = $fleetbase->orders->setOrderDestination(
    [
        'id' => 'order_id-fixture',
        'placeId' => 'waypoint_id-fixture',
    ],
    []
);
```

### Start an Order

Starts an order and transitions it into active execution. Use this when a driver or dispatcher begins fulfillment.

`POST {{base_url}}/{{namespace}}/orders/:id/start`

```php
$result = $fleetbase->orders->startOrder(
    [
        'id' => 'order_id-fixture',
        'body' => [
            'skip_dispatch' => false,
        ],
    ],
    []
);
```

### Update Order Activity

Updates the current activity state for an order. The response returns the order with the latest workflow activity applied.

`POST {{base_url}}/{{namespace}}/orders/:id/update-activity`

```php
$result = $fleetbase->orders->updateOrderActivity(
    [
        'id' => 'order_id-fixture',
        'body' => [
            'activity' => 'next_activity-fixture',
            'skip_dispatch' => false,
        ],
    ],
    []
);
```

### Update an Order

Updates an order and returns the updated order resource. You can update order metadata, notes, status, assignment fields, scheduling fields, proof-of-delivery settings, and payload details.

`PUT {{base_url}}/{{namespace}}/orders/:id`

```php
$result = $fleetbase->orders->updateOrder(
    [
        'id' => 'order_id-fixture',
        'body' => [
            'service_quote' => 'service_quote_id-fixture',
        ],
    ],
    []
);
```

## Organizations

### Get Current Organization

Returns the organization associated with the API key on the request. Use it to confirm which account a credential belongs to, and to obtain the organization's id for endpoints that address an organization by path.

`GET {{base_url}}/{{namespace}}/organizations/current`

```php
$result = $fleetbase->organizations->getCurrentOrganization(
    [],
    []
);
```

### List Organizations

Lists organizations available for driver onboarding and organization selection.

`GET {{base_url}}/{{namespace}}/organizations`

```php
$result = $fleetbase->organizations->listOrganizations(
    [],
    [
        'query' => [
            'limit' => '10',
            'with_driver_onboard' => 'false',
        ],
    ]
);
```

## Parts

### Create a Part

Create a part.

`POST {{base_url}}/{{namespace}}/parts`

```php
$result = $fleetbase->parts->createPart(
    [
        'body' => [
            'sku' => 'FLT-OIL-timestamp-fixture',
            'name' => 'Oil Filter',
            'quantity_on_hand' => 24,
            'unit_cost' => 1200,
            'currency' => 'USD',
            'status' => 'in_stock',
        ],
    ],
    []
);
```

### Delete a Part

Delete a part.

`DELETE {{base_url}}/{{namespace}}/parts/{{part_id}}`

```php
$result = $fleetbase->parts->deletePart(
    [
        'part_id' => 'part_id-fixture',
    ],
    []
);
```

### Query Parts

Query parts.

`GET {{base_url}}/{{namespace}}/parts`

```php
$result = $fleetbase->parts->queryParts(
    [],
    []
);
```

### Retrieve a Part

Retrieve a part.

`GET {{base_url}}/{{namespace}}/parts/{{part_id}}`

```php
$result = $fleetbase->parts->retrievePart(
    [
        'part_id' => 'part_id-fixture',
    ],
    []
);
```

### Update a Part

Update a part.

`PUT {{base_url}}/{{namespace}}/parts/{{part_id}}`

```php
$result = $fleetbase->parts->updatePart(
    [
        'part_id' => 'part_id-fixture',
        'body' => [
            'quantity_on_hand' => 18,
        ],
    ],
    []
);
```

## Payloads

### Create a Payload

Creates a payload containing route endpoints and optional entities. Provide either pickup/dropoff endpoints or a waypoint list, then attach entities as needed.

`POST {{base_url}}/{{namespace}}/payloads`

```php
$result = $fleetbase->payloads->createPayload(
    [
        'body' => [
            'pickup' => [
                'street1' => '10 Bayfront Avenue',
                'city' => 'Singapore',
                'postal_code' => '018956',
                'country' => 'SG',
            ],
            'dropoff' => [
                'street1' => '80 Mandai Lake Rd',
                'city' => 'Singapore',
                'postal_code' => '729826',
                'country' => 'SG',
            ],
            'type' => 'food_delivery',
        ],
    ],
    []
);
```

### Delete a Payload

Delete a Payload.

`DELETE {{base_url}}/{{namespace}}/payloads/:id`

```php
$result = $fleetbase->payloads->deletePayload(
    [
        'id' => 'payload_id-fixture',
    ],
    []
);
```

### Query Payloads

Returns payloads for the current company. Use pagination and sort parameters to page through payload records.

`GET {{base_url}}/{{namespace}}/payloads`

```php
$result = $fleetbase->payloads->queryPayloads(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Payload

Retrieve a Payload.

`GET {{base_url}}/{{namespace}}/payloads/:id`

```php
$result = $fleetbase->payloads->retrievePayload(
    [
        'id' => 'payload_id-fixture',
    ],
    []
);
```

### Update a Payload

Updates a payload's route endpoints, waypoints, entities, cash-on-delivery settings, or metadata. The response returns the updated payload with route and entity data.

`PUT {{base_url}}/{{namespace}}/payloads/:id`

```php
$result = $fleetbase->payloads->updatePayload(
    [
        'id' => 'payload_id-fixture',
        'body' => [
            'pickup' => [
                'street1' => '10 Bayfront Avenue',
                'city' => 'Singapore',
                'postal_code' => '018956',
                'country' => 'SG',
            ],
            'dropoff' => [
                'street1' => '80 Mandai Lake Rd',
                'city' => 'Singapore',
                'postal_code' => '729826',
                'country' => 'SG',
            ],
            'entities' => [
                [
                    'name' => 'UltraHD 4K Smart TV',
                    'description' => '65-inch high-definition smart TV with vibrant colors and a sleek design.',
                    'currency' => 'USD',
                    'price' => 1200,
                ],
                [
                    'name' => 'Bluetooth Wireless Headphones',
                    'description' => 'Noise-cancelling, over-ear headphones with long-lasting battery life.',
                    'currency' => 'USD',
                    'price' => 250,
                ],
                [
                    'name' => 'Smart Fitness Watch',
                    'description' => 'Water-resistant fitness watch with heart rate monitor and GPS tracking.',
                    'currency' => 'USD',
                    'price' => 199.99,
                ],
            ],
        ],
    ],
    []
);
```

## Places

### Create a Place

Creates a place for the current company. Provide structured address fields, a free-form `address` or `street1` value to geocode, or coordinates for Fleetbase to reverse geocode.

`POST {{base_url}}/{{namespace}}/places`

```php
$result = $fleetbase->places->createPlace(
    [
        'body' => [
            'name' => 'Central Park',
            'street1' => '830 5th Ave',
            'city' => 'New York',
            'province' => 'New York',
            'postal_code' => '10065',
            'neighborhood' => 'Manhattan',
            'district' => 'Midtown',
            'building' => 'Park Area',
            'country' => 'US',
            'phone' => '+12123106600',
            'type' => 'Park',
        ],
    ],
    []
);
```

### Delete a Place

Permanently deletes a place. It cannot be undone.

`DELETE {{base_url}}/{{namespace}}/places/:id`

```php
$result = $fleetbase->places->deletePlace(
    [
        'id' => 'place_id-fixture',
    ],
    []
);
```

### List all Places

Returns a paginated list of places for the current organization. Places are sorted by creation date unless another sort order is provided.

`GET {{base_url}}/{{namespace}}/places`

```php
$result = $fleetbase->places->listAllPlaces(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Query Places

Searches and filters places for the current organization. Use query and pagination parameters to find matching saved locations.

`GET {{base_url}}/{{namespace}}/places`

```php
$result = $fleetbase->places->queryPlaces(
    [],
    [
        'query' => [
            'query' => 'place_name-fixture',
            'limit' => '25',
            'offset' => '<string>',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Place

This endpoint allows you to retrieve a place object to view it's details.

`GET {{base_url}}/{{namespace}}/places/:id`

```php
$result = $fleetbase->places->retrievePlace(
    [
        'id' => 'place_id-fixture',
    ],
    []
);
```

### Search Places

Searches places by free-form query and optional lat/lng locale context.

`GET {{base_url}}/{{namespace}}/places/search`

```php
$result = $fleetbase->places->searchPlaces(
    [],
    [
        'query' => [
            'query' => 'place_query-fixture',
            'll' => 'place_ll-fixture',
            'locale' => 'locale-fixture',
        ],
    ]
);
```

### Update a Place

Updates a place by setting the fields included in the request. Send address fields, coordinates, owner assignment, type, phone, or metadata to change only those values.

`PUT {{base_url}}/{{namespace}}/places/:id`

```php
$result = $fleetbase->places->updatePlace(
    [
        'id' => 'place_id-fixture',
        'body' => [
            'name' => 'Central Park Edit',
            'street1' => '830 5th Ave a ',
            'city' => 'New York',
            'province' => 'New York',
            'postal_code' => '10065',
            'neighborhood' => 'Manhattan',
            'district' => 'Midtown',
            'building' => 'Park Area',
            'country' => 'US',
            'phone' => '+12123106600',
            'type' => 'Park',
        ],
    ],
    []
);
```

## Purchase Rates

### Create a Purchase Rate

Only ServiceQuote objects generated using a Payload object may be used to create PurchaseRate objects.

`POST {{base_url}}/{{namespace}}/purchase-rates`

```php
$result = $fleetbase->purchaseRates->createPurchaseRate(
    [
        'body' => [
            'service_quote' => 'service_quote_id-fixture',
        ],
    ],
    []
);
```

### Query Purchase Rates

This endpoint allows you to query purchase-rates you have created, it also provides paginated results on all the purchase-rates in your Fleetbase.

`GET {{base_url}}/{{namespace}}/purchase-rates`

```php
$result = $fleetbase->purchaseRates->queryPurchaseRates(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Purchase Rate

This endpoint allows you to retrieve a purchase-rate object to view it's details.

`GET {{base_url}}/{{namespace}}/purchase-rates/:id`

```php
$result = $fleetbase->purchaseRates->retrievePurchaseRate(
    [
        'id' => 'purchase_rate_id-fixture',
    ],
    []
);
```

## Sensors

### Create a Sensor

Create a sensor.

`POST {{base_url}}/{{namespace}}/sensors`

```php
$result = $fleetbase->sensors->createSensor(
    [
        'body' => [
            'name' => 'Cargo Temperature',
            'type' => 'temperature',
            'device' => 'device_id-fixture',
            'unit' => 'celsius',
            'status' => 'active',
            'min_threshold' => 0,
            'max_threshold' => 8,
        ],
    ],
    []
);
```

### Delete a Sensor

Delete a sensor.

`DELETE {{base_url}}/{{namespace}}/sensors/{{sensor_id}}`

```php
$result = $fleetbase->sensors->deleteSensor(
    [
        'sensor_id' => 'sensor_id-fixture',
    ],
    []
);
```

### Query Sensors

Query sensors.

`GET {{base_url}}/{{namespace}}/sensors`

```php
$result = $fleetbase->sensors->querySensors(
    [],
    []
);
```

### Retrieve a Sensor

Retrieve a sensor.

`GET {{base_url}}/{{namespace}}/sensors/{{sensor_id}}`

```php
$result = $fleetbase->sensors->retrieveSensor(
    [
        'sensor_id' => 'sensor_id-fixture',
    ],
    []
);
```

### Update a Sensor

Update a sensor.

`PUT {{base_url}}/{{namespace}}/sensors/{{sensor_id}}`

```php
$result = $fleetbase->sensors->updateSensor(
    [
        'sensor_id' => 'sensor_id-fixture',
        'body' => [
            'last_value' => '4.2',
            'last_reading_at' => '2026-05-07T08:30:00Z',
        ],
    ],
    []
);
```

## Service Areas

### Create a Service Area

A service area is created by simply providing a city, province or country in which Fleetbase will reverse geocode into a service area. If the service area cannot be reverse geocoded, Fleetbase will return an error.

`POST {{base_url}}/{{namespace}}/service-areas`

```php
$result = $fleetbase->serviceAreas->createServiceArea(
    [
        'body' => [
            'name' => 'Singapore',
            'type' => 'city',
            'latitude' => '1.3521',
            'longitude' => '103.8198',
            'radius' => '30000',
            'country' => 'SG',
            'status' => 'active',
        ],
    ],
    []
);
```

### Delete a Service Area

Use this endpoint to delete a service area, deleting a service area will also delete all of the zones within the service area.

`DELETE {{base_url}}/{{namespace}}/service-areas/:id`

```php
$result = $fleetbase->serviceAreas->deleteServiceArea(
    [
        'id' => 'service_area_id-fixture',
    ],
    []
);
```

### Query Service Areas

Returns service areas matching the supplied filters. Use this to find configured operating regions by name or other query parameters.

`GET {{base_url}}/{{namespace}}/service-areas`

```php
$result = $fleetbase->serviceAreas->queryServiceAreas(
    [],
    [
        'query' => [
            'name' => 'service_area_name-fixture',
        ],
    ]
);
```

### Retrieve a Service Area

This endpoint allows you to retrieve a service area object to view it's details.

`GET {{base_url}}/{{namespace}}/service-areas/:id`

```php
$result = $fleetbase->serviceAreas->retrieveServiceArea(
    [
        'id' => 'service_area_id-fixture',
    ],
    []
);
```

### Update a Service Area

You are only able to update the service area status

`PUT {{base_url}}/{{namespace}}/service-areas/:id`

```php
$result = $fleetbase->serviceAreas->updateServiceArea(
    [
        'id' => 'service_area_id-fixture',
        'body' => [
            'status' => 'active',
        ],
    ],
    []
);
```

## Service Quotes

### Query Service Quotes

This endpoint is used to get the ServiceRate quotes based on pickup point location and drop off point location, or payload. For some quotes such as parcel based, the payload must be included for calculation of the payload cost.

`GET {{base_url}}/{{namespace}}/service-quotes`

```php
$result = $fleetbase->serviceQuotes->queryServiceQuotes(
    [],
    [
        'query' => [
            'payload' => 'payload_id-fixture',
        ],
    ]
);
```

### Retrieve a Service Quote

Retrieves a service quote by id.

`GET {{base_url}}/{{namespace}}/service-quotes/:id`

```php
$result = $fleetbase->serviceQuotes->retrieveServiceQuote(
    [
        'id' => 'service_quote_id-fixture',
    ],
    []
);
```

## Service Rates

### Create a Service Rate

Create a Service Rate.

`POST {{base_url}}/{{namespace}}/service-rates`

```php
$result = $fleetbase->serviceRates->createServiceRate(
    [
        'body' => [
            'service_name' => 'Food Delivery',
            'service_type' => 'food_delivery',
            'rate_calculation_method' => 'per_meter',
            'currency' => 'USD',
            'base_fee' => 10,
            'per_meter_unit' => 'km',
            'per_meter_flat_rate_fee' => 25,
            'has_cod_fee' => true,
            'cod_calculation_method' => 'percentage',
            'cod_flat_fee' => 1,
            'cod_percent' => 0,
            'has_peak_hours_fee' => true,
            'peak_hours_calculation_method' => 'percentage',
            'peak_hours_flat_fee' => 3,
            'peak_hours_percent' => 0,
            'peak_hours_start' => '17:00',
            'peak_hours_end' => '18:45',
            'duration_terms' => 'Standard',
            'estimated_days' => 3,
        ],
    ],
    []
);
```

### Delete a Service Rate

Delete a Service Rate.

`DELETE {{base_url}}/{{namespace}}/service-rates/:id`

```php
$result = $fleetbase->serviceRates->deleteServiceRate(
    [
        'id' => 'service_rate_id-fixture',
    ],
    []
);
```

### Query Service Rates

List all service rates.

`GET {{base_url}}/{{namespace}}/service-rates`

```php
$result = $fleetbase->serviceRates->queryServiceRates(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'currency' => 'USD',
        ],
    ]
);
```

### Retrieve a Service Rate

Retrieves a service rate by ID.

`GET {{base_url}}/{{namespace}}/service-rates/:id`

```php
$result = $fleetbase->serviceRates->retrieveServiceRate(
    [
        'id' => 'service_rate_id-fixture',
    ],
    []
);
```

### Update a Service Rate

Update a Service Rate.

`PUT {{base_url}}/{{namespace}}/service-rates/:id`

```php
$result = $fleetbase->serviceRates->updateServiceRate(
    [
        'id' => 'service_rate_id-fixture',
        'body' => [
            'currency' => 'SGD',
            'base_fee' => 12.66,
            'estimated_days' => 6,
        ],
    ],
    []
);
```

## Tracking Numbers

### Create a Tracking Number

This endpoint allows you to retrieve a tracking-number object to view it's details.

`POST {{base_url}}/{{namespace}}/tracking-numbers`

```php
$result = $fleetbase->trackingNumbers->createTrackingNumber(
    [
        'body' => [
            'region' => 'SG',
            'owner' => 'order_id-fixture',
        ],
    ],
    []
);
```

### Decode Tracking Number QR

Decodes a tracking/entity/order QR code UUID and returns the matching resource.

`POST {{base_url}}/{{namespace}}/tracking-numbers/from-qr`

```php
$result = $fleetbase->trackingNumbers->decodeTrackingNumberQr(
    [
        'body' => [
            'code' => 'qr_code-fixture',
        ],
    ],
    []
);
```

### Delete a Tracking Number

Deletes a tracking number by ID.

`DELETE {{base_url}}/{{namespace}}/tracking-numbers/:id`

```php
$result = $fleetbase->trackingNumbers->deleteTrackingNumber(
    [
        'id' => 'tracking_number_id-fixture',
    ],
    []
);
```

### Query Tracking Numbers

This endpoint allows you to query tracking-numbers you have created, it also provides paginated results on all the tracking-numbers in your Fleetbase.

`GET {{base_url}}/{{namespace}}/tracking-numbers`

```php
$result = $fleetbase->trackingNumbers->queryTrackingNumbers(
    [],
    [
        'query' => [
            'query' => 'SG',
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Tracking Number

This endpoint allows you to retrieve a tracking-number object to view it's details.

`GET {{base_url}}/{{namespace}}/tracking-numbers/:id`

```php
$result = $fleetbase->trackingNumbers->retrieveTrackingNumber(
    [
        'id' => 'tracking_number_id-fixture',
    ],
    []
);
```

## Tracking Statuses

### Create a Tracking Status

Create a new Tracking Status.

`POST {{base_url}}/{{namespace}}/tracking-statuses`

```php
$result = $fleetbase->trackingStatuses->createTrackingStatus(
    [
        'body' => [
            'status' => 'Delivery is en-route',
            'code' => 'delivery-en-route',
            'details' => 'Our driver has picked up your order and is on the way to your address!',
            'tracking_number' => 'tracking_number_id-fixture',
            'location' => [
                1.3521,
                103.8198,
            ],
            'city' => 'Singapore',
        ],
    ],
    []
);
```

### Delete a Tracking Status

Delete a Tracking Status.

`DELETE {{base_url}}/{{namespace}}/tracking-statuses/:id`

```php
$result = $fleetbase->trackingStatuses->deleteTrackingStatus(
    [
        'id' => 'tracking_status_id-fixture',
    ],
    []
);
```

### Query Tracking Statuses

List all Tracking Statuses

`GET {{base_url}}/{{namespace}}/tracking-statuses`

```php
$result = $fleetbase->trackingStatuses->queryTrackingStatuses(
    [],
    [
        'query' => [
            'limit' => '25',
            'tracking_number' => 'tracking_number_id-fixture',
        ],
    ]
);
```

### Retrieve a Tracking Status

Retrieve a Tracking Status.

`GET {{base_url}}/{{namespace}}/tracking-statuses/:id`

```php
$result = $fleetbase->trackingStatuses->retrieveTrackingStatus(
    [
        'id' => 'tracking_status_id-fixture',
    ],
    []
);
```

### Update a Tracking Status

Updates an existing tracking status. The response returns the tracking status with the new values applied.

`PUT {{base_url}}/{{namespace}}/tracking-statuses/:id`

```php
$result = $fleetbase->trackingStatuses->updateTrackingStatus(
    [
        'id' => 'tracking_status_id-fixture',
        'body' => [
            'country' => 'SG',
        ],
    ],
    []
);
```

## Vehicles

### Create a Vehicle

Creates a vehicle for the current company. Send VIN, make/model fields, assignment fields, status, location, capacity, or orchestrator constraints as needed.

`POST {{base_url}}/{{namespace}}/vehicles`

```php
$result = $fleetbase->vehicles->createVehicle(
    [
        'body' => [
            'vin' => '1GCGSBEA0G1111111',
            'year' => 2023,
            'make' => 'Toyota',
            'model' => 'Camry',
            'trim' => 'SE',
            'plate_number' => 'ABC123',
            'status' => 'maintenance',
            'online' => false,
        ],
    ],
    []
);
```

### Delete a Vehicle

Permanently deletes a `Vehicle`. It cannot be undone.

`DELETE {{base_url}}/{{namespace}}/vehicles/:id`

```php
$result = $fleetbase->vehicles->deleteVehicle(
    [
        'id' => 'vehicle_id-fixture',
    ],
    []
);
```

### Query Vehicles

This endpoint allows you to query vehicles you have created, it also provides paginated results on all the vehicles in your Fleetbase.

`GET {{base_url}}/{{namespace}}/vehicles`

```php
$result = $fleetbase->vehicles->queryVehicles(
    [],
    [
        'query' => [
            'query' => 'vehicle_name-fixture',
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a Vehicle

Retrieve details for a specific `Vehicle`.

`GET {{base_url}}/{{namespace}}/vehicles/:id`

```php
$result = $fleetbase->vehicles->retrieveVehicle(
    [
        'id' => 'vehicle_id-fixture',
    ],
    []
);
```

### Track Vehicle

`PATCH {{base_url}}/{{namespace}}/vehicles/:id/track`

```php
$result = $fleetbase->vehicles->trackVehicle(
    [
        'id' => 'vehicle_id-fixture',
    ],
    []
);
```

### Update a Vehicle

Updates a vehicle's identity, operational status, vendor assignment, location, capacity, or orchestrator constraints. Updating the VIN refreshes decoded VIN data.

`PUT {{base_url}}/{{namespace}}/vehicles/:id`

```php
$result = $fleetbase->vehicles->updateVehicle(
    [
        'id' => 'vehicle_id-fixture',
        'body' => [
            'plate_number' => 'ABC123',
            'status' => 'operational',
            'latitude' => 40.7484,
            'longitude' => -73.9857,
            'speed' => 90,
        ],
    ],
    []
);
```

## Vendors

### Create a Vendor

Creates a vendor for the current company. Vendors can be assigned to orders, vehicles, drivers, places, and facilitator workflows.

`POST {{base_url}}/{{namespace}}/vendors`

```php
$result = $fleetbase->vendors->createVendor(
    [
        'body' => [
            'name' => 'ABC Corporation',
            'type' => 'Supplier',
            'email' => 'abc@example.com',
            'phone' => '1234567890',
        ],
    ],
    []
);
```

### Delete a Vendor

Use this endpoint to delete a vendor.

`DELETE {{base_url}}/{{namespace}}/vendors/:id`

```php
$result = $fleetbase->vendors->deleteVendor(
    [
        'id' => 'vendor_id-fixture',
    ],
    []
);
```

### Query Vendors

Returns vendors for the current company. Use search, pagination, and sort parameters to narrow the result set.

`GET {{base_url}}/{{namespace}}/vendors`

```php
$result = $fleetbase->vendors->queryVendors(
    [],
    [
        'query' => [
            'id' => 'vendor_id-fixture',
        ],
    ]
);
```

### Retrieve a Vendor

This endpoint allows you to retrieve a vendor object to view it's details.

`GET {{base_url}}/{{namespace}}/vendors/:id`

```php
$result = $fleetbase->vendors->retrieveVendor(
    [
        'id' => 'vendor_id-fixture',
    ],
    []
);
```

### Update a Vendor

Updates a vendor's profile, primary address, type, contact fields, or metadata.

`PUT {{base_url}}/{{namespace}}/vendors/:id`

```php
$result = $fleetbase->vendors->updateVendor(
    [
        'id' => 'vendor_id-fixture',
        'body' => [
            'name' => 'ABC Corporation',
            'type' => 'Supplier',
            'email' => 'abc@example.com',
            'phone' => '1234567890',
        ],
    ],
    []
);
```

## Work Orders

### Create a Work Order

Create a work order.

`POST {{base_url}}/{{namespace}}/work-orders`

```php
$result = $fleetbase->workOrders->createWorkOrder(
    [
        'body' => [
            'subject' => 'Replace rear tire',
            'category' => 'corrective_maintenance',
            'status' => 'open',
            'priority' => 'high',
            'target_type' => 'fleet-ops:vehicle',
            'target' => 'vehicle_id-fixture',
            'assignee_type' => 'fleet-ops:vendor',
            'assignee' => 'vendor_id-fixture',
        ],
    ],
    []
);
```

### Delete a Work Order

Delete a work order.

`DELETE {{base_url}}/{{namespace}}/work-orders/{{work_order_id}}`

```php
$result = $fleetbase->workOrders->deleteWorkOrder(
    [
        'work_order_id' => 'work_order_id-fixture',
    ],
    []
);
```

### Query Work Orders

Query work orders.

`GET {{base_url}}/{{namespace}}/work-orders`

```php
$result = $fleetbase->workOrders->queryWorkOrders(
    [],
    []
);
```

### Retrieve a Work Order

Retrieve a work order.

`GET {{base_url}}/{{namespace}}/work-orders/{{work_order_id}}`

```php
$result = $fleetbase->workOrders->retrieveWorkOrder(
    [
        'work_order_id' => 'work_order_id-fixture',
    ],
    []
);
```

### Send Work Order

Send this work order to its assigned vendor or contact.

`POST {{base_url}}/{{namespace}}/work-orders/{{work_order_id}}/send`

```php
$result = $fleetbase->workOrders->sendWorkOrder(
    [
        'work_order_id' => 'work_order_id-fixture',
    ],
    []
);
```

### Update a Work Order

Update a work order.

`PUT {{base_url}}/{{namespace}}/work-orders/{{work_order_id}}`

```php
$result = $fleetbase->workOrders->updateWorkOrder(
    [
        'work_order_id' => 'work_order_id-fixture',
        'body' => [
            'status' => 'in_progress',
        ],
    ],
    []
);
```

## Zones

### Create a Zone

Creates a zone inside a service area. Provide either a GeoJSON boundary or a center point with radius, and Fleetbase will store the zone for geofencing and service coverage checks.

`POST {{base_url}}/{{namespace}}/zones`

```php
$result = $fleetbase->zones->createZone(
    [
        'body' => [
            'name' => 'Center of Singapore',
            'service_area' => 'service_area_id-fixture',
            'color' => '#66e0ff',
            'stroke_color' => '#00bfff',
            'border' => [
                'type' => 'Polygon',
                'bbox' => [
                    103.867493,
                    1.35085,
                    103.912125,
                    1.383113,
                ],
                'coordinates' => [
                    [
                        [
                            103.907661,
                            1.362863,
                        ],
                        [
                            103.892555,
                            1.357714,
                        ],
                        [
                            103.891525,
                            1.353252,
                        ],
                        [
                            103.883629,
                            1.35085,
                        ],
                        [
                            103.874702,
                            1.351193,
                        ],
                        [
                            103.870583,
                            1.358744,
                        ],
                        [
                            103.867493,
                            1.368354,
                        ],
                        [
                            103.870926,
                            1.377621,
                        ],
                        [
                            103.875732,
                            1.38174,
                        ],
                        [
                            103.886032,
                            1.383113,
                        ],
                        [
                            103.900452,
                            1.383113,
                        ],
                        [
                            103.909721,
                            1.381397,
                        ],
                        [
                            103.912125,
                            1.374189,
                        ],
                        [
                            103.907661,
                            1.362863,
                        ],
                    ],
                ],
            ],
        ],
    ],
    []
);
```

### Delete a Zone

Use this endpoint to delete a zone.

`DELETE {{base_url}}/{{namespace}}/zones/:id`

```php
$result = $fleetbase->zones->deleteZone(
    [
        'id' => 'zone_id-fixture',
    ],
    []
);
```

### Query Zones

Returns zones matching the supplied filters. Use this to find configured geofences by name or other query parameters.

`GET {{base_url}}/{{namespace}}/zones`

```php
$result = $fleetbase->zones->queryZones(
    [],
    [
        'query' => [
            'name' => 'zone_name-fixture',
        ],
    ]
);
```

### Retrieve a zone

Retrieves a single zone by ID. The response includes the zone geometry, display styling, and service area relationship.

`GET {{base_url}}/{{namespace}}/zones/:id`

```php
$result = $fleetbase->zones->retrieveZone(
    [
        'id' => 'zone_id-fixture',
    ],
    []
);
```

### Update a Zone

You can update all properties of the Zone.

`PUT {{base_url}}/{{namespace}}/zones/:id`

```php
$result = $fleetbase->zones->updateZone(
    [
        'id' => 'zone_id-fixture',
        'body' => [
            'color' => '#ff00000',
        ],
    ],
    []
);
```

## Chat Channels

### Add Participant

Adds a user in the current organization to an existing chat channel. The response returns the created chat participant resource.

`POST {{base_url}}/{{namespace}}/chat-channels/:id/add-participant`

```php
$result = $fleetbase->chatChannels->addParticipant(
    [
        'id' => 'chat_channel_id-fixture',
        'body' => [
            'user' => 'user_id-fixture',
        ],
    ],
    []
);
```

### Create Chat Channel

Creates a chat channel for the current organization. Include participant user IDs to add those users to the channel immediately after it is created.

`POST {{base_url}}/{{namespace}}/chat-channels`

```php
$result = $fleetbase->chatChannels->createChatChannel(
    [
        'body' => [
            'name' => 'Dispatch',
            'participants' => [
                'user_id-fixture',
            ],
        ],
    ],
    []
);
```

### Create Read Receipt

Marks a chat message as read for a participant. If a receipt already exists for the message and participant, Fleetbase returns the existing receipt.

`POST {{base_url}}/{{namespace}}/chat-channels/read-message/:chatMessageId`

```php
$result = $fleetbase->chatChannels->createReadReceipt(
    [
        'chatMessageId' => 'chat_message_id-fixture',
        'body' => [
            'participant' => 'chat_participant_id-fixture',
        ],
    ],
    []
);
```

### Delete Chat Channel

Deletes a chat channel by ID. The response returns a deleted-resource envelope for the removed channel.

`DELETE {{base_url}}/{{namespace}}/chat-channels/:id`

```php
$result = $fleetbase->chatChannels->deleteChatChannel(
    [
        'id' => 'chat_channel_id-fixture',
    ],
    []
);
```

### Delete Message

Deletes a chat message by ID. Use this when a previously sent message should be removed from the channel feed.

`DELETE {{base_url}}/{{namespace}}/chat-channels/delete-message/:chatMessageId`

```php
$result = $fleetbase->chatChannels->deleteMessage(
    [
        'chatMessageId' => 'chat_message_id-fixture',
    ],
    []
);
```

### List Available Participants

Lists users in the current organization that can be added to a chat channel. When a channel ID is provided, users already participating in that channel are excluded.

`GET {{base_url}}/{{namespace}}/chat-channels/available-participants`

```php
$result = $fleetbase->chatChannels->listAvailableParticipants(
    [],
    [
        'query' => [
            'channel' => 'chat_channel_id-fixture',
        ],
    ]
);
```

### Query Chat Channels

Returns chat channels visible to the current organization. Use query parameters to filter, sort, and paginate the result set.

`GET {{base_url}}/{{namespace}}/chat-channels`

```php
$result = $fleetbase->chatChannels->queryChatChannels(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Remove Participant

Removes a participant from a chat channel by participant ID. The channel remains active for the remaining participants.

`DELETE {{base_url}}/{{namespace}}/chat-channels/remove-participant/:participantId`

```php
$result = $fleetbase->chatChannels->removeParticipant(
    [
        'participantId' => 'chat_participant_id-fixture',
    ],
    []
);
```

### Retrieve Chat Channel

Retrieves a chat channel by ID, including its participants, feed, and latest message metadata.

`GET {{base_url}}/{{namespace}}/chat-channels/:id`

```php
$result = $fleetbase->chatChannels->retrieveChatChannel(
    [
        'id' => 'chat_channel_id-fixture',
    ],
    []
);
```

### Send Message

Sends a message to a chat channel as an existing chat participant. File IDs can be included to attach previously uploaded files to the message.

`POST {{base_url}}/{{namespace}}/chat-channels/:id/send-message`

```php
$result = $fleetbase->chatChannels->sendMessage(
    [
        'id' => 'chat_channel_id-fixture',
        'body' => [
            'sender' => 'chat_participant_id-fixture',
            'content' => 'Hello from Fleetbase API',
            'files' => [],
        ],
    ],
    []
);
```

### Update Chat Channel

Updates a chat channel's name. The response returns the updated chat channel resource.

`PUT {{base_url}}/{{namespace}}/chat-channels/:id`

```php
$result = $fleetbase->chatChannels->updateChatChannel(
    [
        'id' => 'chat_channel_id-fixture',
        'body' => [
            'name' => 'Dispatch Updates',
        ],
    ],
    []
);
```

## Comments

### Create Comment

Creates a comment on a subject resource or as a reply to an existing comment. Provide either a subject reference or a parent comment ID with the comment content.

`POST {{base_url}}/{{namespace}}/comments`

```php
$result = $fleetbase->comments->createComment(
    [
        'body' => [
            'content' => 'Example comment',
            'subject' => [
                'id' => 'file_id-fixture',
                'type' => 'file',
            ],
        ],
    ],
    []
);
```

### Delete Comment

Deletes a comment by ID. The response returns a deleted-resource envelope for the removed comment.

`DELETE {{base_url}}/{{namespace}}/comments/:id`

```php
$result = $fleetbase->comments->deleteComment(
    [
        'id' => 'comment_id-fixture',
    ],
    []
);
```

### Query Comments

Returns comments for the current organization. Use query parameters to filter, sort, and paginate the result set.

`GET {{base_url}}/{{namespace}}/comments`

```php
$result = $fleetbase->comments->queryComments(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve Comment

Retrieves a comment by ID, including its author and any nested replies returned by the API resource.

`GET {{base_url}}/{{namespace}}/comments/:id`

```php
$result = $fleetbase->comments->retrieveComment(
    [
        'id' => 'comment_id-fixture',
    ],
    []
);
```

### Update Comment

Updates the content of an existing comment. The subject and parent linkage are not changed by this endpoint.

`PUT {{base_url}}/{{namespace}}/comments/:id`

```php
$result = $fleetbase->comments->updateComment(
    [
        'id' => 'comment_id-fixture',
        'body' => [
            'content' => 'Updated comment',
        ],
    ],
    []
);
```

## Files

### Delete a File

Deletes a file record by ID. The response returns a deleted-resource envelope for the removed file.

`DELETE {{base_url}}/{{namespace}}/files/:id`

```php
$result = $fleetbase->files->deleteFile(
    [
        'id' => 'file_id-fixture',
    ],
    []
);
```

### Download File

Downloads the binary contents of a file by ID. The API streams the stored file using its original filename.

`GET {{base_url}}/{{namespace}}/files/:id/download`

```php
$result = $fleetbase->files->downloadFile(
    [
        'id' => 'file_id-fixture',
    ],
    []
);
```

### Query Files

Returns uploaded files for the current organization. Use query parameters to filter, sort, and paginate the result set.

`GET {{base_url}}/{{namespace}}/files`

```php
$result = $fleetbase->files->queryFiles(
    [],
    [
        'query' => [
            'limit' => '25',
            'offset' => '0',
            'sort' => 'created_at',
        ],
    ]
);
```

### Retrieve a File

Retrieves a file record by ID, including its URL, original filename, content type, size, caption, and metadata.

`GET {{base_url}}/{{namespace}}/files/:id`

```php
$result = $fleetbase->files->retrieveFile(
    [
        'id' => 'file_id-fixture',
    ],
    []
);
```

### Update File

Updates a file record's caption, metadata, or original filename. The uploaded binary object is not replaced.

`PUT {{base_url}}/{{namespace}}/files/:id`

```php
$result = $fleetbase->files->updateFile(
    [
        'id' => 'file_id-fixture',
        'body' => [
            'caption' => 'Updated caption',
            'meta' => [],
        ],
    ],
    []
);
```

### Upload Base64 File

Creates a file from base64-encoded data. Fleetbase stores the decoded file, creates a file record, and optionally associates it with a subject resource.

`POST {{base_url}}/{{namespace}}/files/base64`

```php
$result = $fleetbase->files->uploadBase64File(
    [
        'body' => [
            'data' => 'base64_file_data-fixture',
            'file_name' => 'example.png',
            'file_type' => 'image',
            'content_type' => 'image/png',
            'path' => 'uploads',
        ],
    ],
    []
);
```

### Upload File

Uploads a multipart file and creates a file record. The response includes the stored file URL and metadata captured from the upload.

`POST {{base_url}}/{{namespace}}/files`

```php
$result = $fleetbase->files->uploadFile(
    [],
    [
        'multipart' => [
            [
                'name' => 'file',
                'contents' => 'replace-with-file-contents',
            ],
            [
                'name' => 'path',
                'contents' => 'uploads',
            ],
            [
                'name' => 'type',
                'contents' => 'attachment',
            ],
        ],
    ]
);
```

## Organizations

### Get Current Organization

Returns the organization associated with the API credential.

`GET {{base_url}}/{{namespace}}/organizations/current`

```php
$result = $fleetbase->organizations->getCurrentOrganization(
    [],
    []
);
```
