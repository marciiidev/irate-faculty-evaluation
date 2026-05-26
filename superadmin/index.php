<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'superadmin') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$faculties = $conn->query("SELECT count(*) as count FROM faculty_list WHERE is_deleted = 0")->fetch_assoc()['count'];
$students = $conn->query("SELECT count(*) as count FROM student_list WHERE is_deleted = 0")->fetch_assoc()['count'];
$classes = $conn->query("SELECT count(*) as count FROM class_list WHERE is_deleted = 0")->fetch_assoc()['count'];
$academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Dashboard | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="md:p-8 p-4 space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-green-950">Welcome, <?php echo explode(' ', $_SESSION['login_name'])[0]; ?>!</h1>
                    <p class="text-green-700 text-sm md:text-base">Here's what's happening in the system today.</p>
                </div>
                <div class="bg-white px-6 py-4 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="text-green-700 w-5 h-5"></i>
                    </div>
                    <div class="text-right">
                        <p id="currentDate" class="text-sm font-bold text-green-950"></p>
                        <p id="currentTime" class="text-2xl font-black text-green-600 tabular-nums"></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-4 md:p-6 rounded-2xl border border-green-300 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-bold uppercase tracking-tighter">Academic Year</p>
                        <p class="text-lg font-black text-green-950"><?php echo $academic['year'] ?? 'Not Set'; ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-green-300 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-bold uppercase tracking-tighter">Semester</p>
                        <p class="text-lg font-black text-green-950">
                            <?php echo isset($academic['semester']) ? ($academic['semester'] == 1 ? '1st' : '2nd') : 'N/A'; ?> Semester
                        </p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-green-300 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-bold uppercase tracking-tighter">Evaluation Status</p>
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest <?php 
                            echo isset($academic['status']) ? ($academic['status'] == 1 ? 'bg-green-100 text-green-700' : ($academic['status'] == 2 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')) : 'bg-amber-100 text-amber-700';
                        ?>">
                            <?php echo isset($academic['status']) ? ($academic['status'] == 1 ? 'Ongoing' : ($academic['status'] == 2 ? 'Closed' : 'Pending')) : 'Pending'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 md:p-8 rounded-2xl border border-green-300 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-600 opacity-5 -mr-8 -mt-8 rounded-full transition-transform group-hover:scale-110"></div>
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-green-600/20">
                            <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-green-950"><?php echo $faculties; ?></p>
                            <p class="text-green-700 font-bold uppercase text-xs tracking-widest">Total Faculties</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-green-300 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-500 opacity-5 -mr-8 -mt-8 rounded-full transition-transform group-hover:scale-110"></div>
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-green-500/20">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-green-950"><?php echo $students; ?></p>
                            <p class="text-green-700 font-bold uppercase text-xs tracking-widest">Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-green-300 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-700 opacity-5 -mr-8 -mt-8 rounded-full transition-transform group-hover:scale-110"></div>
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 bg-green-700 rounded-xl flex items-center justify-center text-white shadow-lg shadow-green-700/20">
                            <i data-lucide="layers" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-green-950"><?php echo $classes; ?></p>
                            <p class="text-green-700 font-bold uppercase text-xs tracking-widest">Total Classes</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12 pb-12">
                <!-- Students who have not yet evaluated -->
                <div class="bg-white rounded-3xl border-2 border-green-200 overflow-hidden shadow-lg shadow-green-900/5 flex flex-col h-[450px]">
                    <div class="bg-green-950 p-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="text-green-300 w-6 h-6"></i>
                            <h2 class="text-white font-black text-lg uppercase tracking-tight">Pending Student Evaluations</h2>
                        </div>
                        <span class="bg-green-100 text-green-950 px-3 py-1 rounded-full text-xs font-black">
                            <?php 
                                $acid = $academic['id'] ?? 0;
                                $pending_students = $conn->query("SELECT count(*) as total FROM student_list s2 WHERE s2.is_deleted = 0 AND s2.id IN (SELECT s3.id FROM student_list s3 JOIN restriction_list r ON s3.class_id = r.class_id LEFT JOIN evaluation_list el ON el.student_id = s3.id AND el.faculty_id = r.faculty_id AND el.subject_id = r.subject_id AND el.academic_id = r.academic_id WHERE r.academic_id = $acid GROUP BY s3.id HAVING COUNT(r.id) > COUNT(el.id))");
                                echo $pending_students ? $pending_students->fetch_assoc()['total'] : 0;
                            ?>
                        </span>
                    </div>
                    <div class="p-0 overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-green-50 z-10">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-black text-green-800 uppercase border-b border-green-200">Student Name</th>
                                    <th class="px-6 py-4 text-xs font-black text-green-800 uppercase border-b border-green-200">Class</th>
                                    <th class="px-6 py-4 text-xs font-black text-green-800 uppercase border-b border-green-200 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $students_list = $conn->query("SELECT s.*, c.class_name FROM student_list s JOIN class_list c ON s.class_id = c.id WHERE s.is_deleted = 0 AND s.id IN (SELECT s2.id FROM student_list s2 JOIN restriction_list r ON s2.class_id = r.class_id LEFT JOIN evaluation_list el ON el.student_id = s2.id AND el.faculty_id = r.faculty_id AND el.subject_id = r.subject_id AND el.academic_id = r.academic_id WHERE r.academic_id = $acid GROUP BY s2.id HAVING COUNT(r.id) > COUNT(el.id)) ORDER BY s.lastname ASC LIMIT 50");
                                if($students_list->num_rows > 0):
                                while($row = $students_list->fetch_assoc()):
                                ?>
                                <tr class="hover:bg-green-50/50 border-b border-green-50 group">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-green-950 text-sm group-hover:text-green-600 transition-colors"><?php echo $row['lastname'].', '.$row['firstname'] ?></p>
                                        <p class="text-[10px] text-green-400 font-mono"><?php echo $row['school_id'] ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-green-700"><?php echo $row['class_name'] ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded-md uppercase">Pending</span>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-20 text-center text-green-300 italic">No pending student evaluations.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Faculty who have not yet been evaluated -->
                <div class="bg-white rounded-3xl border-2 border-green-200 overflow-hidden shadow-lg shadow-green-900/5 flex flex-col h-[450px]">
                    <div class="bg-green-950 p-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i data-lucide="graduation-cap" class="text-green-300 w-6 h-6"></i>
                            <h2 class="text-white font-black text-lg uppercase tracking-tight">Unevaluated Faculty</h2>
                        </div>
                        <span class="bg-green-100 text-green-950 px-3 py-1 rounded-full text-xs font-black">
                            <?php 
                                $acid = $academic['id'] ?? 0;
                                $unevaluated_fac = $conn->query("SELECT count(*) as total FROM faculty_list f WHERE f.is_deleted = 0 AND f.id IN (SELECT r.faculty_id FROM restriction_list r WHERE r.academic_id = $acid) AND f.id NOT IN (SELECT DISTINCT faculty_id FROM evaluation_list WHERE academic_id = $acid)");
                                echo $unevaluated_fac ? $unevaluated_fac->fetch_assoc()['total'] : 0;
                            ?>
                        </span>
                    </div>
                    <div class="p-0 overflow-y-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-green-50 z-10">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-black text-green-800 uppercase border-b border-green-200">Faculty Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $fac_list = $conn->query("SELECT f.* FROM faculty_list f WHERE f.is_deleted = 0 AND f.id IN (SELECT r.faculty_id FROM restriction_list r WHERE r.academic_id = $acid) AND f.id NOT IN (SELECT DISTINCT faculty_id FROM evaluation_list WHERE academic_id = $acid) ORDER BY f.lastname ASC LIMIT 50");
                                if($fac_list->num_rows > 0):
                                while($row = $fac_list->fetch_assoc()):
                                ?>
                                <tr class="hover:bg-green-50/50 border-b border-green-50 group">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-green-950 text-sm group-hover:text-green-600 transition-colors"><?php echo $row['lastname'].', '.$row['firstname'] ?></p>
                                        <p class="text-[10px] text-green-400 font-mono"><?php echo $row['school_id'] ?></p>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="1" class="px-6 py-20 text-center text-green-300 italic">All assigned faculty have been evaluated.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();

        function updateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            
            document.getElementById('currentDate').innerText = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('currentTime').innerText = now.toLocaleTimeString('en-US', timeOptions);
        }

        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>
