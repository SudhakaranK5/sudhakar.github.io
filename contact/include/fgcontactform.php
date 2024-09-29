<?php
require_once("class.phpmailer.php");

/*
Interface to Captcha handler
*/
class FG_CaptchaHandler
{
    function Validate() { return false; }
    function GetError() { return ''; }
}

/*
FGContactForm is a general purpose contact form class
It supports Captcha, HTML Emails, sending emails
conditionally, file attachments, and more.
*/
class FGContactForm
{
    var $recipients;
    var $errors;
    var $error_message;
    var $name;
    var $email;
    var $message;
    var $from_address;
    var $form_random_key;
    var $conditional_field;
    var $arr_conditional_recipients;
    var $fileupload_fields;
    var $captcha_handler;
    var $mailer;

    function __construct() // Updated constructor
    {
        $this->recipients = array();
        $this->errors = array();
        $this->form_random_key = 'HTgsjhartag';
        $this->conditional_field = '';
        $this->arr_conditional_recipients = array();
        $this->fileupload_fields = array();
        
        $this->mailer = new PHPMailer(); // Ensure PHPMailer is properly instantiated
        $this->mailer->CharSet = 'utf-8';
    }

    function EnableCaptcha($captcha_handler)
    {
        $this->captcha_handler = $captcha_handler;
    }

    function AddRecipient($email, $name = "")
    {
        if ($this->mailer) { // Ensure $mailer is initialized
            $this->mailer->AddAddress($email, $name);
        } else {
            $this->add_error("Mailer not initialized.");
        }
    }

    function SetFromAddress($from)
    {
        $this->from_address = $from;
    }
    
    function SetFormRandomKey($key)
    {
        $this->form_random_key = $key;
    }
    
    function GetSpamTrapInputName()
    {
        return 'sp' . md5('KHGdnbvsgst' . $this->GetKey());
    }
    
    function SafeDisplay($value_name)
    {
        return empty($_POST[$value_name]) ? '' : htmlentities($_POST[$value_name]);
    }
    
    function GetFormIDInputName()
    {
        $rand = md5('TygshRt' . $this->GetKey());
        return 'id' . substr($rand, 0, 20);
    }

    function GetFormIDInputValue()
    {
        return md5('jhgahTsajhg' . $this->GetKey());
    }

    function SetConditionalField($field)
    {
        $this->conditional_field = $field;
    }

    function AddConditionalRecipient($value, $email)
    {
        $this->arr_conditional_recipients[$value] = $email;
    }

    function AddFileUploadField($file_field_name, $accepted_types, $max_size)
    {
        $this->fileupload_fields[] = array(
            "name" => $file_field_name,
            "file_types" => $accepted_types,
            "maxsize" => $max_size
        );
    }

    function ProcessForm()
    {
        if (!isset($_POST['submitted'])) {
            return false;
        }
        if (!$this->Validate()) {
            $this->error_message = implode('<br/>', $this->errors);
            return false;
        }
        $this->CollectData();
        return $this->SendFormSubmission();
    }

    function RedirectToURL($url)
    {
        header("Location: $url");
        exit;
    }

    function GetErrorMessage()
    {
        return $this->error_message;
    }

    function GetSelfScript()
    {
        return htmlentities($_SERVER['PHP_SELF']);
    }

    function GetName()
    {
        return $this->name;
    }

    function GetEmail()
    {
        return $this->email;
    }

    function GetMessage()
    {
        return htmlentities($this->message, ENT_QUOTES, "UTF-8");
    }

    /*--------  Private (Internal) Functions -------- */

