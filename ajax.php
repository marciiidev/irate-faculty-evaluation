<?php
session_start();
require_once 'evaluation_db/db_connect.php';

$action = $_GET['action'] ?? '';

if ($action == 'save_faculty') {
    extract($_POST);
    $school_id = $conn->real_escape_string($school_id);
    $firstname = $conn->real_escape_string($firstname);
    $lastname = $conn->real_escape_string($lastname);
    $email = $conn->real_escape_string($email);

    $data = " school_id = '$school_id' ";
    $data .= ", firstname = '$firstname' ";
    $data .= ", lastname = '$lastname' ";
    $data .= ", email = '$email' ";
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $data .= ", password = '$hashed' ";
        $data .= ", password_text = '" . $conn->real_escape_string($password) . "' ";
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name'] != '') {
        $fname = time() . '_' . $_FILES['avatar']['name'];
        $move = move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $fname);
        if ($move) {
            $data .= ", avatar = '$fname' ";
            if (!empty($id)) {
                $old_res = $conn->query("SELECT avatar FROM faculty_list WHERE id = $id");
                if($old_res && $old_res->num_rows > 0){
                    $old_avatar = $old_res->fetch_assoc()['avatar'];
                    if (!empty($old_avatar) && is_file('assets/uploads/' . $old_avatar)) {
                        unlink('assets/uploads/' . $old_avatar);
                    }
                }
            }
        }
    }

    if (empty($id)) {
        $save = $conn->query("INSERT INTO faculty_list SET $data");
    } else {
        $save = $conn->query("UPDATE faculty_list SET $data WHERE id = $id");
    }

    if ($save) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'update_account') {
    extract($_POST);
    $id = $_SESSION['login_id'];
    $type = $_SESSION['login_type'];
    
    if ($type == 'superadmin' || $type == 'admin') {
        $table = 'users';
    } else if ($type == 'faculty') {
        $table = 'faculty_list';
    } else if ($type == 'student') {
        $table = 'student_list';
    }

    $firstname = $conn->real_escape_string($firstname);
    $lastname = $conn->real_escape_string($lastname);
    $email = $conn->real_escape_string($email);

    $data = " firstname = '$firstname' ";
    $data .= ", lastname = '$lastname' ";
    $data .= ", email = '$email' ";

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $data .= ", password = '$hashed' ";
        $data .= ", password_text = '" . $conn->real_escape_string($password) . "' ";
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name'] != '') {
        $fname = time() . '_' . $_FILES['avatar']['name'];
        $move = move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $fname);
        if ($move) {
            $data .= ", avatar = '$fname' ";
            if (isset($_SESSION['login_avatar']) && !empty($_SESSION['login_avatar']) && $_SESSION['login_avatar'] != $fname && is_file('assets/uploads/' . $_SESSION['login_avatar'])) {
                unlink('assets/uploads/' . $_SESSION['login_avatar']);
            }
            $_SESSION['login_avatar'] = $fname;
        }
    }

    $save = $conn->query("UPDATE $table SET $data WHERE id = $id");
    if ($save) {
        $_SESSION['login_firstname'] = $firstname;
        $_SESSION['login_lastname'] = $lastname;
        $_SESSION['login_email'] = $email;
        $_SESSION['login_name'] = $firstname . ' ' . $lastname;
        echo 1;
    } else {
        echo "Error: " . $conn->error;
    }
}

if ($action == 'delete_faculty') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE faculty_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'perm_delete_data') {
    extract($_POST);
    $table = '';
    if ($type == 'student') $table = 'student_list';
    else if ($type == 'faculty') $table = 'faculty_list';
    else if ($type == 'admin' || $type == 'superadmin' || $type == 'user') $table = 'users';
    else if ($type == 'academic') $table = 'academic_list';
    else if ($type == 'class') $table = 'class_list';
    else if ($type == 'subject') $table = 'subject_list';
    else if ($type == 'criteria') $table = 'criteria_list';
    else if ($type == 'question') $table = 'question_list';
    else if ($type == 'restriction') $table = 'restriction_list';
    
    if (!empty($table)) {
        $delete = $conn->query("DELETE FROM $table WHERE id = $id");
        if ($delete) echo 1;
    }
}

if ($action == 'save_student') {
    extract($_POST);
    $school_id = $conn->real_escape_string($school_id);
    $firstname = $conn->real_escape_string($firstname);
    $lastname = $conn->real_escape_string($lastname);
    $email = $conn->real_escape_string($email);
    $class_id = $conn->real_escape_string($class_id);

    $data = " school_id = '$school_id' ";
    $data .= ", firstname = '$firstname' ";
    $data .= ", lastname = '$lastname' ";
    $data .= ", email = '$email' ";
    $data .= ", class_id = '$class_id' ";
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $data .= ", password = '$hashed' ";
        $data .= ", password_text = '" . $conn->real_escape_string($password) . "' ";
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name'] != '') {
        $fname = time() . '_' . $_FILES['avatar']['name'];
        $move = move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $fname);
        if ($move) {
            $data .= ", avatar = '$fname' ";
            if (!empty($id)) {
                $old_res = $conn->query("SELECT avatar FROM student_list WHERE id = $id");
                if($old_res && $old_res->num_rows > 0){
                    $old_avatar = $old_res->fetch_assoc()['avatar'];
                    if (!empty($old_avatar) && is_file('assets/uploads/' . $old_avatar)) {
                        unlink('assets/uploads/' . $old_avatar);
                    }
                }
            }
        }
    }

    if (empty($id)) {
        $save = $conn->query("INSERT INTO student_list SET $data");
    } else {
        $save = $conn->query("UPDATE student_list SET $data WHERE id = $id");
    }

    if ($save) echo 1;
}

