<?php
session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Report Submission</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Test Report Submission</h1>
    
    <?php if (isset($_SESSION['test_message'])): ?>
        <div class="<?= $_SESSION['test_message_type'] ?? 'error' ?>">
            <?= $_SESSION['test_message'] ?>
        </div>
        <?php unset($_SESSION['test_message'], $_SESSION['test_message_type']); ?>
    <?php endif; ?>
    
    <form method="post" action="test_process.php">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        
        <div class="form-group">
            <label>University ID:</label>
            <input type="number" name="uni_id" value="1" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="verified_email" value="test@university.edu" required>
        </div>
        
        <div class="form-group">
            <label>Incident Type:</label>
            <select name="incident_type" required>
                <option value="bullying">Bullying</option>
                <option value="verbal_harassment">Verbal Harassment</option>
                <option value="physical_harassment">Physical Harassment</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Department:</label>
            <select name="department" required>
                <option value="engineering">Engineering</option>
                <option value="science">Science</option>
                <option value="management">Management</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Location:</label>
            <input type="text" name="location" value="Test Location">
        </div>
        
        <div class="form-group">
            <label>Incident Date/Time:</label>
            <input type="datetime-local" name="incident_datetime" required>
        </div>
        
        <div class="form-group">
            <label>Details:</label>
            <textarea name="details" rows="4" required>This is a test report for debugging purposes.</textarea>
        </div>
        
        <button type="submit">Submit Test Report</button>
    </form>
    
    <script>
        // Set default datetime to current time
        document.querySelector('input[name="incident_datetime"]').value = 
            new Date().toISOString().slice(0, 16);
    </script>
</body>
</html> 