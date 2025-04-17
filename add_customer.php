<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/header.php';
require_once 'includes/nav.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $taxNumber = $_POST['tax_number'];
    
    $customerId = addCustomer($name, $phone, $address, $taxNumber);
    
    header('Location: add_transaction.php?customer_id=' . $customerId);
    exit;
}
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card vat-card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-person-plus"></i> إضافة عميل جديد</h4>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">اسم العميل</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">العنوان</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_number" class="form-label">الرقم الضريبي</label>
                        <input type="text" class="form-control" id="tax_number" name="tax_number" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> حفظ العميل
                        </button>
                        <a href="transactions.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>