    function SendFormSubmission()
    {
        $this->CollectConditionalRecipients();

        $this->mailer->CharSet = 'utf-8';
        $this->mailer->Subject = "Contact form submission from $this->name";
        $this->mailer->From = $this->GetFromAddress();
        $this->mailer->FromName = $this->name;
        $this->mailer->AddReplyTo($this->email);
        $message = $this->ComposeFormToEmail();

        $textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $message)));
        $this->mailer->AltBody = @html_entity_decode($textMsg, ENT_QUOTES, "UTF-8");
        $this->mailer->MsgHTML($message);
        
        $this->AttachFiles();

        if (!$this->mailer->Send()) {
            $this->add_error("Failed sending email!");
            return false;
        }

        return true;
    }

    function CollectConditionalRecipients()
    {
        if (count($this->arr_conditional_recipients) > 0 && !empty($this->conditional_field) && !empty($_POST[$this->conditional_field])) {
            foreach ($this->arr_conditional_recipients as $condn => $rec) {
                if (strcasecmp($condn, $_POST[$this->conditional_field]) == 0 && !empty($rec)) {
                    $this->AddRecipient($rec);
                }
            }
        }
    }

    function IsInternalVariable($varname)
    {
        $arr_internal_vars = array(
            'scaptcha',
            'submitted',
            $this->GetSpamTrapInputName(),
            $this->GetFormIDInputName()
        );
        return in_array($varname, $arr_internal_vars);
    }

    function FormSubmissionToMail()
    {
        $ret_str = '';
        foreach ($_POST as $key => $value) {
            if (!$this->IsInternalVariable($key)) {
                $value = htmlentities($value, ENT_QUOTES, "UTF-8");
                $value = nl2br($value);
                $key = ucfirst($key);
                $ret_str .= "<div class='label'>$key :</div><div class='value'>$value </div>\n";
            }
        }
        foreach ($this->fileupload_fields as $upload_field) {
            $field_name = $upload_field["name"];
            if (!$this->IsFileUploaded($field_name)) {
                continue;
            }        
            $filename = basename($_FILES[$field_name]['name']);
            $ret_str .= "<div class='label'>File upload '$field_name' :</div><div class='value'>$filename </div>\n";
        }
        return $ret_str;
    }

    function ExtraInfoToMail()
    {
        return '<div class="label">IP address of the submitter:</div><div class="value">' . $_SERVER['REMOTE_ADDR'] . '</div>';
    }

    function GetMailStyle()
    {
        return "\n<style>".
        "body,.label,.value { font-family:Arial,Verdana; } ".
        ".label {font-weight:bold; margin-top:5px; font-size:1em; color:#333;} ".
        ".value {margin-bottom:15px;font-size:0.8em;padding-left:5px;} ".
        "</style>\n";
    }

    function GetHTMLHeaderPart()
    {
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">'."\n".
               '<html><head><title></title>'.
               '<meta http-equiv=Content-Type content="text/html; charset=utf-8">'.
               $this->GetMailStyle() . '</head><body>';
    }

    function GetHTMLFooterPart()
    {
        return '</body></html>';
    }

    function ComposeFormToEmail()
    {
        $header = $this->GetHTMLHeaderPart();
        $formsubmission = $this->FormSubmissionToMail();
        $extra_info = $this->ExtraInfoToMail();
        $footer = $this->GetHTMLFooterPart();

        return $header."Submission from 'contact us' form:<p>$formsubmission</p><hr/>$extra_info".$footer;
    }

    function AttachFiles()
    {
        foreach ($this->fileupload_fields as $upld_field) {
            $field_name = $upld_field["name"];
            if (!$this->IsFileUploaded($field_name)) {
                continue;
            }
            $filename = basename($_FILES[$field_name]['name']);
            $this->mailer->AddAttachment($_FILES[$field_name]["tmp_name"], $filename);
        }
    }

    function GetFromAddress()
    {
        return !empty($this->from_address) ? $this->from_address : "nobody@" . $_SERVER['SERVER_NAME'];
    }

    function Validate()
    {
        $ret = true;

        if (empty($_POST[$this->GetFormIDInputName()]) ||
            $_POST[$this->GetFormIDInputName()] != $this->GetFormIDInputValue()) {
            $this->add_error("Automated submission prevention: case 1 failed");
            $ret = false;
        }

        if ($this->captcha_handler && $this->captcha_handler->Validate() === false) {
            $this->add_error($this->captcha_handler->GetError());
            $ret = false;
        }

        if (empty($_POST['name'])) {
            $this->add_error("Name is required.");
            $ret = false;
        }

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->add_error("A valid email is required.");
            $ret = false;
        }

        if (empty($_POST['message'])) {
            $this->add_error("Message is required.");
            $ret = false;
        }

        return $ret;
    }

    function Sanitize($value)
    {
        return htmlentities(trim($value), ENT_QUOTES, "UTF-8");
    }

    function StripSlashes($value)
    {
        return stripslashes(trim($value));
    }

    function CollectData()
    {
        $this->name = $this->Sanitize($_POST['name']);
        $this->email = $this->Sanitize($_POST['email']);
        $this->message = $this->StripSlashes($_POST['message']);
    }

    function add_error($error)
    {
        array_push($this->errors, $error);
    }

    function IsFileUploaded($field_name)
    {
        return isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK;
    }

    function GetKey()
    {
        return md5("unique_form_id_" . session_id());
    }
}
?>
