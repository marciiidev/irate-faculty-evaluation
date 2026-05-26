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
    <title>Academic Year | Faculty Evaluation System</title>
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
                    <h1 class="text-2xl font-bold text-green-950">Academic Year List</h1>
                    <p class="text-green-700">Manage academic years and semesters.</p>
                </div>
                <button onclick="openModal()" class="w-full md:w-auto bg-green-950 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-900 transition-all text-sm whitespace-nowrap">
                    Add New Academic Year
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-200">
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Year</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Semester</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Status</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800">Default</th>
                                <th class="px-6 py-4 text-sm font-bold text-green-800 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM academic_list WHERE is_deleted = 0 ORDER BY year DESC, semester ASC");
                            while($row = $result->fetch_assoc()):
                            ?>
                            <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                <td class="px-6 py-4 text-sm font-bold text-green-950"><?php echo $row['year']; ?></td>
                                <td class="px-6 py-4 text-sm text-green-700"><?php echo $row['semester'] == 1 ? '1st' : '2nd'; ?> Semester</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <select onchange="updateStatus(<?php echo $row['id']; ?>, this.value)" class="bg-transparent border-none text-xs font-bold uppercase tracking-wider focus:ring-0 cursor-pointer <?php 
                                        echo $row['status'] == 1 ? 'text-green-600' : ($row['status'] == 2 ? 'text-red-700' : 'text-amber-600');
                                    ?>">
                                        <option value="0" <?php echo $row['status'] == 0 ? 'selected' : ''; ?>>Pending</option>
                                        <option value="1" <?php echo $row['status'] == 1 ? 'selected' : ''; ?>>Ongoing</option>
                                        <option value="2" <?php echo $row['status'] == 2 ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <?php if($row['is_default']): ?>
                                        <span class="text-green-600 font-bold">Yes</span>
                                    <?php else: ?>
                                        <button onclick="makeDefault(<?php echo $row['id']; ?>)" class="text-green-400 hover:text-green-950 text-xs font-bold">Set as Default</button>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <button onclick='openModal(<?php echo json_encode($row); ?>)' class="text-green-600 font-bold hover:underline">Edit</button>
                                    <span class="mx-2 text-green-300">|</span>
                                    <button onclick="deleteAcademic(<?php echo $row['id']; ?>)" class="text-red-600 font-bold hover:underline">Delete</button>
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
    <div id="academicModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-green-100 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-bold text-green-950">Add Academic Year</h2>
                <button onclick="closeModal()" class="text-green-400 hover:text-green-950"><i data-lucide="x"></i></button>
            </div>
            <form id="academicForm" class="p-8 space-y-4">
                <input type="hidden" name="id" id="academic_id">
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Year (e.g., 2023-2024)</label>
                    <input type="text" name="year" id="year" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all placeholder:text-green-200" placeholder="2023-2024">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-black text-green-900 uppercase">Semester</label>
                    <select name="semester" id="semester" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                    </select>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all">Save Academic Year</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openModal(data = null) {
            const modal = document.getElementById('academicModal');
            const form = document.getElementById('academicForm');
            const title = document.getElementById('modalTitle');
            
            form.reset();
            document.getElementById('academic_id').value = '';
            
            if (data) {
                title.innerText = 'Edit Academic Year';
                document.getElementById('academic_id').value = data.id;
                document.getElementById('year').value = data.year;
                document.getElementById('semester').value = data.semester;
            } else {
                title.innerText = 'Add Academic Year';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('academicModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('academicForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax.php?action=save_academic', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Academic year has been saved successfully.',
                        confirmButtonColor: '#052e16'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data,
                        confirmButtonColor: '#052e16'
                    });
                }
            });
        };

        function makeDefault(id) {
            const formData = new FormData();
            formData.append('id', id);
            fetch('../ajax.php?action=make_default_academic', {
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

        function updateStatus(id, status) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            fetch('../ajax.php?action=update_academic_status', {
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

        function deleteAcademic(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this academic year? Evaluators linked to this period may be affected.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#052e16',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    fetch('../ajax.php?action=delete_academic', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Academic year has been deleted.',
                                confirmButtonColor: '#052e16'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data,
                                confirmButtonColor: '#052e16'
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