if ($action == 'delete_student') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE student_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'save_user') {
    extract($_POST);
    $data = " firstname = '$firstname' ";
    $data .= ", lastname = '$lastname' ";
    $data .= ", email = '$email' ";
    $data .= ", role = '$role' ";
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $data .= ", password = '$hashed' ";
        $data .= ", password_text = '" . $conn->real_escape_string($password) . "' ";
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name'] != '') {
        $fname = time() . '_' . $_FILES['avatar']['name'];
        $move = move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $fname);
        if ($move) {
            $data .= ", avatar = '$fname' ";
            if (!empty($id)) {
                $old_res = $conn->query("SELECT avatar FROM users WHERE id = $id");
                if($old_res && $old_res->num_rows > 0){
                    $old_avatar = $old_res->fetch_assoc()['avatar'];
                    if (!empty($old_avatar) && is_file('assets/uploads/' . $old_avatar)) {
                        unlink('assets/uploads/' . $old_avatar);
                    }
                }
            }
        }
    }

    if (empty($id)) {
        $save = $conn->query("INSERT INTO users SET $data");
    } else {
        $save = $conn->query("UPDATE users SET $data WHERE id = $id");
    }

    if ($save) echo 1;
}

if ($action == 'delete_user') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE users SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'restore_data') {
    extract($_POST);
    $table = '';
    if ($type == 'student') $table = 'student_list';
    else if ($type == 'faculty') $table = 'faculty_list';
    else if ($type == 'admin' || $type == 'superadmin' || $type == 'user') $table = 'users';
    else if ($type == 'academic') $table = 'academic_list';
    else if ($type == 'class') $table = 'class_list';
    else if ($type == 'subject') $table = 'subject_list';
    else if ($type == 'criteria') $table = 'criteria_list';
    else if ($type == 'question') $table = 'question_list';
    else if ($type == 'restriction') $table = 'restriction_list';
    
    if (!empty($table)) {
        $restore = $conn->query("UPDATE $table SET is_deleted = 0 WHERE id = $id");
        if ($restore) echo 1;
    }
}

if ($action == 'save_academic') {
    extract($_POST);
    $data = " year = '$year' ";
    $data .= ", semester = '$semester' ";
    
    if (empty($id)) {
        $save = $conn->query("INSERT INTO academic_list SET $data");
    } else {
        $save = $conn->query("UPDATE academic_list SET $data WHERE id = $id");
    }

    if ($save) echo 1;
}

if ($action == 'make_default_academic') {
    $id = $_POST['id'];
    $conn->query("UPDATE academic_list SET is_default = 0");
    $update = $conn->query("UPDATE academic_list SET is_default = 1 WHERE id = $id");
    if ($update) echo 1;
}

if ($action == 'update_academic_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $update = $conn->query("UPDATE academic_list SET status = $status WHERE id = $id");
    if ($update) echo 1;
}

if ($action == 'delete_academic') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE academic_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'save_class') {
    extract($_POST);
    $data = " class_name = '$class_name' ";
    if (empty($id)) {
        $save = $conn->query("INSERT INTO class_list SET $data");
    } else {
        $save = $conn->query("UPDATE class_list SET $data WHERE id = $id");
    }
    if ($save) echo 1;
}

if ($action == 'delete_class') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE class_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'save_subject') {
    extract($_POST);
    $data = " subject_code = '$subject_code' ";
    $data .= ", subject_name = '$subject_name' ";
    if (empty($id)) {
        $save = $conn->query("INSERT INTO subject_list SET $data");
    } else {
        $save = $conn->query("UPDATE subject_list SET $data WHERE id = $id");
    }
    if ($save) echo 1;
}

if ($action == 'delete_subject') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE subject_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'save_criteria') {
    extract($_POST);
    $data = " criteria = '$criteria' ";
    $data .= ", order_by = '$order_by' ";
    if (empty($id)) {
        $save = $conn->query("INSERT INTO criteria_list SET $data");
    } else {
        $save = $conn->query("UPDATE criteria_list SET $data WHERE id = $id");
    }
    if ($save) echo 1;
}

if ($action == 'delete_criteria') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE criteria_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'save_question') {
    extract($_POST);
    $data = " question = '$question' ";
    $data .= ", criteria_id = '$criteria_id' ";
    $data .= ", academic_id = '$academic_id' ";
    $order_by = isset($order_by) ? $order_by : 0;
    $data .= ", order_by = '$order_by' ";
    if (empty($id)) {
        $save = $conn->query("INSERT INTO question_list SET $data");
    } else {
        $save = $conn->query("UPDATE question_list SET $data WHERE id = $id");
    }
    if ($save) echo 1;
}

if ($action == 'delete_question') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE question_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
}

