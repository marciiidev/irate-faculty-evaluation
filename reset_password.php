<?php
require_once 'evaluation_db/db_connect.php';

$token = trim($_GET['token'] ?? '');
$error = "";
$success = "";
$valid_token = false;
$email = "";
$table = "";

if (empty($token)) {
    $error = "Invalid or missing reset token. Please request a new one from the login page.";
} else {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $valid_token = true;
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $table = $row['table_name'];
    } else {
        $error = "This reset link has expired or is invalid. Please request a new one.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    // This logic is now handled by ajax.php?action=complete_password_reset
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center p-6" style="background-image: url('assets/bg.Bpc.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="w-full max-w-md p-4">
        <!-- Glass Container -->
        <div class="relative bg-white/20 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-white/30 overflow-hidden">
            <!-- Glass Header Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 to-transparent pointer-events-none"></div>
            
            <div class="p-8 text-center relative z-10">
                <div class="w-24 h-24 mx-auto mb-6 relative">
                    <!-- Glow effect behind logo -->
                    <div class="absolute inset-0 bg-green-400/30 blur-2xl rounded-full"></div>
                    <img src="assets/Bpc logo.png" alt="BPC Logo" class="w-full h-full object-contain relative z-10 drop-shadow-2xl">
                </div>
                
                <!-- Plaque-style title -->
                <div class="space-y-1">
                    <h2 class="text-xs font-black text-green-200 uppercase tracking-[0.3em] mb-1">Account Security</h2>
                    <h1 class="text-3xl font-black text-white drop-shadow-md">Reset Password</h1>
                    <div class="w-12 h-1 bg-green-400 mx-auto rounded-full mt-4"></div>
                </div>
            </div>
            
            <div class="p-8 pt-0 space-y-6 relative z-10">
                <div id="alertBox" class="hidden p-4 rounded-2xl text-sm font-medium border text-center animate-pulse backdrop-blur-md"></div>

                <?php if ($valid_token): ?>
                    <form id="resetForm" class="space-y-5">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        
                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-green-200 uppercase tracking-widest pl-1">New Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-300">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <input type="password" name="password" id="password" required minlength="6"
                                       class="w-full pl-11 pr-12 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-green-200/50 outline-none focus:bg-white/20 focus:border-green-400/50 focus:ring-4 focus:ring-green-400/10 transition-all text-sm"
                                       placeholder="Enter new password">
                                <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-green-300 hover:text-white transition-all focus:outline-none" data-target="password">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-green-200 uppercase tracking-widest pl-1">Confirm New Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-300">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                </div>
                                <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                                       class="w-full pl-11 pr-12 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-green-200/50 outline-none focus:bg-white/20 focus:border-green-400/50 focus:ring-4 focus:ring-green-400/10 transition-all text-sm"
                                       placeholder="Confirm new password">
                                <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-green-300 hover:text-white transition-all focus:outline-none" data-target="confirm_password">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn"
                                class="w-full bg-green-600 hover:bg-green-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-[0_10px_30px_rgba(22,163,74,0.3)] hover:shadow-green-500/40 active:scale-[0.98] flex items-center justify-center gap-2">
                            Update Password
                        </button>
                    </form>
                <?php else: ?>
                    <div class="bg-red-500/20 backdrop-blur-md text-white p-6 rounded-[2rem] text-sm font-medium border border-red-400/30 text-center">
                        <?php echo $error; ?>
                    </div>
                    <a href="index.php" class="block w-full bg-white/10 hover:bg-white/20 text-white py-4 rounded-2xl font-bold transition-all border border-white/20 text-center uppercase text-xs tracking-widest">
                        Return to Login
                    </a>
                <?php endif; ?>
            </div>

            <!-- Bottom Accent -->
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Password Toggle Logic
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    input.type = 'password';
                    icon.setAttribute('data-lucide', 'eye');
                }
                
                // Refresh only the icon for this button
                lucide.createIcons({
                    nameAttr: 'data-lucide',
                    icons: { eye: lucide.icons.eye, 'eye-off': lucide.icons['eye-off'] }
                });
                
                // Re-create icons for just this button's container
                const parent = this;
                const newIconName = input.type === 'text' ? 'eye-off' : 'eye';
                parent.innerHTML = `<i data-lucide="${newIconName}" class="w-5 h-5"></i>`;
                lucide.createIcons();
            });
        });

        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.onsubmit = function(e) {
                e.preventDefault();
                const btn = document.getElementById('submitBtn');
                const alertBox = document.getElementById('alertBox');
                const password = document.getElementById('password').value;
                const confirm_password = document.getElementById('confirm_password').value;

                if (password !== confirm_password) {
                    alertBox.className = "bg-red-500/20 backdrop-blur-md text-white p-4 rounded-2xl text-sm font-medium border border-red-400/30 text-center block mb-4 animate-bounce";
                    alertBox.innerHTML = "Passwords do not match.";
                    return;
                }

                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Changing password...';

                const formData = new FormData(this);

                fetch('ajax.php?action=complete_password_reset', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data == 1) {
                        alertBox.className = "bg-green-500/20 backdrop-blur-md text-white p-4 rounded-2xl text-sm font-medium border border-green-400/30 text-center block mb-4";
                        alertBox.innerHTML = "Password changed successfully! Redirecting to login...";
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1000);
                    } else {
                        alertBox.className = "bg-red-500/20 backdrop-blur-md text-white p-4 rounded-2xl text-sm font-medium border border-red-400/30 text-center block mb-4";
                        alertBox.innerHTML = data;
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alertBox.className = "bg-red-500/20 backdrop-blur-md text-white p-4 rounded-2xl text-sm font-medium border border-red-400/30 text-center block";
                    alertBox.innerHTML = "An error occurred. Please try again.";
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            };
        }
    </script>
</body>
</html>
