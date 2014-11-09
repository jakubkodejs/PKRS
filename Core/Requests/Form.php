<?php
/********************************************
 *
 * Form.php, created 5.8.14
 *
 * Copyright (C) 2014 by Petr Klimes & development team
 *
 *
 ***************************************************************
 *
 * Contacts:
 * @author: Petr Klimeš <djpitrrs@gmail.com>
 * @url: http://www.pkrs.eu
 * @url: https://github.com/pitrrs/PKRS
 *
 ***************************************************************
 *
 * Compatibility:
 * PHP     v. 5.4 or higher
 * MySQL   v. 5.5 or higher
 * MariaDB v. 5.5 or higher
 *
 **************************************************************/
namespace PKRS\Core\Requests;

use PKRS\Core\Exception\FileException;
use PKRS\Core\Service\Service;
use PKRS\Core\Service\ServiceContainer;

class Form extends Service
{

    // Field types
    const TYPE_INT = 1; // číselný prvek
    const TYPE_STR = 2; // Textový prvek
    const TYPE_FILE = 3; // soubory
    const TYPE_FILE_MULTI = 4; // vícenásobné soubory pod daným názvem (<input type="file[]">)
    const TYPE_MAIL = 5; // text s validací emailu
    const TYPE_DATE = 6; // Prvek s datem nebo časem
    const TYPE_CHECKBOX = 7; // zaškrtávací pole
    const TYPE_LIST = 8; // select, radio, ... (více výběrné prvky) TODO: Není hotové

    // Additional validating
    const VALIDATE_MIN_LENGTH = 100; // minimalni delka
    const VALIDATE_MAX_LENGTH = 110; // maximalni delka
    const VALIDATE_COMPARE_WITH_FIELD = 200; // porovnani s jinym definovanym polem
    const VALIDATE_COMPARE_WITH_STRING = 210; // porovnani se stringem
    const VALIDATE_COMPARE_WITH_SHA1_STRING = 230; // porovnani se SHA1 stringem (napr validace hesla)
    const VALIDATE_NUMBER_MIN_VALUE = 300; // minimální hodnota číselného typu
    const VALIDATE_NUMBER_MAX_VALUE = 310; // maximální hodnota číselného typu
    const VALIDATE_DATE_FORMAT = 320; // Parametr formátu data a času (viz. PHP date() funkce)
    const VALIDATE_CHECKBOX_MUST_CHECK = 400; // Checkbox MUSI byt zaskrtly

    // Extra params
    const EXTRA_NO_VALIDATE = 500; // Nevaliduj
    const EXTRA_NOT_OPTIONAL = 510; // Pole není povinné
    const EXTRA_FILE_UPLOAD_TARGET_DIR = 520; // cílový soubor při uploadu
    const EXTRA_FILE_UPLOAD_RENAME_TO = 530; // Nový název souboru (přejmenovat)
    const EXTRA_FILE_MAX_SIZE_BYTES = 540; // maximální velikost souboru
    const EXTRA_FILE_VALIDATE_TYPE = 550; // Ověření typu souboru
    const EXTRA_STRING_STRIP_HTML = 560; // Odstranění HTML ze stringu
    const EXTRA_STRING_TO_HTML_ENTITIES = 570; // Převedení na HTML entity
    const EXTRA_CHECKBOX_NOT_CHECKED_VAL = 580; // Hodnota když checkbox není zaškrtlý

    // typy souborů
    // TODO: add types
    const FILE_IMAGE_JPEG = "image/jpeg";
    const FILE_IMAGE_PNG = "image/png";
    const FILE_IMAGE_BMP = "image/bmp";

    private $smarty;
    private $service;
    private $form_name;
    private $fields = array();
    private $posted_data = array();
    private $has_err = false;
    private $files_count = 0;
    private $type = "POST";
    private $data = array();
    private $identifier = "form_ID";
    private $smarty_variable = "forms";
    private $multifiles = array();
    private $setted = false;

