<?php
/**************************************************************
 *
 * Mailer.php, created 4.11.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 * Company: ManSkal - Martin Skalický
 *
 ***************************************************************
 *
 * Contacts:
 * Core developer - petr.klimes@manskal.com
 * More info      - info@manskal.com
 * Website        - www.manskal.com
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS\Core\Mailer;

class Mailer extends \PKRS\Core\Service\Service
{

    private $mailer;
    private $vars = array();
    private $template = '';
    private $config = array();
    private $from = array();
    private $reply_to = array();
    private $_html; // overide $smarty->fetch, if setted, $this->template not used
    private $css = array();
    private $last_mail_html = '';
    private $imitation = false;

    public function __construct(\PHPMailer $PHPMailer)
    {
        $this->mailer = $PHPMailer;
        self::gc()->get_hooks()->execute("mailer", "on_create");
    }

    public function reset_mailer()
    {
        $this->mailer = new \PHPMailer();
        $this->mailer->CharSet = "UTF-8";
        $this->config = self::gc()->get_config()->get("mailer");
        if ($this->config["use_smtp"] == "true") {
            $this->mailer->IsSMTP();
            $this->mailer->Host = $this->config["smtp_hots"];
            $this->mailer->SMTPAuth = $this->config["smtp_use_auth"] == "true";
            $this->mailer->Username = $this->config["smtp_user"];
            $this->mailer->Password = $this->config["smtp_pass"];
        }
        return $this;
    }

    public function set_send_imitation($set_imitation = false)
    {
        $this->imitation = $set_imitation;
        return $this;
    }

    public function get_last_mail_html()
    {
        return $this->last_mail_html;
    }

    public function set_template($tpl_file)
    {
        if (!file_exists($tpl_file)) {
            throw new \PKRS\Core\Exception\FileException("Mailer: template $tpl_file not exists!");
        }
        $this->template = $tpl_file;
        return $this;
    }

    public function set_vars($array)
    {
        foreach ($array as $k => $v) {
            $this->vars[$k] = $v;
        }
        return $this;
    }

    public function set_var($key, $value)
    {
        $this->vars[$key] = $value;
        return $this;
    }

    public function reset_vars()
    {
        $this->vars = array();
        return $this;
    }

    public function reset_attachements()
    {
        $this->mailer->clearAttachments();
        return $this;
    }

    public function set_attachement($file, $name = '')
    {
        if (!file_exists($file)) {
            throw new \PKRS\Core\Exception\FileException("Mailer: attachement file $file not exists!");
        }
        if ($this->mailer->addAttachment($file, $name))
            return $this;
        else throw new \PKRS\Core\Exception\FileException("Mailer: attachement file $file not assigned to mailer!");
    }

    public function set_from($from_mail, $from_name = '')
    {
        $this->from = array("mail" => $from_mail, "name" => $from_name);
        return $this;
    }

    public function set_reply_to($mail, $name = '')
    {
        $this->reply_to = array("mail" => $mail, "name" => $name);
        return $this;
    }

    public function set_html($html_smarty_string)
    {
        $this->_html = $html_smarty_string;
        return $this;
    }

    public function set_css($css)
    {
        if (file_exists($css)) {
            $this->css[] = file_get_contents($css);
        } else $this->css[] = $css;
        return $this;
    }

    public function send($subject, $receiver_mail, $receiver_name = '', $alternate_body = '', $disable_html = false)
    {
        self::gc()->get_hooks()->execute("mailer", "before_send", array("email" => $receiver_mail, "name" => $receiver_name, "subject" => $subject));
        if (!self::gc()->get_validator()->is_email($receiver_mail)) return false;
        if ($this->_html)
            $this->set_template($this->template); // fix, if template not exist or !isset - throw exception
        $smarty = self::gc()->get_view()->new_instace();
        foreach ($this->vars as $k => $v) {
            $smarty->assign($k, $v);
        }
        if ($this->_html)
            $html = $smarty->fetch("string:" . $this->_html);
        else $html = $smarty->fetch($this->template);
        if (is_array($this->from)) {
            if (isset($this->from["mail"]))
                $this->mailer->From = $this->from["mail"];
            if (isset($this->from["name"]))
                $this->mailer->FromName = $this->from["name"];
        }
        if (is_array($this->reply_to)) {
            $this->mailer->addReplyTo($this->reply_to["mail"], $this->reply_to["name"]);
        }
        $this->mailer->addAddress($receiver_mail, $receiver_name);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $html;
        if ($alternate_body != '')
            $this->mailer->AltBody = $alternate_body;
        $this->mailer->isHTML(!$disable_html);
        if ($this->imitation)
            $result = true;
        else
            $result = $this->mailer->send();
        self::gc()->get_hooks()->execute("mailer", "on_send", array("email" => $receiver_mail, "name" => $receiver_name, "html" => $html, "subject" => $subject));
        if ($result) self::gc()->get_hooks()->execute("mailer", "on_send_ok", array("email" => $receiver_mail, "name" => $receiver_name, "html" => $html, "subject" => $subject));
        else         self::gc()->get_hooks()->execute("mailer", "on_send_err", array("email" => $receiver_mail, "name" => $receiver_name, "html" => $html, "subject" => $subject));
        self::gc()->get_hooks()->execute("mailer", "after_send", array("email" => $receiver_mail, "name" => $receiver_name, "html" => $html, "subject" => $subject));
        $this->last_mail_html = $html;
        return $result;
    }

    public function send_multiple($subject, $receivers = array(), $alternate_body = '', $disable_html = false)
    {
        self::gc()->get_hooks()->execute("mailer", "on_multiple", func_get_args());
        $ok = 0;
        $e = 0;
        foreach ($receivers as $r) {
            if (!isset($r["mail"])) {
                if (isset($r["email"]))
                    $r["mail"] = $r["email"];
                else {
                    $e++;
                    continue;
                }
            }
            if (!isset($r["name"])) $r["name"] = '';
            if ($this->send($subject, $r["mail"], $r["name"], $alternate_body, $disable_html))
                $ok++;
            else $e++;
        }
        return array("ok" => $ok, "err" => $e, "result" => $e == 0);
    }

}