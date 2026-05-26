<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'student') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex text-green-950">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>

        <div class="p-8 space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-bold text-green-950">Welcome, <?php echo explode(' ', $_SESSION['login_name'])[0]; ?>!</h1>
                    <p class="text-green-700">Participate in the faculty evaluation process.</p>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-medium">Academic Year</p>
                        <p class="text-lg font-bold text-green-950"><?php echo $academic['year'] ?? 'Not Set'; ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-medium">Semester</p>
                        <p class="text-lg font-bold text-green-950">
                            <?php echo isset($academic['semester']) ? ($academic['semester'] == 1 ? '1st' : '2nd') : 'N/A'; ?> Semester
                        </p>
                    </div>
                </div>
                <!-- Evaluation Status Card -->
                <div class="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i data-lucide="check-circle" class="text-green-600 w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-green-700 font-medium">Evaluation Status</p>
                        <?php
                        $status = "NOT SET";
                        $status_class = "bg-gray-100 text-gray-700";
                        if(isset($academic['status'])){
                            if($academic['status'] == 0){
                                $status = "PENDING";
                                $status_class = "bg-amber-100 text-amber-700";
                            }elseif($academic['status'] == 1){
                                $status = "ONGOING";
                                $status_class = "bg-green-100 text-green-700";
                            }elseif($academic['status'] == 2){
                                $status = "CLOSED";
                                $status_class = "bg-red-100 text-red-700";
                            }
                        }
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo $status_class; ?> mt-1 uppercase tracking-wider">
                            <?php echo $status; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Evaluation Section -->
            <div class="pt-8 border-t border-green-200">
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-green-950">Faculty Evaluation</h2>
                        <p class="text-green-700">Complete all assigned evaluations to receive your certificate.</p>
                    </div>
                    <?php
                    $sid = $_SESSION['login_id'];
                    $academic_id = $academic['id'] ?? 0;
                    
                    // Get student's class
                    $student = $conn->query("SELECT class_id FROM student_list WHERE id = $sid")->fetch_assoc();
                    $class_id = $student['class_id'] ?? 0;
                    
                    // Check for pending evaluations
                    $pending_check = $conn->prepare("SELECT 1 FROM restriction_list r WHERE r.academic_id = ? AND r.class_id = ? AND NOT EXISTS (SELECT 1 FROM evaluation_list e WHERE e.academic_id = r.academic_id AND e.student_id = ? AND e.faculty_id = r.faculty_id AND e.subject_id = r.subject_id) LIMIT 1");
                    $pending_check->bind_param("iii", $academic_id, $class_id, $sid);
                    $pending_check->execute();
                    $has_pending = $pending_check->get_result()->num_rows > 0;
                    
                    // Check if they have done at least one evaluation
                    $has_done_any = $conn->query("SELECT 1 FROM evaluation_list WHERE academic_id = $academic_id AND student_id = $sid LIMIT 1")->num_rows > 0;
                    
                    $all_done = (!$has_pending && $has_done_any);
                    
                    if($all_done):
                    ?>
                    <a href="certificate.php" class="bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center gap-2 self-start md:self-auto animate-bounce">
                        <i data-lucide="award" class="w-5 h-5"></i>
                        Download Certificate
                    </a>
                    <?php endif; ?>
                </div>

                <?php if(!$academic || $academic['status'] != 1): ?>
                <div class="bg-amber-50 border border-amber-200 p-8 rounded-3xl text-center">
                    <i data-lucide="alert-circle" class="w-12 h-12 text-amber-500 mx-auto mb-4"></i>
                    <h2 class="text-xl font-bold text-amber-900">Evaluation is currently closed.</h2>
                    <p class="text-amber-700">Please wait for the administrator to open the evaluation period.</p>
                </div>
                <?php else: ?>
                
                <div class="space-y-12">
                    <!-- List of Evaluations (Pending) -->
                    <section>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-green-950 text-white rounded-xl flex items-center justify-center">
                                <i data-lucide="list-todo" class="w-5 h-5"></i>
                            </div>
                            <h2 class="text-xl font-black text-green-950 uppercase tracking-tight">List of Evaluations</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            $pending_stmt = $conn->prepare("SELECT r.*, f.firstname, f.lastname, f.school_id, s.subject_code, s.subject_name FROM restriction_list r JOIN faculty_list f ON r.faculty_id = f.id JOIN subject_list s ON r.subject_id = s.id WHERE r.academic_id = ? AND r.class_id = ? AND NOT EXISTS (SELECT 1 FROM evaluation_list e WHERE e.academic_id = r.academic_id AND e.student_id = ? AND e.faculty_id = r.faculty_id AND e.subject_id = r.subject_id)");
                            $pending_stmt->bind_param("iii", $academic_id, $class_id, $sid);
                            $pending_stmt->execute();
                            $pending_res = $pending_stmt->get_result();
                            
                            if($pending_res->num_rows == 0):
                            ?>
                            <div class="col-span-full bg-green-100/50 border-2 border-dashed border-green-200 rounded-3xl p-8 text-center">
                                <p class="text-green-600 font-bold">No pending evaluations. Great job!</p>
                            </div>
                            <?php else: while($row = $pending_res->fetch_assoc()): ?>
                            <div class="bg-white p-6 rounded-2xl border border-green-200 shadow-sm hover:shadow-md transition-all group">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-400">
                                        <i data-lucide="user" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-green-950"><?php echo $row['firstname'].' '.$row['lastname']; ?></h3>
                                        <p class="text-xs text-green-400 font-mono"><?php echo $row['school_id']; ?></p>
                                    </div>
                                </div>
                                <div class="mb-6 p-3 bg-green-50 rounded-xl border border-green-100">
                                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-1">Subject</p>
                                    <p class="text-sm font-bold text-green-800"><?php echo $row['subject_code'].' - '.$row['subject_name']; ?></p>
                                </div>
                                <a href="evaluation_form.php?fid=<?php echo $row['faculty_id']; ?>&sid=<?php echo $row['subject_id']; ?>&rid=<?php echo $row['id']; ?>" class="block w-full bg-green-950 text-white py-3 rounded-xl font-bold hover:bg-green-900 transition-all text-center shadow-lg">
                                    Start Evaluation
                                </a>
                            </div>
                            <?php endwhile; endif; ?>
                        </div>
                    </section>

                    <!-- Evaluated Subjects (Completed) -->
                    <section>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-green-600 text-white rounded-xl flex items-center justify-center">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <h2 class="text-xl font-black text-green-950 uppercase tracking-tight">Evaluated Subjects</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 opacity-75">
                            <?php
                            $done_stmt = $conn->prepare("SELECT r.*, f.firstname, f.lastname, f.school_id, s.subject_code, s.subject_name FROM restriction_list r JOIN faculty_list f ON r.faculty_id = f.id JOIN subject_list s ON r.subject_id = s.id WHERE r.academic_id = ? AND r.class_id = ? AND EXISTS (SELECT 1 FROM evaluation_list e WHERE e.academic_id = r.academic_id AND e.student_id = ? AND e.faculty_id = r.faculty_id AND e.subject_id = r.subject_id)");
                            $done_stmt->bind_param("iii", $academic_id, $class_id, $sid);
                            $done_stmt->execute();
                            $done_res = $done_stmt->get_result();
                            
                            if($done_res->num_rows == 0):
                            ?>
                            <div class="col-span-full text-center py-8 text-green-400 italic">
                                You haven't completed any evaluations yet.
                            </div>
                            <?php else: while($row = $done_res->fetch_assoc()): ?>
                            <div class="bg-green-50/50 p-6 rounded-2xl border border-green-200 shadow-sm grayscale-[0.5]">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-12 h-12 bg-green-200 rounded-xl flex items-center justify-center text-green-600">
                                        <i data-lucide="check" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-green-950"><?php echo $row['firstname'].' '.$row['lastname']; ?></h3>
                                        <p class="text-xs text-green-400 font-mono"><?php echo $row['school_id']; ?></p>
                                    </div>
                                </div>
                                <div class="p-3 bg-white rounded-xl border border-green-100">
                                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest mb-1">Subject</p>
                                    <p class="text-sm font-bold text-green-800"><?php echo $row['subject_code'].' - '.$row['subject_name']; ?></p>
                                </div>
                            </div>
                            <?php endwhile; endif; ?>
                        </div>
                    </section>
                </div>
                <?php endif; ?>
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
