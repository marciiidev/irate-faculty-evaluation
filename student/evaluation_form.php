<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'student') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$fid = $_GET['fid'] ?? '';
$sid = $_GET['sid'] ?? '';
$rid = $_GET['rid'] ?? '';

if (empty($fid) || empty($sid)) {
    header("location: index.php");
    exit();
}

$academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
if (!$academic || $academic['status'] != 1) {
    header("location: index.php");
    exit();
}

$faculty = $conn->query("SELECT * FROM faculty_list WHERE id = $fid")->fetch_assoc();
$subject = $conn->query("SELECT * FROM subject_list WHERE id = $sid")->fetch_assoc();
$student = $conn->query("SELECT * FROM student_list WHERE id = {$_SESSION['login_id']}")->fetch_assoc();

// Check if already evaluated
$check = $conn->query("SELECT id FROM evaluation_list WHERE academic_id = {$academic['id']} AND student_id = {$_SESSION['login_id']} AND faculty_id = $fid AND subject_id = $sid");
if ($check->num_rows > 0) {
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Form | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-green-50 min-h-screen flex text-green-950">
    <?php include 'sidebar.php'; ?>
    <main class="flex-1 flex flex-col min-w-0">
        <?php include 'topbar.php'; ?>
        <div class="md:p-8 p-4 max-w-5xl mx-auto w-full bg-white shadow-2xl rounded-3xl md:rounded-[40px] md:my-8 my-4 border border-green-100">
            <!-- Header from Image -->
            <div class="text-center mb-8 space-y-2">
                <img src="../assets/Bpc logo.png" alt="BPC Logo" class="w-16 h-16 md:w-24 md:h-24 mx-auto mb-4 object-contain rounded-full">
                <h1 class="text-lg md:text-2xl font-black text-green-950 tracking-tight uppercase">BULACAN POLYTECHNIC COLLEGE</h1>
                <p class="text-sm md:text-base text-green-800 font-bold">Bulihan, City of Malolos, Bulacan</p>
                
                <div class="pt-4 md:pt-6 space-y-1">
                    <h2 class="text-base md:text-xl font-black text-green-950 tracking-wide uppercase">FACULTY EVALUATION F-1: STUDENTS' EVALUATION</h2>
                    <h3 class="text-sm md:text-lg font-bold text-green-900 uppercase">FACULTY EVALUATION FORM</h3>
                    <p class="text-sm md:text-base text-green-800 font-black">SY <?php echo $academic['year']; ?> <?php echo ($academic['semester'] == 1) ? '1st' : '2nd'; ?> Semester</p>
                </div>
            </div>

            <div class="space-y-4 md:space-y-6 mb-8 md:mb-10">
                <p class="text-xs md:text-sm text-green-900 leading-relaxed">
                    <span class="font-black italic">Note:</span> This questionnaire gives you an opportunity to express anonymously your views about your instructor. Carefully and honestly rate the performance of your instructor.
                </p>
                <p class="text-xs md:text-sm text-green-900 leading-relaxed">
                    <span class="font-black italic">Instructions:</span> Read each statement carefully and indicate your response by writing your rating on the provided answer sheet. The number rating stands for the following:
                </p>
            </div>

            <!-- Rating Scale Box from Image -->
            <div class="relative border-2 border-green-600 rounded-2xl p-4 md:p-6 mb-8 md:mb-12">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-white px-3 md:-top-4 md:px-4">
                    <span class="text-sm md:text-xl font-black text-green-900 uppercase tracking-widest">Rating Scale</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-4 text-center">
                    <div class="text-[10px] md:text-sm font-black text-green-900">1 - Never/Rarely manifested</div>
                    <div class="text-[10px] md:text-sm font-black text-green-900">2 - Seldom manifested</div>
                    <div class="text-[10px] md:text-sm font-black text-green-900">3 - Sometimes manifested</div>
                    <div class="text-[10px] md:text-sm font-black text-green-900">4 - Often manifested</div>
                    <div class="text-[10px] md:text-sm font-black text-green-900 col-span-2 md:col-span-1">5 - Always manifested</div>
                </div>
            </div>

            <div class="mb-6 md:mb-8 p-4 md:p-6 bg-green-50 rounded-2xl md:rounded-3xl border border-green-200 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-950 text-white rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="user" class="w-5 h-5 md:w-6 md:h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-green-400 uppercase tracking-widest">Instructor</p>
                        <p class="text-base md:text-lg font-black text-green-950"><?php echo $faculty['firstname'].' '.$faculty['lastname']; ?></p>
                    </div>
                </div>
                <div class="text-left md:text-right w-full md:w-auto">
                    <p class="text-[10px] font-bold text-green-400 uppercase tracking-widest">Subject</p>
                    <p class="text-xs md:text-sm font-bold text-green-800"><?php echo $subject['subject_code'].' - '.$subject['subject_name']; ?></p>
                </div>
            </div>

            <form id="evaluationForm" class="space-y-6 md:space-y-8 pb-10 md:pb-20">
                <input type="hidden" name="academic_id" value="<?php echo $academic['id']; ?>">
                <input type="hidden" name="faculty_id" value="<?php echo $fid; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $sid; ?>">
                <input type="hidden" name="class_id" value="<?php echo $student['class_id']; ?>">

                <?php
                $criteria_res = $conn->query("SELECT * FROM criteria_list ORDER BY order_by ASC");
                while($crit = $criteria_res->fetch_assoc()):
                ?>
                <div class="bg-white rounded-2xl md:rounded-3xl border border-green-200 shadow-sm overflow-hidden">
                    <div class="bg-green-50 px-6 md:px-8 py-3 md:py-4 border-b border-green-200">
                        <h3 class="font-bold text-sm md:text-base text-green-950"><?php echo $crit['criteria']; ?></h3>
                    </div>
                    <div class="divide-y divide-green-100">
                        <?php
                        $q_stmt = $conn->prepare("SELECT * FROM question_list WHERE criteria_id = ? AND academic_id = ? ORDER BY order_by ASC");
                        $q_stmt->bind_param("ii", $crit['id'], $academic['id']);
                        $q_stmt->execute();
                        $q_res = $q_stmt->get_result();
                        while($q = $q_res->fetch_assoc()):
                        ?>
                        <div class="p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-6">
                            <p class="text-sm md:text-base text-green-800 flex-1"><?php echo $q['question']; ?></p>
                            <div class="flex gap-1.5 md:gap-2 w-full md:w-auto justify-between md:justify-start">
                                <?php for($i=5; $i>=1; $i--): ?>
                                <label class="cursor-pointer group flex-1 md:flex-none">
                                    <input type="radio" name="rate[<?php echo $q['id']; ?>]" value="<?php echo $i; ?>" required class="hidden peer">
                                    <div class="w-full md:w-10 h-10 rounded-xl border-2 border-green-200 flex items-center justify-center font-bold text-xs md:text-base text-green-400 peer-checked:bg-green-950 peer-checked:border-green-950 peer-checked:text-white transition-all group-hover:border-green-400">
                                        <?php echo $i; ?>
                                    </div>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endwhile; ?>

                <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-green-200 shadow-sm space-y-4">
                    <label class="text-xs md:text-sm font-bold text-green-800 uppercase tracking-widest">Please write your comments, suggestions or opinions about the instructor here.</label>
                    <textarea name="comment" class="w-full px-4 md:px-6 py-3 md:py-4 rounded-xl md:rounded-2xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none transition-all min-h-[100px] md:min-h-[120px] text-sm md:text-base" placeholder="Share your thoughts about the faculty's performance..."></textarea>
                </div>

                <div class="text-center py-4 md:py-8">
                    <p class="text-sm md:text-xl font-black text-green-950 tracking-widest uppercase">THANK YOU FOR YOUR HONEST PARTICIPATION !!! ☺☺☺</p>
                </div>

                <div class="flex flex-col md:flex-row gap-3 md:gap-4">
                    <a href="index.php" class="bg-green-200 text-green-800 py-3 md:py-4 rounded-xl md:rounded-2xl font-bold hover:bg-green-300 transition-all text-center order-2 md:order-1 flex-1">Cancel</a>
                    <button type="submit" class="bg-green-950 text-white py-3 md:py-4 rounded-xl md:rounded-2xl font-bold hover:bg-green-900 transition-all shadow-xl order-1 md:order-2 flex-[2]">Submit Evaluation</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        lucide.createIcons();

        document.getElementById('evaluationForm').onsubmit = function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Submit Evaluation?',
                text: "This action cannot be undone. Are you sure you're finished with this evaluation?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#052e16',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);
                    
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait while we record your feedback.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('../ajax.php?action=save_evaluation', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
                        if (data.status == 1) {
                            // Create a custom success overlay
                            const overlay = document.createElement('div');
                            overlay.className = 'fixed inset-0 bg-green-950/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4';
                            
                            let certificateLink = '';
                            if (data.completed_all) {
                                certificateLink = `
                                    <a href="certificate.php?auto_send=1" class="bg-green-600 text-white py-4 rounded-2xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
                                        <i data-lucide="award" class="w-5 h-5"></i>
                                        View & Email Your Certificate
                                    </a>
                                `;
                            } else {
                                certificateLink = `
                                    <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 text-center">
                                        <p class="text-amber-800 text-sm font-bold">You have more evaluations to complete before you can download your certificate.</p>
                                    </div>
                                `;
                            }

                            overlay.innerHTML = `
                                <div class="bg-white p-12 rounded-[40px] shadow-2xl max-w-lg w-full text-center space-y-8 animate-in fade-in zoom-in duration-300">
                                    <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                        <i data-lucide="check-circle" class="w-12 h-12"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <h2 class="text-3xl font-black text-green-950">Evaluation Submitted!</h2>
                                        <p class="text-green-700">Thank you for your participation. Your feedback is highly valued.</p>
                                    </div>
                                    <div class="flex flex-col gap-3 pt-4">
                                        ${certificateLink}
                                        <a href="index.php" class="text-green-400 font-bold hover:text-green-950 transition-all">Back to Dashboard</a>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(overlay);
                            lucide.createIcons();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data,
                                confirmButtonColor: '#052e16'
                            });
                        }
                    })
                    .catch(err => {
                        Swal.close();
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "An error occurred while saving your evaluation.",
                            confirmButtonColor: '#052e16'
                        });
                    });
                }
            });
        };
    </script>
</body>
</html>
