<!-- student/topbar.php -->
<header class="h-20 bg-white border-b border-green-200 px-8 flex items-center justify-between sticky top-0 z-40">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden bg-green-700 text-white p-2 rounded-lg hover:bg-green-800 transition-all shadow-md">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <h2 class="text-green-600 font-medium">Student Dashboard</h2>
    </div>
    <div class="flex items-center gap-4">
        <!-- User Dropdown -->
        <div class="relative" id="userDropdown">
            <button class="flex items-center gap-3 p-1 rounded-full hover:bg-green-100 transition-all" id="userDropdownBtn">
                <div class="text-right hidden md:block px-2">
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
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-green-200 py-2 hidden z-50" id="userDropdownMenu">
                <button class="w-full flex items-center gap-3 px-4 py-2 text-sm text-green-600 hover:bg-green-50 transition-all" id="manageAccountBtn">
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
<div class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] hidden" id="accountModal">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-green-100 flex items-center justify-between">
            <h3 class="text-xl font-bold text-green-900">Manage Account</h3>
            <button class="p-2 hover:bg-green-100 rounded-full transition-all" id="closeAccountModal">
                <i data-lucide="x" class="w-5 h-5 text-green-500"></i>
            </button>
        </div>
        <form id="updateAccountForm" class="p-6 space-y-4" enctype="multipart/form-data">
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
                    <label class="text-sm font-semibold text-green-700">First Name</label>
                    <input type="text" name="firstname" value="<?php echo $_SESSION['login_firstname'] ?? ''; ?>" required
                        class="w-full px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-900 focus:border-green-900 transition-all outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-green-700">Last Name</label>
                    <input type="text" name="lastname" value="<?php echo $_SESSION['login_lastname'] ?? ''; ?>" required
                        class="w-full px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-900 focus:border-green-900 transition-all outline-none">
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-green-700">Email</label>
                <input type="email" name="email" value="<?php echo $_SESSION['login_email'] ?? ''; ?>" required
                    class="w-full px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-900 focus:border-green-900 transition-all outline-none">
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-green-700">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="manage_password" placeholder="Leave blank to keep current"
                        class="w-full px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-900 focus:border-green-900 transition-all outline-none">
                    <button type="button" onclick="toggleManagePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-green-400 hover:text-green-600 transition-all">
                        <i data-lucide="eye" class="w-4 h-4" id="manage_password_icon"></i>
                    </button>
                </div>
                <p class="text-[10px] text-green-400">Leave this blank if you don't want to change the password.</p>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" id="cancelAccountUpdate" class="flex-1 px-6 py-3 border border-green-200 text-green-600 font-bold rounded-xl hover:bg-green-50 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-green-900 text-white font-bold rounded-xl hover:bg-green-800 transition-all shadow-lg shadow-green-900/20">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('userDropdownBtn');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        const accountModal = document.getElementById('accountModal');
        const manageAccountBtn = document.getElementById('manageAccountBtn');
        const closeAccountModal = document.getElementById('closeAccountModal');
        const cancelAccountUpdate = document.getElementById('cancelAccountUpdate');
        const updateAccountForm = document.getElementById('updateAccountForm');

        // Toggle Dropdown
        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            dropdownMenu.classList.add('hidden');
        });

        // Open Modal
        manageAccountBtn.addEventListener('click', () => {
            accountModal.classList.remove('hidden');
            dropdownMenu.classList.add('hidden');
        });

        // Close Modal
        const closeModal = () => {
            accountModal.classList.add('hidden');
        };

        closeAccountModal.addEventListener('click', closeModal);
        cancelAccountUpdate.addEventListener('click', closeModal);

        window.toggleManagePassword = function() {
            const passwordInput = document.getElementById('manage_password');
            const icon = document.getElementById('manage_password_icon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
            lucide.createIcons();
        };

        window.previewAvatar = function(input) {
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
        };

        // Handle Form Submission
        updateAccountForm.addEventListener('submit', function(e) {
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
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'An error occurred while updating the account.',
                    confirmButtonColor: '#052e16'
                });
            });
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
