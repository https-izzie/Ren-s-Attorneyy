<?php
header('Content-Type: application/json');
require '../includes/db_public.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed.']));
}

$first        = trim($_POST['first_name'] ?? '');
$last         = trim($_POST['last_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$state        = trim($_POST['state'] ?? '');
$practiceArea = trim($_POST['practice_area'] ?? '');
$consultType  = trim($_POST['consult_type'] ?? '');
$message      = trim($_POST['message'] ?? '');
$consent      = !empty($_POST['consent']);
$full_name    = trim("$first $last");

// ---- Check the text fields ----
$errors = [];
if ($first === '' || $last === '')               $errors[] = 'First and last name are required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'A valid email is required.';
if ($phone === '')                               $errors[] = 'Phone number is required.';
if ($state === '')                               $errors[] = 'Please select your state.';
if ($practiceArea === '')                        $errors[] = 'Please select a practice area.';
if ($consultType === '')                         $errors[] = 'Please select a consultation type.';
if ($message === '')                             $errors[] = 'Please describe your legal matter.';
if (!$consent)                                   $errors[] = 'Please tick the consent box.';

// ---- Check the files (optional, can be several) ----
$allowed  = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt', 'xlsx'];
$maxBytes = 10 * 1024 * 1024; // 10 MB per file
$toSave   = [];

if (!empty($_FILES['documents']['name'][0])) {
    $count = count($_FILES['documents']['name']);
    if ($count > 5) {
        $errors[] = 'You can attach up to 5 files.';
    } else {
        for ($i = 0; $i < $count; $i++) {
            $name = $_FILES['documents']['name'][$i];
            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload failed for $name.";
            } elseif (!in_array($ext, $allowed, true)) {
                $errors[] = "$name is not an allowed file type.";
            } elseif ($_FILES['documents']['size'][$i] > $maxBytes) {
                $errors[] = "$name is larger than 10 MB.";
            } else {
                $toSave[] = [
                    'tmp'      => $_FILES['documents']['tmp_name'][$i],
                    'stored'   => bin2hex(random_bytes(12)) . '.' . $ext,
                    'original' => basename($name),
                ];
            }
        }
    }
}

if ($errors) {
    http_response_code(400);
    exit(json_encode(['errors' => $errors]));
}

// ---- Save the files into /uploads ----
foreach ($toSave as $f) {
    if (!move_uploaded_file($f['tmp'], '../uploads/' . $f['stored'])) {
        http_response_code(500);
        exit(json_encode(['error' => 'Could not save a file.']));
    }
}

// ---- Save to the database ----
$stmt = $pdo->prepare(
    'INSERT INTO consultations
     (full_name, email, phone, state, practice_area, consult_type, message)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$full_name, $email, $phone, $state, $practiceArea, $consultType, $message]);
$consultationId = $pdo->lastInsertId();

$fileStmt = $pdo->prepare(
    'INSERT INTO consultation_files (consultation_id, file_path, original_filename) VALUES (?, ?, ?)'
);
foreach ($toSave as $f) {
    $fileStmt->execute([$consultationId, $f['stored'], $f['original']]);
}

echo json_encode(['message' => 'Consultation request received. We will contact you shortly.']);