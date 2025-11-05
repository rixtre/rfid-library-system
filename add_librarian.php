<?php
session_start();
include 'db.php';

// require admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

// Flags set by server logic
$show_success = false;
$show_duplicate = false;
$duplicate_message = "⚠️ This librarian already exists. RFID or Employee ID is already registered.";
$insert_error = '';
$success_name = '';
$success_gender = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect and sanitize inputs
    $employee_id = trim($_POST['employee_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $rfid_tag = trim($_POST['rfid_tag'] ?? '');

    if ($employee_id === '' || $full_name === '' || $gender === '' || $rfid_tag === '') {
        $insert_error = "Please complete all required fields (Employee ID, Name, Gender, RFID).";
    } else {
        $username_e = mysqli_real_escape_string($conn, $employee_id);
        $name_e = mysqli_real_escape_string($conn, $full_name);
        $gender_e = mysqli_real_escape_string($conn, $gender);
        $rfid_e = mysqli_real_escape_string($conn, $rfid_tag);

        // duplicate check
        $chk_sql = "SELECT id FROM users WHERE username = '$username_e' OR rfid_tag = '$rfid_e' LIMIT 1";
        $chk_res = mysqli_query($conn, $chk_sql);
        if ($chk_res && mysqli_num_rows($chk_res) > 0) {
            $show_duplicate = true;
        } else {
            // insert (password plain lib123)
            $pwd_plain = 'lib123';
            $pwd_e = mysqli_real_escape_string($conn, $pwd_plain);

            $insert_sql = "INSERT INTO users (username, password, role, rfid_tag, name, gender, created_at)
                           VALUES ('$username_e', '$pwd_e', 'librarian', '$rfid_e', '$name_e', '$gender_e', NOW())";

            if (mysqli_query($conn, $insert_sql)) {
                $show_success = true;
                $success_name = $full_name;
                $success_gender = $gender;
            } else {
                // If DB returned duplicate constraint, show duplicate modal
                $dberr = mysqli_error($conn);
                if (stripos($dberr, 'Duplicate') !== false) {
                    $show_duplicate = true;
                } else {
                    $insert_error = "Database error: " . $dberr;
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Librarian — PCC RFID</title>
<style>
  /* ---------- Basic / Layout ---------- */
  :root{
    --bg:#f5f5f5; --card:#fff; --pcc-red:#A40000; --pcc-orange:#E46E00; --muted:#666;
  }
  *{box-sizing:border-box}
  html,body{height:100%;margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:#222}
  .fade-in{animation:fadeIn .36s ease-in-out forwards;opacity:0}
  @keyframes fadeIn{to{opacity:1}}

  .form-container{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .form-wrapper{width:100%;max-width:520px;background:var(--card);padding:18px 22px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border:1px solid rgba(0,0,0,0.03)}
  h2{color:var(--pcc-red);text-align:center;margin:6px 0 6px;font-size:22px;font-weight:800}
  .title-underline{width:72px;height:6px;background:linear-gradient(90deg,var(--pcc-orange),var(--pcc-red));margin:6px auto 14px;border-radius:4px}

  .form-group{margin-bottom:12px}
  label{display:block;margin-bottom:6px;font-weight:700;font-size:13px}
  input[type="text"], select {
    width:100%;padding:8px 10px;height:36px;border:1px solid #d1d1d1;border-radius:0;font-size:14px
  }
  input[type="text"]:focus, select:focus{outline:none;border-color:var(--pcc-orange);box-shadow:0 6px 18px rgba(228,110,0,0.06)}

  .form-actions{display:flex;gap:12px;margin-top:6px;align-items:center;justify-content:center}
  .btn{padding:10px 12px;font-weight:700;border-radius:6px;border:none;cursor:pointer;font-size:14px}
  .btn-primary{flex:2;background:linear-gradient(180deg,var(--pcc-orange),#d35000);color:#fff}
  .btn-primary:hover{transform:translateY(-2px)}
  .btn-back{flex:1;background:#6c757d;color:#fff}
  .btn-back:hover{background:#555;transform:translateY(-2px)}

  .note{font-size:13px;color:var(--muted);text-align:center;margin-top:8px}
  .message{padding:10px;border-radius:6px;margin-bottom:12px;font-weight:700;text-align:center}
  .message.error{background:#fff3f3;color:#b00020;border:1px solid #f5c6cb}

  /* ---------- Modal ---------- */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.48);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px}
  .modal-overlay.show{display:flex}
  .modal-box{width:100%;max-width:460px;background:#fff;border-radius:10px;box-shadow:0 18px 60px rgba(0,0,0,0.3);overflow:hidden;transform:translateY(8px);opacity:0;transition:transform .18s ease,opacity .18s ease}
  .modal-overlay.show .modal-box{transform:none;opacity:1}
  .modal-header{background:linear-gradient(90deg,var(--pcc-red),var(--pcc-orange));color:#fff;padding:12px 14px;font-weight:800}
  .modal-body{padding:14px;color:#222}
  .modal-body .line{display:flex;gap:12px;align-items:center;background:#eaf7ee;padding:10px;border-left:4px solid #6fc06d;border-radius:6px;margin-bottom:8px}
  .modal-body .dup{background:#fff3f3;border-left:4px solid #b00020;color:#721c24}
  .modal-footer{padding:12px 14px;display:flex;gap:10px;justify-content:flex-end}
  .modal-footer .btn{width:140px}

  @media (max-width:480px){
    .form-wrapper{padding:14px;border-radius:8px}
    .modal-footer .btn{width:110px}
  }

  /* Accessibility focus */
  input:focus, select:focus, .btn:focus { outline: 3px solid rgba(228,110,0,0.14); outline-offset:2px; }
</style>
</head>
<body class="fade-in">

<div class="form-container">
  <div class="form-wrapper" role="main" aria-labelledby="pageTitle">
    <h2 id="pageTitle">Add New Librarian</h2>
    <div class="title-underline" aria-hidden="true"></div>

    <?php if (!empty($insert_error) && !$show_duplicate && !$show_success): ?>
      <div class="message error"><?php echo htmlspecialchars($insert_error); ?></div>
    <?php endif; ?>

    <form id="addForm" method="post" autocomplete="off">
      <div class="form-group">
        <label for="employee_id">Employee ID (username):</label>
        <input type="text" id="employee_id" name="employee_id" placeholder="e.g. librarian1" required>
      </div>

      <div class="form-group">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" placeholder="Juan Dela Cruz" required>
      </div>

      <div class="form-group">
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
          <option value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </div>

      <div class="form-group">
        <label for="rfid_input">RFID Tag (tap card):</label>
        <input type="text" id="rfid_input" name="rfid_tag" placeholder="Tap RFID card here" required autofocus>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Librarian</button>
        <a href="admin_dashboard.php.php" class="btn btn-back" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;color:#fff;">Back</a>
      </div>

      <div class="note">Default password for new librarians: <strong>lib123</strong></div>
    </form>
  </div>
</div>

<!-- SUCCESS MODAL -->
<div id="successModal" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="modal-box">
    <div class="modal-header">Librarian added</div>
    <div class="modal-body">
      <div class="line">
        <div style="font-size:20px">✅</div>
        <div>
          <div style="font-weight:700">Success</div>
          <div id="successText">Librarian added successfully!</div>
        </div>
      </div>
      <div style="color:#666;font-size:13px">Choose next action</div>
    </div>
    <div class="modal-footer">
      <button id="addAnotherBtn" class="btn btn-back">Add Another</button>
      <button id="backMenuBtn" class="btn btn-primary">Back to Menu</button>
    </div>
  </div>
</div>

<!-- DUPLICATE MODAL -->
<div id="dupModal" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="modal-box">
    <div class="modal-header">Duplicate Entry</div>
    <div class="modal-body">
      <div class="line dup">
        <div style="font-size:20px">⚠️</div>
        <div>
          <div style="font-weight:700">Duplicate</div>
          <div id="dupText"><?php echo htmlspecialchars($duplicate_message); ?></div>
        </div>
      </div>
      <div style="color:#666;font-size:13px">No data was saved.</div>
    </div>
    <div class="modal-footer">
      <button id="dupOkBtn" class="btn btn-primary">OK</button>
    </div>
  </div>
</div>

<script>
/* ---------- client logic ---------- */
(function(){
  const rfid = document.getElementById('rfid_input');
  const emp = document.getElementById('employee_id');
  const nameInput = document.getElementById('full_name');
  const genderSelect = document.getElementById('gender');

  // debounce helper
  let timer;
  rfid.addEventListener('input', function(){
    clearTimeout(timer);
    timer = setTimeout(() => {
      const v = rfid.value.trim();
      const u = emp.value.trim();
      const n = nameInput.value.trim();
      const g = genderSelect.value;
      // auto-submit only when employee id, name and gender are provided to avoid accidental submits
      if (v.length >= 5 && u.length > 0 && n.length > 0 && g !== '') {
        document.getElementById('addForm').submit();
      }
    }, 220);
  });

  // modal helpers
  function showModal(id){ const el = document.getElementById(id); if(!el) return; el.classList.add('show'); el.setAttribute('aria-hidden','false'); }
  function hideModal(id){ const el = document.getElementById(id); if(!el) return; el.classList.remove('show'); el.setAttribute('aria-hidden','true'); }

  // buttons
  document.getElementById('addAnotherBtn').addEventListener('click', function(e){
    e.preventDefault();
    hideModal('successModal');
    document.getElementById('addForm').reset();
    emp.focus();
  });
  document.getElementById('backMenuBtn').addEventListener('click', function(e){
    e.preventDefault();
    window.location.href = 'admin_dashboard.php.php';
  });
  document.getElementById('dupOkBtn').addEventListener('click', function(e){
    e.preventDefault();
    hideModal('dupModal');
    rfid.focus();
  });

  // PHP-driven flags -> JS (safe json encoding)
  const phpFlags = {
    success: <?php echo json_encode($show_success ? true : false); ?>,
    duplicate: <?php echo json_encode($show_duplicate ? true : false); ?>,
    success_name: <?php echo json_encode($success_name); ?>,
    success_gender: <?php echo json_encode($success_gender); ?>,
    duplicate_message: <?php echo json_encode($duplicate_message); ?>
  };

  // show appropriate modal after page load if needed
  if (phpFlags.success) {
    // create greeting
    const g = (phpFlags.success_gender === 'male') ? 'Sir' : 'Ma\'am';
    const name = phpFlags.success_name || '';
    const text = name ? `Librarian added successfully! Welcome ${g} ${name}!` : 'Librarian added successfully!';
    document.getElementById('successText').innerText = text;
    showModal('successModal');
  } else if (phpFlags.duplicate) {
    // show duplicate modal and message
    document.getElementById('dupText').innerText = phpFlags.duplicate_message || 'Duplicate entry detected.';
    showModal('dupModal');
  }
})();
</script>
</body>
</html>
