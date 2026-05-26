<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'admin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$restriction = [];
if (!empty($id)) {
    $res_query = $conn->query("SELECT * FROM restriction_list WHERE id = $id");
    if ($res_query->num_rows > 0) {
        $restriction = $res_query->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo empty($id) ? 'Add New' : 'Edit'; ?> Restriction | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-green-50 min-h-screen flex text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="p-8">
            <div class="mb-8">
                <button onclick="location.href='restriction.php'" class="flex items-center gap-2 text-green-500 hover:text-green-950 transition-all mb-4">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to List
                </button>
                <h1 class="text-2xl font-bold text-green-950"><?php echo empty($id) ? 'Add New' : 'Edit'; ?> Restriction</h1>
                <p class="text-green-700">Assign a faculty member to a class and course for evaluation.</p>
            </div>

            <div class="bg-white rounded-3xl border-2 border-green-200 shadow-sm max-w-2xl overflow-hidden">
                <form id="restrictionForm" class="p-8 space-y-6">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">Academic Year</label>
                        <select name="academic_id" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                            <option value="">Select Academic Year</option>
                            <?php
                            $acad_query = $conn->query("SELECT * FROM academic_list ORDER BY year DESC, semester DESC");
                            while($row = $acad_query->fetch_assoc()):
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo (isset($restriction['academic_id']) && $restriction['academic_id'] == $row['id']) || ($row['is_default'] == 1 && empty($id)) ? 'selected' : ''; ?>>
                                <?php echo $row['year'] . " " . ($row['semester'] == 1 ? '1st Sem' : '2nd Sem'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">Faculty Member</label>
                        <select name="faculty_id" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                            <option value="">Select Faculty</option>
                            <?php
                            $faculty_query = $conn->query("SELECT * FROM faculty_list ORDER BY lastname ASC");
                            while($row = $faculty_query->fetch_assoc()):
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo isset($restriction['faculty_id']) && $restriction['faculty_id'] == $row['id'] ? 'selected' : ''; ?>>
                                <?php echo $row['firstname'] . " " . $row['lastname']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">Class</label>
                        <select name="class_id" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                            <option value="">Select Class</option>
                            <?php
                            $class_query = $conn->query("SELECT * FROM class_list ORDER BY class_name ASC");
                            while($row = $class_query->fetch_assoc()):
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo isset($restriction['class_id']) && $restriction['class_id'] == $row['id'] ? 'selected' : ''; ?>>
                                <?php echo $row['class_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-black text-green-900 uppercase">Course</label>
                        <select name="subject_id" required class="w-full px-4 py-2 rounded-xl border-2 border-green-300 focus:ring-2 focus:ring-green-950 outline-none transition-all">
                            <option value="">Select Course</option>
                            <?php
                            $subject_query = $conn->query("SELECT * FROM subject_list ORDER BY subject_code ASC");
                            while($row = $subject_query->fetch_assoc()):
                            ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo isset($restriction['subject_id']) && $restriction['subject_id'] == $row['id'] ? 'selected' : ''; ?>>
                                <?php echo $row['subject_code'] . " - " . $row['subject_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all">Save Restriction</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        document.getElementById('restrictionForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../ajax.php?action=save_restriction', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if (data == 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Restriction has been saved successfully.',
                        confirmButtonColor: '#052e16'
                    }).then(() => {
                        location.href = 'restriction.php';
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
    </script>
</body>
</html>