if ($action == 'save_evaluation') {
    extract($_POST);
    $student_id = $_SESSION['login_id'];
    
    // Check if already evaluated
    $check = $conn->query("SELECT id FROM evaluation_list WHERE academic_id = $academic_id AND student_id = $student_id AND faculty_id = $faculty_id AND subject_id = $subject_id");
    if ($check->num_rows > 0) {
        echo "You have already evaluated this faculty for this subject.";
        exit();
    }

    // Save to evaluation_list
    $data = " academic_id = $academic_id ";
    $data .= ", student_id = $student_id ";
    $data .= ", faculty_id = $faculty_id ";
    $data .= ", class_id = $class_id ";
    $data .= ", subject_id = $subject_id ";
    
    $save = $conn->query("INSERT INTO evaluation_list SET $data");
    if ($save) {
        $evaluation_id = $conn->insert_id;
        
        // Save answers
        foreach ($rate as $question_id => $rating) {
            $conn->query("INSERT INTO evaluation_answers SET evaluation_id = $evaluation_id, question_id = $question_id, rating = $rating");
        }
        
        // Save comment
        if (!empty($comment)) {
            $comment = $conn->real_escape_string($comment);
            $conn->query("INSERT INTO evaluation_comments SET evaluation_id = $evaluation_id, comment = '$comment'");
        }
        
        // Check if this was the last one
        $student = $conn->query("SELECT class_id FROM student_list WHERE id = $student_id")->fetch_assoc();
        $class_id = $student['class_id'] ?? 0;
        
        $pending_check = $conn->prepare("SELECT 1 FROM restriction_list r WHERE r.academic_id = ? AND r.class_id = ? AND NOT EXISTS (SELECT 1 FROM evaluation_list e WHERE e.academic_id = r.academic_id AND e.student_id = ? AND e.faculty_id = r.faculty_id AND e.subject_id = r.subject_id) LIMIT 1");
        $pending_check->bind_param("iii", $academic_id, $class_id, $student_id);
        $pending_check->execute();
        $has_pending = $pending_check->get_result()->num_rows > 0;
        
        echo json_encode(['status' => 1, 'completed_all' => !$has_pending]);
    } else {
        echo "Error: " . $conn->error;
    }
}

