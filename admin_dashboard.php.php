<?php
session_start();
include 'db.php';

// Admin protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_username = $_SESSION['username'] ?? ($_SESSION['name'] ?? 'Admin');

// Fetch librarians for dropdown
$librarians = [];
$ql = "SELECT username, name FROM users WHERE role='librarian' ORDER BY name";
$res = $conn->query($ql);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $librarians[] = $r;
    }
}

// Check GET params for modal feedback
$show_success = (isset($_GET['reset']) && $_GET['reset'] == '1');
$show_error = (isset($_GET['reset']) && $_GET['reset'] == '0');
$reset_lib = isset($_GET['lib']) ? urldecode($_GET['lib']) : '';
$reset_msg = isset($_GET['msg']) ? urldecode($_GET['msg']) : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — PCC RFID</title>
<style>
  :root{--red:#A40000;--orange:#E46E00;--bg:#f5f5f5}
  *{box-sizing:border-box}
  body{font-family:Arial;margin:0;background:var(--bg);color:#222}
  header{background:var(--red);color:#fff;padding:16px;text-align:center;font-size:1.25rem}
  .wrap{max-width:1000px;margin:24px auto;padding:16px}
  .grid{display:flex;gap:12px;flex-wrap:wrap}
  .card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06);flex:1;min-width:220px;text-align:center}
  .btn{display:inline-block;padding:12px 18px;background:linear-gradient(180deg,var(--orange),#d35000);color:#fff;border-radius:8px;text-decoration:none;font-weight:700;border:none;cursor:pointer}
  .btn.alt{background:#6c757d}
  .small{font-size:0.9rem;color:#555;margin-top:8px}

  /* modal */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px}
  .modal-overlay.show{display:flex}
  .modal{background:#fff;width:100%;max-width:480px;border-radius:10px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
  .modal .head{padding:12px 16px;background:linear-gradient(90deg,var(--red),var(--orange));color:#fff;font-weight:800}
  .modal .body{padding:16px}
  .modal .foot{padding:12px 16px;text-align:right;background:#fafafa}
  .form-row{margin-bottom:12px;text-align:left}
  label{display:block;font-weight:700;margin-bottom:6px}
  select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px}
  .modal .foot .btn{margin-left:8px}

  /* success box inside modal */
  .success-line{display:flex;gap:12px;align-items:center;background:#e9f7ee;padding:12px;border-left:6px solid #28a745;border-radius:6px}
  .success-line small{display:block;color:#555;margin-top:6px}

  @media (max-width:600px){ .grid{flex-direction:column} }
</style>
</head>
<body>
<header style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#8B0000;
    color:#fff;
    padding:18px 30px;
    font-size:1.4rem;
    font-weight:600;
">
    <div>Welcome, <?php echo htmlspecialchars($admin_username); ?> — Rian</div>

    <a href="logout.php" style="
        background:#555;
        color:#fff;
        padding:10px 20px;
        border-radius:6px;
        font-weight:bold;
        text-decoration:none;
        transition:0.2s;
    " 
    onmouseover="this.style.background='#444'" 
    onmouseout="this.style.background='#555'">
        Logout
    </a>
</header>



<div class="wrap">
  <div class="grid">
    <div class="card">
      <h3>Manage Accounts</h3>
      <button class="btn" id="openResetBtn">Reset Librarian Password</button>
      <div class="small">Reset a librarian's password back to the default.</div>
    </div>

    <div class="card">
      <h3>Quick Links</h3>
      <a href="add_librarian.php" class="btn" style="background:var(--red)">Add Librarian</a>
      <a href="view_transactions.php" class="btn alt" style="margin-left:8px">View Transactions</a>
    </div>
  </div>
</div>

<!-- Reset Modal -->
<div id="resetModal" class="modal-overlay" role="dialog" aria-hidden="true">
  <div class="modal" role="document">
    <div class="head">Reset Librarian Password</div>
    <div class="body">
      <form id="resetForm" method="post" action="reset_password.php">
        <div class="form-row">
          <label for="librarian_select">Choose librarian</label>
          <select id="librarian_select" name="librarian_username" required>
            <option value="">-- select librarian --</option>
            <?php foreach ($librarians as $lib): 
               $display = trim($lib['name']) ? $lib['name'] . ' (' . $lib['username'] . ')' : $lib['username'];
            ?>
              <option value="<?php echo htmlspecialchars($lib['username']); ?>"><?php echo htmlspecialchars($display); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="font-size:0.95rem;color:#444">
          Default password will be set to: <strong>lib123</strong>
        </div>

        <div class="foot">
          <button type="button" class="btn alt" id="cancelReset">Cancel</button>
          <button type="submit" class="btn">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal (shows after redirect) -->
<div id="successModal" class="modal-overlay" role="dialog" aria-hidden="true">
  <div class="modal">
    <div class="head" style="background:linear-gradient(90deg,#28a745,#2fbf6b);">Success</div>
    <div class="body">
      <div class="success-line">
        <div style="font-size:22px">✅</div>
        <div>
          <div id="successText" style="font-weight:700">Password reset successfully!</div>
          <small id="successSub"></small>
        </div>
      </div>
    </div>
    <div class="foot">
      <button id="successOk" class="btn">OK</button>
    </div>
  </div>
</div>

<script>
  // open modal handlers
  const openResetBtn = document.getElementById('openResetBtn');
  const resetModal = document.getElementById('resetModal');
  const cancelReset = document.getElementById('cancelReset');
  openResetBtn.addEventListener('click', ()=> {
    resetModal.classList.add('show');
    resetModal.setAttribute('aria-hidden','false');
    document.getElementById('librarian_select').focus();
  });
  cancelReset.addEventListener('click', ()=> {
    resetModal.classList.remove('show');
    resetModal.setAttribute('aria-hidden','true');
  });

  // Success modal logic (driven by PHP GET params)
  const showSuccess = <?php echo json_encode($show_success ? true : false); ?>;
  const showError = <?php echo json_encode($show_error ? true : false); ?>;
  const resetLib = <?php echo json_encode($reset_lib); ?>;
  const resetMsg = <?php echo json_encode($reset_msg); ?>;

  const successModal = document.getElementById('successModal');
  const successText = document.getElementById('successText');
  const successSub = document.getElementById('successSub');
  const successOk = document.getElementById('successOk');

  if (showSuccess) {
    successText.innerText = "Password reset successfully!";
    successSub.innerHTML = "New password for <strong>" + escapeHtml(resetLib) + "</strong> is: <strong>lib123</strong>";
    successModal.classList.add('show'); successModal.setAttribute('aria-hidden','false');
  } else if (showError) {
    successText.innerText = "Reset failed";
    successSub.innerText = resetMsg || "An error occurred when resetting the password.";
    successModal.classList.add('show'); successModal.setAttribute('aria-hidden','false');
  }

  successOk.addEventListener('click', ()=> {
    successModal.classList.remove('show');
    successModal.setAttribute('aria-hidden','true');
    // Remove query string from URL so modal won't reappear on refresh
    if (history.replaceState) {
      const cleanUrl = window.location.pathname;
      history.replaceState({}, document.title, cleanUrl);
    }
  });

  // small helper to escape
  function escapeHtml(s) {
    if (!s) return s;
    return String(s).replace(/[&<>"'`=\/]/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;','`':'&#96;','=':'&#61;'}[c];
    });
  }
</script>
</body>
</html>
