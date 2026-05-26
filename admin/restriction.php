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
    <title>Restriction | Faculty Evaluation System</title>
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
                    <h1 class="text-2xl font-black text-green-950 uppercase tracking-tight">Restriction</h1>
                    <p class="text-green-700 text-sm font-medium">Assign faculty members to specific classes for evaluation.</p>
                </div>
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <a href="../superadmin/export_csv.php?type=restriction" class="bg-white text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-50 transition-all flex items-center gap-2 text-xs border border-green-200">
                        <i data-lucide="download" class="w-4 h-4"></i> Export CSV
                    </a>
                    <button onclick="document.getElementById('import_csv').click()" class="bg-white text-green-800 px-4 py-2 rounded-xl font-bold hover:bg-green-50 transition-all flex items-center gap-2 text-xs border border-green-200">
                        <i data-lucide="upload" class="w-4 h-4"></i> Bulk Import
                    </button>
                    <input type="file" id="import_csv" class="hidden" accept=".csv">
                    <button onclick="location.href='manage_restriction.php'" class="bg-green-950 text-white px-4 py-2 rounded-xl font-black hover:bg-green-900 transition-all text-xs uppercase tracking-widest whitespace-nowrap shadow-lg shadow-green-900/20">
                        Assign Faculty
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-green-50 border-b border-green-200">
                                <th class="px-6 py-4 text-[10px] font-black text-green-800 uppercase tracking-widest">Academic Year</th>
                                <th class="px-6 py-4 text-[10px] font-black text-green-800 uppercase tracking-widest">Faculty Member</th>
                                <th class="px-6 py-4 text-[10px] font-black text-green-800 uppercase tracking-widest">Target Class</th>
                                <th class="px-6 py-4 text-[10px] font-black text-green-800 uppercase tracking-widest">Assigned Course</th>
                                <th class="px-6 py-4 text-[10px] font-black text-green-800 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT r.*, a.year, a.semester, f.firstname, f.lastname, c.class_name, s.subject_code, s.subject_name 
                                                  FROM restriction_list r 
                                                  JOIN academic_list a ON r.academic_id = a.id 
                                                  JOIN faculty_list f ON r.faculty_id = f.id 
                                                  JOIN class_list c ON r.class_id = c.id 
                                                  JOIN subject_list s ON r.subject_id = s.id 
                                                  WHERE r.is_deleted = 0
                                                  ORDER BY a.year DESC, a.semester DESC, f.lastname ASC");
                            while($row = $result->fetch_assoc()):
                            ?>
                            <tr class="border-b border-green-100 hover:bg-green-50 transition-all">
                                <td class="px-6 py-4 text-sm text-green-700">
                                    <?php echo $row['year'] . " " . ($row['semester'] == 1 ? '1st Sem' : '2nd Sem'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-green-950">
                                    <?php echo $row['firstname'] . " " . $row['lastname']; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-green-700">
                                    <?php echo $row['class_name']; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-green-700">
                                    <div class="max-w-[200px] truncate" title="<?php echo $row['subject_code'] . " - " . $row['subject_name']; ?>">
                                        <?php echo $row['subject_code'] . " - " . $row['subject_name']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <button onclick="location.href='manage_restriction.php?id=<?php echo $row['id']; ?>'" class="text-green-600 font-bold hover:underline">Edit</button>
                                    <span class="mx-2 text-green-300">|</span>
                                    <button onclick="deleteRestriction(<?php echo $row['id']; ?>)" class="text-red-600 font-bold hover:underline">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($result->num_rows <= 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-green-500 italic">No restrictions defined yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function deleteRestriction(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#052e16',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    fetch('../ajax.php?action=delete_restriction', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Restriction has been deleted.',
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

        document.getElementById('import_csv').onchange = function() {
            if (this.files[0]) {
                const formData = new FormData();
                formData.append('csv_file', this.files[0]);
                formData.append('type', 'restriction');
                
                fetch('../ajax.php?action=import_csv', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data == "1") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Restrictions successfully imported.',
                            confirmButtonColor: '#052e16'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        const div = document.createElement('div');
                        div.innerHTML = data;
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            html: div.innerHTML,
                            confirmButtonColor: '#052e16'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        };
    </script>
</body>
</html>