if ($action == 'get_evaluation_details') {
    $id = $_GET['id'];
    $eval = $conn->query("SELECT el.*, CONCAT(s.firstname, ' ', s.lastname) as student_name, CONCAT(f.firstname, ' ', f.lastname) as faculty_name, a.year, a.semester, ec.comment 
                          FROM evaluation_list el 
                          JOIN student_list s ON el.student_id = s.id 
                          JOIN faculty_list f ON el.faculty_id = f.id 
                          JOIN academic_list a ON el.academic_id = a.id 
                          LEFT JOIN evaluation_comments ec ON ec.evaluation_id = el.id 
                          WHERE el.id = $id")->fetch_assoc();
    
    $answers = [];
    $ans_res = $conn->query("SELECT ea.rating, q.question 
                             FROM evaluation_answers ea 
                             JOIN question_list q ON ea.question_id = q.id 
                             WHERE ea.evaluation_id = $id 
                             ORDER BY q.order_by ASC");
    while($row = $ans_res->fetch_assoc()) {
        $answers[] = $row;
    }
    
    $eval['answers'] = $answers;
    header('Content-Type: application/json');
    echo json_encode($eval);
    exit();
}

if ($action == 'import_csv') {
    extract($_POST);
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        
        // Handle BOM
        $content = file_get_contents($file);
        if (substr($content, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $content = substr($content, 3);
        }
        $temp_file = tmpfile();
        fwrite($temp_file, $content);
        fseek($temp_file, 0);
        
        $count = 0;
        $errors = [];
        
        // Skip header
        fgetcsv($temp_file, 1000, ",");
        
        while (($data = fgetcsv($temp_file, 1000, ",")) !== FALSE) {
            // Skip empty rows
            if (empty($data) || !isset($data[0]) || empty($data[0])) continue;

            // Trim all data
            $data = array_map('trim', $data);

            if ($type == 'student') {
                // Expected CSV: school_id, firstname, lastname, email, [password], class_name
                $school_id = $conn->real_escape_string($data[0]);
                $firstname = $conn->real_escape_string($data[1]);
                $lastname = $conn->real_escape_string($data[2]);
                $email = $conn->real_escape_string($data[3]);
                
                $password_plain = '';
                $class_val = '';

                if (count($data) >= 6) {
                    $password_plain = $data[4];
                    $class_val = $conn->real_escape_string($data[5]);
                } else if (count($data) == 5) {
                    // If 5 columns, we assume it's the exported format: school_id, firstname, lastname, email, class_name
                    $class_val = $conn->real_escape_string($data[4]);
                }
                
                $class_id = 0;
                if (!empty($class_val)) {
                    // Try to find the class ID by name first
                    $class_query = $conn->query("SELECT id FROM class_list WHERE LOWER(TRIM(class_name)) = LOWER(TRIM('$class_val')) ");
                    if ($class_query->num_rows > 0) {
                        $class_id = $class_query->fetch_assoc()['id'];
                    } else if (is_numeric($class_val)) {
                        // Fallback to numeric ID if name not found
                        $class_id = $class_val;
                    }
                }

                $check = $conn->query("SELECT * FROM student_list where school_id = '$school_id' ");
                if ($check->num_rows > 0) {
                    $update_sql = "UPDATE student_list SET firstname = '$firstname', lastname = '$lastname', email = '$email', class_id = '$class_id'";
                    if (!empty($password_plain)) {
                        $password = password_hash($password_plain, PASSWORD_DEFAULT);
                        $update_sql .= ", password = '$password', password_text = '" . $conn->real_escape_string($password_plain) . "'";
                    }
                    $update_sql .= " WHERE school_id = '$school_id'";
                    $save = $conn->query($update_sql);
                } else {
                    if (empty($password_plain)) $password_plain = $school_id;
                    $password = password_hash($password_plain, PASSWORD_DEFAULT);
                    $save = $conn->query("INSERT INTO student_list (school_id, firstname, lastname, email, password, password_text, class_id) VALUES ('$school_id', '$firstname', '$lastname', '$email', '$password', '" . $conn->real_escape_string($password_plain) . "', '$class_id')");
                }
                if ($save) $count++;
                else $errors[] = "Error importing student $school_id: " . $conn->error;

            } else if ($type == 'faculty') {
                // Expected CSV: school_id, firstname, lastname, email, [password]
                $school_id = $conn->real_escape_string($data[0]);
                $firstname = $conn->real_escape_string($data[1]);
                $lastname = $conn->real_escape_string($data[2]);
                $email = $conn->real_escape_string($data[3]);
                
                $password_plain = isset($data[4]) ? $data[4] : '';
                
                $check = $conn->query("SELECT * FROM faculty_list where school_id = '$school_id' ");
                if ($check->num_rows > 0) {
                    $update_sql = "UPDATE faculty_list SET firstname = '$firstname', lastname = '$lastname', email = '$email'";
                    if (!empty($password_plain)) {
                        $password = password_hash($password_plain, PASSWORD_DEFAULT);
                        $update_sql .= ", password = '$password', password_text = '" . $conn->real_escape_string($password_plain) . "'";
                    }
                    $update_sql .= " WHERE school_id = '$school_id'";
                    $save = $conn->query($update_sql);
                } else {
                    if (empty($password_plain)) $password_plain = $school_id;
                    $password = password_hash($password_plain, PASSWORD_DEFAULT);
                    $save = $conn->query("INSERT INTO faculty_list (school_id, firstname, lastname, email, password, password_text) VALUES ('$school_id', '$firstname', '$lastname', '$email', '$password', '" . $conn->real_escape_string($password_plain) . "')");
                }
                if ($save) $count++;
                else $errors[] = "Error importing faculty $school_id: " . $conn->error;
            } else if ($type == 'admin' || $type == 'superadmin') {
                // Expected CSV: firstname, lastname, email, password
                $firstname = $conn->real_escape_string($data[0]);
                $lastname = $conn->real_escape_string($data[1]);
                $email = $conn->real_escape_string($data[2]);
                $password_plain = isset($data[3]) ? $data[3] : '';

                $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
                if ($check->num_rows > 0) {
                    $update_sql = "UPDATE users SET firstname = '$firstname', lastname = '$lastname', role = '$type'";
                    if (!empty($password_plain)) {
                        $password = password_hash($password_plain, PASSWORD_DEFAULT);
                        $update_sql .= ", password = '$password', password_text = '" . $conn->real_escape_string($password_plain) . "'";
                    }
                    $update_sql .= " WHERE email = '$email'";
                    $save = $conn->query($update_sql);
                } else {
                    if (empty($password_plain)) $password_plain = 'password'; // Default password if empty
                    $password = password_hash($password_plain, PASSWORD_DEFAULT);
                    $save = $conn->query("INSERT INTO users (firstname, lastname, email, password, password_text, role) VALUES ('$firstname', '$lastname', '$email', '$password', '" . $conn->real_escape_string($password_plain) . "', '$type')");
                }
                if ($save) $count++;
                else $errors[] = "Error importing $type $email: " . $conn->error;
            } else if ($type == 'questionnaire') {
                // Expected CSV: Question, Criteria Order
                
                $question = isset($data[0]) ? trim($data[0]) : '';
                $criteria_order = isset($data[1]) && is_numeric($data[1]) ? (int)$data[1] : 0;
                
                $academic_id = 0;
                if (isset($_POST['academic_id']) && !empty($_POST['academic_id'])) {
                    $academic_id = (int)$_POST['academic_id'];
                }

                if ($academic_id == 0) {
                    $def_acad = $conn->query("SELECT id FROM academic_list WHERE is_default = 1 LIMIT 1")->fetch_assoc();
                    if ($def_acad) {
                        $academic_id = (int)$def_acad['id'];
                    } else {
                        // Fallback to latest academic year
                        $last_acad = $conn->query("SELECT id FROM academic_list ORDER BY year DESC, semester DESC LIMIT 1")->fetch_assoc();
                        if ($last_acad) {
                            $academic_id = (int)$last_acad['id'];
                        }
                    }
                }

                if ($academic_id == 0) {
                    $errors[] = "Line ".($count+count($errors)+1).": Academic period not found. Please add a semester first.";
                    continue;
                }
                if (empty($question)) continue;

                $esc_question = $conn->real_escape_string($question);

                // Move question to criteria matching the order_by value
                $criteria_query = $conn->query("SELECT id FROM criteria_list WHERE order_by = $criteria_order LIMIT 1");
                if ($criteria_query && $criteria_query->num_rows > 0) {
                    $criteria_id = $criteria_query->fetch_assoc()['id'];
                } else {
                    // Fallback: try to find any criteria if order matches nothing
                    $any_crit = $conn->query("SELECT id FROM criteria_list LIMIT 1")->fetch_assoc();
                    if ($any_crit) {
                        $criteria_id = $any_crit['id'];
                    } else {
                        $errors[] = "Line ".($count+count($errors)+1).": No criteria found. Please add categories in 'Criteria' first.";
                        continue;
                    }
                }

                // Check for existing question
                $check = $conn->query("SELECT id FROM question_list WHERE criteria_id = $criteria_id AND question = '$esc_question' AND academic_id = $academic_id");
                if ($check->num_rows == 0) {
                    // Set order_by to 0 or auto-increment etc. User said question order doesn't matter for visibility
                    $save = $conn->query("INSERT INTO question_list (criteria_id, question, academic_id, order_by) VALUES ('$criteria_id', '$question', '$academic_id', 0)");
                    if ($save) $count++;
                } else {
                    // Already exists, just skip or update if needed
                    $count++;
                }
            } else if ($type == 'criteria') {
                // Expected CSV: criteria, order_by
                $criteria = $conn->real_escape_string($data[0]);
                $order_by = isset($data[1]) && is_numeric($data[1]) ? (int)$data[1] : 0;

                $check = $conn->query("SELECT id FROM criteria_list WHERE TRIM(LOWER(criteria)) = TRIM(LOWER('$criteria'))");
                if ($check->num_rows > 0) {
                    $cid = $check->fetch_assoc()['id'];
                    $save = $conn->query("UPDATE criteria_list SET order_by = '$order_by' WHERE id = $cid");
                } else {
                    $save = $conn->query("INSERT INTO criteria_list (criteria, order_by) VALUES ('$criteria', '$order_by')");
                }
                if ($save) $count++;
                else $errors[] = "Error importing criteria: " . $conn->error;
            } else if ($type == 'class') {
                // Expected CSV: Class Name
                $class_name = $conn->real_escape_string($data[0]);
                if (empty($class_name)) continue;

                $check = $conn->query("SELECT id FROM class_list WHERE TRIM(LOWER(class_name)) = TRIM(LOWER('$class_name'))");
                if ($check->num_rows == 0) {
                    $save = $conn->query("INSERT INTO class_list (class_name) VALUES ('$class_name')");
                    if ($save) $count++;
                    else $errors[] = "Error importing class: " . $conn->error;
                } else {
                    $count++; // Already exists
                }
            } else if ($type == 'subject') {
                // Expected CSV: Subject Code, Subject Name
                $code = isset($data[0]) ? $conn->real_escape_string($data[0]) : '';
                $name = isset($data[1]) ? $conn->real_escape_string($data[1]) : '';
                
                if (empty($code) || empty($name)) continue;

                $check = $conn->query("SELECT id FROM subject_list WHERE TRIM(LOWER(subject_code)) = TRIM(LOWER('$code'))");
                if ($check->num_rows == 0) {
                    $save = $conn->query("INSERT INTO subject_list (subject_code, subject_name) VALUES ('$code', '$name')");
                    if ($save) $count++;
                    else $errors[] = "Error importing subject $code: " . $conn->error;
                } else {
                    $sid = $check->fetch_assoc()['id'];
                    $save = $conn->query("UPDATE subject_list SET subject_name = '$name' WHERE id = $sid");
                    if ($save) $count++;
                }
            } else if ($type == 'restriction') {
                // Expected CSV: Academic Year, Faculty Member, Class, Subject
                $acad_val = isset($data[0]) ? trim($data[0]) : '';
                $faculty_val = isset($data[1]) ? trim($data[1]) : '';
                $class_val = isset($data[2]) ? trim($data[2]) : '';
                $subject_val = isset($data[3]) ? trim($data[3]) : '';

                if (empty($acad_val) || empty($faculty_val) || empty($class_val) || empty($subject_val)) continue;

                // Match Academic Year (e.g., "2025-2026 1st Sem")
                $acad_id = 0;
                $acad_parts = explode(' ', $acad_val);
                if (count($acad_parts) >= 2) {
                    $year = $conn->real_escape_string($acad_parts[0]);
                    $sem_text = strtolower($acad_parts[1]);
                    $semester = (stripos($sem_text, '1st') !== false) ? 1 : 2;
                    $acad_query = $conn->query("SELECT id FROM academic_list WHERE year = '$year' AND semester = $semester");
                    if ($acad_query->num_rows > 0) $acad_id = $acad_query->fetch_assoc()['id'];
                }

                // Match Faculty (e.g., "John Doe")
                $faculty_id = 0;
                $faculty_val_esc = $conn->real_escape_string($faculty_val);
                $faculty_query = $conn->query("SELECT id FROM faculty_list WHERE TRIM(CONCAT(firstname, ' ', lastname)) = '$faculty_val_esc'");
                if ($faculty_query->num_rows > 0) $faculty_id = $faculty_query->fetch_assoc()['id'];

                // Match Class (e.g., "BSIT 1-A")
                $class_id = 0;
                $class_val_esc = $conn->real_escape_string($class_val);
                $class_query = $conn->query("SELECT id FROM class_list WHERE TRIM(class_name) = '$class_val_esc'");
                if ($class_query->num_rows > 0) $class_id = $class_query->fetch_assoc()['id'];

                // Match Subject (e.g., "IT101 - Intro to IT")
                $subject_id = 0;
                $subject_val_esc = $conn->real_escape_string($subject_val);
                $subject_query = $conn->query("SELECT id FROM subject_list WHERE TRIM(CONCAT(subject_code, ' - ', subject_name)) = '$subject_val_esc'");
                if ($subject_query->num_rows > 0) $subject_id = $subject_query->fetch_assoc()['id'];

                if ($acad_id > 0 && $faculty_id > 0 && $class_id > 0 && $subject_id > 0) {
                    $check = $conn->query("SELECT id FROM restriction_list WHERE academic_id = $acad_id AND faculty_id = $faculty_id AND class_id = $class_id AND subject_id = $subject_id");
                    if ($check->num_rows == 0) {
                        $save = $conn->query("INSERT INTO restriction_list (academic_id, faculty_id, class_id, subject_id) VALUES ($acad_id, $faculty_id, $class_id, $subject_id)");
                        if ($save) $count++;
                        else $errors[] = "Error importing restriction for $faculty_val: " . $conn->error;
                    } else {
                        $count++;
                    }
                } else {
                    $missing = [];
                    if ($acad_id == 0) $missing[] = "Academic period ($acad_val)";
                    if ($faculty_id == 0) $missing[] = "Faculty ($faculty_val)";
                    if ($class_id == 0) $missing[] = "Class ($class_val)";
                    if ($subject_id == 0) $missing[] = "Subject ($subject_val)";
                    $errors[] = "Row ".($count+count($errors)+1).": Could not find " . implode(', ', $missing);
                }
            }
        }
        fclose($temp_file);
        if ($count > 0) {
            if (empty($errors)) echo 1;
            else echo "Imported $count records with some errors:<br>" . implode("<br>", $errors);
        } else {
            if (empty($errors)) echo "No records found in CSV or CSV format is incorrect.";
            else echo "Import failed:<br>" . implode("<br>", $errors);
        }
    } else {
        echo "Error uploading file.";
    }
}

if ($action == 'save_restriction') {
    extract($_POST);
    $academic_id = $conn->real_escape_string($academic_id);
    $faculty_id = $conn->real_escape_string($faculty_id);
    $class_id = $conn->real_escape_string($class_id);
    $subject_id = $conn->real_escape_string($subject_id);
    
    $data = " academic_id = '$academic_id' ";
    $data .= ", faculty_id = '$faculty_id' ";
    $data .= ", class_id = '$class_id' ";
    $data .= ", subject_id = '$subject_id' ";

    // Check for duplicates
    $check_sql = "SELECT id FROM restriction_list WHERE academic_id = '$academic_id' AND faculty_id = '$faculty_id' AND class_id = '$class_id' AND subject_id = '$subject_id'";
    if (!empty($id)) {
        $check_sql .= " AND id != $id";
    }
    $check = $conn->query($check_sql);
    if ($check->num_rows > 0) {
        echo "This restriction already exists.";
        exit();
    }

    if (empty($id)) {
        $save = $conn->query("INSERT INTO restriction_list SET $data");
    } else {
        $save = $conn->query("UPDATE restriction_list SET $data WHERE id = $id");
    }
    if ($save) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'delete_restriction') {
    $id = $_POST['id'];
    $delete = $conn->query("UPDATE restriction_list SET is_deleted = 1 WHERE id = $id");
    if ($delete) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'send_certificate_email') {
    extract($_POST);
    if (empty($email) || empty($imgData)) {
        echo "Missing data";
        exit();
    }

    // Prepare the image data
    $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
    $imgData = str_replace(' ', '+', $imgData);
    $fileData = base64_decode($imgData);
    
    $filename = "Certificate_" . str_replace(' ', '_', $name) . ".jpg";
    
    $to = $email;
    $subject = "Certificate of Participation - Faculty Evaluation System";
    $boundary = md5(time());
    
    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    // Message
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= "<html><body>";
    $message .= "<h2>Congratulations, $name!</h2>";
    $message .= "<p>Thank you for participating in the Faculty Evaluation System. Attached is your Certificate of Participation.</p>";
    $message .= "<p>Best regards,<br>Bulacan Polytechnic College</p>";
    $message .= "</body></html>\r\n\r\n";
    
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: image/jpeg; name=\"$filename\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
    $message .= chunk_split(base64_encode($fileData)) . "\r\n\r\n";
    $message .= "--$boundary--";

    // SMTP Configuration
    $smtp_host = 'ssl://smtp.gmail.com';
    $smtp_port = 465;
    $smtp_user = 'marcelinoorienza01@gmail.com';
    $smtp_pass = 'yhpc urbs mctr mktn';
    $from_email = 'marcelinoorienza01@gmail.com';
    $from_name = 'Faculty Evaluation System';

    $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15);
    if (!$socket) {
        echo "Connection failed: $errstr ($errno)";
        exit();
    }

    fgets($socket, 512);
    fwrite($socket, "EHLO localhost\r\n");
    while($res = fgets($socket, 512)) { if(substr($res, 3, 1) == ' ') break; }

    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    $res = fgets($socket, 512);
    if (strpos($res, '235') === false) {
        echo "Authentication failed: " . $res;
        fclose($socket);
        exit();
    }

    fwrite($socket, "MAIL FROM: <$from_email>\r\n");
    fgets($socket, 512);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    fgets($socket, 512);
    fwrite($socket, "DATA\r\n");
    fgets($socket, 512);

    $data = "To: <$to>\r\n";
    $data .= "From: $from_name <$from_email>\r\n";
    $data .= "Subject: $subject\r\n";
    $data .= $headers . "\r\n";
    $data .= $message . "\r\n";
    $data .= ".\r\n";

    fwrite($socket, $data);
    $res = fgets($socket, 512);
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (strpos($res, '250') !== false) {
        echo 1;
    } else {
        echo "Send Error: " . $res;
    }
    exit();
}

if ($action == 'forgot_password') {
    extract($_POST);
    if (empty($email)) {
        echo "Email is required";
        exit();
    }
    
    $email = $conn->real_escape_string($email);

    $found = false;
    $table = "";
    $user_id = 0;
    $name = "";

    // Check users
    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $found = true;
        $table = "users";
        $row = $check->fetch_assoc();
        $user_id = $row['id'];
        $name = $row['firstname'];
    }

    // Check faculty
    if (!$found) {
        $check = $conn->query("SELECT * FROM faculty_list WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $found = true;
            $table = "faculty_list";
            $row = $check->fetch_assoc();
            $user_id = $row['id'];
            $name = $row['firstname'];
        }
    }

    // Check students
    if (!$found) {
        $check = $conn->query("SELECT * FROM student_list WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $found = true;
            $table = "student_list";
            $row = $check->fetch_assoc();
            $user_id = $row['id'];
            $name = $row['firstname'];
        }
    }

    if (!$found) {
        echo "Email address not found in our records.";
        exit();
    }

    // Create password_resets table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        table_name VARCHAR(50) NOT NULL,
        expires_at DATETIME NOT NULL
    )");

    // Generate unique token
    $token = bin2hex(random_bytes(32));

    // Delete any existing tokens for this email
    $conn->query("DELETE FROM password_resets WHERE email = '$email'");

    // Store token
    $insert = $conn->query("INSERT INTO password_resets (email, token, table_name, expires_at) VALUES ('$email', '$token', '$table', DATE_ADD(NOW(), INTERVAL 1 HOUR))");
    if (!$insert) {
        echo "Failed to generate reset token: " . $conn->error;
        exit();
    }

    // Get base URL more robustly
    $protocol = "http";
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = "https";
    }
    
    $host = $_SERVER['HTTP_HOST'];
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    
    $script_name = $_SERVER['PHP_SELF'];
    $dir = dirname($script_name);
    // Correct for single slash or backslash
    $dir = ($dir == DIRECTORY_SEPARATOR) ? "" : $dir;
    
    $base_url = $protocol . "://" . $host . $dir;
    $reset_link = rtrim($base_url, '/\\') . "/reset_password.php?token=" . $token;

    // Send Email
    $to = $email;
    $subject = "Password Reset Request - Faculty Evaluation System";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
    
    $message = "<html><body>";
    $message .= "<h2>Hello, $name!</h2>";
    $message .= "<p>We received a request to reset your password for the Faculty Evaluation System.</p>";
    $message .= "<p>Click the link below to set a new password. This link will expire in 1 hour.</p>";
    $message .= "<p><a href='$reset_link' style='display:inline-block; background:#064e3b; color:white; padding:12px 24px; text-decoration:none; border-radius:8px; font-weight:bold;'>Reset My Password</a></p>";
    $message .= "<p>If you did not request this, please ignore this email.</p>";
    $message .= "<p>Best regards,<br>Bulacan Polytechnic College</p>";
    $message .= "</body></html>";

    // SMTP Configuration (Reuse existing logic)
    $smtp_host = 'ssl://smtp.gmail.com';
    $smtp_port = 465;
    $smtp_user = 'marcelinoorienza01@gmail.com';
    $smtp_pass = 'yhpc urbs mctr mktn';
    $from_email = 'marcelinoorienza01@gmail.com';
    $from_name = 'Faculty Evaluation System';

    $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15);
    if (!$socket) {
        echo "Email connection failed: $errstr ($errno)";
        exit();
    }

    fgets($socket, 512);
    fwrite($socket, "EHLO localhost\r\n");
    while($res = fgets($socket, 512)) { if(substr($res, 3, 1) == ' ') break; }

    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    $res = fgets($socket, 512);
    if (strpos($res, '235') === false) {
        echo "Email authentication failed: " . $res;
        fclose($socket);
        exit();
    }

    fwrite($socket, "MAIL FROM: <$from_email>\r\n");
    fgets($socket, 512);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    fgets($socket, 512);
    fwrite($socket, "DATA\r\n");
    fgets($socket, 512);

    $data = "To: <$to>\r\n";
    $data .= "From: $from_name <$from_email>\r\n";
    $data .= "Subject: $subject\r\n";
    $data .= $headers . "\r\n";
    $data .= $message . "\r\n";
    $data .= ".\r\n";

    fwrite($socket, $data);
    $res = fgets($socket, 512);
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (strpos($res, '250') !== false) {
        echo 1;
    } else {
        echo "Email send error: " . $res;
    }
    exit();
}

