<?php
// One-time git pull helper - DELETE AFTER USE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $output = shell_exec('cd /home/u118669189/domains/mimar.xsofty.com/public_html && git pull 2>&1');
    echo '<pre>' . htmlspecialchars($output) . '</pre>';
    echo '<p style="color:green">Done. <a href="/web/index.php/passwordManager/viewPasswordManager">Go to Password Manager</a></p>';
} else {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Git Pull</title>
    </head>

    <body style="font-family:sans-serif;padding:40px">
        <h2>Pull latest code from GitHub</h2>
        <p>This will run <code>git pull</code> on the server to deploy the latest fixes.</p>
        <form method="POST">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit"
                style="background:#ff5500;color:white;padding:12px 24px;border:none;border-radius:6px;font-size:1rem;cursor:pointer">
                Run git pull
            </button>
        </form>
    </body>

    </html>
<?php } ?>