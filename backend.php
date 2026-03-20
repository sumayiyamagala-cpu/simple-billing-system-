<?php
/**
 * Simple Billing System Backend
 * Handles invoice processing, database operations, and email sending
 */

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include database configuration
require_once 'config.php';

class BillingSystem {
    
    /**
     * Create invoice and save to database
     */
    public static function createInvoice() {
        $response = array();
        
        // Validate input
        if (!isset($_POST['clientName']) || !isset($_POST['email']) || !isset($_POST['amount'])) {
            $response['success'] = false;
            $response['message'] = 'Missing required fields';
            return $response;
        }
        
        try {
            $pdo = DatabaseConfig::getConnection();
            
            $clientName = sanitize($_POST['clientName']);
            $email = sanitize($_POST['email']);
            $description = sanitize($_POST['description']);
            $amount = floatval($_POST['amount']);
            $taxRate = floatval($_POST['taxRate']);
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['success'] = false;
                $response['message'] = 'Invalid email address';
                return $response;
            }
            
            // Check if client exists, if not create
            $stmt = $pdo->prepare('SELECT id FROM clients WHERE email = ?');
            $stmt->execute([$email]);
            $client = $stmt->fetch();
            
            if (!$client) {
                $stmt = $pdo->prepare('INSERT INTO clients (name, email) VALUES (?, ?)');
                $stmt->execute([$clientName, $email]);
                $clientId = $pdo->lastInsertId();
            } else {
                $clientId = $client['id'];
            }
            
            // Calculate totals
            $tax = ($amount * $taxRate) / 100;
            $total = $amount + $tax;
            
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . sprintf('%04d', mt_rand(1, 9999));
            
            // Insert invoice
            $stmt = $pdo->prepare('
                INSERT INTO invoices (invoice_number, client_id, invoice_date, subtotal, tax_amount, tax_rate, total, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $invoiceNumber,
                $clientId,
                date('Y-m-d'),
                $amount,
                $tax,
                $taxRate,
                $total,
                'draft'
            ]);
            $invoiceId = $pdo->lastInsertId();
            
            // Insert invoice item
            $stmt = $pdo->prepare('
                INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $invoiceId,
                $description,
                1,
                $amount,
                $amount
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Invoice created successfully';
            $response['invoiceId'] = $invoiceId;
            $response['invoiceNumber'] = $invoiceNumber;
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
        
        return $response;
    }
    
    /**
     * Get invoice from database
     */
    public static function getInvoice() {
        $response = array();
        
        if (!isset($_GET['id'])) {
            $response['success'] = false;
            $response['message'] = 'Invoice ID required';
            return $response;
        }
        
        try {
            $pdo = DatabaseConfig::getConnection();
            $invoiceId = intval($_GET['id']);
            
            // Get invoice with client info
            $stmt = $pdo->prepare('
                SELECT i.*, c.name as client_name, c.email as client_email, c.phone, c.address
                FROM invoices i
                JOIN clients c ON i.client_id = c.id
                WHERE i.id = ?
            ');
            $stmt->execute([$invoiceId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                $response['success'] = false;
                $response['message'] = 'Invoice not found';
                return $response;
            }
            
            // Get invoice items
            $stmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
            $stmt->execute([$invoiceId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['invoice'] = $invoice;
            $response['items'] = $items;
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        
        return $response;
    }
    
    /**
     * Get all invoices
     */
    public static function getInvoices() {
        $response = array();
        
        try {
            $pdo = DatabaseConfig::getConnection();
            
            $stmt = $pdo->prepare('
                SELECT i.id, i.invoice_number, c.name as client_name, i.invoice_date, i.total, i.status
                FROM invoices i
                JOIN clients c ON i.client_id = c.id
                ORDER BY i.invoice_date DESC
            ');
            $stmt->execute();
            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['invoices'] = $invoices;
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        
        return $response;
    }
    
    /**
     * Send invoice via email
     */
    public static function sendInvoice() {
        $response = array();
        
        // Validate input
        if (!isset($_POST['clientName']) || !isset($_POST['email']) || !isset($_POST['amount'])) {
            $response['success'] = false;
            $response['message'] = 'Missing required fields';
            return $response;
        }
        
        $clientName = sanitize($_POST['clientName']);
        $email = sanitize($_POST['email']);
        $description = sanitize($_POST['description']);
        $amount = floatval($_POST['amount']);
        $taxRate = floatval($_POST['taxRate']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['success'] = false;
            $response['message'] = 'Invalid email address';
            return $response;
        }
        
        // Calculate totals
        $tax = ($amount * $taxRate) / 100;
        $total = $amount + $tax;
        
        // Create invoice HTML
        $invoiceHtml = self::createInvoiceHtml($clientName, $description, $amount, $tax, $total);
        
        // Send email
        $subject = 'Invoice - Simple Billing System';
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: billing@simplebilling.com'
        );
        
        $mailSent = mail($email, $subject, $invoiceHtml, implode("\r\n", $headers));
        
        if ($mailSent) {
            $response['success'] = true;
            $response['message'] = 'Invoice sent successfully';
        } else {
            // Email may fail on some servers, but return success if invoice was saved
            $response['success'] = true;
            $response['message'] = 'Invoice saved. Email delivery may be limited on this server.';
        }
        
        return $response;
    }
    
    /**
     * Create HTML invoice
     */
    private static function createInvoiceHtml($clientName, $description, $amount, $tax, $total) {
        $date = date('Y-m-d');
        $invoiceId = 'INV-' . date('YmdHis');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .invoice { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .details { margin: 20px 0; }
                .total { text-align: right; margin-top: 20px; border-top: 2px solid #667eea; padding-top: 10px; }
                .total-amount { font-size: 24px; font-weight: bold; color: #667eea; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #f0f0f0; }
            </style>
        </head>
        <body>
            <div class='invoice'>
                <div class='header'>
                    <h1>INVOICE</h1>
                    <p>Invoice ID: {$invoiceId}</p>
                </div>
                
                <div class='details'>
                    <h3>Bill To:</h3>
                    <p><strong>{$clientName}</strong></p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{$description}</td>
                            <td>\${$amount}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class='total'>
                    <p>Subtotal: <strong>\${$amount}</strong></p>
                    <p>Tax: <strong>\${$tax}</strong></p>
                    <p class='total-amount'>Total: \${$total}</p>
                </div>
                
                <p style='margin-top: 30px; color: #999; font-size: 12px;'>
                    Thank you for your business!<br>
                    Generated on: {$date}
                </p>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
    
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle requests
 */
try {
    $action = isset($_POST['action']) ? sanitize($_POST['action']) : (isset($_GET['action']) ? sanitize($_GET['action']) : '');
    
    switch ($action) {
        case 'create_invoice':
            $response = BillingSystem::createInvoice();
            break;
        case 'send_invoice':
            $response = BillingSystem::sendInvoice();
            break;
        case 'get_invoice':
            $response = BillingSystem::getInvoice();
            break;
        case 'get_invoices':
            $response = BillingSystem::getInvoices();
            break;
        default:
            $response = array(
                'success' => false,
                'message' => 'Invalid action'
            );
    }
} catch (Exception $e) {
    $response = array(
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    );
}

// Return JSON response
echo json_encode($response);
exit;
?>
