<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
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
    <title>Courses | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-green-50 min-h-screen flex flex-col lg:flex-row text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6 md:gap-8">
                <div>
                    <h1 class="text-2xl font-bold text-green-950">Course List</h1>
                    <p class="text-green-700">Manage all courses. <span class="text-xs text-green-500 font-normal block mt-1">CSV Format: Course Code, Course Name</span></p>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <a href="../superadmin/export_csv.php?type=subject" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="download" class="w-4 h-4"></i> Export CSV
                    </a>
                    <button onclick="document.getElementById('import_csv').click()" class="bg-green-100 text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-200 transition-all flex items-center gap-2 text-sm flex-1 md:flex-none justify-center">
                        <i data-lucide="upload" class="w-4 h-4"></i> Bulk Import CSV
                    </button>
                    <input type="file" id="import_csv" class="hidden" accept=".csv">
                    <button onclick="openModal()" class="bg-green-950 text-white px-4 py-2 rounded-xl font-bold hover:bg-green-900 transition-all text-sm flex-1 md:flex-none justify-center whitespace-nowrap">
                        Add New Course
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-200">
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Code</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Course Name</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM subject_list WHERE is_deleted = 0 ORDER BY subject_name ASC");
                            while($row = $result->fetch_assoc()):
                            ?>
                            <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                <td class="px-6 py-4 text-sm text-green-700 font-mono"><?php echo $row['subject_code']; ?></td>
                                <td class="px-6 py-4 text-sm font-bold text-green-950"><?php echo $row['subject_name']; ?></td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <button onclick='openModal(<?php echo json_encode($row); ?>)' class="text-green-600 font-bold hover:underline">Edit</button>
                                    <span class="mx-2 text-green-300">|</span>
                                    <button onclick="deleteSubject(<?php echo $row['id']; ?>)" class="text-red-600 font-bold hover:underline">Delete</button>
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
    <div id="subjectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-green-100 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-bold text-green-950">Add New Course</h2>
                <button onclick="closeModal()" class="text-green-400 hover:text-green-950"><i data-lucide="x"></i></button>
            </div>
            <form id="subjectForm" class="p-8 space-y-4">
                <input type="hidden" name="id" id="subject_id">
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Course Code</label>
                    <input type="text" name="subject_code" id="subject_code_input" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200" placeholder="IT 101">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Course Name</label>
                    <input type="text" name="subject_name" id="subject_name_input" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200" placeholder="Introduction to Computing">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all">Save Course</button>
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
                formData.append('type', 'subject');

                Swal.fire({
                    title: 'Importing Subjects...',
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
                        Swal.fire({ icon: 'success', title: 'Success!', text: 'Subjects imported successfully!' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'info', title: 'Import Result', html: data, confirmButtonText: 'Close' });
                    }
                });
            }
        };

        function openModal(data = null) {
            const modal = document.getElementById('subjectModal');
            const form = document.getElementById('subjectForm');
            const title = document.getElementById('modalTitle');
            
            form.reset();
            document.getElementById('subject_id').value = '';
            
            if (data) {
                title.innerText = 'Edit Course';
                document.getElementById('subject_id').value = data.id;
                document.getElementById('subject_code_input').value = data.subject_code;
                document.getElementById('subject_name_input').value = data.subject_name;
            } else {
                title.innerText = 'Add New Course';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('subjectModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('subjectForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax.php?action=save_subject', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Subject has been saved.' }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data });
                }
            });
        };

        function deleteSubject(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#052c1e',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    fetch('../ajax.php?action=delete_subject', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire('Deleted!', 'Subject has been deleted.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