if ($action == 'complete_password_reset') {
    extract($_POST);
    if (empty($token) || empty($password)) {
        echo "Missing data";
        exit();
    }

    $token = $conn->real_escape_string($token);
    
    // Validate token
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "This reset link has expired or is invalid.";
        exit();
    }

    $row = $result->fetch_assoc();
    $email = $row['email'];
    $table = $row['table_name'];

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    // Update user password
    $update = $conn->query("UPDATE $table SET password = '$hashed', password_text = '" . $conn->real_escape_string($password) . "' WHERE email = '$email'");
    
    if (!$update) {
        echo "Failed to update password: " . $conn->error;
        exit();
    }

    // Get user name for email
    $user_check = $conn->query("SELECT firstname FROM $table WHERE email = '$email'");
    $user_row = $user_check->fetch_assoc();
    $name = $user_row['firstname'];

    // Delete the token
    $conn->query("DELETE FROM password_resets WHERE token = '$token'");

    // Send Confirmation Email with the new password
    $to = $email;
    $subject = "Password Changed Successfully - Faculty Evaluation System";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
    
    $message = "<html><body>";
    $message .= "<h2>Hello, $name!</h2>";
    $message .= "<p>Your password for the Faculty Evaluation System has been successfully changed.</p>";
    $message .= "<p>Here are your updated login credentials:</p>";
    $message .= "<p><b>Email/ID:</b> $email</p>";
    $message .= "<p><b>New Password:</b> $password</p>";
    $message .= "<p>You can now log in using your new password.</p>";
    $message .= "<p>Best regards,<br>Bulacan Polytechnic College</p>";
    $message .= "</body></html>";

    // SMTP Configuration
    $smtp_host = 'ssl://smtp.gmail.com';
    $smtp_port = 465;
    $smtp_user = 'marcelinoorienza01@gmail.com';
    $smtp_pass = 'yhpc urbs mctr mktn';
    $from_email = 'marcelinoorienza01@gmail.com';
    $from_name = 'Faculty Evaluation System';

    $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15);
    if (!$socket) {
        echo 1; // Still return success for password change even if email fails
        exit();
    }

    fgets($socket, 512);
    fwrite($socket, "EHLO localhost\r\n");
    while($res = fgets($socket, 512)) { if(substr($res, 3, 1) == ' ') break; }

    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 512);
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    $res = fgets($socket, 512);
    if (strpos($res, '235') === false) {
        echo 1;
        fclose($socket);
        exit();
    }

    fwrite($socket, "MAIL FROM: <$from_email>\r\n");
    fgets($socket, 512);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    fgets($socket, 512);
    fwrite($socket, "DATA\r\n");
    fgets($socket, 512);

    $data = "To: <$to>\r\n";
    $data .= "From: $from_name <$from_email>\r\n";
    $data .= "Subject: $subject\r\n";
    $data .= $headers . "\r\n";
    $data .= $message . "\r\n";
    $data .= ".\r\n";

    fwrite($socket, $data);
    $res = fgets($socket, 512);
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    echo 1;
    exit();
}
if ($action == 'toggle_report_publish') {
    extract($_POST);
    $fid = (int)$fid;
    $academic_id = (int)$academic_id;
    $publish = (int)$publish;

    $check = $conn->query("SELECT id FROM published_results WHERE faculty_id = $fid AND academic_id = $academic_id");
    if ($check->num_rows > 0) {
        $save = $conn->query("UPDATE published_results SET is_published = $publish WHERE faculty_id = $fid AND academic_id = $academic_id");
    } else {
        $save = $conn->query("INSERT INTO published_results SET is_published = $publish, faculty_id = $fid, academic_id = $academic_id");
    }
    if ($save) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'toggle_comment_visibility') {
    extract($_POST);
    $id = (int)$id;
    $publish = (int)$publish;

    $save = $conn->query("UPDATE evaluation_comments SET is_published = $publish WHERE id = $id");
    if ($save) echo 1;
    else echo "Error: " . $conn->error;
}

if ($action == 'batch_toggle_comment_visibility') {
    extract($_POST);
    $ids_array = json_decode($ids, true);
    $publish = (int)$publish;
    
    if (empty($ids_array)) {
        echo 1;
        exit();
    }
    
    $ids_str = implode(',', array_map('intval', $ids_array));
    $save = $conn->query("UPDATE evaluation_comments SET is_published = $publish WHERE id IN ($ids_str)");
    
    if ($save) echo 1;
    else echo "Error: " . $conn->error;
}
?>
