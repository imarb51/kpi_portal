<?php
/**
 * Password Hash Generator
 * Open this file in your browser: http://localhost/KPI-portal/hash_generator.php
 */

// The password you want to hash
$password = 'Anita@123';

// Generate bcrypt hash
$hash = password_hash($password, PASSWORD_BCRYPT);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .hash-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin: 20px 0;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .label {
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .sql-query {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Hash Generator</h1>
        
        <div class="success">
            ✅ Hash generated successfully!
        </div>

        <div class="label">Password:</div>
        <div class="hash-box"><?php echo htmlspecialchars($password); ?></div>

        <div class="label">Bcrypt Hash:</div>
        <div class="hash-box" id="hash"><?php echo htmlspecialchars($hash); ?></div>

        <button onclick="copyHash()">📋 Copy Hash</button>

        <h2>SQL Query to Update Database:</h2>
        <div class="sql-query">UPDATE users<br>
SET password_hash = '<?php echo $hash; ?>'<br>
WHERE email = 'imran.shaikh@gozoop.com';</div>

        <button onclick="copySQL()">📋 Copy SQL Query</button>

        <h2>Instructions:</h2>
        <ol>
            <li>Click "Copy SQL Query" above</li>
            <li>Open phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
            <li>Select the <code>kpi_portal</code> database</li>
            <li>Click the "SQL" tab</li>
            <li>Paste the query and click "Go"</li>
            <li>Try logging in again with:<br>
                Email: <strong>imran.shaikh@gozoop.com</strong><br>
                Password: <strong><?php echo htmlspecialchars($password); ?></strong>
            </li>
        </ol>
    </div>

    <script>
        function copyHash() {
            const hash = document.getElementById('hash').textContent;
            navigator.clipboard.writeText(hash).then(() => {
                alert('Hash copied to clipboard!');
            });
        }

        function copySQL() {
            const sql = `UPDATE users\nSET password_hash = '<?php echo $hash; ?>'\nWHERE email = 'imran.shaikh@gozoop.com';`;
            navigator.clipboard.writeText(sql).then(() => {
                alert('SQL query copied to clipboard! Now paste it in phpMyAdmin.');
            });
        }
    </script>
</body>
</html>
