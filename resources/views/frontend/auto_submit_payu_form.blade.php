<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayU...</title>
</head>
<body>
    <h1>Redirecting to PayU...</h1>

    <form id="payuForm" method="POST" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
        @foreach ($payuFormData as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="submit" value="Submit" style="display: none;">
    </form>

    <script>
        // Submit the form automatically when the page loads
        document.getElementById("payuForm").submit();
    </script>
</body>
</html>
