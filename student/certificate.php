<?php
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'student') {
    header("location: ../index.php");
    exit();
}
require_once '../evaluation_db/db_connect.php';

$sid = $_SESSION['login_id'];
$academic = $conn->query("SELECT * FROM academic_list WHERE is_default = 1")->fetch_assoc();
$student = $conn->query("SELECT * FROM student_list WHERE id = $sid")->fetch_assoc();

if (!$academic || !$student) {
    echo "Invalid access.";
    exit();
}

$school_year = $academic['year'];
$semester = ($academic['semester'] == 1) ? "1st Semester" : "2nd Semester";
$student_name = $student['firstname'] . ' ' . $student['lastname'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Participation | Faculty Evaluation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white !important; padding: 0 !important; }
            .cert-container { border: none !important; box-shadow: none !important; width: 100% !important; max-width: none !important; margin: 0 !important; }
        }
        .cert-bg {
            background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('../assets/bg.Bpc.png');
            background-size: cover;
            background-position: center;
        }
        .serif { font-family: 'Playfair Display', serif; }
        
        /* Fix for logo overlap and layout */
        .cert-content {
            padding-top: 4rem;
            padding-bottom: 3rem;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 md:p-8" style="background-image: url('../assets/bg.Bpc.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    
    <div class="no-print mb-6 w-full max-w-2xl px-4 space-y-4">
        <button id="downloadBtn" onclick="downloadCertificate()" class="w-full bg-green-950 text-white px-8 py-3.5 rounded-2xl font-bold hover:bg-green-900 transition-all shadow-lg flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span class="text-base md:text-lg">Download Certificate</span>
        </button>
        <div class="flex gap-4">
            <button id="emailBtn" onclick="sendToEmail()" class="flex-1 bg-blue-600 text-white px-6 py-3.5 rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-lg flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
                <span class="text-sm md:text-base">Send to Email</span>
            </button>
            <a href="index.php" class="flex-1 bg-white text-green-950 px-6 py-3.5 rounded-2xl font-bold hover:bg-green-50 transition-all border-2 border-green-200 flex items-center justify-center gap-2 text-center">
                <span class="text-sm md:text-base">Back to Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Container for scaling the certificate on mobile -->
    <div class="w-full max-w-6xl px-4 flex justify-center mb-10">
        <div id="certificate-wrapper" class="transform-gpu transition-all duration-300">
            <div id="certificate" class="cert-container bg-white w-[1000px] shadow-2xl border-[16px] border-green-800 p-1 relative overflow-hidden">
                <div class="cert-bg h-full w-full border-4 border-green-800 flex flex-col items-center justify-center text-center p-12 relative cert-content">
                    
                    <!-- Header -->
                    <div class="mb-4 md:mb-6">
                        <img src="../assets/Bpc logo.png" alt="BPC Logo" class="w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 object-contain rounded-full" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Bulacan_Polytechnic_College_Logo.png/200px-Bulacan_Polytechnic_College_Logo.png'">
                        <h2 class="text-xl md:text-2xl font-bold text-green-800 tracking-widest uppercase mb-1">Bulacan Polytechnic College</h2>
                        <p class="text-sm md:text-lg text-green-700 font-medium tracking-widest uppercase">Faculty Evaluation System</p>
                    </div>

                    <!-- Title -->
                    <div class="mb-6 md:mb-8">
                        <h1 class="text-4xl md:text-5xl serif text-green-800 mb-2">Certificate of Participation</h1>
                        <p class="text-base md:text-lg text-green-700">This is to certify that</p>
                    </div>

                    <!-- Name -->
                    <div class="mb-6 md:mb-8 w-full max-w-2xl px-4">
                        <div class="text-3xl md:text-4xl font-bold text-green-950 border-b-2 border-green-300 pb-2 mb-4 uppercase tracking-wide">
                            <?php echo $student_name; ?>
                        </div>
                        <p class="text-base md:text-lg text-green-700 leading-relaxed">
                            has actively participated in the Faculty Evaluation System for the <br>
                            <span class="font-bold text-green-800">School Year <?php echo $school_year; ?> and <?php echo $semester; ?>.</span>
                        </p>
                    </div>

                    <!-- Footer Message -->
                    <div class="max-w-3xl px-6">
                        <p class="text-green-600 italic text-sm">
                            The feedback and insights provided contribute significantly to the continuous improvement of our faculty members.
                        </p>
                    </div>

                    <!-- Decorative Elements -->
                    <div class="absolute bottom-12 left-12 w-32 h-32 border-l-4 border-b-4 border-green-800/20"></div>
                    <div class="absolute top-12 right-12 w-32 h-32 border-r-4 border-t-4 border-green-800/20"></div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Scaling logic for mobile view
        function scaleCertificate() {
            const wrapper = document.getElementById('certificate-wrapper');
            const cert = document.getElementById('certificate');
            const container = wrapper.parentElement;
            
            if (window.innerWidth < 1000) {
                const scale = (container.offsetWidth) / 1000;
                wrapper.style.transform = `scale(${scale})`;
                wrapper.style.transformOrigin = 'top center';
                wrapper.style.marginBottom = '-' + (cert.offsetHeight * (1 - scale)) + 'px';
            } else {
                wrapper.style.transform = 'none';
                wrapper.style.marginBottom = '0';
            }
        }

        window.addEventListener('resize', scaleCertificate);
        window.addEventListener('load', scaleCertificate);
        scaleCertificate();

        async function downloadCertificate() {
            const btn = document.getElementById('downloadBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

            try {
                const { jsPDF } = window.jspdf;
                const element = document.getElementById('certificate');
                
                const canvas = await html2canvas(element, {
                    scale: 3, // High resolution
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: 1000,
                    onclone: (clonedDoc) => {
                        const clonedWrapper = clonedDoc.getElementById('certificate-wrapper');
                        const clonedCert = clonedDoc.getElementById('certificate');
                        if (clonedWrapper) {
                            clonedWrapper.style.transform = 'none';
                            clonedWrapper.style.margin = '0';
                            clonedWrapper.style.width = '1000px';
                        }
                    }
                });
                
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [canvas.width, canvas.height]
                });
                
                pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height);
                pdf.save('BPC_Certificate_<?php echo str_replace(" ", "_", $student_name); ?>.pdf');
            } catch (error) {
                console.error('Download failed:', error);
                alert('Failed to generate PDF. Please try using the Print option instead (Ctrl+P).');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function sendToEmail(isAuto = false) {
            const studentEmail = '<?php echo $student['email']; ?>';
            if (!studentEmail || !studentEmail.includes('@')) {
                console.error('Invalid student email:', studentEmail);
                if (!isAuto) alert('Your account does not have a valid email address. Please update your profile.');
                return;
            }

            const btn = document.getElementById('emailBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ' + (isAuto ? 'Auto-sending...' : 'Sending...');

            try {
                const element = document.getElementById('certificate');
                const canvas = await html2canvas(element, {
                    scale: 3, // Match download quality
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    windowWidth: 1000,
                    onclone: (clonedDoc) => {
                        const clonedWrapper = clonedDoc.getElementById('certificate-wrapper');
                        if (clonedWrapper) {
                            clonedWrapper.style.transform = 'none';
                            clonedWrapper.style.margin = '0';
                            clonedWrapper.style.width = '1000px';
                        }
                    }
                });
                
                const imgData = canvas.toDataURL('image/jpeg', 0.9); // High quality JPEG
                
                const formData = new FormData();
                formData.append('imgData', imgData);
                formData.append('name', '<?php echo $student_name; ?>');
                formData.append('email', '<?php echo $student['email']; ?>');

                const res = await fetch('../ajax.php?action=send_certificate_email', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.text();
                if (data == 1) {
                    if (!isAuto) alert('Certificate has been sent to your email: <?php echo $student['email']; ?>');
                    else console.log('Auto-sent certificate to <?php echo $student['email']; ?>');
                } else {
                    if (!isAuto) alert('Failed to send email: ' + data);
                    else console.error('Auto-send failed:', data);
                }
            } catch (error) {
                console.error('Email failed:', error);
                if (!isAuto) alert('An error occurred while sending the email: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Auto-send logic
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_send') === '1') {
                // Wait a bit for the certificate to be fully rendered
                setTimeout(() => {
                    sendToEmail(true);
                }, 1500);
            }
        }
    </script>
</body>
</html>