    public function __construct(ServiceContainer $container)
    {
        $this->service = $container;
        $this->smarty = $container->get_view()->smarty();
    }

    public function new_instance()
    {
        return new Form($this->service);
    }

    public function settings($form_name, $form_identifier = "form_ID", $type = "POST", $smarty_variable = "forms")
    {
        $this->fields = array();
        $this->posted_data = array();
        $this->has_err = false;
        $this->files_count = 0;
        $this->data = array();
        $this->multifiles = array();
        $this->form_name = $form_name;
        if ($type == "POST" || $type == "GET") {
            $this->type = $type;
            if ($type == "POST") $this->data = $_POST;
            if ($type == "GET") $this->data = $_GET;
        } else throw new \Exception("Form: type must be POST or GET");
        $this->identifier = $form_identifier;
        $this->smarty_variable = $smarty_variable;
        $forms = $this->smarty->getTemplateVars($this->smarty_variable);
        if (!is_array($forms)) {
            $forms = array();
            $forms[$this->form_name] = array("errors" => array());
        } else {
            if (!isset($forms[$this->form_name])) {
                $forms[$this->form_name] = array("errors" => array());
            }
        }
        $this->smarty->assign($this->smarty_variable, $forms);
        $this->setted = true;
        return $this;
    }

    public function is_submited()
    {
        return isset($this->data[$this->identifier]) && $this->data[$this->identifier] == $this->form_name;
    }

    public function get_result($key)
    {
        return $this->posted_data[$key];
    }

