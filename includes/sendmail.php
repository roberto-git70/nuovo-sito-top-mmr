<?php

$recaptcha_secretkey = "6LfF4zYeAAAAAHXzq-bRki1ME__LnfQMbeBrDlqw"; // Sostituisci con la tua chiave segreta

function verifyReCaptcha($recaptchaResponse, $userIP, $secretKey) {
    $request = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}&remoteip={$userIP}");
    return strstr($request, "true");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";
$status = "false";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica reCAPTCHA
    if (isset($_POST['g-recaptcha-response'])) {
        $userIP = $_SERVER["REMOTE_ADDR"];
        $recaptchaResponse = $_POST['g-recaptcha-response'];
        $secretKey = $recaptcha_secretkey;

        if (!verifyReCaptcha($recaptchaResponse, $userIP, $secretKey)) {
            $message = '<strong>Errore!</strong> C\'è stato un problema con il Captcha. Sembra che tu sia un robot, o che tu non l\'abbia cliccato.';
            $status = "false";
            $status_array = array('message' => $message, 'status' => $status);
            echo json_encode($status_array);
            exit; // Interrompi l'esecuzione se reCAPTCHA fallisce
        }
    } else {
        $message = '<strong>Errore!</strong> reCAPTCHA non trovato.';
        $status = "false";
        $status_array = array('message' => $message, 'status' => $status);
        echo json_encode($status_array);
        exit;
    }

    // Processa il form se reCAPTCHA ha successo
    if ($_POST['form_name'] != '' && $_POST['form_email'] != '' && $_POST['form_subject'] != '') {
        // ... (il tuo codice per inviare l'email con PHPMailer)
        require_once('phpmailer/class.phpmailer.php');
        require_once('phpmailer/class.smtp.php');

        $mail = new PHPMailer();

        //$mail->SMTPDebug = 3;                                     // Enable verbose debug output
        $mail->isSMTP();                                            // Set mailer to use SMTP
        $mail->Host = 'mail.soluzioniwebdesign.com';                      // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                     // Enable SMTP authentication
        $mail->Username = 'roberto.riccardi@soluzioniwebdesign.com';                 // SMTP username
        $mail->Password = 'anacleto64599';                              // SMTP password
        $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                          // TCP port to connect to

        $name = $_POST['form_name'];
        $email = $_POST['form_email'];
        $subject = $_POST['form_subject'];
        $phone = $_POST['form_phone'];
        $message = $_POST['form_message'];

        $subject = isset($subject) ? $subject : 'Nuovo Messaggio | Modulo di Contatto';

        $botcheck = $_POST['form_botcheck'];

        $toemail = 'roberto.riccardi@soluzioniwebdesign.com'; // Your Email Address
        $toname = 'roberto.riccardi@soluzioniwebdesign.com'; // Your Name

        if ($botcheck == '') {
            $mail->SetFrom($toemail, $toname);
            $mail->AddReplyTo($email, $name);
            $mail->AddAddress($toemail, $toname);
            $mail->Subject = $subject;

            $name = isset($name) ? "Nome: $name<br><br>" : '';
            $email = isset($email) ? "Email: $email<br><br>" : '';
            $phone = isset($phone) ? "Telefono: $phone<br><br>" : '';
            $message = isset($message) ? "Messaggio: $message<br><br>" : '';

            $referrer = $_SERVER['HTTP_REFERER'] ? '<br><br><br>Questa mail è stata inviata da: ' . $_SERVER['HTTP_REFERER'] : '';

            $body = "$name $email $phone $message $referrer";

            $mail->MsgHTML($body);

            try {
                $sendEmail = $mail->Send();

                if ($sendEmail == true):
                    $message = 'Il tuo messaggio <strong> è stato inviato con successo!</strong> Ti risponderemo il prima possibile.';
                    $status = "true";
                else:
                    $message = 'L\'email <strong>non è stata inviata</strong> a causa di un errore imprevisto. Riprova più tardi.<br /><br /><strong>Motivo:</strong><br />' . $mail->ErrorInfo . '';
                    $status = "false";
                endif;
            } catch (Exception $e) {
                $message = 'L\'email <strong>non è stata inviata</strong> a causa di un errore imprevisto. Riprova più tardi.<br /><br /><strong>Motivo:</strong><br />' . $e->getMessage() . '';
                $status = "false";
            }
        } else {
            $message = 'Bot <strong>Rilevato</strong>.! Pulisciti Botster.!';
            $status = "false";
        }
    } else {
        $message = 'Per favore <strong>Compila</strong> tutti i campi e riprova.';
        $status = "false";
    }
} else {
    $message = 'Si è verificato un <strong>errore imprevisto</strong>. Riprova più tardi.';
    $status = "false";
}

$status_array = array('message' => $message, 'status' => $status);
echo json_encode($status_array);
?>