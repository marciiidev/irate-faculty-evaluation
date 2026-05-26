<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'faculty') {
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
    <title>Faculty Dashboard | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="p-8 space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-bold text-green-950">Welcome, <?php echo explode(' ', $_SESSION['login_name'])[0]; ?>!</h1>
                    <p class="text-green-700">View your performance results and student feedback.</p>
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
