<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

$customers = getCustomers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $customerId = $_POST['customer_id'];
    $date = $_POST['date'];
    $description = $_POST['description'] ?? '';
    
    $items = [];
    $totalAmount = 0;
    
    foreach ($_POST['item_description'] as $index => $itemDesc) {
        $quantity = floatval($_POST['item_quantity'][$index]);
        $price = floatval($_POST['item_price'][$index]);
        $isTaxIncluded = isset($_POST['item_tax_included'][$index]);
        
        $subtotal = $quantity * $price;
        
        if ($isTaxIncluded) {
            $vatAmount = $subtotal * VAT_RATE / (1 + VAT_RATE);
            $netAmount = $subtotal - $vatAmount;
        } else {
            $vatAmount = $subtotal * VAT_RATE;
            $netAmount = $subtotal;
        }
        
        $items[] = [
            'description' => $itemDesc,
            'quantity' => $quantity,
            'price' => $price,
            'net_amount' => $netAmount,
            'vat_amount' => $vatAmount,
            'total_amount' => $netAmount + $vatAmount,
            'is_tax_included' => $isTaxIncluded
        ];
        
        $totalAmount += $netAmount + $vatAmount;
    }
    
    $transaction = addTransaction($type, $customerId, $totalAmount, $description, $date, false, $items);
    
    header('Location: view_invoice.php?id=' . $transaction['id']);
    exit;
}
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-10">
        <div class="card vat-card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-plus-circle"></i> إضافة فاتورة جديدة</h4>
            </div>
            <div class="card-body">
                <form method="post" id="invoiceForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">نوع الفاتورة</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="<?= TRANSACTION_TYPE_SALE ?>">فاتورة مبيعات</option>
                                <option value="<?= TRANSACTION_TYPE_PURCHASE ?>">فاتورة مشتريات</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date" class="form-label">تاريخ الفاتورة</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">العميل</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">اختر عميلاً</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= $customer['name'] ?> (<?= $customer['tax_number'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">إذا لم يكن العميل موجوداً، <a href="add_customer.php">أضفه أولاً</a></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">وصف عام للفاتورة</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    
                    <h5 class="mt-4 mb-3">عناصر الفاتورة</h5>
                    <div id="itemsContainer">
                        <div class="item-row row mb-3 border-bottom pb-3">
                            <div class="col-md-4">
                                <label class="form-label">وصف العنصر</label>
                                <input type="text" class="form-control" name="item_description[]" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الكمية</label>
                                <input type="number" class="form-control item-quantity" name="item_quantity[]" step="0.01" min="0" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">السعر</label>
                                <input type="number" class="form-control item-price" name="item_price[]" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الضريبة</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input item-tax" type="checkbox" name="item_tax_included[]">
                                    <label class="form-check-label">شاملة الضريبة</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الإجمالي</label>
                                <input type="text" class="form-control item-total" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" id="addItemBtn" class="btn btn-outline-primary mb-4">
                        <i class="bi bi-plus-circle"></i> إضافة عنصر جديد
                    </button>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>الإجمالي قبل الضريبة:</th>
                                            <td id="subtotal">0.00 ر.س</td>
                                        </tr>
                                        <tr>
                                            <th>الضريبة (<?= (VAT_RATE * 100) ?>%):</th>
                                            <td id="vatAmount">0.00 ر.س</td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <th>الإجمالي النهائي:</th>
                                            <td id="totalAmount">0.00 ر.س</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> حفظ الفاتورة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // إضافة عنصر جديد
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const newItem = document.querySelector('.item-row').cloneNode(true);
        newItem.querySelectorAll('input').forEach(input => {
            if (input.type !== 'checkbox') input.value = '';
            else input.checked = false;
        });
        document.getElementById('itemsContainer').appendChild(newItem);
        attachItemEvents(newItem);
    });
    
    // إرفاق الأحداث للعناصر الموجودة
    document.querySelectorAll('.item-row').forEach(row => {
        attachItemEvents(row);
    });
    
    function attachItemEvents(row) {
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');
        const taxCheckbox = row.querySelector('.item-tax');
        const totalInput = row.querySelector('.item-total');
        
        const calculateItemTotal = () => {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = quantity * price;
            
            if (taxCheckbox.checked) {
                const vatAmount = subtotal * <?= VAT_RATE ?> / (1 + <?= VAT_RATE ?>);
                const netAmount = subtotal - vatAmount;
                totalInput.value = subtotal.toFixed(2);
            } else {
                const vatAmount = subtotal * <?= VAT_RATE ?>;
                totalInput.value = (subtotal + vatAmount).toFixed(2);
            }
            
            calculateInvoiceTotal();
        };
        
        quantityInput.addEventListener('input', calculateItemTotal);
        priceInput.addEventListener('input', calculateItemTotal);
        taxCheckbox.addEventListener('change', calculateItemTotal);
    }
    
    function calculateInvoiceTotal() {
        let subtotal = 0;
        let vatAmount = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const isTaxIncluded = row.querySelector('.item-tax').checked;
            const itemSubtotal = quantity * price;
            
            if (isTaxIncluded) {
                const itemVat = itemSubtotal * <?= VAT_RATE ?> / (1 + <?= VAT_RATE ?>);
                subtotal += itemSubtotal - itemVat;
                vatAmount += itemVat;
            } else {
                subtotal += itemSubtotal;
                vatAmount += itemSubtotal * <?= VAT_RATE ?>;
            }
        });
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ر.س';
        document.getElementById('vatAmount').textContent = vatAmount.toFixed(2) + ' ر.س';
        document.getElementById('totalAmount').textContent = (subtotal + vatAmount).toFixed(2) + ' ر.س';
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>