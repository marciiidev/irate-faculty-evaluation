<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'superadmin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculties | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-green-50 min-h-screen flex text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="md:p-8 p-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-green-950">Faculty List</h1>
                    <p class="text-green-700 text-sm md:text-base">Manage all faculty members in the system.</p>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <a href="export_csv.php?type=faculty" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="download" class="w-4 h-4"></i> Export CSV
                    </a>
                    <button onclick="document.getElementById('import_csv').click()" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="upload" class="w-4 h-4"></i> Bulk Import
                    </button>
                    <input type="file" id="import_csv" class="hidden" accept=".csv">
                    <button onclick="openModal()" class="bg-green-950 text-white px-4 py-2 rounded-xl font-bold hover:bg-green-900 transition-all text-sm flex-1 md:flex-none justify-center whitespace-nowrap">
                        Add New Faculty
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-200">
                                <th class="px-6 py-4 text-sm font-bold text-green-800">School ID</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Name</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Email</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Password</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM faculty_list WHERE is_deleted = 0 ORDER BY lastname ASC");
                            while($row = $result->fetch_assoc()):
                            ?>
                            <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                <td class="px-6 py-4 text-sm text-green-700 font-mono"><?php echo $row['school_id']; ?></td>
                                <td class="px-6 py-4 text-sm font-bold text-green-950 min-w-[200px]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 overflow-hidden flex-shrink-0 flex items-center justify-center border border-green-200">
                                            <?php if(!empty($row['avatar']) && is_file('../assets/uploads/'.$row['avatar'])): ?>
                                                <img src="../assets/uploads/<?php echo $row['avatar'] ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i data-lucide="user" class="w-4 h-4 text-green-400"></i>
                                            <?php endif; ?>
                                        </div>
                                        <span class="break-words"><?php echo $row['firstname'].' '.$row['lastname']; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-green-700"><?php echo $row['email']; ?></td>
                                <td class="px-6 py-4 text-sm text-green-700">
                                    <div class="flex items-center gap-2">
                                        <span id="pass_<?php echo $row['id'] ?>" class="password-field">••••••••</span>
                                        <button onclick="togglePassword(<?php echo $row['id'] ?>, '<?php echo addslashes($row['password_text'] ?? 'Not Set') ?>')" class="text-green-600 hover:text-green-800">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <button onclick='openModal(<?php echo json_encode($row); ?>)' class="text-green-600 font-bold hover:underline">Edit</button>
                                    <span class="mx-2 text-green-300">|</span>
                                    <button onclick="deleteFaculty(<?php echo $row['id']; ?>)" class="text-red-600 font-bold hover:underline">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="facultyModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-green-100 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-bold text-green-950">Add New Faculty</h2>
                <button onclick="closeModal()" class="text-green-400 hover:text-green-950"><i data-lucide="x"></i></button>
            </div>
            <form id="facultyForm" class="p-8 space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="id" id="faculty_id">
                
                <div class="flex flex-col items-center gap-2 mb-4">
                    <div class="w-20 h-20 rounded-full bg-green-50 border-2 border-dashed border-green-200 flex items-center justify-center overflow-hidden relative group" id="faculty-avatar-container">
                        <i data-lucide="user" class="w-8 h-8 text-green-200" id="faculty-avatar-placeholder"></i>
                        <img id="faculty-avatar-preview" class="w-full h-full object-cover hidden">
                        <label for="faculty_avatar" class="absolute inset-0 flex items-center justify-center bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-all cursor-pointer">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </label>
                    </div>
                    <input type="file" name="avatar" id="faculty_avatar" class="hidden" accept="image/*" onchange="previewFacultyAvatar(this)">
                    <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Profile Photo</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">First Name</label>
                        <input type="text" name="firstname" id="firstname" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">Last Name</label>
                        <input type="text" name="lastname" id="lastname" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">School ID</label>
                    <input type="text" name="school_id" id="school_id" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Email Address</label>
                    <input type="email" name="email" id="email" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all pr-10 placeholder:text-green-200" placeholder="Leave blank to keep current">
                        <button type="button" onclick="toggleInputPassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600 hover:text-green-900 transition-all">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all">Save Faculty</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        document.getElementById('import_csv').onchange = function() {
            if (this.files[0]) {
                const formData = new FormData();
                formData.append('csv_file', this.files[0]);
                formData.append('type', 'faculty');

                Swal.fire({
                    title: 'Importing...',
                    text: 'Please wait while we process the CSV file.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('../ajax.php?action=import_csv', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data == 1) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: 'Data imported successfully!' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'info', title: 'Import Result', html: data, confirmButtonText: 'Close' });
                    }
                });
            }
        };

        function togglePassword(id, text) {
            const span = document.getElementById('pass_' + id);
            if (span.innerText === '••••••••') {
                span.innerText = text;
                event.currentTarget.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            } else {
                span.innerText = '••••••••';
                event.currentTarget.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i>';
            }
            lucide.createIcons();
        }

        function toggleInputPassword(id) {
            const input = document.getElementById(id);
            const btn = event.currentTarget;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i>';
            }
            lucide.createIcons();
        }

        function previewFacultyAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('faculty-avatar-preview');
                    const placeholder = document.getElementById('faculty-avatar-placeholder');
                    const container = document.getElementById('faculty-avatar-container');
                    
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    container.classList.remove('border-dashed');
                    container.classList.add('border-solid', 'border-green-600');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openModal(data = null) {
            const modal = document.getElementById('facultyModal');
            const form = document.getElementById('facultyForm');
            const title = document.getElementById('modalTitle');
            const preview = document.getElementById('faculty-avatar-preview');
            const placeholder = document.getElementById('faculty-avatar-placeholder');
            const container = document.getElementById('faculty-avatar-container');
            
            form.reset();
            document.getElementById('faculty_id').value = '';
            preview.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
            container.classList.add('border-dashed');
            container.classList.remove('border-solid', 'border-green-600');
            
            if (data) {
                title.innerText = 'Edit Faculty';
                document.getElementById('faculty_id').value = data.id;
                document.getElementById('firstname').value = data.firstname;
                document.getElementById('lastname').value = data.lastname;
                document.getElementById('school_id').value = data.school_id;
                document.getElementById('email').value = data.email;
                document.getElementById('password').required = false;

                if (data.avatar) {
                    preview.src = '../assets/uploads/' + data.avatar;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    container.classList.remove('border-dashed');
                    container.classList.add('border-solid', 'border-green-600');
                }
            } else {
                title.innerText = 'Add New Faculty';
                document.getElementById('password').required = true;
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('facultyModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('facultyForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax.php?action=save_faculty', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        };

        function deleteFaculty(id) {
            if (confirm('Are you sure you want to delete this faculty?')) {
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('../ajax.php?action=delete_faculty', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data == 1) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>
