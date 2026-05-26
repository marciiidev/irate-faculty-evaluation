<?php
session_start();
require_once 'evaluation_db/db_connect.php';

if (isset($_SESSION['login_id'])) {
    header("location: " . $_SESSION['login_type'] . "/index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Removed trim from password

    if ($conn->connect_error) {
        $error = "Database connection failed: " . $conn->connect_error;
    } else {
        $found = false;
        
        // 1. Check users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_deleted = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $found = true;
            if (password_verify($password, $user['password'])) {
                $_SESSION['login_id'] = $user['id'];
                $_SESSION['login_type'] = $user['role'];
                $_SESSION['login_firstname'] = $user['firstname'];
                $_SESSION['login_lastname'] = $user['lastname'];
                $_SESSION['login_email'] = $user['email'];
                $_SESSION['login_name'] = $user['firstname'] . ' ' . $user['lastname'];
                $_SESSION['login_avatar'] = $user['avatar'];
                header("location: " . $user['role'] . "/index.php");
                exit();
            }
        }

        // 2. Check faculty table if not found in users
        if (!$found) {
            $stmt = $conn->prepare("SELECT * FROM faculty_list WHERE (school_id = ? OR email = ?) AND is_deleted = 0");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                $found = true;
                if (password_verify($password, $user['password'])) {
                    $_SESSION['login_id'] = $user['id'];
                    $_SESSION['login_type'] = 'faculty';
                    $_SESSION['login_firstname'] = $user['firstname'];
                    $_SESSION['login_lastname'] = $user['lastname'];
                    $_SESSION['login_email'] = $user['email'];
                    $_SESSION['login_name'] = $user['firstname'] . ' ' . $user['lastname'];
                    $_SESSION['login_avatar'] = $user['avatar'];
                    header("location: faculty/index.php");
                    exit();
                }
            }
        }

        // 3. Check student table if not found yet
        if (!$found) {
            $stmt = $conn->prepare("SELECT * FROM student_list WHERE (school_id = ? OR email = ?) AND is_deleted = 0");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                $found = true;
                if (password_verify($password, $user['password'])) {
                    $_SESSION['login_id'] = $user['id'];
                    $_SESSION['login_type'] = 'student';
                    $_SESSION['login_firstname'] = $user['firstname'];
                    $_SESSION['login_lastname'] = $user['lastname'];
                    $_SESSION['login_email'] = $user['email'];
                    $_SESSION['login_name'] = $user['firstname'] . ' ' . $user['lastname'];
                    $_SESSION['login_avatar'] = $user['avatar'];
                    header("location: student/index.php");
                    exit();
                }
            }
        }

        if (!$found) {
            $error = "User ID/Email '$email' not found in database. Did you import the SQL file?";
        } else {
            $error = "Incorrect password for '$email'. Please try 'password'.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h2 class="text-xs font-black text-green-200 uppercase tracking-[0.3em] mb-1">Bulacan Polytechnic College</h2>
                    <h1 class="text-3xl font-black text-white drop-shadow-md">Faculty Evaluation System</h1>
                    <div class="w-12 h-1 bg-green-400 mx-auto rounded-full mt-4"></div>
                </div>
            </div>
            
            <!-- Form Area -->
            <form method="POST" class="p-8 pt-2 space-y-5 relative z-10">
                <?php if ($error): ?>
                    <div class="bg-red-500/20 backdrop-blur-md text-white p-4 rounded-2xl text-sm font-medium border border-red-400/30 text-center animate-pulse">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
 
                <div class="space-y-2 group">
                    <label class="text-[10px] font-bold text-green-200 uppercase tracking-widest pl-1">School ID or Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-300">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="email" required 
                               class="w-full pl-11 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-green-200/50 outline-none focus:bg-white/20 focus:border-green-400/50 focus:ring-4 focus:ring-green-400/10 transition-all text-sm"
                               placeholder="Enter your ID or email">
                    </div>
                </div>
 
                <div class="space-y-2 group">
                    <div class="flex justify-between items-center pl-1">
                        <label class="text-[10px] font-bold text-green-200 uppercase tracking-widest">Password</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-300">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="password" id="password" required 
                               class="w-full pl-11 pr-12 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-green-200/50 outline-none focus:bg-white/20 focus:border-green-400/50 focus:ring-4 focus:ring-green-400/10 transition-all text-sm"
                               placeholder="Enter your password">
                        <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-green-300 hover:text-white transition-all">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="button" onclick="openForgotModal()" class="text-[10px] font-bold text-green-300 hover:text-white uppercase tracking-widest transition-all">Forgot Password?</button>
                </div>
 
                <button type="submit" 
                        class="w-full bg-green-600 hover:bg-green-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-[0_10px_30px_rgba(22,163,74,0.3)] hover:shadow-green-500/40 active:scale-[0.98]">
                    Log In
                </button>
            </form>

            <!-- Bottom Accent -->
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="fixed inset-0 bg-green-950/60 backdrop-blur-xl z-[100] hidden items-center justify-center p-4">
        <div class="relative bg-white/20 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl max-w-md w-full border border-white/30 overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Glass Header Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 to-transparent pointer-events-none"></div>
            
            <div class="p-8 text-center relative z-10">
                <div class="w-16 h-16 bg-green-400/20 text-green-200 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/20">
                    <i data-lucide="key-round" class="w-8 h-8 drop-shadow-lg"></i>
                </div>
                <h2 class="text-2xl font-black text-white drop-shadow-md uppercase tracking-tight">Recover Password</h2>
                <p class="text-green-200 text-sm mt-1">We'll send a new password to your email.</p>
            </div>
            
            <form id="forgotForm" class="p-8 pt-0 space-y-4 relative z-10">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-green-200 uppercase tracking-widest pl-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-300">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="forgot_email" required 
                               class="w-full pl-11 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-green-200/50 outline-none focus:bg-white/20 focus:border-green-400/50 focus:ring-4 focus:ring-green-400/10 transition-all text-sm"
                               placeholder="Enter your registered email">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeForgotModal()" 
                            class="flex-1 px-4 py-4 rounded-2xl font-bold text-green-200 border border-white/20 hover:bg-white/10 transition-all text-sm uppercase tracking-widest">
                        Cancel
                    </button>
                    <button type="submit" id="forgotSubmit"
                            class="flex-1 bg-green-600 hover:bg-green-500 text-white px-4 py-4 rounded-2xl font-black transition-all shadow-lg flex items-center justify-center gap-2 text-sm uppercase tracking-widest">
                        Send Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openForgotModal() {
            document.getElementById('forgotModal').classList.remove('hidden');
            document.getElementById('forgotModal').classList.add('flex');
        }

        function closeForgotModal() {
            document.getElementById('forgotModal').classList.add('hidden');
            document.getElementById('forgotModal').classList.remove('flex');
        }

        document.getElementById('forgotForm').onsubmit = function(e) {
            e.preventDefault();
            const btn = document.getElementById('forgotSubmit');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';

            const email = this.forgot_email.value;
            const formData = new FormData();
            formData.append('email', email);

            fetch('ajax.php?action=forgot_password', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Sent',
                        text: 'A reset link has been sent to your email. Please check your inbox.',
                        confirmButtonColor: '#052e16'
                    }).then(() => {
                        closeForgotModal();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data,
                        confirmButtonColor: '#052e16'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again later.',
                    confirmButtonColor: '#052e16'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        };

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // toggle the eye slash icon
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.setAttribute('data-lucide', 'eye');
            } else {
                icon.setAttribute('data-lucide', 'eye-off');
            }
            lucide.createIcons();
        });
    </script>
</body>
</html>
