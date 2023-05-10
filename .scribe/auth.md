# Authenticating requests

To authenticate requests, include a **`X-XSRF-TOKEN`** header with the value **`"{YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

You can retrieve your token by making a request to /sanctum/csrf-cookie
