<!-- faculty/topbar.php -->
<header class="h-20 bg-white border-b border-green-200 px-8 flex items-center justify-between sticky top-0 z-40">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden bg-green-700 text-white p-2 rounded-lg hover:bg-green-800 transition-all shadow-md">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <h2 class="text-green-600 font-medium">Faculty Dashboard</h2>
    </div>
    <div class="flex items-center gap-4">
        <div class="relative group">
            <button class="flex items-center gap-3 focus:outline-none" id="user-dropdown-btn">
                <div class="text-right">
                    <p class="text-sm font-bold text-green-900"><?php echo $_SESSION['login_name']; ?></p>
                    <p class="text-xs text-green-400 capitalize"><?php echo $_SESSION['login_type']; ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 border border-green-200 flex items-center justify-center text-green-400 overflow-hidden relative">
                    <?php if(!empty($_SESSION['login_avatar']) && is_file('../assets/uploads/'.$_SESSION['login_avatar'])): ?>
                        <img src="../assets/uploads/<?php echo $_SESSION['login_avatar'] ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i data-lucide="user" class="w-6 h-6"></i>
                    <?php endif; ?>
                </div>
            </button>
            
            <!-- Dropdown Menu -->
            <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-green-200 py-2 z-50">
                <button onclick="openManageAccountModal()" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-green-600 hover:bg-green-50 transition-all">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    Settings
                </button>
                <hr class="my-2 border-green-100">
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Manage Account Modal -->
<div id="manageAccountModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[100] p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-green-100 flex justify-between items-center bg-green-50">
            <h3 class="text-xl font-bold text-green-900">Manage Account</h3>
            <button onclick="closeManageAccountModal()" class="text-green-400 hover:text-green-600 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="manage-account-form" class="p-6 space-y-4" enctype="multipart/form-data">
            <!-- Profile Picture Section -->
            <div class="flex flex-col items-center gap-3 mb-6">
                <div class="relative group">
                    <div class="w-24 h-24 rounded-full bg-green-50 border-2 border-dashed border-green-200 flex items-center justify-center overflow-hidden" id="avatar-preview-container">
                        <?php if(!empty($_SESSION['login_avatar']) && is_file('../assets/uploads/'.$_SESSION['login_avatar'])): ?>
                            <img src="../assets/uploads/<?php echo $_SESSION['login_avatar'] ?>" id="avatar-preview" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i data-lucide="user" class="w-12 h-12 text-green-200" id="avatar-placeholder"></i>
                            <img id="avatar-preview" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                    </div>
                    <label for="avatar-input" class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-all cursor-pointer rounded-full">
                        <i data-lucide="camera" class="w-6 h-6"></i>
                    </label>
                    <input type="file" name="avatar" id="avatar-input" class="hidden" accept="image/*" onchange="previewAvatar(this)">
                </div>
                <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Click to upload photo</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-green-500 uppercase tracking-wider">First Name</label>
                    <input type="text" name="firstname" value="<?php echo $_SESSION['login_firstname']; ?>" required
                           class="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-900 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-green-500 uppercase tracking-wider">Last Name</label>
                    <input type="text" name="lastname" value="<?php echo $_SESSION['login_lastname']; ?>" required
                           class="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-900 outline-none transition-all text-sm">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-green-500 uppercase tracking-wider">Email</label>
                <input type="email" name="email" value="<?php echo $_SESSION['login_email']; ?>" required
                       class="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-900 outline-none transition-all text-sm">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-green-500 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="manage_password"
                           class="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-900 outline-none transition-all text-sm"
                           placeholder="Leave blank if no change">
                    <button type="button" onclick="toggleManagePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-green-400 hover:text-green-600 transition-all">
                        <i data-lucide="eye" class="w-4 h-4" id="manage_password_icon"></i>
                    </button>
                </div>
                <p class="text-[10px] text-green-400">Leave this blank if you don't want to change the password.</p>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeManageAccountModal()"
                        class="flex-1 px-6 py-3 rounded-xl border border-green-200 font-bold text-green-600 hover:bg-green-50 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-6 py-3 rounded-xl bg-green-900 text-white font-bold hover:bg-green-800 transition-all shadow-lg shadow-green-900/20 text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('user-dropdown-btn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('user-dropdown').classList.toggle('hidden');
    });

    document.addEventListener('click', function() {
        document.getElementById('user-dropdown').classList.add('hidden');
    });

    function openManageAccountModal() {
        document.getElementById('manageAccountModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeManageAccountModal() {
        document.getElementById('manageAccountModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleManagePassword() {
        const passwordInput = document.getElementById('manage_password');
        const icon = document.getElementById('manage_password_icon');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        icon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
        lucide.createIcons();
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                
                document.getElementById('avatar-preview-container').classList.remove('border-dashed');
                document.getElementById('avatar-preview-container').classList.add('border-solid', 'border-green-600');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('manage-account-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../ajax.php?action=update_account', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data == 1) {
                Swal.fire({
                    icon: 'success',
                    title: 'Account Updated',
                    text: 'Your profile has been successfully updated.',
                    confirmButtonColor: '#052e16'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: data,
                    confirmButtonColor: '#052e16'
                });
            }
        });
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
</script>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-black/50 z-[45] lg:hidden animate-in fade-in duration-300"></div>