    public function doProcess()
    {
        $error_fields = array();
        if (isset($this->data[$this->identifier]) && $this->data[$this->identifier] == $this->form_name) {
            foreach ($this->fields as $key => $value) {
                if ($value["data_type"] == self::TYPE_FILE_MULTI) {
                    $files = $this->service->get_transformArrays()->diverse_FILES($_FILES[$key]);
                    foreach ($files as $fname => $file) {
                        if (!in_array(self::EXTRA_NOT_OPTIONAL, $value["extra_params"])) {
                            $value["extra_params"][self::EXTRA_NOT_OPTIONAL] = self::EXTRA_NOT_OPTIONAL;
                        }
                        $old = null;
                        if (isset($value["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO])) {
                            $old = $value["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO];
                            $value["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO] = $value["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO] . "[" . $fname . "]";
                        }
                        $this->add_field("!!MULTI!!" . $key . "--[" . $fname . "]--", $value["default_value"], self::TYPE_FILE, $value["not_valid_message"], $value["additional_validate"], $value["extra_params"]);
                        if (!is_null($old)) {
                            $value["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO] = $old;
                        }
                        unset($old);
                        $this->fields["!!MULTI!!" . $key . "--[" . $fname . "]--"]["file"] = $file;
                    }
                    $this->multifiles[] = $key;
                    unset($this->fields[$key]);
                }
            }
            foreach ($this->fields as $key => $value) {
                if ($value["data_type"] == self::TYPE_FILE_MULTI) continue;
                if ($value["data_type"] != self::TYPE_FILE && $value["data_type"] != self::TYPE_FILE_MULTI && $value["data_type"] != self::TYPE_CHECKBOX && in_array($key, array_keys($this->data))) {
                    $this->fields[$key]["value"] = $this->data[$key];
                    if (!$this->validate($this->fields[$key])) {
                        $this->service->get_messages()->set($this->fields[$key]["not_valid_message"], "err");
                        $this->has_err = true;
                        $error_fields[] = $key;
                    } else {
                        $this->posted_data[$key] = $this->data[$key];
                    }
                } else {
                    if ($value["data_type"] == self::TYPE_CHECKBOX) {
                        if ($this->validate($this->fields[$key])) {
                            $this->posted_data[$key] = $this->fields[$key]["value"];
                        } else {
                            $this->service->get_messages()->set($this->fields[$key]["not_valid_message"], "err");
                            $this->has_err = true;
                            $error_fields[] = $key;
                        }
                    }
                    if ($value["data_type"] == self::TYPE_FILE) {
                        if (!$this->validate($this->fields[$key])) {
                            $this->service->get_messages()->set($this->fields[$key]["not_valid_message"], "err");
                            $this->has_err = true;
                            $error_fields[] = $key;
                        } else {
                            if (isset($_FILES[$key])) {
                                // adding extra information to uploaded file
                                if (!isset($this->fields[$key]["file_name"]) || empty($this->fields[$key]["file_name"])) continue;
                                $this->posted_data[$key] = $_FILES[$key];
                                $this->posted_data[$key]["file_path"] = $this->fields[$key]["value"];
                                $this->posted_data[$key]["file_name"] = $this->fields[$key]["file_name"];
                                $this->posted_data[$key]["size"] = filesize($this->fields[$key]["value"]);
                                $this->posted_data[$key]["web_pah"] = "/" . implode("/", explode(DS, substr($this->fields[$key]["value"], strlen(ROOT_DIR), strlen($this->fields[$key]["value"]))));
                                $this->posted_data[$key]["nice_size"] = self::gc()->get_transformNumbers()->human_filesize($this->posted_data[$key]["size"]);
                            } elseif (isset($this->fields[$key]["file"])) {
                                // extra information to uploaded file - MULTIFILE
                                if (!isset($this->fields[$key]["file_name"]) || empty($this->fields[$key]["file_name"])) continue;
                                $this->posted_data[$key] = $this->fields[$key]["file"];
                                $this->posted_data[$key]["file_path"] = $this->fields[$key]["value"];
                                $this->posted_data[$key]["file_name"] = $this->fields[$key]["file_name"];
                                $this->posted_data[$key]["size"] = filesize($this->fields[$key]["value"]);
                                $this->posted_data[$key]["web_pah"] = "/" . implode("/", explode(DS, substr($this->fields[$key]["value"], strlen(ROOT_DIR), strlen($this->fields[$key]["value"]))));
                                $this->posted_data[$key]["nice_size"] = self::gc()->get_transformNumbers()->human_filesize($this->posted_data[$key]["size"]);
                            }
                        }
                    }
                }
            }
            if (!$this->has_err) {
                $forms = $this->smarty->getTemplateVars($this->smarty_variable);
                foreach ($this->posted_data as $k => $v) {
                    $forms[$this->form_name][$k] = $v;
                }
                $this->smarty->assign($this->smarty_variable, $forms);
                $this->sanitize_posted_data();
                return $this->posted_data;
            } else {
                $forms = $this->smarty->getTemplateVars($this->smarty_variable);
                $forms[$this->form_name]["errors"] = $error_fields;
                $this->smarty->assign($this->smarty_variable, $forms);
            }
        }
        return false;
    }

    public function add_field(
        $name,
        $default_value,
        $data_type,
        $not_valid_message,
        $additional_validate = array(),
        $extra_params = array()
    )
    {
        if (in_array($name, array_keys($this->fields))) {
            throw new \Exception("Form: Field $name is defined!");
        }
        if ($data_type == self::TYPE_FILE) {
            if (!isset($extra_params[self::EXTRA_FILE_UPLOAD_TARGET_DIR])) {
                throw new FileException("Form: Not isset extra param EXTRA_FILE_UPLOAD_TARGET_DIR");
            } else {
                if (!is_dir($extra_params[self::EXTRA_FILE_UPLOAD_TARGET_DIR])) {
                    if (!mkdir($extra_params[self::EXTRA_FILE_UPLOAD_TARGET_DIR], 0777, true)) {
                        throw new FileException("Form: Can't not create target dir: " . $extra_params[self::EXTRA_FILE_UPLOAD_TARGET_DIR]);
                    }
                }
            }
        }
        if ($data_type == self::TYPE_DATE) {
            if (!isset($extra_params[self::VALIDATE_DATE_FORMAT])) {
                throw new \Exception("Form: Not isset additional validate param VALIDATE_DATE_FORMAT");
            }
        }
        $forms = $this->smarty->getTemplateVars($this->smarty_variable);
        $forms[$this->form_name][$name] = $default_value;
        $this->smarty->assign($this->smarty_variable, $forms);
        $this->fields[$name] = array(
            "name" => $name,
            "default_value" => $default_value,
            "data_type" => $data_type,
            "value" => null,
            "not_valid_message" => $not_valid_message,
            "additional_validate" => $additional_validate,
            "extra_params" => $extra_params
        );
        if ($data_type == self::TYPE_FILE) {
            $this->files_count++;
        }
    }

    private function validate($field)
    {
        // extra params
        if (isset($field["extra_params"]) && is_array($field["extra_params"]) && !empty($field["extra_params"])) {
            if (isset($field["extra_params"][self::EXTRA_NO_VALIDATE])) {
                return true;
            }
            if (isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL]) && (trim($field["value"]) == "" || empty($field["value"]))) {
                return true;
            }
            if (isset($field["extra_params"][self::EXTRA_FILE_MAX_SIZE_BYTES]) && $field["data_type"] == self::TYPE_FILE) {
                if (isset($_FILES[$field["name"]]) && !isset($field["file"])) {
                    $size = filesize($_FILES[$field["name"]]["tmp_name"]);
                    if ($size > $field["extra_params"][self::EXTRA_FILE_MAX_SIZE_BYTES]) {
                        return false;
                    }
                } elseif (isset($field["file"])) { // multifile
                    $size = filesize($field["file"]["tmp_name"]);
                    if ($size > $field["extra_params"][self::EXTRA_FILE_MAX_SIZE_BYTES]) {
                        return false;
                    }
                }
            }
            if (isset($field["extra_params"][self::EXTRA_STRING_STRIP_HTML]) && $field["data_type"] == self::TYPE_STR) {
                $field["value"] = strip_tags($field["value"]);
            }
            if (isset($field["extra_params"][self::EXTRA_STRING_TO_HTML_ENTITIES]) && $field["data_type"] == self::TYPE_STR) {
                $field["value"] = htmlentities($field["value"], null, "UTF-8");
            }
        }

        // Additional validating
        if (isset($field["additional_validate"]) && is_array($field["additional_validate"]) && !empty($field["additional_validate"])) {
            if (isset($field["additional_validate"][self::VALIDATE_MIN_LENGTH])) {
                if (strlen($field["value"]) < $field["additional_validate"][self::VALIDATE_MIN_LENGTH]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_NUMBER_MIN_VALUE])) {
                if (intval($field["value"]) < $field["additional_validate"][self::VALIDATE_NUMBER_MIN_VALUE]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_MAX_LENGTH])) {
                if (strlen($field["value"]) > $field["additional_validate"][self::VALIDATE_MAX_LENGTH]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_NUMBER_MAX_VALUE])) {
                if (intval($field["value"]) > $field["additional_validate"][self::VALIDATE_NUMBER_MAX_VALUE]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_COMPARE_WITH_FIELD])) {
                if (!isset($this->fields[$field["additional_validate"][self::VALIDATE_COMPARE_WITH_FIELD]]) || !isset($this->data[$field["additional_validate"][self::VALIDATE_COMPARE_WITH_FIELD]])) {
                    return false;
                }
                if ($field["value"] != $this->data[$field["additional_validate"][self::VALIDATE_COMPARE_WITH_FIELD]]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_COMPARE_WITH_STRING])) {
                if ($field["value"] != $field["additional_validate"][self::VALIDATE_COMPARE_WITH_STRING]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_COMPARE_WITH_SHA1_STRING])) {
                if (sha1($field["value"]) != $field["additional_validate"][self::VALIDATE_COMPARE_WITH_SHA1_STRING]) {
                    return false;
                }
            }
            if (isset($field["additional_validate"][self::VALIDATE_CHECKBOX_MUST_CHECK]) && $field["data_type"] == self::TYPE_CHECKBOX) {
                if (!isset($this->data[$field["name"]])) return false;
            }
        }
        if ($field["data_type"] == self::TYPE_FILE) {

            $file = isset($field["file"]) && !empty($field["file"])
                ? $field["file"]
                : null;
            if (is_null($file)) {
                if (isset($_FILES[$field["name"]])) {
                    $file = $_FILES[$field["name"]];
                }
            }
            if ($file["error"] == 0) {
                $name = isset($field["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO])
                    ? $field["extra_params"][self::EXTRA_FILE_UPLOAD_RENAME_TO] . "." . @end(explode(".", $file["name"]))
                    : $file["name"];
                if (isset($field["extra_params"][self::EXTRA_FILE_VALIDATE_TYPE])) {
                    $types = $field["extra_params"][self::EXTRA_FILE_VALIDATE_TYPE];
                    if (!is_array($types)) {
                        if ($file["type"] != $types) return false;
                    } else {
                        if (!in_array($file["type"], $types)) return false;
                    }
                }
                if (move_uploaded_file($file["tmp_name"], rtrim($field["extra_params"][self::EXTRA_FILE_UPLOAD_TARGET_DIR], DS) . DS . $name)) {
                    $this->fields[$field["name"]]["value"] = rtrim($field["extra_params"][self::EXTRA_FILE_UPLOAD_TARGET_DIR], DS) . DS . $name;
                    $this->fields[$field["name"]]["file_name"] = $name;
                    return true;
                } else {
                    if (isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
                    return false;
                }
            } else {
                if (isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
                return false;
            }
        } elseif ($field["data_type"] == self::TYPE_INT) {
            if (trim($field["value"]) == "" && isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
            return intval($field["value"]) . "" == $field["value"];
        } else if ($field["data_type"] == self::TYPE_STR) {
            if (trim($field["value"]) == "" && isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
            return is_string($field["value"]);
        } else if ($field["data_type"] == self::TYPE_MAIL) {
            if (trim($field["value"]) == "" && isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
            return $this->service->get_validator()->is_email($field["value"]);
        } else if ($field["data_type"] == self::TYPE_DATE) {
            if (trim($field["value"]) == "" && isset($field["extra_params"][self::EXTRA_NOT_OPTIONAL])) return true;
            return $this->service->get_validator()->is_date_format($field["value"], $field["extra_params"][self::VALIDATE_DATE_FORMAT]);
        } else if ($field["data_type"] == self::TYPE_CHECKBOX) {
            if (!isset($this->data[$field["name"]])) {
                if (isset($field["extra_params"][self::EXTRA_CHECKBOX_NOT_CHECKED_VAL])) {
                    $this->fields[$field["name"]]["value"] = $field["extra_params"][self::EXTRA_CHECKBOX_NOT_CHECKED_VAL];
                } else {
                    $this->fields[$field["name"]]["value"] = false;
                }
            } else {
                $this->fields[$field["name"]]["value"] = $this->data[$field["name"]];
            }
            return true;
        }
        return false;
    }

    private function sanitize_posted_data()
    {
        foreach ($this->multifiles as $key) {
            $nkey = "!!MULTI!!" . $key . "--[";
            $sanite = array();
            foreach ($this->posted_data as $k => $v) {
                if (substr($k, 0, strlen($nkey)) == $nkey) {
                    $sanite[substr($k, strlen($nkey), -3)] = $v;
                    unset($this->posted_data[$k]);
                }
            }
            $this->posted_data[$key] = $sanite;
        }
    }

}