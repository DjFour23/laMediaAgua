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
        <?php $__currentLoopData = $payuFormData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <input type="submit" value="Submit" style="display: none;">
    </form>

    <script>
        // Submit the form automatically when the page loads
        document.getElementById("payuForm").submit();
    </script>
</body>
</html>
<?php /**PATH D:\Repositorios\laMediaAgua\resources\views/frontend/auto_submit_payu_form.blade.php ENDPATH**/ ?>