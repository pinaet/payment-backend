Please wait...
<script>
    window.onload = function() {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("/ttb/2c2p/card/execute-request") }}';
        form.style.display = 'none';
    
        // Create CSRF token input
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}'; // Get the CSRF token
        form.appendChild(csrfInput);
    
        var params = {!! json_encode(compact('product_id', 'reference_order', 'amount', 'currency', 'customer_id', 'customer_name', 'customer_email', 'customer_phone', 'autoredirect', 'posturl', 'product_name')) !!};
    
        for (var key in params) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        }
    
        document.body.appendChild(form);
        form.submit();
    };
    </script>
    