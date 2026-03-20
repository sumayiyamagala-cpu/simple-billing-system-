document.getElementById('billingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const clientName = document.getElementById('clientName').value;
    const email = document.getElementById('email').value;
    const description = document.getElementById('description').value;
    const amount = parseFloat(document.getElementById('amount').value);
    const taxRate = parseFloat(document.getElementById('taxRate').value);
    
    // Calculate totals
    const tax = (amount * taxRate) / 100;
    const total = amount + tax;
    
    // Format currency
    const formatCurrency = (num) => {
        return '$' + num.toFixed(2);
    };
    
    // Get current date
    const today = new Date();
    const dateStr = today.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Update invoice display
    document.getElementById('invoiceDate').textContent = `Date: ${dateStr}`;
    document.getElementById('invoiceClient').textContent = clientName;
    document.getElementById('invoiceEmail').textContent = email;
    document.getElementById('invoiceDesc').textContent = description;
    document.getElementById('invoiceAmt').textContent = formatCurrency(amount);
    document.getElementById('subtotal').textContent = formatCurrency(amount);
    document.getElementById('tax').textContent = formatCurrency(tax);
    document.getElementById('total').textContent = formatCurrency(total);
    
    // Show invoice section
    document.getElementById('invoiceSection').style.display = 'block';
    
    // Scroll to invoice
    document.getElementById('invoiceSection').scrollIntoView({ behavior: 'smooth' });
});

// Send invoice via email (calls PHP backend)
function sendInvoice() {
    const clientName = document.getElementById('clientName').value;
    const email = document.getElementById('email').value;
    const description = document.getElementById('description').value;
    const amount = document.getElementById('amount').value;
    const taxRate = document.getElementById('taxRate').value;
    
    // Send data to PHP backend
    const formData = new FormData();
    formData.append('action', 'send_invoice');
    formData.append('clientName', clientName);
    formData.append('email', email);
    formData.append('description', description);
    formData.append('amount', amount);
    formData.append('taxRate', taxRate);
    
    fetch('backend.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invoice sent successfully to ' + email);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send invoice');
    });
}
